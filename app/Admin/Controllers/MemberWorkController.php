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
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Dcat\Admin\Grid\Displayers\Checkbox;

class MemberWorkController extends AdminController
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
        return Grid::make(new MemberWork(['member']), function (Grid $grid) {

            // 根據User裝置顯示對應的view，電腦版為預設view，手機版為客製view
            $agent = new Agent();
            if ($agent->isMobile()) {
                $grid->view('admin.grid.member_work');
            }
            
            //根據使用者抓取 使用者下層的會員列表
            $memberService = new RoleBasedMemberService();
            $memberIds = $memberService->filterMembersByRole();
            $grid->model()->whereIn('admin_user_id', $memberIds);

            //顯示會員的上層代理
            $grid->column('agent_name', admin_trans_label('agent_name'))->display(function () {
                $parentId = Administrator::where('id', $this->admin_user_id)->value('parent_id');
                $parentName = Administrator::where('id', $parentId)->value('username');
                return $parentName;
            });

            //會員登入不可篩選帳號
            if (Admin::user()->inRoles(['member'])) {
                $grid->column('admin_user_name');
                //會員登入禁用功能
                $grid->disableCreateButton(); //新增
                $grid->disableActions(); //操作
                $grid->disableRowSelector();//勾選框
                // $grid->disableBatchCheckbox();
                $grid->disableBatchDelete();//批量刪除
                $memberWorkRepo = new MemberWork();
                // 获取会员的最后一笔上班打卡记录
                $lastWorkRecord = $memberWorkRepo->model()::where('admin_user_id', Admin::user()->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastWorkRecord && !empty($lastWorkRecord->work_start_time) && empty($lastWorkRecord->work_end_time)) {
                    // 如果沒有上班打卡記錄或已經打卡下班，顯示上班打卡按鈕
                    $grid->tools('<a href="#" class="btn btn-danger disable-outline">' . admin_trans_label('start_work_button') . '</a>');
                    $grid->tools('<a href="/admin/api/work_end" class="btn btn-info disable-outline">' . admin_trans_label('end_work_button') . '</a>');
                } else {
                    // 如果有上班打卡記錄且尚未打卡下班，顯示下班打卡按鈕
                    $grid->tools('<a href="/admin/api/work_start" class="btn btn-info disable-outline">' . admin_trans_label('start_work_button') . '</a>');
                    $grid->tools('<a href="#" class="btn btn-danger disable-outline">' . admin_trans_label('end_work_button') . '</a>');
                }

            }else{
                //會員帳號
                $grid->column('member.username', admin_trans_field('admin_user_name'))->filter('like');

            }

            //會員名稱
            $grid->column('member.name', admin_trans_label('member_name'));
            
            $grid->column('work_date')->sortable()->style('white-space: nowrap')->display(function ($value) {
                return date('m/d', strtotime($value));
            });

            $grid->column('work_start_time')->display(function ($value) {
                return $value ? date('H:i', strtotime($value)) : '';
            });
            
            $grid->column('work_end_time')->display(function ($value) {
                return $value ? date('H:i', strtotime($value)) : '';
            });
            $grid->column('total_hours');
            $grid->column('hourly_rate');
            $grid->column('record_salary');
            //會員移除售價 新增按鈕 操作欄位 勾選框刪除
            if (!Admin::user()->inRoles(['member'])) {
                $grid->column('sale_price'); //售價
                $grid->column('is_checkout')->switch();
                $grid->column('note');
            }
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand(false); // 將筛選器設置為預設收起狀態
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

            $show->field('id');
            // $show->field('admin_user_id');
            $show->field('admin_user_id','會員資料')->as(function ($adminUserId) {
                $member = Administrator::find($adminUserId);
                if ($member) {
                    $parentName = Administrator::where('id', $member->parent_id)->value('username');
                    return '名稱: ' . $member->name . ', 帳號: ' . $member->username. ', 編號: ' . $member->id . ', 上層代理: ' . $parentName;
                }
                return '';
            });

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
                    $users = Administrator::where('id', Admin::user()->id)->get();
                    break;

                default:
                    // 其他角色，跳轉首頁
                    return redirect('/admin');
            }

            $form->display('id');
            
            if ($form->isCreating()) {
                // 新增表單，使用下拉選擇框選擇會員
                if ($role != 'member') {
                    $users = $users->pluck('username', 'id');
                    $form->select('admin_user_id', '選擇會員')->options($users);

                    $form->saving(function (Form $form) {
                        $selectedUserId = $form->input('admin_user_id');
                        $form->input('admin_user_id', $selectedUserId);
                    });
                }
            } else {
                // 編輯表單，顯示固定的會員帳號
                $form->display('admin_user_id','會員資料')->customFormat(function ($adminUserId) {
                    $member = Administrator::find($adminUserId);
                    if ($member) {
                        return '名稱: ' . $member->name . ', 帳號: ' . $member->username. ', 編號: ' . $member->id;
                    }
                    return '';
                });
                // $form->display('admin_user_id');
            }

            $form->date('work_date')->default(date('Y-m-d'));
            $form->datetime('work_start_time')->default(date('Y-m-d H:i:s'));
            $form->datetime('work_end_time')->default(date('Y-m-d H:i:s', strtotime('+1 hour')));
            $form->number('total_hours')->default(1)->attribute('min', 0);
            $form->text('hourly_rate')->default(1500);
            $form->text('record_salary')->default(1500);
            $form->text('sale_price')->default(2000);
            $form->switch('is_checkout', '結帳');

            $form->text('note');
        
            $form->html('
                <script>
                $(document).ready(function(){
                    function calculateSalary() {
                        var total_hours = $("input[name=\'total_hours\']").val();
                        var hourly_rate = $("input[name=\'hourly_rate\']").val();
                        $("input[name=\'record_salary\']").val(total_hours * hourly_rate);
                    }

                    $("select[name=\'admin_user_id\']").change(function(){
                        var selectedUserId = $(this).val();
                        $.ajax({
                            url: "/admin/api/hourly_rate",
                            type: "GET",
                            data: { admin_user_id: selectedUserId  },
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

            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }

    //取得選擇的會員時薪
    public function getHourlyRate(Request $request) 
    {
        $adminUserId = $request->get('admin_user_id');

        $user = Administrator::find($adminUserId);

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

        return redirect('/admin/member_work');
    }

    //下班打卡鈕 功能在App\Admin\Repositories\MemberWork
    public function endWork(Request $request)
    {
        $memberWorkRepo = new MemberWork();
        
        // 更新最后一条工作记录的下班时间、总工时和工资
        $memberWorkRepo->updateLastWorkRecord(Admin::user()->id, date('Y-m-d H:i:s'));

        admin_success(admin_trans_label('success_end_work'));

        return redirect('/admin/member_work');
    }

    //結帳切換
    public function checkout($id)
    {
        // 找到对应的 member_work
        $memberWorkRepo = new MemberWork();
        // 获取会员的最后一笔上班打卡记录
        $member_work = $memberWorkRepo->model()::where('id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        // 如果找不到，返回一个错误响应
        if (!$member_work) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // 切换 is_checkout 的值
        $member_work->is_checkout = !$member_work->is_checkout;
        $member_work->save();

        // 返回一个成功的响应
        return response()->json(['message' => 'Success']);
    }

}
