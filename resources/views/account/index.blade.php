@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
    @php
        $dateOfBirth = old('DateOfBirth', $profile->DateOfBirth?->format('Y-m-d'));
        $gender = old('Gender', $profile->Gender);
        $city = old('City', $profile->City);
        $district = old('District', $profile->District);
    @endphp

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                <div class="profile-form mb-5">
                    <!-- Header -->
                    <div class="mb-4">
                        <h2 class="fs-3 mb-2">Thông tin cá nhân</h2>
                        <p class="text-muted">Cập nhật thông tin cá nhân của bạn để hoàn thiện hồ sơ.</p>
                    </div>

                    <form action="{{ route('account.update') }}" method="post" class="profile-form-content" id="profileForm">
                        @csrf

                        <!-- Họ và tên -->
                        <div class="mb-3">
                            <label for="FullName" class="form-label fs-6 text-dark fw-semibold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input class="form-control shadow-none px-3 py-2 @error('FullName') is-invalid @enderror" id="FullName" name="FullName"
                                   placeholder="Nhập họ và tên đầy đủ" required="required" type="text"
                                   value="{{ old('FullName', $profile->FullName) }}">
                            @error('FullName')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <!-- Số điện thoại và Ngày sinh -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="PhoneNumber" class="form-label fs-6 text-dark fw-semibold">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input class="form-control shadow-none px-3 py-2 @error('PhoneNumber') is-invalid @enderror" id="PhoneNumber" name="PhoneNumber"
                                       placeholder="0912345678" required="required" type="tel"
                                       value="{{ old('PhoneNumber', $profile->PhoneNumber) }}">
                                @error('PhoneNumber')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="DateOfBirth" class="form-label fs-6 text-dark fw-semibold">
                                    Ngày sinh
                                </label>
                                <input class="form-control shadow-none px-3 py-2 @error('DateOfBirth') is-invalid @enderror" id="DateOfBirth" name="DateOfBirth"
                                       type="date" value="{{ $dateOfBirth }}" max="{{ now()->subDay()->format('Y-m-d') }}">
                                @error('DateOfBirth')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- Giới tính -->
                        <div class="mb-3">
                            <label class="form-label fs-6 text-dark fw-semibold d-block">Giới tính</label>
                            @foreach (['genderMale' => 'Nam', 'genderFemale' => 'Nữ', 'genderOther' => 'Khác'] as $inputId => $value)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" id="{{ $inputId }}" name="Gender" type="radio"
                                           value="{{ $value }}" @checked($gender === $value)>
                                    <label class="form-check-label" for="{{ $inputId }}">{{ $value }}</label>
                                </div>
                            @endforeach
                            @error('Gender')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                        </div>

                        <!-- Địa chỉ -->
                        <div class="mb-3">
                            <label for="Address" class="form-label fs-6 text-dark fw-semibold">
                                Địa chỉ
                            </label>
                            <input class="form-control shadow-none px-3 py-2 @error('Address') is-invalid @enderror" id="Address" name="Address"
                                   placeholder="Số nhà, tên đường" type="text"
                                   value="{{ old('Address', $profile->Address) }}">
                            @error('Address')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <!-- Tỉnh/Thành phố và Quận/Huyện -->
                        <div class="row mb-4">
                            <div class="mb-3">
                                <label for="province" class="form-label fs-6 text-dark fw-semibold">
                                    Tỉnh / Thành phố <span class="text-danger">*</span>
                                </label>
                                <select class="form-control shadow-none px-3 py-2 @error('City') is-invalid @enderror" id="province" name="City"
                                        required="required" data-selected="{{ $city }}">
                                    <option value="">-- Chọn tỉnh / thành phố --</option>
                                    @if ($city)
                                        <option value="{{ $city }}" selected>{{ $city }}</option>
                                    @endif
                                </select>
                                @error('City')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="district" class="form-label fs-6 text-dark fw-semibold">
                                    Quận / Huyện <span class="text-danger">*</span>
                                </label>
                                <select class="form-control shadow-none px-3 py-2 @error('District') is-invalid @enderror" id="district" name="District"
                                        required="required" data-selected="{{ $district }}">
                                    <option value="">-- Chọn quận / huyện --</option>
                                    @if ($district)
                                        <option value="{{ $district }}" selected>{{ $district }}</option>
                                    @endif
                                </select>
                                @error('District')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary btn-rounded-none  w-100 mb-1">Xác nhận</button>
                        <a href="{{ route('product.index') }}" class="btn btn-dark btn-rounded-none  w-100">Trở lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Danh sách tỉnh / quận lấy từ provinces.open-api.vn như bản gốc.
        // Giá trị đang lưu được chọn sẵn; nếu API lỗi thì vẫn giữ giá trị hiện có.
        const PROVINCE_API = 'https://provinces.open-api.vn/api';
        let provinces = [];

        $(function () {
            loadProvinces();
        });

        function fillSelect($select, placeholder, names, selected) {
            $select.empty().append($('<option>', { value: '', text: placeholder }));
            if (selected && !names.includes(selected)) {
                names = [selected].concat(names);
            }
            names.forEach(function (name) {
                $select.append($('<option>', { value: name, text: name, selected: name === selected }));
            });
        }

        function loadProvinces() {
            const $province = $('#province');

            $.get(PROVINCE_API + '/?depth=1', function (res) {
                provinces = res;
                fillSelect($province, '-- Chọn tỉnh --', res.map(p => p.name), $province.data('selected'));
                loadDistricts($('#district').data('selected'));
            });
        }

        function loadDistricts(selectedDistrict) {
            const provinceName = $('#province').val();
            const $district = $('#district');
            const province = provinces.find(p => p.name === provinceName);

            if (!province) {
                fillSelect($district, '-- Chọn quận --', [], selectedDistrict);
                return;
            }

            $.get(PROVINCE_API + '/p/' + province.code + '?depth=2', function (res) {
                fillSelect($district, '-- Chọn quận --', res.districts.map(d => d.name), selectedDistrict);
            });
        }

        $('#province').on('change', function () {
            loadDistricts('');
        });
    </script>
@endsection
