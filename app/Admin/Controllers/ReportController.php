<?php

namespace App\Admin\Controllers;

use App\Services\RoleBasedMemberService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\UserController;
use Dcat\Admin\Models\Administrator;
use App\Admin\Repositories\MemberWork;
use App\Models\Report;
use Dcat\Admin\Admin;
use App\Admin\Filters\DateRangeFilter;
use Illuminate\Support\Facades\Request;

use Dcat\Admin\Models\Role;
use Dcat\Admin\Http\Auth\Permission;
use Dcat\Admin\Models\Administrator as AdministratorModel;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Widgets\Tree;
use Jenssegers\Agent\Agent;

class ReportController extends UserController
{
    public function __construct()
    {
        //$this->middleware('check_member_permission')->only(['edit', 'view', 'destroy']);
    }

    public function title()
    {
        return trans('admin.report');
    }

    protected function grid()
    {
        if(Admin::user()->isRole('agent')){
            return Grid::make(new MemberWork(), function (Grid $grid) {
                $grid->withBorder();
    
                // 根據User裝置顯示對應的view，電腦版為預設view，手機版為客製view
                $agent = new Agent();
                if ($agent->isMobile()) {
                    $grid->view('admin.grid.report_member_mobile');
                }
                else{
                    $grid->view('admin.grid.report_member');
                }
    
                // 指定自定义视图&篩選器
                $grid->header(view('admin.filters.date_range'));
                
                //日期預設一週
                $monday     = strtotime('last monday', strtotime('tomorrow'));
                $sunday     = strtotime('+6 days', $monday);
                $start_date = !empty(request()->input('start_date')) ? request()->input('start_date') : date('Y-m-d', $monday);
                $end_date   = !empty(request()->input('end_date')) ? request()->input('end_date') : date('Y-m-d', $sunday);
    
                $grid->model()->select('member_work.*')
                ->join('admin_users', 'member_work.admin_user_id', '=', 'admin_users.id')
                ->where('admin_users.parent_id', Admin::user()->id)
                ->whereBetween('work_date', [$start_date, $end_date])
                ->groupBy('member_work.admin_user_id');
    
                //另外呼叫memberwork repo
                $memberWorkRepo     = new MemberWork();
                
                //根據使用者抓取 使用者下層的會員列表
                $memberService = new RoleBasedMemberService();
                $memberIds = $memberService->filterMembersByRole();
                $grid->model()->whereIn('member_work.admin_user_id', $memberIds);
    
                //會員帳號
                $grid->column('admin_user_name', '會員帳號')->display(function () {
                    $memberUsername  = Administrator::where('id', $this->admin_user_id)->value('username');
                    return $memberUsername ;
                });

                //顯示會員的上層代理
                $grid->column('agent_name', '代理')->display(function () {
                    $parentId = Administrator::where('id', $this->admin_user_id)->value('parent_id');
                    $parentName = Administrator::where('id', $parentId)->value('username');
                    return $parentName;
                });
                
                //會員名稱
                $grid->column('admin_user_id', '會員名稱')->display(function () {
                    $memberName = Administrator::where('id', $this->admin_user_id)->value('name');
                    return $memberName;
                });
    
                $grid->column('total_count', '筆數')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                    $total_count = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->count('id');
                    return $total_count;
                });
    
                $grid->column('total_hours', '時數')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                    $total_hours = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('total_hours');
                    return $total_hours;
                });
    
                $grid->column('hourly_rate', '時薪')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                    $hourly_rate = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('hourly_rate');
                    return $hourly_rate;
                });
    
                $grid->column('record_salary', '薪水')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                    $record_salary = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('record_salary');
                    return $record_salary;
                });
    
                $grid->column('sale_price', '客收')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                    $sale_price = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('sale_price');
                    return $sale_price;
                });
    
                $return_button = '<center><a href="/admin/report" class="btn btn-primary mt-2">
                <i class="feather icon-arrow-left"></i><span class="d-sm-inline">&nbsp;&nbsp;返回</span>
                <span class="filter-count"></span></a></center>';

                // 返回按鈕
                $grid->header(function () use ($return_button) {
                    return $return_button;
                });
                $grid->footer(function () use ($return_button) {
                    return $return_button;
                });
    
                $grid->disableCreateButton(); //新增
                $grid->disableActions();
                $grid->disableRowSelector();//勾選框
    
            });
        }
        else{
            return Grid::make(Report::with(['roles']), function (Grid $grid) {
                if (Admin::user()->isAdministrator()) {
                    // 顯示所有代理
                    $grid->model()->whereIn('id', function ($query) {
                        $query->select('user_id')->from('admin_role_users')->where('role_id', 3);
                    });
    
                    // $grid->column('parent_id', '上層公司')->display(function ($parentId) {
                    //     $parent = Administrator::find($parentId);
                    //     return $parent ? $parent->name : '';
                    // });
    
                } elseif (Admin::user()->isRole('company')) {
                    // 只顯示屬於當前公司的代理
                    $grid->model()->where('parent_id', Admin::user()->id);
    
                } else {
                    // 其他角色，跳轉首頁
                    return redirect('/member_work');
                }
                
                $monday = strtotime('last monday', strtotime('tomorrow'));
                $sunday = strtotime('+6 days', $monday);
    
                $start_date = !empty(request()->input('start_date')) ? request()->input('start_date') : date('Y-m-d', $monday);
                $end_date   = !empty(request()->input('end_date')) ? request()->input('end_date') : date('Y-m-d', $sunday);
    
                // 指定自定义视图
                $grid->view('admin.grid.report');
                $grid->header(view('admin.filters.date_range'));
    
                $grid->column('name')->display(function ($value) use ($start_date,$end_date) {
                    return "<a href='/admin/report-member?agent={$this->id}&start_date={$start_date}&end_date={$end_date}'>{$value}</a>";
                });
    
                $grid->column('total_count', '筆數')->display(function () use ($start_date,$end_date) {
                    $member     = $this->member;
                    $member_id  = collect($member)->pluck('id')->implode(',');
        
                    $query = \App\Models\MemberWork::whereRaw("find_in_set(admin_user_id,'{$member_id}')")->whereBetween('work_date', [$start_date, $end_date]);
    
                    return $query->count('id');
                });
    
                $grid->column('total_salary', '薪資')->display(function () use ($start_date,$end_date) {
                    $member     = $this->member;
                    $member_id  = collect($member)->pluck('id')->implode(',');
        
                    $query = \App\Models\MemberWork::whereRaw("find_in_set(admin_user_id,'{$member_id}')")->whereBetween('work_date', [$start_date, $end_date]);
    
                    return $query->sum('record_salary');
                });
    
                $grid->column('sales_salary', '客收')->display(function () use ($start_date,$end_date) {
                    $member     = $this->member;
                    $member_id  = collect($member)->pluck('id')->implode(',');
        
                    $query = \App\Models\MemberWork::whereRaw("find_in_set(admin_user_id,'{$member_id}')")->whereBetween('work_date', [$start_date, $end_date]);
    
                    return $query->sum('sale_price');
                });
    
                //$grid->quickSearch(['id', 'name', 'username']);
    
                $grid->disableCreateButton(); //新增
                $grid->disableActions();
                $grid->disableRowSelector();//勾選框
            });
        }
    }
}
