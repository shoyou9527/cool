<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\SwitchGridView;
use App\Admin\Repositories\Image;
use Dcat\Admin\Layout\Content;
use App\Traits\PreviewCode;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\UserController;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Admin;
use Dcat\Admin\Models\Role;
use Dcat\Admin\Http\Auth\Permission;
use Dcat\Admin\Models\Administrator as AdministratorModel;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Widgets\Tree;
use Jenssegers\Agent\Agent;

class MemberController extends UserController
{
    public function __construct()
    {
        $this->middleware('check_member_permission')->only(['edit', 'show', 'destroy']);
    }
    
    public function title()
    {
        return trans('admin.member');
    }


    public function index(Content $content)
    {
        return $content
            ->title('會員列表')
            ->description('Member List')
            ->body($this->grid());
    }

    protected function grid()
    {
        return Grid::make(Administrator::with(['roles']), function (Grid $grid) {

            // 根據User裝置顯示對應的view，電腦版為預設view，手機版為客製view
            $agent = new Agent();
            if ($agent->isMobile()) {
                $grid->view('admin.grid.member');
            }

            if (Admin::user()->isAdministrator()) {
                // 總管理員登入顯示所有會員
                $grid->model()->whereIn('id', function ($query) {
                    $query->select('user_id')->from('admin_role_users')->where('role_id', 4);
                });
            } elseif (Admin::user()->isRole('company')) {
                // 找尋此公司所有的代理
                $agents = Administrator::where('parent_id', Admin::user()->id)->pluck('id');
                // 按照找尋出的代理顯示這些代理所有的會員
                $grid->model()->whereIn('parent_id', $agents);
            } elseif (Admin::user()->isRole('agent')) {
                // 只顯示屬於當前代理的會員
                $grid->model()->where('parent_id', Admin::user()->id);
            } else {
                // 其他角色，跳轉首頁
                return redirect('/admin');
            }

            $grid->column('id', 'ID')->sortable();
            $grid->column('agent_name', '上層代理')->display(function () {
                return Administrator::find($this->parent_id)->name;
            });
            $grid->column('username');
            $grid->column('name');
            $grid->column('avatar','頭像')->image();
            $grid->column('default_fee', '時薪');

            $grid->column('lang', '英文')->switch()->help('給予會員登入後使用英文介面網站觀看');;

            $grid->quickSearch(['id', 'name', 'username']); //快速搜索
            $grid->showQuickEditButton(); //快速编辑按钮
            // $grid->enableDialogCreate(); //对话框中显示
            // $grid->showColumnSelector(); //列选择器
            $grid->disableEditButton(); //移除编辑按钮

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->getKey() == AdministratorModel::DEFAULT_ID) {
                    $actions->disableDelete();
                }
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id, Administrator::with(['roles']), function (Show $show) {
            $show->field('id');
            $show->field('parent_name', '上層代理名稱')->as(function () {
                return Administrator::find($this->parent_id) ? Administrator::find($this->parent_id)->name : 'N/A';
            });
            $show->field('username');
            $show->field('name');
            $show->field('avatar', __('admin.avatar'))->image();
            $show->field('default_fee','時薪');
            if (config('admin.permission.enable')) {
                $show->field('roles')->as(function ($roles) {
                    if (! $roles) {
                        return;
                    }

                    return collect($roles)->pluck('name');
                })->label();

                if (Admin::user()->isAdministrator()) {
                    $show->field('permissions')->unescape()->as(function () {
                        $roles = $this->roles->toArray();

                        $permissionModel = config('admin.database.permissions_model');
                        $roleModel = config('admin.database.roles_model');
                        $permissionModel = new $permissionModel();
                        $nodes = $permissionModel->allNodes();

                        $tree = Tree::make($nodes);

                        $isAdministrator = false;
                        foreach (array_column($roles, 'slug') as $slug) {
                            if ($roleModel::isAdministrator($slug)) {
                                $tree->checkAll();
                                $isAdministrator = true;
                            }
                        }

                        if (! $isAdministrator) {
                            $keyName = $permissionModel->getKeyName();
                            $tree->check(
                                $roleModel::getPermissionId(array_column($roles, $keyName))->flatten()
                            );
                        }

                        return $tree->render();
                    });
                }
            }

            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    public function form()
    {
        return Form::make(Administrator::with(['roles']), function (Form $form) {

            if (Admin::user()->isAdministrator()) {
                //總管理員登入時的FORM表單顯示所有上層代理
                $companyOptions = Administrator::whereHas('roles', function ($query) {
                    $query->where('id', 3);
                })->pluck('name', 'id');
                $form->select('parent_id', '上層代理')->options($companyOptions);
            } elseif (Admin::user()->isRole('company')) {
                //公司登入時 抓取此公司的上層代理供選擇
                $companyOptions = Administrator::where('parent_id', Admin::user()->id)
                    ->whereHas('roles', function ($query) {
                        $query->where('id', 3);
                    })->pluck('name', 'id');
                $form->select('parent_id', '上層代理')->options($companyOptions);
            } elseif (Admin::user()->isRole('agent')) {
                //代理登入時 帶入此代理為上層ID
                $form->hidden('parent_id')->value(Admin::user()->id);
            } else {
                // 其他角色，跳轉首頁
                return redirect('/admin');
            }
            
            $userTable = config('admin.database.users_table');

            $connection = config('admin.database.connection');

            $id = $form->getKey();

            $form->display('id', 'ID');

            $form->text('username', trans('admin.username'))
                ->required()
                ->creationRules(['required', "unique:{$connection}.{$userTable}"])
                ->updateRules(['required', "unique:{$connection}.{$userTable},username,$id"]);
            $form->text('name', trans('admin.name'))->required();
            $form->image('avatar', trans('admin.avatar'))->autoUpload();
            $form->text('default_fee', trans('時薪'));

            $form->switch('lang', '英文');

            if ($id) {
                $form->password('password', trans('admin.password'))
                    ->minLength(5)
                    ->maxLength(20)
                    ->customFormat(function () {
                        return '';
                    });
            } else {
                $form->password('password', trans('admin.password'))
                    ->required()
                    ->minLength(5)
                    ->maxLength(20);
            }

            $form->password('password_confirmation', trans('admin.password_confirmation'))->same('password');

            $form->ignore(['password_confirmation']);

            if (config('admin.permission.enable')) {
                if ($form->isCreating()) {
                    // 创建用户时，设置角色为“会员”，并且隐藏这个字段
                    $form->hidden('roles')->default(4, true);
                }
                // 在编辑用户时，不显示角色字段
            }

            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            if ($id == AdministratorModel::DEFAULT_ID) {
                $form->disableDeleteButton();
            }
        })->saving(function (Form $form) {
            if ($form->isCreating()) {
                $form->roles = 4; // 強制角色為 "会员" has ID of 4
            }
            if ($form->password && $form->model()->get('password') != $form->password) {
                $form->password = bcrypt($form->password);
            }

            if (! $form->password) {
                $form->deleteInput('password');
            }
        });
    }

    public function destroy($id)
    {
        if (in_array(AdministratorModel::DEFAULT_ID, Helper::array($id))) {
            Permission::error();
        }

        return parent::destroy($id);
    }
}
