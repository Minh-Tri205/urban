<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const COMPLETED = 'Completed';

    public function index(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 7);

        return view('admin.dashboard.index', [
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
            'totalRevenue' => (float) Order::where('Status', self::COMPLETED)->whereBetween('OrderDate', [$start, $end])->sum('TotalAmount'),
            'totalOrders' => Order::whereBetween('OrderDate', [$start, $end])->count(),
            'totalCustomers' => Customer::where('RoleID', Role::CUSTOMER)->count(),
            'totalProducts' => Product::count(),
            'pendingOrders' => Order::where('Status', OrderStatus::Pending->value)->count(),
        ]);
    }

    public function donHangReport(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        return view('admin.dashboard.don-hang-report', [
            'orders' => Order::with('customer:CustomerID,UserName')
                ->whereBetween('OrderDate', [$start, $end])
                ->orderByDesc('OrderDate')
                ->get(),
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
        ]);
    }

    public function customersReport(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $customers = Customer::where('RoleID', Role::CUSTOMER)
            ->whereBetween('CreatedAt', [$start, $end])
            ->withCount([
                'orders as all_orders',
                'orders as completed_orders' => fn ($q) => $q->where('Status', self::COMPLETED),
            ])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('Status', self::COMPLETED)], 'TotalAmount')
            ->orderByDesc('CreatedAt')
            ->get();

        return view('admin.dashboard.customers-report', [
            'customers' => $customers,
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
        ]);
    }

    public function revenueReport(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        return view('admin.dashboard.revenue-report', [
            'orders' => Order::with('customer:CustomerID,UserName')
                ->where('Status', self::COMPLETED)
                ->whereBetween('OrderDate', [$start, $end])
                ->orderByDesc('OrderDate')
                ->get(),
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
        ]);
    }

    public function sanPhamReport(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        return view('admin.dashboard.san-pham-report', [
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
        ]);
    }

    // ================= API cho biểu đồ và bảng (JSON, giữ tên thuộc tính như bản gốc) =================

    /** Top 5 sản phẩm bán chạy kèm % trên tổng số lượng bán ra (toàn thời gian). */
    public function getTopProductsReport()
    {
        $totalQty = (int) $this->completedDetails()->sum('od.Quantity') ?: 1;

        $data = $this->completedDetails()
            ->join('ProductVariants as pv', 'pv.VariantID', '=', 'od.VariantID')
            ->join('Products as p', 'p.ProductID', '=', 'pv.ProductID')
            ->groupBy('p.ProductName')
            ->orderByDesc('Qty')
            ->limit(5)
            ->get(['p.ProductName as Name', DB::raw('SUM(od.Quantity) as Qty')])
            ->map(fn ($row) => [
                'Name' => $row->Name,
                'Qty' => (int) $row->Qty,
                'Percentage' => intdiv((int) $row->Qty * 100, $totalQty),
            ]);

        return response()->json($data);
    }

    /** Biểu đồ đường: tổng số lượng bán ra theo ngày. */
    public function getTotalQtyTrend(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $data = $this->completedDetails($start, $end)
            ->groupBy(DB::raw('DATE(o.OrderDate)'))
            ->orderBy('Day')
            ->get([DB::raw('DATE(o.OrderDate) as Day'), DB::raw('SUM(od.Quantity) as Qty')])
            ->map(fn ($row) => ['Date' => CarbonImmutable::parse($row->Day)->format('d/m'), 'Qty' => (int) $row->Qty]);

        return response()->json($data);
    }

    /** Top 10 khách hàng chi tiêu nhiều nhất (đơn hoàn thành). */
    public function getTop10SpendingCustomers()
    {
        $data = DB::table('Orders as o')
            ->join('Customers as c', 'c.CustomerID', '=', 'o.CustomerID')
            ->where('o.Status', self::COMPLETED)
            ->groupBy('o.CustomerID', 'c.UserName')
            ->orderByDesc('TotalSpent')
            ->limit(10)
            ->get(['c.UserName as Name', DB::raw('SUM(o.TotalAmount) as TotalSpent')])
            ->map(fn ($row) => ['Name' => $row->Name, 'TotalSpent' => (float) $row->TotalSpent]);

        return response()->json($data);
    }

    /** Tỷ lệ khách quay lại: phân nhóm khách hàng theo số đơn và số đơn hoàn thành. */
    public function getCustomerRetention()
    {
        $stats = Customer::where('RoleID', Role::CUSTOMER)
            ->withCount([
                'orders as all_orders',
                'orders as completed_orders' => fn ($q) => $q->where('Status', self::COMPLETED),
            ])
            ->get(['CustomerID']);

        $loyal = $stats->filter(fn ($c) => $c->completed_orders >= 2)->count();
        $potential = $stats->filter(fn ($c) => $c->all_orders >= 2 && $c->completed_orders === 1)->count();
        $never = $stats->filter(fn ($c) => $c->all_orders === 0)->count();

        return response()->json([
            ['label' => 'Trung thành (>=2 đơn xong)', 'value' => $loyal, 'color' => '#1cc88a'],
            ['label' => 'Tiềm năng (Đặt >=2, xong 1)', 'value' => $potential, 'color' => '#f6c23e'],
            ['label' => 'Chưa mua gì', 'value' => $never, 'color' => '#e74a3b'],
            ['label' => 'Đã mua 1 lần', 'value' => $stats->count() - $loyal - $potential - $never, 'color' => '#858796'],
        ]);
    }

    public function getCustomerSpendingByDay(Request $request)
    {
        return $this->revenueByDay($request);
    }

    /** Biểu đồ cột chồng: số lượng bán theo ngày, mỗi sản phẩm một dataset. */
    public function getProductBreakdown(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $rows = $this->completedDetails($start, $end)
            ->join('ProductVariants as pv', 'pv.VariantID', '=', 'od.VariantID')
            ->join('Products as p', 'p.ProductID', '=', 'pv.ProductID')
            ->groupBy(DB::raw('DATE(o.OrderDate)'), 'p.ProductName')
            ->get([DB::raw('DATE(o.OrderDate) as Day'), 'p.ProductName as Name', DB::raw('SUM(od.Quantity) as Qty')]);

        $days = $rows->pluck('Day')->unique()->sort()->values();
        $datasets = $rows->groupBy('Name')->map(fn ($productRows, $name) => [
            'label' => $name,
            'data' => $days->map(fn ($day) => (int) ($productRows->firstWhere('Day', $day)?->Qty ?? 0))->all(),
        ])->values();

        return response()->json([
            'labels' => $days->map(fn ($day) => CarbonImmutable::parse($day)->format('d/m'))->all(),
            'datasets' => $datasets,
        ]);
    }

    /** Bảng tất cả sản phẩm: số lượng bán trong khoảng ngày, % và tồn kho hiện tại. */
    public function getAllProductsReport(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $sold = $this->completedDetails($start, $end)
            ->join('ProductVariants as pv', 'pv.VariantID', '=', 'od.VariantID')
            ->groupBy('pv.ProductID')
            ->get(['pv.ProductID', DB::raw('SUM(od.Quantity) as Qty')])
            ->pluck('Qty', 'ProductID');
        $totalQty = (int) $sold->sum();

        $data = Product::withSum('productVariants as stock', 'Stock')
            ->orderBy('ProductID')
            ->get(['ProductID', 'ProductName'])
            ->map(function (Product $product) use ($sold, $totalQty) {
                $qty = (int) ($sold[$product->ProductID] ?? 0);

                return [
                    'Name' => $product->ProductName,
                    'Qty' => $qty,
                    'Stock' => (int) $product->stock,
                    'Percentage' => $totalQty > 0 ? intdiv($qty * 100, $totalQty) : 0,
                ];
            })
            ->sortByDesc('Qty')
            ->values();

        return response()->json($data);
    }

    public function revenueByDay(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $data = Order::where('Status', self::COMPLETED)
            ->whereBetween('OrderDate', [$start, $end])
            ->groupBy(DB::raw('DATE(OrderDate)'))
            ->orderBy('Day')
            ->get([DB::raw('DATE(OrderDate) as Day'), DB::raw('SUM(TotalAmount) as Total')])
            ->map(fn ($row) => ['Date' => CarbonImmutable::parse($row->Day)->format('d/m/Y'), 'Total' => (float) $row->Total]);

        return response()->json($data);
    }

    public function customersByDay(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $data = Customer::whereBetween('CreatedAt', [$start, $end])
            ->groupBy(DB::raw('DATE(CreatedAt)'))
            ->orderBy('Day')
            ->get([DB::raw('DATE(CreatedAt) as Day'), DB::raw('COUNT(*) as Total')])
            ->map(fn ($row) => ['Date' => CarbonImmutable::parse($row->Day)->format('d/m/Y'), 'Total' => (int) $row->Total]);

        return response()->json($data);
    }

    /** Số đơn theo ngày, tách theo từng trạng thái. */
    public function ordersByDay(Request $request)
    {
        [$start, $end] = $this->dateRange($request, 30);

        $columns = [DB::raw('DATE(OrderDate) as Day')];
        foreach (OrderStatus::cases() as $status) {
            $columns[] = DB::raw("SUM(CASE WHEN Status = '{$status->value}' THEN 1 ELSE 0 END) as {$status->value}");
        }

        $data = Order::whereBetween('OrderDate', [$start, $end])
            ->groupBy(DB::raw('DATE(OrderDate)'))
            ->orderBy('Day')
            ->get($columns)
            ->map(function ($row) {
                $item = ['Date' => CarbonImmutable::parse($row->Day)->format('d/m/Y')];
                foreach (OrderStatus::cases() as $status) {
                    $item[$status->value] = (int) $row->{$status->value};
                }

                return $item;
            });

        return response()->json($data);
    }

    public function topProducts()
    {
        $data = $this->completedDetails()
            ->join('ProductVariants as pv', 'pv.VariantID', '=', 'od.VariantID')
            ->join('Products as p', 'p.ProductID', '=', 'pv.ProductID')
            ->groupBy('p.ProductName')
            ->orderByDesc('Qty')
            ->limit(5)
            ->get([
                'p.ProductName as Name',
                DB::raw('SUM(od.Quantity) as Qty'),
                DB::raw('SUM(od.Quantity * od.Price * (1 - COALESCE(od.DiscountPercent, 0) / 100)) as Revenue'),
            ])
            ->map(fn ($row) => ['Name' => $row->Name, 'Qty' => (int) $row->Qty, 'Revenue' => round((float) $row->Revenue, 2)]);

        return response()->json($data);
    }

    public function topCustomers()
    {
        $data = DB::table('Orders as o')
            ->join('Customers as c', 'c.CustomerID', '=', 'o.CustomerID')
            ->where('o.Status', self::COMPLETED)
            ->groupBy('c.UserName')
            ->orderByDesc('Total')
            ->limit(5)
            ->get(['c.UserName as Name', DB::raw('SUM(o.TotalAmount) as Total')])
            ->map(fn ($row) => ['Name' => $row->Name, 'Total' => (float) $row->Total]);

        return response()->json($data);
    }

    /** Chi tiết của các đơn đã hoàn thành, lọc theo ngày đặt nếu có. */
    private function completedDetails(?CarbonImmutable $start = null, ?CarbonImmutable $end = null): Builder
    {
        return DB::table('OrderDetails as od')
            ->join('Orders as o', 'o.OrderID', '=', 'od.OrderID')
            ->where('o.Status', self::COMPLETED)
            ->when($start && $end, fn ($q) => $q->whereBetween('o.OrderDate', [$start, $end]));
    }

    /**
     * Khoảng ngày từ query fromDate / toDate (yyyy-mm-dd), mặc định $defaultDays ngày gần nhất.
     * Ngày kết thúc lấy tới 23:59:59. Ngày không hợp lệ thì dùng mặc định, đảo lại nếu từ ngày > đến ngày.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dateRange(Request $request, int $defaultDays): array
    {
        $parse = function (mixed $value): ?CarbonImmutable {
            if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }

            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $date : null;
        };

        $today = CarbonImmutable::today();
        $start = $parse($request->input('fromDate')) ?? $today->subDays($defaultDays);
        $end = $parse($request->input('toDate')) ?? $today;

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }
}
