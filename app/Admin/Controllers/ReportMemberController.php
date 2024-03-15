<?php

namespace App\Admin\Controllers;

use App\Services\RoleBasedMemberService;
use App\Admin\Repositories\MemberWork;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Jenssegers\Agent\Agent;
use Dcat\Admin\Grid\Displayers\Checkbox;

class ReportMemberController extends AdminController
{
    public function __construct()
    {
        $this->middleware('check_member_permission:true')->only(['edit', 'view', 'destroy']);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
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

            $agent_data = Admin::user()->id;
            if(Admin::user()->isRole('company') || Admin::user()->isAdministrator()){
                $agent_data = request()->input('agent');
            }

            $grid->model()->select('member_work.*')
            ->join('admin_users', 'member_work.admin_user_id', '=', 'admin_users.id')
            ->where('admin_users.parent_id', $agent_data)
            ->whereBetween('work_date', [$start_date, $end_date])
            ->groupBy('member_work.admin_user_id');

            //另外呼叫memberwork repo
            $memberWorkRepo     = new MemberWork();

            
            
            //根據使用者抓取 使用者下層的會員列表
            $memberService = new RoleBasedMemberService();
            $memberIds = $memberService->filterMembersByRole();
            $grid->model()->whereIn('member_work.admin_user_id', $memberIds);

            //會員帳號
            $grid->column('admin_user_name', admin_trans_field('admin_user_name'))->display(function () {
                $memberUsername  = Administrator::where('id', $this->admin_user_id)->value('username');
                return $memberUsername ;
            });

            //顯示會員的上層代理
            $grid->column('agent_name', admin_trans_label('agent_name'))->display(function () {
                $parentId = Administrator::where('id', $this->admin_user_id)->value('parent_id');
                $parentName = Administrator::where('id', $parentId)->value('username');
                return $parentName;
            });  
            
            //會員名稱
            $grid->column('admin_user_id', admin_trans_label('member_name'))->display(function () {
                $memberName = Administrator::where('id', $this->admin_user_id)->value('name');
                return $memberName;
            });

            $grid->column('total_count', '筆數')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                $total_count = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->count('id');
                return $total_count;
            });

            $grid->column('total_hours')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                $total_hours = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('total_hours');
                return $total_hours;
            });

            $grid->column('hourly_rate')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                $hourly_rate = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('hourly_rate');
                return $hourly_rate;
            });

            $grid->column('record_salary')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                $record_salary = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('record_salary');
                return $record_salary;
            });

            $grid->column('sale_price')->display(function () use ($memberWorkRepo, $start_date, $end_date) {
                $sale_price = $memberWorkRepo->model()::where('admin_user_id',$this->admin_user_id)->whereBetween('work_date', [$start_date, $end_date])->sum('sale_price');
                return $sale_price;
            });

            if (Admin::user()->isRole('company')) {
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
            }

            $grid->disableCreateButton(); //新增
            $grid->disableActions();
            $grid->disableRowSelector();//勾選框

        });
    }
}
