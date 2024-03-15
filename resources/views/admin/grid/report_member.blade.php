<style>
    .TdStyle{
        border-top: 2px solid #bbbb9f!important;
        background:#fff8b4!important;
        color: #000!important;
    }
</style>

<div class="dcat-box">

    <div class="d-block pb-0">
        @include('admin::grid.table-toolbar')
    </div>

    {!! $grid->renderFilter() !!}

    {!! $grid->renderHeader() !!}

    <div class="{!! $grid->formatTableParentClass() !!}">
        <table class="{{ $grid->formatTableClass() }}" id="{{ $tableId }}" >
            <thead>
            @if ($headers = $grid->getVisibleComplexHeaders())
                <tr>
                    @foreach($headers as $header)
                        {!! $header->render() !!}
                    @endforeach
                </tr>
            @endif
            <tr>
                @foreach($grid->getVisibleColumns() as $column)
                    <th {!! $column->formatTitleAttributes() !!}>{!! $column->getLabel() !!}{!! $column->renderHeader() !!}</th>
                @endforeach
            </tr>
            </thead>

            @if ($grid->hasQuickCreate())
                {!! $grid->renderQuickCreate() !!}
            @endif

            <tbody>
            @foreach($grid->rows() as $row)
                <tr {!! $row->rowAttributes() !!}>
                    @foreach($grid->getVisibleColumnNames() as $name)
                        <td {!! $row->columnAttributes($name) !!}>{!! $row->column($name) !!}</td>
                    @endforeach
                </tr>
            @endforeach
            @if ($grid->rows()->isEmpty())
                <tr>
                    <td colspan="{!! count($grid->getVisibleColumnNames()) !!}">
                        <div style="margin:5px 0 0 10px;"><span class="help-block" style="margin-bottom:0"><i class="feather icon-alert-circle"></i>&nbsp;{{ trans('admin.no_data') }}</span></div>
                    </td>
                </tr>
            @endif

            <tr>
                <td colspan="3" class="TdStyle">總計：</td>
                <td class="TdStyle" id="TotalCount"></td>
                <td class="TdStyle" id="TotalHour"></td>
                <td class="TdStyle" id="TotalRate"></td>
                <td class="TdStyle" id="TotalSalary"></td>
                <td class="TdStyle" id="TotalSale"></td>
            <tr>
            </tbody>
        </table>
    </div>

    {!! $grid->renderFooter() !!}

    {!! $grid->renderPagination() !!}

</div>

<script>
    rowIdx = 1;
    var rowCount = $('.data-table tbody tr').length - 2;

    $('.data-table > tbody > tr').each(function(i,v){
        var tds = $(v).children('td');

        if(rowIdx <= rowCount){
            var td_count_val   = +(tds.eq(3).text());
            var CountText      = +($('#TotalCount').text());
            var CountTotal     = CountText + td_count_val;
            $('#TotalCount').text(CountTotal);

            var td_hour_val   = +(tds.eq(4).text());
            var HourText      = +($('#TotalHour').text());
            var HourTotal     = HourText + td_hour_val;
            $('#TotalHour').text(HourTotal);

            var td_rate_val   = +(tds.eq(5).text());
            var RateText      = +($('#TotalRate').text());
            var RateTotal     = RateText + td_rate_val;
            $('#TotalRate').text(RateTotal);

            var td_salary_val   = +(tds.eq(6).text());
            var SalaryText      = +($('#TotalSalary').text());
            var SalaryTotal     = SalaryText + td_salary_val;
            $('#TotalSalary').text(SalaryTotal);

            var td_sale_val = +(tds.eq(7).text());
            var SaleText    = +($('#TotalSale').text());
            var SaleTotal   = SaleText + td_sale_val;
            $('#TotalSale').text(SaleTotal);
        }

        ++rowIdx;
    });
</script>