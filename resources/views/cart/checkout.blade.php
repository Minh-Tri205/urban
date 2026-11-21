@extends('layouts.app')

@section('title', 'Xác nhận thông tin đặt hàng')

@section('content')
    @php
        $city = old('CustomerProfile.City', $profile->City);
        $district = old('CustomerProfile.District', $profile->District);
    @endphp

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                <div class="profile-form mb-5">
                    <!-- Header -->
                    <div class="mb-4">
                        <h2 class="fs-3 mb-2">Xác nhận thông tin đặt hàng</h2>
                        <p class="text-muted">Cập nhật thông tin nhận hàng của bạn để hoàn thiện đơn hàng.</p>
                    </div>

                    <form action="{{ route('cart.checkout.post') }}" method="post" class="profile-form-content" id="profileForm">
                        @csrf
                        @include('partials.validation-summary', ['class' => 'alert alert-danger'])

                        <!-- Họ tên và Số điện thoại -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="FullName" class="form-label fs-6 text-dark fw-semibold">
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input class="form-control shadow-none px-3 py-2" id="FullName" name="CustomerProfile[FullName]"
                                       placeholder="Nhập họ và tên đầy đủ" required="required" type="text"
                                       value="{{ old('CustomerProfile.FullName', $profile->FullName) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="PhoneNumber" class="form-label fs-6 text-dark fw-semibold">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input class="form-control shadow-none px-3 py-2" id="PhoneNumber" name="CustomerProfile[PhoneNumber]"
                                       placeholder="0912345678" required="required" type="tel"
                                       value="{{ old('CustomerProfile.PhoneNumber', $profile->PhoneNumber) }}">
                            </div>
                        </div>

                        <!-- Tỉnh/Thành phố và Quận/Huyện -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label fs-6 text-dark fw-semibold">
                                    Tỉnh/Thành phố <span class="text-danger">*</span>
                                </label>
                                <select class="form-control shadow-none px-3 py-2" id="province" name="CustomerProfile[City]"
                                        required="required" data-selected="{{ $city }}">
                                    <option value="">-- Chọn tỉnh/thành phố --</option>
                                    @if ($city)
                                        <option value="{{ $city }}" selected>{{ $city }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="district" class="form-label fs-6 text-dark fw-semibold">
                                    Quận/Huyện <span class="text-danger">*</span>
                                </label>
                                <select class="form-control shadow-none px-3 py-2" id="district" name="CustomerProfile[District]"
                                        required="required" data-selected="{{ $district }}">
                                    <option value="">-- Chọn quận/huyện --</option>
                                    @if ($district)
                                        <option value="{{ $district }}" selected>{{ $district }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="mb-3">
                            <label for="Address" class="form-label fs-6 text-dark fw-semibold">
                                Địa chỉ
                            </label>
                            <input class="form-control shadow-none px-3 py-2" id="Address" name="CustomerProfile[Address]"
                                   placeholder="Số nhà, tên đường" type="text" maxlength="255"
                                   value="{{ old('CustomerProfile.Address', $profile->Address) }}">
                        </div>

                        <!-- Phương thức giao hàng -->
                        <div class="mb-4">
                            <label class="form-label fs-6 text-dark fw-semibold mb-3">
                                Phương thức giao hàng
                            </label>
                            <div class="shipping-methods">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="shippingStandard" name="ShippingMethod" type="radio" value="Standard" checked="checked">
                                    <label class="form-check-label" for="shippingStandard">
                                        <strong>Giao hàng tiêu chuẩn</strong> - Giao hàng trong 3-5 ngày làm việc
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="mb-4">
                            <label class="form-label fs-6 text-dark fw-semibold mb-3">
                                Phương thức thanh toán
                            </label>
                            <div class="payment-methods">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="paymentCOD" name="PaymentMethod" type="radio" value="COD" checked="checked">
                                    <label class="form-check-label" for="paymentCOD">
                                        <strong>COD</strong> - Thanh toán khi nhận hàng
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tổng đơn hàng -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Tổng đơn hàng</h5>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính ({{ $items->sum('Quantity') }} sản phẩm):</span>
                                    <span class="fw-bold">{{ vnd($summary['SubTotal']) }}</span>
                                </div>

                                @if ($summary['TotalDiscount'] > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Giảm giá ({{ $summary['DiscountPercent'] }}%):</span>
                                        <span class="text-success">-{{ vnd($summary['TotalDiscount']) }}</span>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="fw-bold" id="shippingFee">{{ vnd($summary['ShippingFee']) }}</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <span class="h5 mb-0">Tổng cộng:</span>
                                    <span class="h5 mb-0 text-danger fw-bold" id="grandTotal">
                                        {{ vnd($summary['GrandTotal']) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary btn-rounded-none  w-100 mb-1">Xác nhận đặt hàng</button>
                        <a href="{{ route('cart.index') }}" class="btn btn-dark btn-rounded-none  w-100">Trở lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const PROVINCE_API = 'https://provinces.open-api.vn/api';
        const SHIPPING = {
            warehouseCity: @json(\App\Services\ShippingService::WAREHOUSE_CITY),
            intracityFee: {{ \App\Services\ShippingService::INTRACITY_FEE }},
            intercityFee: {{ \App\Services\ShippingService::INTERCITY_FEE }},
            productTotal: {{ $summary['SubTotal'] - $summary['TotalDiscount'] }}
        };
        let provinces = [];

        // Cùng quy tắc với ShippingService: bỏ tiền tố "Tỉnh", "Thành phố", "TP." rồi so sánh với tỉnh của kho
        function normalizeCity(name) {
            name = (name || '').trim();
            const prefix = ['Tỉnh ', 'Thành phố ', 'TP. ', 'TP ', 'Tp. ', 'Tp '].find(p => name.toLowerCase().startsWith(p.toLowerCase()));
            return (prefix ? name.substring(prefix.length) : name).trim().toLowerCase();
        }

        function formatVnd(value) {
            return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + 'đ';
        }

        function updateShippingFee() {
            const city = $('#province').val();
            const fee = city && normalizeCity(city) === normalizeCity(SHIPPING.warehouseCity) ? SHIPPING.intracityFee : SHIPPING.intercityFee;
            $('#shippingFee').text(formatVnd(fee));
            $('#grandTotal').text(formatVnd(SHIPPING.productTotal + fee));
        }

        function fillSelect($select, placeholder, names, selected) {
            $select.empty().append($('<option>', { value: '', text: placeholder }));
            if (selected && !names.includes(selected)) {
                names = [selected].concat(names);
            }
            names.forEach(function (name) {
                $select.append($('<option>', { value: name, text: name, selected: name === selected }));
            });
        }

        function loadDistricts(selectedDistrict) {
            const $district = $('#district');
            const province = provinces.find(p => p.name === $('#province').val());

            if (!province) {
                fillSelect($district, '-- Chọn quận/huyện --', [], selectedDistrict);
                return;
            }

            $.get(PROVINCE_API + '/p/' + province.code + '?depth=2', function (res) {
                fillSelect($district, '-- Chọn quận/huyện --', res.districts.map(d => d.name), selectedDistrict);
            });
        }

        $(function () {
            const $province = $('#province');

            $.get(PROVINCE_API + '/?depth=1', function (res) {
                provinces = res;
                fillSelect($province, '-- Chọn tỉnh/thành phố --', res.map(p => p.name), $province.data('selected'));
                loadDistricts($('#district').data('selected'));
            });

            $province.on('change', function () {
                loadDistricts('');
                updateShippingFee();
            });
        });
    </script>
@endsection
