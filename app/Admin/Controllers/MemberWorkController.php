<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\MemberWork;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class MemberWorkController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
        return Grid::make(new MemberWork(), function (Grid $grid) {

            // 根據User裝置顯示對應的view，電腦版為預設view，手機版為客製view
            $agent = new Agent();
            if ($agent->isMobile()) {
                $grid->view('admin.grid.member_work');
            }
            
            // 根据管理员角色过滤会员列表
            $userRole = Admin::user()->roles->first()->slug;
            $memberWorkRepo = new MemberWork();
            $memberIds = collect();

            switch ($userRole) {
                case 'administrator':
                    // 管理员显示所有会员 不需要进一步筛选
                    break;

                case 'company':
                    // 公司角色显示该公司的代理和会员
                    $agents = Administrator::where('parent_id', Admin::user()->id)->pluck('id');
                    $memberIds = Administrator::whereIn('parent_id', $agents)->pluck('id');
                    break;

                case 'agent':
                    // 代理角色显示属于当前代理的会员
                    $memberIds = Administrator::where('parent_id', Admin::user()->id)->pluck('id');
                    break;

                case 'member':
                    // 会员只能看到自己 並顯示打卡按鈕
                    app('translator')->setLocale('en'); //設定會員登入為英文測試

                    // 获取会员的最后一笔上班打卡记录
                    $lastWorkRecord = $memberWorkRepo->model()::where('admin_user_id', Admin::user()->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!empty($lastWorkRecord->work_end_time)) {
                        // 有上班打卡记录且尚未打卡下班，显示下班打卡按钮
                        $grid->tools('<a href="/admin/api/work_start" class="btn btn-info disable-outline">' . admin_trans_label('start_work_button') . '</a>');
                        $grid->tools('<a href="#" class="btn btn-danger disable-outline">' . admin_trans_label('end_work_button') . '</a>');
                    } else {
                        // 没有上班打卡记录或已经打卡下班，显示上班打卡按钮
                        $grid->tools('<a href="#" class="btn btn-danger disable-outline">' . admin_trans_label('start_work_button') . '</a>');
                        $grid->tools('<a href="/admin/api/work_end" class="btn btn-info disable-outline">' . admin_trans_label('end_work_button') . '</a>');
                    }
                    
                    $memberIds->push(Admin::user()->id);
                    break;

                default:
                    // 其他角色，跳转首页
                    return redirect('/admin');
            }

            if (!$memberIds->isEmpty()) {
                $grid->model()->whereIn('admin_user_id', $memberIds);
            }

            // $grid->column('id');
            // $grid->column('admin_user_id');
            $grid->column('admin_user_name')->filter('like');
            $grid->column('work_date')->sortable();
            $grid->column('work_start_time');
            $grid->column('work_end_time');
            $grid->column('total_hours');
            $grid->column('hourly_rate');
            $grid->column('record_salary');
            $grid->column('sale_price');
            $grid->column('note');
            // $grid->column('created_at');
            // $grid->column('updated_at');
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->date('work_date')->width(3);
                $filter->where('global_search', function ($query) {
                    $query->where('admin_user_name', 'like', "%{$this->input}%")
                        ->orWhere('work_date', 'like', "%{$this->input}%")
                        ->orWhere('work_start_time', 'like', "%{$this->input}%")
                        ->orWhere('work_end_time', 'like', "%{$this->input}%")
                        ->orWhere('total_hours', 'like', "%{$this->input}%")
                        ->orWhere('hourly_rate', 'like', "%{$this->input}%")
                        ->orWhere('record_salary', 'like', "%{$this->input}%")
                        ->orWhere('note', 'like', "%{$this->input}%");
                }, admin_trans_label('global_search'))->width(4)->placeholder(admin_trans_label('search_content'));
            });

            // 總金額
            $grid->footer(function ($query) {
                $subtotal = $query->sum('record_salary');
                echo "<tr><td colspan='4'>" . admin_trans_label('sub_total') . "：</td><td>{$subtotal}</td></tr>";
            });

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new MemberWork(), function (Show $show) {

            if(Admin::user()->isRole('member'))
            {
                app('translator')->setLocale('en');
            }

            $show->field('id');
            $show->field('admin_user_id');
            $show->field('admin_user_name');
            $show->field('work_date');
            $show->field('work_start_time');
            $show->field('work_end_time');
            $show->field('total_hours');
            $show->field('hourly_rate');
            $show->field('record_salary');
            $show->field('sale_price');
            $show->field('note');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new MemberWork(), function (Form $form) {

            $role = Admin::user()->roles->first()->slug; //取得目前用戶的角色
            $users = collect();

            switch ($role) {
                case 'administrator':
                    // 總管理登入抓取所有會員
                    $users = Administrator::whereHas('roles', function ($query) {
                        $query->where('slug', 'member');
                    })->get();
                    break;

                case 'company':
                    // 公司登入時要抓取屬於當前公司的代理再抓取這些代理的會員
                    $agents = Administrator::where('parent_id', Admin::user()->id)->pluck('id');
                    $users = Administrator::whereHas('roles', function ($query) use ($agents) {
                        $query->where('slug', 'member')->whereIn('parent_id', $agents);
                    })->get();
                    break;

                case 'agent':
                    // 抓取此代理的會員
                    $users = Administrator::whereHas('roles', function ($query) {
                        $query->where('slug', 'member')->where('parent_id', Admin::user()->id);
                    })->get();
                    break;

                case 'member':
                    // 會員只能看到自己
                    app('translator')->setLocale('en'); //設定會員登入為英文測試
                    $users = Administrator::where('id', Admin::user()->id)->get();
                    break;

                default:
                    // 其他角色，跳轉首頁
                    return redirect('/admin');
            }

            //會員登入時不用下拉選單
            if ($role == 'member') {
                $form->hidden('admin_user_name')->default($users->first()->username);
                $form->hidden('admin_user_id')->default($users->first()->id);
            } else {
                $users = $users->pluck('username', 'username');
                $form->select('admin_user_name')->options($users);

                //按照帳號查詢ID並新增使用
                $form->hidden('admin_user_id');
                $form->saving(function (Form $form) {
                    $selectedUserName = $form->input('admin_user_name');
                    $selectedUserId = Administrator::where('username', $selectedUserName)->first()->id;
                    $form->input('admin_user_id', $selectedUserId);
                });
            }

            $form->display('id');
            $form->date('work_date')->default(date('Y-m-d'));
            $form->time('work_start_time')->default(date('H:i:s'));
            $form->time('work_end_time')->default(date('H:i:s', strtotime('+1 hour')));
            $form->number('total_hours')->default(1)->attribute('min', 0);
            $form->text('hourly_rate')->default(1500);
            $form->text('record_salary')->default(1500);
            $form->text('sale_price')->default(2000);
            $form->text('note');
        
            $form->html('
                <script>
                $(document).ready(function(){
                    function calculateSalary() {
                        var total_hours = $("input[name=\'total_hours\']").val();
                        var hourly_rate = $("input[name=\'hourly_rate\']").val();
                        $("input[name=\'record_salary\']").val(total_hours * hourly_rate);
                    }

                    $("select[name=\'admin_user_name\']").change(function(){
                        var username = $(this).val();
                        $.ajax({
                            url: "/admin/api/hourly_rate",
                            type: "GET",
                            data: { username: username },
                            success: function(data){
                                if(data.hourly_rate){
                                    $("input[name=\'hourly_rate\']").val(data.hourly_rate);
                                    calculateSalary();
                                }
                            }
                        });
                    });
                    
                    $(document).on("click", ".number-group .btn", function() {
                        setTimeout(calculateSalary, 100);
                    });
                    
                    $("input[name=\'total_hours\'], input[name=\'hourly_rate\']").change(calculateSalary);
                });
                $("input[name=\'total_hours\']").on("input", function() {
                    setTimeout(calculateSalary, 100);
                });
                </script>'
            );

            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    //取得選擇的會員時薪
    public function getHourlyRate(Request $request) 
    {
        $username = $request->get('username');

        $user = Administrator::where('username', $username)->first();

        if ($user) {
            return response()->json([
                'hourly_rate' => $user->default_fee,
            ]);
        }
        
        return response()->json([]);
    }

    //上班打卡鈕 功能在App\Admin\Repositories\MemberWork
    public function startWork(Request $request)
    {
        $memberWorkRepo = new MemberWork();
        
        // 创建一条工作记录
        $memberWork = $memberWorkRepo->createWorkRecord(
            Admin::user()->id,
            Admin::user()->username,
            Administrator::where('id', Admin::user()->id)->first()->default_fee
        );

        admin_success(admin_trans_label('success_start_work'));

        return back();
    }

    //下班打卡鈕 功能在App\Admin\Repositories\MemberWork
    public function endWork(Request $request)
    {
        $memberWorkRepo = new MemberWork();
        
        // 更新最后一条工作记录的下班时间、总工时和工资
        $memberWorkRepo->updateLastWorkRecord(Admin::user()->id, date('H:i:s'));

        admin_success(admin_trans_label('success_end_work'));

        return back();
    }

}
