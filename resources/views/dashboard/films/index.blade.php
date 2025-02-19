@extends('layouts.dashboard.app')

@section('content')

    @push('styles')
        <link rel="stylesheet" href="{{asset('dashboard_files/assets/plugins/sweetalert/sweetalert.css')}}"/>
        <link href="{{asset('dashboard_files/assets/plugins/bootstrap-select/css/bootstrap-select.css')}}"
              rel="stylesheet"/>
    @endpush

    <section class="content">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-7 col-md-5 col-sm-12">
                    <h2>Tất cả phim
                        {{-- <small class="text-muted">Welcome to Films</small> --}}
                    </h2>
                </div>
                <div class="col-lg-5 col-md-7 col-sm-12">
                    @if(auth()->guard('admin')->user()->hasPermission('create_films'))
                        <a href="{{route('dashboard.films.create')}}">
                            <button class="btn btn-primary btn-icon btn-round d-none d-md-inline-block float-right m-l-10"
                                    type="button">
                                <i class="zmdi zmdi-plus"></i>
                            </button>
                        </a>
                    @else
                        <button class="btn btn-primary btn-icon btn-round d-none d-md-inline-block float-right m-l-10 disabled"
                                style="cursor: no-drop"
                                type="button">
                            <i class="zmdi zmdi-plus"></i>
                        </button>
                    @endif
                    <ul class="breadcrumb float-md-right">
                        <li class="breadcrumb-item"><a href="{{url('dashboard')}}"><i class="zmdi zmdi-home"></i> Films</a>
                        </li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Films</a></li>
                        <li class="breadcrumb-item active">All Films</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-md-12">
                    <div class="card patients-list">
                        <div class="header">
                            <h2><strong>Phim </strong><span>({{$films->total()}})</span></h2>
                        </div>
                        <div class="body">

                            <form action="{{ route('dashboard.films.index') }}" method="GET">
                                <div class="row clearfix">
                                    <div class="col-5">
                                        <div class="form-group">
                                            <input type="text" name="searchKey" class="form-control"
                                                   placeholder="Tìm kiếm..." value="{{ request()->searchKey }}">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <select name="searchKeyCategory" class="form-control z-index show-tick" data-live-search="true">
                                            <option value="">- Tất cả danh mục -</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}" {{request()->searchKeyCategory == $category->id ? 'selected' : ''}}>{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <select name="searchKeyActor" class="form-control z-index show-tick" data-live-search="true">
                                            <option value="">- Tất cả diễn viên -</option>
                                            @foreach($actors as $actor)
                                                <option value="{{$actor->id}}" {{request()->searchKeyActor == $actor->id ? 'selected' : ''}}>{{$actor->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            </form>

                            <div class="tab-content m-t-10">
                                <div class="tab-pane table-responsive active">
                                    <table class="table m-b-0 table-hover">
                                        <thead>
                                        <tr>
                                            <th>Poster</th>
                                            <th>Tên</th>
                                            <th>Năm sản xuất</th>
                                            <th>Đánh giá</th>
                                            <th>Tỗng quan</th>
                                            <th>Danh mục</th>
                                            <th>Các mối quan hệ khác</th>
                                            <th>Hành động khác</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($films as $film)
                                            <tr>
                                                <td>
                                                    <span class="list-icon">
                                                        <img src="{{$film->poster}}"
                                                             alt=""
                                                             style="width: 50px; height: 50px;">
                                                    </span>
                                                </td>
                                                <td><span class="list-name">{{$film->name}}</span></td>
                                                <td>{{$film->year}}</td>
                                                <td>
                                                    <i class="zmdi zmdi-star"></i> {{round($film->ratings->avg('rating'), 2)}}
                                                    <a href="{{route('dashboard.ratings.index', ['searchKeyFilm' => $film->id])}}"><small style="font-size: 10px">({{$film->ratings->count()}} votes)</small></a>
                                                </td>
                                                <td>
                                                    <button title="show overview"
                                                            value="{{$film->overview}}"
                                                            class="btn btn-icon btn-neutral btn-icon-mini show_overview">
                                                        <i class="zmdi zmdi-reader"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    @foreach($film->categories as $category)
                                                        <span class="badge badge-info">{{$category->name}}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if(auth()->guard('admin')->user()->hasPermission('read_actors'))
                                                        <a href="{{ route('dashboard.actors.index', ['searchKeyFilm' => $film->id]) }}"
                                                           class="btn btn-info btn-sm">Diễn viên</a>
                                                    @else
                                                        <button class="btn btn-info btn-sm disabled" style="cursor: no-drop">Films</button>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(auth()->guard('admin')->user()->hasPermission('update_films'))
                                                        <a href="{{route('dashboard.films.edit', $film)}}">
                                                            <button class="btn btn-icon btn-neutral btn-icon-mini"
                                                                    title="Sửa">
                                                                <i class="zmdi zmdi-edit"></i>
                                                            </button>
                                                        </a>
                                                    @else
                                                        <button class="btn btn-icon btn-neutral btn-icon-mini disabled"
                                                                style="cursor: no-drop"
                                                                title="Sửa">
                                                            <i class="zmdi zmdi-edit"></i>
                                                        </button>
                                                    @endif

                                                    @if(auth()->guard('admin')->user()->hasPermission('delete_films'))
                                                        <form action="{{ route('dashboard.films.destroy', $film) }}"
                                                              method="POST" style="display: inline-block">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit"
                                                                    class="btn btn-icon btn-neutral btn-icon-mini remove_film"
                                                                    title="Xoá" value="{{$film->id}}">
                                                                <i class="zmdi zmdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-icon btn-neutral btn-icon-mini remove_admin disabled"
                                                                style="cursor: no-drop"
                                                                title="Xoá">
                                                            <i class="zmdi zmdi-delete"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="5">Không có dữ liệu...</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{$films->appends(request()->query())->links()}}
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="{{asset('dashboard_files/assets/plugins/sweetalert/sweetalert.min.js')}}"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $(".remove_film").click(function (e) {
                    var that = $(this);
                    e.preventDefault();

                    var id = $(this).val();
                    swal({
                        title: "Xác nhận?",
                        text: "Bạn sẽ không thể khôi phục lại!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Xoá",
                        cancelButtonText: "Huỷ bỏ",
                        closeOnConfirm: false
                    }, function () {
                        that.closest('form').submit();
                    });
                });

                $(".show_overview").click(function () {
                    var overview = $(this).val();
                    swal({
                        title: "<spna style='color: #8CD4F5'>Tỗng quan</span>",
                        text: "<textarea rows='15' class='form-control no-resize' style='background-color: white!important; cursor: auto!important;' readonly>" + overview + "</textarea>",
                        html: true
                    });
                });
            });
        </script>
    @endpush

@endsection