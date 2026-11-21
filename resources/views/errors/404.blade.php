{{-- Port từ Views/Shared/NotFound.cshtml (Laravel tự dùng view này cho lỗi 404) --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Lỗi</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="stylesheet" href="{{ asset('Content/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('Content/css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Upright:wght@300;400;500;600;700&family=Sofia+Sans:ital,wght@0,1..1000;1,1..1000&display=swap"
        rel="stylesheet">
</head>

<body class="bg-body">
<div class="error-page d-flex align-items-center justify-content-center"
     style="background-image: url('{{ asset('Content/img/error-image.jpg') }}'); width: auto; height: 100vh; background-repeat: no-repeat; background-size: cover; background-position: center;">
    <div class="container">
        <div class="col-lg-6 mx-auto">
            <div class="page-content bg-light p-5 text-center">
                <h1 class="display-1">Error 404</h1>
                <p>Rất tiếc, trang bạn đang tìm kiếm không tồn tại.</p>
                <a href="{{ url('/') }}" class="btn btn-outline-dark btn-medium text-capitalize mt-3">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
</div>
</body>

</html>
