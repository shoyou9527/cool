<style>
    .card-text{
        font-size:1rem;;
        margin: 4px 0;
    }
</style>

@if (Admin::user()->inRoles(['member']))
    <style>
        .content-header{
            display: none;
        }
    </style>
@endif

<div class="dcat-box custom-data-table dt-bootstrap4">

    @include('admin::grid.table-toolbar')

    {!! $grid->renderFilter() !!}

    {!! $grid->renderHeader() !!}

    <div class="table-responsive table-wrapper" style="margin-top:2rem!important;overflow-x:hidden">
        <div class="row" id="{{ $tableId }}">
            @foreach($grid->rows() as $row)
                <div class="col-12">
                    <div class="card" style="margin-bottom:0.5rem">
                        <div class="card-body">

                            @if (!Admin::user()->inRoles(['member']))
                            <div class="row">
                                <div class="col-6">
                                    <p class="card-text">
                                        {!! $row->column('admin_user_name') !!}
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p class="card-text">
                                        結帳: {!! $row->column('is_checkout') !!}
                                    </p>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-6">
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('work_date')->getLabel() !!}: </b>{!! $row->column('work_date') !!}
                                    </p>
                                    
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('work_start_time')->getLabel() !!}: </b>
                                        {{ date('H:i:s', strtotime($row->column('work_start_time'))) }}
                                    </p>
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('work_end_time')->getLabel() !!}: </b>
                                        {{ date('H:i:s', strtotime($row->column('work_end_time'))) }}
                                    </p>

                                    @if (!Admin::user()->inRoles(['member']))
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('sale_price')->getLabel() !!}: </b>{!! $row->column('sale_price') !!}
                                    </p>
                                    @endif
                                    
                                </div>
                                <div class="col-6">
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('hourly_rate')->getLabel() !!}: </b>{!! $row->column('hourly_rate') !!}
                                    </p>

                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('total_hours')->getLabel() !!}: </b>{!! $row->column('total_hours') !!}
                                    </p>

                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('record_salary')->getLabel() !!}: </b>{!! $row->column('record_salary') !!}
                                    </p>
                                </div>
                            </div>

                            @if (!Admin::user()->inRoles(['member']))
                            <div class="row">
                                <div class="col-12">
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('note')->getLabel() !!}: </b>{!! $row->column('note') !!}
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if (!Admin::user()->inRoles(['member']))
                            <span class="d-flex justify-content-between">
                                {!! $row->column(Dcat\Admin\Grid\Column::SELECT_COLUMN_NAME) !!}
                                <div>
                                    <!-- <button class="btn btn-warning btn-sm" onclick="checkout({{ $row->id }})">
                                        <i class="feather icon-dollar-sign"></i>
                                        廢棄結帳切換按鈕改原生切換開關
                                    </button> -->
                                    {!! $row->column(Dcat\Admin\Grid\Column::ACTION_COLUMN_NAME) !!}
                                </div>
                            </span>
                            @endif
                            
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <script>
        function checkout(id) {
            $.ajax({
                url: '/admin/member_work/checkout/' + id,
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'}, // 这个是为了防止 CSRF 攻击
                success: function(response) {
                    // 你可以在这里处理成功的响应，例如刷新页面
                    location.reload();
                },
                error: function(response) {
                    // 你可以在这里处理错误的响应
                    alert('Error');
                }
            });
        }
    </script>
    {!! $grid->renderFooter() !!}

    @include('admin::grid.table-pagination')

</div>
