<style>
    .card-text{
        font-size:1rem;;
        margin: 4px 0;
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

                            <div class="row">
                                <div class="col-12">
                                    <p class="card-text">
                                    <b>{!! $row->column('admin_user_name') !!} </b>
                                    </p>
                                </div>
                                
                                <div class="col-6">
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('admin_user_id')->getLabel() !!}: </b>{!! $row->column('admin_user_id') !!}
                                    </p>                           
                                    
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('total_hours')->getLabel() !!}: </b>{!! $row->column('total_hours') !!}
                                    </p>

                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('record_salary')->getLabel() !!}: </b>{!! $row->column('record_salary') !!}
                                    </p>

                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('sale_price')->getLabel() !!}: </b>{!! $row->column('sale_price') !!}
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('agent_name')->getLabel() !!}: </b>{!! $row->column('agent_name') !!}
                                    </p>
                                    
                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('total_count')->getLabel() !!}: </b>{!! $row->column('total_count') !!}
                                    </p>

                                    <p class="card-text">
                                        <b>{!! $grid->columns()->get('hourly_rate')->getLabel() !!}: </b>{!! $row->column('hourly_rate') !!}
                                    </p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $grid->renderFooter() !!}

    @include('admin::grid.table-pagination')

</div>
