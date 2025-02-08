@extends('layouts.web.app')
@section('content')
    @push('style')
        <style rel="stylesheet">
            li.active {
                color: yellow;
            }
        </style>
        <script src="https://use.fontawesome.com/78c5b92ede.js"></script>
    @endpush

    <div class="hero common-hero">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="hero-ct">
                        <h1>{{$user->first_name . ' ' . $user->last_name}}</h1>
                        <ul class="breadcumb">
                            <li class="active"><a href="#">{{ __('Trang chủ') }}</a></li>
                            <li><span class="ion-ios-arrow-right"></span>{{ __('Hồ sơ') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-single">
        <div class="container">
            <div class="row ipad-width2">
                <div class="col-md-3 col-sm-12 col-xs-12">
                    <div class="user-information">
                        <div style="margin: 0" class="user-img">
                            <a href="#"><img alt="" src="{{$user->avatar}}"
                                             style="width: 150px; height: 150px; border-radius: 50%"><br></a>
                        </div>
                        <div class="user-fav">
                            <p>{{ __('Chi tiết tài khoản') }}</p>
                            <ul>
                                <li ><a href="{{url('user/profile')}}">{{ __('Hồ sơ') }}</a></li>
                                <li><a href="{{url('user/favorites')}}">{{ __('Phim yêu thích') }}</a></li>
                                <li><a href="{{url('user/ratings')}}">{{ __('Phim đã đánh giá') }}</a></li>
                                <li><a href="{{url('user/reviews')}}">{{ __('Phim đã bình luận') }}</a></li>
                                <li><a href="{{url('user/upgrade-account')}}">{{ __('Nâng cấp thành viên') }}</a></li>
                                <li class="active"><a href="{{url('user/orders')}}">{{ __('Lịch sử giao dịch') }}</a></li>
                            </ul>
                        </div>
                        <div class="user-fav">
                            <p>{{ __('Cài đặt') }}</p>
                            <ul>
                                <li><a href="{{url('user/change_password/')}}">{{ __('Thay đổi mật khẩu') }}</a></li>
                                <li><a href="{{route('logout')}}">{{ __('Đăng xuất') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9 col-sm-12 col-xs-12">
                    <div class="form-style-1 user-pro">
                        <h4 style="color: white;">{{ __('Chi tiết giao dịch') }}</h4>
                        <div class="row">
                            <div class="col-md-6 form-it">
                                <label>{{ __('Phương thức thanh toán') }}</label>
                                <p>{{ ucfirst($order->payment_name) }}</p>
                            </div>
                            <div class="col-md-6 form-it">
                                <label>{{ __('Trạng thái') }}</label>
                                <div>
                                    @include('components.order-status', ['status' => $order->status])
                                </div>
                            </div>
                            <div class="col-md-6 form-it">
                                <label>{{ __('Số tiền') }}</label>
                                <p>{{ $order->amount }}</p>
                            </div>
                            <div class="col-md-6 form-it">
                                <label>{{ __('Đơn vị tiền tệ') }}</label>
                                <p>{{ $order->currency }}</p>
                            </div>
                            <div class="col-md-6 form-it">
                                <label>{{ __('Thời gian giao dịch') }}</label>
                                <p>{{ $order->created_at }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="d-flex">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary fs-5">{{ __('Quay lại') }}</a>
                                @if($isCanPayOrCancel)
                                <a href="{{ route('user.upgrade-account', ['orderId' => $order->id]) }}"  class="btn btn-success mx-2 fs-5">{{ __('Thanh toán') }}</a>
                                <form action="{{ route('user.cancel-order', ['orderId' => $order->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning fs-5">{{ __('Huỷ đơn hàng') }}</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="{{asset('dashboard_files/assets/plugins/bootstrap-notify/bootstrap-notify.js')}}"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                var allowDismiss = true;
                @if(session('delete_review'))
                $.notify({
                        message: "{{ session('delete_review') }}"
                    },
                    {
                        type: "alert-success",
                        allow_dismiss: allowDismiss,
                        newest_on_top: true,
                        timer: 1000,
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        animate: {
                            enter: "animated fadeIn",
                            exit: "animated fadeOut"
                        },
                        template: '<div data-notify="container" class="bootstrap-notify-container alert alert-dismissible {0} ' + (allowDismiss ? "p-r-35" : "") + '" role="alert">' +
                            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                            '<span data-notify="icon"></span> ' +
                            '<span data-notify="title">{1}</span> ' +
                            '<span data-notify="message">{2}</span>' +
                            '<div class="progress" data-notify="progressbar">' +
                            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                            '</div>' +
                            '<a href="{3}" target="{4}" data-notify="url"></a>' +
                            '</div>'
                    });
                @endif

            });
        </script>
    @endpush

@endsection