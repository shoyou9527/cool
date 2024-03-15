<style>
    .filter-box {
        border-top: 1px solid #eee;
        margin-top: 10px;
        margin-bottom: -.5rem!important;
        padding: 1.8rem;
    }
</style>

<link rel="stylesheet" href="https://cool.troublesboy.com/vendor/dcat-admin/dcat/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v2.2.2-beta">

<div class="filter-box shadow-0 card mb-0">
    <div class="card-body" style="padding:0">
        <form action="{{ Request::url() }}" class="form-horizontal grid-filter-form" pjax-container="" method="get">
            <div class="row mb-0">
                <div class="filter-input col-sm-4">
                    <div class="form-group">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text text-capitalize bg-white"><b>日期</b></span>
                            </div>

                            @php
                                $monday = strtotime('last monday', strtotime('tomorrow'));
                                $sunday = strtotime('+6 days', $monday);

                                $start_date = !empty(Request::get('start_date')) ? Request::get('start_date') : date('Y-m-d', $monday);
                                $end_date   = !empty(Request::get('end_date')) ? Request::get('end_date') : date('Y-m-d', $sunday);
                            @endphp

                            <input type="hidden" class="form-control" id="agent" name="agent" value="{{ request('agent') }}" />
                            <input type="text" class="form-control" id="start_date" name="start_date" value="{{ $start_date }}" />
                            <span class="input-group-addon" style="border-left: 0; border-right: 0;">To</span>
                            <input type="text" class="form-control" id="end_date" name="end_date" value="{{ $end_date }}" />
                        </div>
                    </div>
                </div>
                                    
                <button class="btn btn-primary btn-sm btn-mini submit" style="margin-left: 12px" type="submit">
                    <i class="feather icon-search"></i><span class="d-none d-sm-inline">&nbsp;&nbsp;搜索</span>
                </button>

                <a style="margin-left: 6px" href="{{ Request::url() }}?agent={{ request('agent') }}" class="reset btn btn-white btn-sm ">
                    <i class="feather icon-rotate-ccw"></i><span class="d-none d-sm-inline">&nbsp;&nbsp;重置</span>
                </a>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="/vendor/dcat-admin/dcat/plugins/moment/moment-with-locales.min.js?v2.2.2-beta"></script>
<script type="text/javascript" src="/vendor/dcat-admin/dcat/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v2.2.2-beta"></script>

<script>
Dcat.ready(function () {
    $('#start_date, #end_date').datetimepicker({
        format:"YYYY-MM-DD",
        locale:"zh_TW"
    });
});
</script>