<style>
    .card-text{
        font-size:16px;
        margin-bottom:0;
    }
</style>

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
                            <h5 class="card-title mb-1">{!! $row->column('admin_user_name') !!}</h5>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('work_date')->getLabel() !!}：</b>{!! $row->column('work_date') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('total_hours')->getLabel() !!}：</b>{!! $row->column('total_hours') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('hourly_rate')->getLabel() !!}：</b>{!! $row->column('hourly_rate') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('record_salary')->getLabel() !!}：</b>{!! $row->column('record_salary') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('sale_price')->getLabel() !!}：</b>{!! $row->column('sale_price') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('note')->getLabel() !!}：</b>{!! $row->column('note') !!}
                            </p>

                            <span class="d-flex justify-content-between mt-1">
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
