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
                            {!! $row->column('avatar') !!}
                            <p class="card-text mt-1">
                                <b>{!! $grid->columns()->get('id')->getLabel() !!}：</b>{!! $row->column('id') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('username')->getLabel() !!}：</b>{!! $row->column('username') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('name')->getLabel() !!}：</b>{!! $row->column('name') !!}
                            </p>
                            <p class="card-text">
                                <b>{!! $grid->columns()->get('roles')->getLabel() !!}：</b>{!! $row->column('roles') !!}
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
