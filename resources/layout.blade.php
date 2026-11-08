<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>Sandip Indian Restaurant - Hệ thống quản lý booking</title>
        <meta property="og:type" content="website" />
        <meta property="og:image" content="images/logo.jpg" />
        <meta name="robots" content="noindex" />
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300&display=swap" rel="stylesheet" />
        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="{{ asset('admin/bootstrap/css/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css') }}" />
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css?v=2.1') }}" />
        <!-- Ionicons -->
        <link rel="stylesheet" href="{{ asset('css/ionicons.min.css?v=2.1') }}" />
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('admin/dist/css/AdminLTE.css?v=2.9') }}" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="{{ asset('admin/dist/css/skins/_all-skins.min.css?v=2.2') }}" />
        <!-- iCheck -->
        <link rel="stylesheet" href="{{ asset('admin/plugins/iCheck/flat/blue.css?v=2.0') }}" />
        <link rel="stylesheet" href="{{ asset('admin/plugins/colorpicker/bootstrap-colorpicker.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('admin/dist/css/select2.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('admin/dist/css/sweetalert2.min.css') }}" />
        <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style type="text/css">
            fieldset.scheduler-border {
                border: 1px groove red !important;
                padding: 0 5px 5px 5px !important;
                margin: 0 0 5px 0 !important;
                -webkit-box-shadow: 0px 0px 0px 0px #000;
                box-shadow: 0px 0px 0px 0px #000;
            }

            legend.scheduler-border {
                font-size: 1.2em !important;
                font-weight: bold !important;
                text-align: left !important;
                width: auto;
                padding: 0 5px;
                border-bottom: none;
                margin-bottom: 0px;
            }
            .alert-success {
                color: #155724 !important;
                background-color: #d4edda !important;
                border-color: #c3e6cb !important;
                padding: 5px;
                font-size: 13px;
            }
        </style>
    </head>
    <body class="skin-blue sidebar-mini sidebar-collapse">
        <div class="wrapper">
            @include('partials.header') 
            @if(Auth::user()->id == 515)
                @include('partials.sidebar-515')           
            @else
                @include('partials.sidebar')           
            @endif
            @yield('content')
            <div style="display: none;" id="box_uploadimages">
                <div class="upload_wrapper block_auto">
                    <div class="note" style="text-align: center;">Nhấn <strong>Ctrl</strong> để chọn nhiều hình.</div>
                    <form id="upload_files_new" method="post" enctype="multipart/form-data" enctype="multipart/form-data" action="{{ route('ck-upload')}}">
                        <fieldset style="width: 100%; margin-bottom: 10px; height: 47px; padding: 5px;">
                            <legend><b>&nbsp;&nbsp;Chọn hình từ máy tính&nbsp;&nbsp;</b></legend>
                            <input style="border-radius: 2px;" type="file" id="myfile" name="myfile[]" multiple />
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <div class="clear"></div>
                            <div class="progress_upload" style="text-align: center; border: 1px solid; border-radius: 3px; position: relative; display: none;">
                                <div class="bar_upload" style="background-color: grey; border-radius: 1px; height: 13px; width: 0%;"></div>
                                <div class="percent_upload" style="color: #ffffff; left: 140px; position: absolute; top: 1px;">0%</div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            @include('customer.customer-notification-modal')
            <!-- /.content-wrapper -->
            <footer class="main-footer">
                <div class="pull-right hidden-xs"><b>Version</b> 2.3.9</div>
                <strong>Copyright &copy; 2022 <a href="mailto:contact@plantotravel.vn">contact@plantotravel.vn</a>.</strong> All rights reserved.
            </footer>

            <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
            <input type="hidden" id="route_update_order" value="{{ route('update-order') }}" />
            <input type="hidden" id="route_get_slug" value="{{ route('get-slug') }}" />
            <div class="control-sidebar-bg"></div>
        </div>
        <input type="hidden" id="app_url" value="{{ env('APP_URL') }}" />
        <input type="hidden" id="url_open_kc_finder" value="{{ asset('admin/dist/js/kcfinder/browse.php?type=images') }}" />
        <input type="hidden" id="route-change-value" value="{{ route('change-value') }}" />
        <input type="hidden" id="get-child-route" value="{{ route('get-child') }}" />
        <input type="hidden" id="upload_url" value="{{ config('plantotravel.upload_url') }}" />
        <!-- ./wrapper -->

        <!-- jQuery 2.2.3 -->
        <script src="{{ asset('admin/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="{{ asset('js/jquery-ui.js') }}"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
            $.widget.bridge("uibutton", $.ui.button);
        </script>
        <script type="text/javascript">
            var public_url = '{{ env('APP_URL') }}';
        </script>
        <!-- Bootstrap 3.3.6 -->
        <script src="{{ asset('admin/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('admin/dist/js/ajax-upload.js') }}"></script>
        <script src="{{ asset('admin/dist/js/form.js') }}"></script>
        <script src="{{ asset('admin/dist/js/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('admin/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('admin/dist/js/es6-promise.min.js') }}"></script>
        <script src="{{ asset('js/moment.min.js') }}"></script>

        <!-- Slimscroll -->
        <script src="{{ asset('admin/plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('admin/dist/js/app.min.js') }}"></script>
        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <script src="{{ asset('admin/dist/js/pages/dashboard.js?v=1.0.0') }}"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="{{ asset('admin/dist/js/demo.js') }}"></script>
        <script src="{{ asset('admin/dist/js/lazy.js') }}"></script>
        <script src="{{ asset('admin/dist/js/number.js') }}"></script>
        <script src="{{ asset('admin/dist/js/ckeditor/ckeditor.js') }}"></script>

        <style type="text/css">
            .form-group label {
                margin-top: 5px;
            }
            @media (max-width: 767px) {
                .main-header .navbar {
                    margin-top: -15px;
                }
                .skin-blue .main-header .navbar .sidebar-toggle {
                    font-size: 22px !important;
                    padding-top: 3px;
                    margin-top: 5px;
                }
                #setting_top_2 {
                    font-size: 22px;
                }
                .mgb15 {
                    margin-bottom: 15px;
                }
            }
        </style>
        <script type="text/javascript" type="text/javascript">
            $(document).on("click", "#btnSaveNoti", function () {
                var content = CKEDITOR.instances["contentNoti"].getData();
                if (content != "") {
                    $.ajax({
                        url: $("#formNoti").attr("action"),
                        type: "POST",
                        data: {
                            data: $("#formNoti").serialize(),
                            content: content,
                        },
                        success: function (data) {
                            alert("Gửi tin nhắn thành công.");
                            $("#notifiModal").modal("hide");
                        },
                    });
                }
            });

            $(document).ready(function () {
                // $.ajax({
                //     url: "{{ route('booking.not-export') }}",
                //     type: "GET",
                //     success: function (data) {
                //         $("#content_alert").append(data);
                //     },
                // });
                @if(Auth::user()->id == 60 || Auth::user()->id == 245)
                 $.ajax({
                    url: "{{ route('payment-request.urgent') }}",
                    type: "GET",
                    success: function (data) {
                        $("#content_alert").append(data);
                    },
                });
                @endif
                $("input.number").number(true, 0);
                $("img.lazy").lazyload();
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                });

                $(".sendNoti").click(function () {
                    var customer_id = $(this).data("customer-id");
                    var order_id = $(this).data("order-id");
                    var notiType = $(this).data("type");
                    $("#customer_id_noti").val(customer_id);
                    $("#order_id_noti").val(order_id);
                    $("#notifiModal").modal("show");
                    $("#notifiModal  #type").val(notiType);
                    processNotiType(notiType);
                });
                $("#notifiModal  #type").change(function () {
                    processNotiType($(this).val());
                });
                CKEDITOR.editorConfig = function (config) {
                    config.toolbarGroups = [
                        { name: "clipboard", groups: ["clipboard", "undo"] },
                        { name: "editing", groups: ["find", "selection", "spellchecker", "editing"] },
                    ];

                    config.removeButtons = "Underline,Subscript,Superscript";
                };
                if ($("#contentNoti").length == 1) {
                    var editor2 = CKEDITOR.replace("contentNoti", {
                        language: "vi",
                        height: 100,
                        toolbarGroups: [{ name: "basicstyles", groups: ["basicstyles", "cleanup"] }, { name: "links", groups: ["links"] }, "/"],
                    });
                }
            });

            function processNotiType(type) {
                if (type == 1) {
                    $("#notifiModal #url-km").show();
                } else {
                    $("#notifiModal #url-km").hide();
                }
            }
        </script>
        <style type="text/css">
            .pagination > .active > a,
            .pagination > .active > a:focus,
            .pagination > .active > a:hover,
            .pagination > .active > span,
            .pagination > .active > span:focus,
            .pagination > .active > span:hover {
                z-index: 1 !important;
            }
            @if (\Request:: route()->getName() == "compare.index") .content-wrapper, .main-footer {
                margin-left: 0px !important;
            }
            @endif;
        </style>

        @yield('js')
        <script type="text/javascript">
            $(document).ready(function () {
                $(".datepicker").datepicker({
                    dateFormat: "dd/mm/yy",
                });
                // $(".timepicker").datetimepicker({
                //   //  dateFormat: "H:i",
                // });
                $("#btnQuickSearch").click(function () {
                    if ($.trim($("#keyword").val()) != "") {
                        location.href = "{{ route('booking.fast-search')}}?keyword=" + $("#keyword").val();
                    }
                });
                $("#keyword").on("keydown", function (e) {
                    if (e.which == 13) {
                        if ($.trim($("#keyword").val()) != "") {
                            location.href = "{{ route('booking.fast-search')}}?keyword=" + $("#keyword").val();
                        }
                    }
                });
            });
        </script>
    </body>
</html>
