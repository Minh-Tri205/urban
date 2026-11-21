<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $categories = Category::with('categoryGroup')
            ->withCount('products')
            ->when($search !== '', fn ($q) => $q->where('CategoryName', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->orderBy('GroupID')
            ->orderBy('CategoryID')
            ->get();

        return view('admin.category.index', [
            'categories' => $categories,
            'categoryGroups' => CategoryGroup::orderBy('GroupID')->get(),
            'search' => $search,
        ]);
    }

    public function create(Request $request)
    {
        $data = $this->validated($request);

        if ($data === null) {
            return redirect()->route('admin.category.index');
        }

        Category::create($data);

        return redirect()->route('admin.category.index')->with('Success', 'Thêm danh mục thành công.');
    }

    public function update(Request $request)
    {
        $category = Category::find((int) $request->input('CategoryID'));

        if ($category === null) {
            return redirect()->route('admin.category.index')->with('Error', 'Cập nhật danh mục thất bại. Danh mục không tồn tại.');
        }

        $data = $this->validated($request, $category);

        if ($data === null) {
            return redirect()->route('admin.category.index');
        }

        $category->update($data);

        return redirect()->route('admin.category.index')->with('Success', 'Cập nhật danh mục thành công.');
    }

    public function delete(Request $request, ?string $id = null)
    {
        $category = Category::withCount('products')->find((int) ($id ?? $request->input('id')));

        if ($category === null) {
            return redirect()->route('admin.category.index')->with('Error', 'Xóa danh mục thất bại. Danh mục không tồn tại.');
        }

        if ($category->products_count > 0) {
            return redirect()->route('admin.category.index')->with('Error', 'Không thể xóa danh mục vì có sản phẩm liên kết.');
        }

        $category->delete();

        return redirect()->route('admin.category.index')->with('Success', 'Xóa danh mục thành công.');
    }

    /** Kiểm tra dữ liệu, lỗi đầu tiên được đưa vào toast như bản gốc. Trả về null nếu không hợp lệ. */
    private function validated(Request $request, ?Category $ignore = null): ?array
    {
        $request->merge(['CategoryName' => trim((string) $request->input('CategoryName'))]);

        $validator = validator($request->all(), [
            'CategoryName' => [
                'required', 'string', 'max:100',
                Rule::unique('Categories', 'CategoryName')
                    ->where('GroupID', (int) $request->input('GroupID'))
                    ->ignore($ignore?->CategoryID, 'CategoryID'),
            ],
            'GroupID' => ['required', 'integer', 'exists:CategoryGroups,GroupID'],
        ], [
            'CategoryName.required' => 'Tên danh mục không được để trống.',
            'CategoryName.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
            'CategoryName.unique' => 'Danh mục này đã tồn tại trong nhóm đã chọn.',
            'GroupID.*' => 'Nhóm danh mục không hợp lệ.',
        ]);

        if ($validator->fails()) {
            session()->flash('Error', $validator->errors()->first());

            return null;
        }

        return ['CategoryName' => $request->input('CategoryName'), 'GroupID' => (int) $request->input('GroupID')];
    }
}
