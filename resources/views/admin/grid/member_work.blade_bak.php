<style>
    .card-text{
        font-size:16px;
        margin-bottom:0;
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
                            
                            <div class="d-flex justify-content-between item card-text">
                                <span>{!! $row->column('admin_user_name') !!}</span>
                                {!! $row->column('work_date') !!}
                            </div>
                            <div class="d-flex justify-content-between item card-text">
                                <span>{!! $grid->columns()->get('work_start_time')->getLabel() !!}{!! $row->column('work_start_time') !!}</span>
                                {!! $grid->columns()->get('work_end_time')->getLabel() !!}{!! $row->column('work_end_time') !!}
                            </div>

                            <div class="d-flex justify-content-between item card-text">
                                <span>{!! $grid->columns()->get('total_hours')->getLabel() !!}：{!! $row->column('total_hours') !!}</span>
                                {!! $grid->columns()->get('hourly_rate')->getLabel() !!}：{!! $row->column('hourly_rate') !!}
                            </div>

                            <div class="d-flex justify-content-between item card-text">
                                <span>{!! $grid->columns()->get('record_salary')->getLabel() !!}：{!! $row->column('record_salary') !!}</span>
                                @if (!Admin::user()->inRoles(['member']))
                                    {!! $grid->columns()->get('sale_price')->getLabel() !!}：{!! $row->column('sale_price') !!}
                                @endif
                            </div>

                            @if (!empty($row->column('note')))
                                <p class="card-text">
                                    <b>{!! $grid->columns()->get('note')->getLabel() !!}：</b>{!! $row->column('note') !!}
                                </p>
                            @endif

                            <span class="d-flex justify-content-between">
                                {!! $row->column(Dcat\Admin\Grid\Column::SELECT_COLUMN_NAME) !!}
                                <div>{!! $row->column(Dcat\Admin\Grid\Column::ACTION_COLUMN_NAME) !!}</div>
                            </span>
                            
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {!! $grid->renderFooter() !!}

    @include('admin::grid.table-pagination')

</div>
