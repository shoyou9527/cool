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

class MemberController extends UserController
{

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

            // 设置自定义视图
            $grid->view('admin.grid.member');

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
                return redirect('/');
            }

            $grid->column('id', 'ID')->sortable();
            $grid->column('username');
            $grid->column('name');
            $grid->column('avatar')->image();
            $grid->column('default_fee', '時薪');

            if (config('admin.permission.enable')) {
                $grid->column('roles')->pluck('name')->label('primary', 3);

                $permissionModel = config('admin.database.permissions_model');
                $roleModel = config('admin.database.roles_model');
                $nodes = (new $permissionModel())->allNodes();
            }

            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            // $grid->quickSearch(['id', 'name', 'username']);：这个函数启用快速搜索功能，允许用户输入搜索关键字在'id'、'name'和'username'这三个字段中进行搜索。
            // $grid->showQuickEditButton();：这个函数会在表格中每一行的操作列增加一个快速编辑按钮，用户点击这个按钮可以直接在表格中编辑该行的数据。
            // $grid->enableDialogCreate();：这个函数使创建新数据行的表单在一个对话框中显示，而不是在新的页面中。
            // $grid->showColumnSelector();：这个函数会在表格的工具栏中增加一个列选择器，用户可以通过这个选择器来选择哪些列在表格中显示。
            // $grid->disableEditButton();：这个函数会在表格中每一行的操作列移除编辑按钮，使用户不能编辑数据行。

            $grid->quickSearch(['id', 'name', 'username']);
            $grid->showQuickEditButton();
            // $grid->enableDialogCreate();
            // $grid->showColumnSelector();
            $grid->disableEditButton();

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
            $show->field('username');
            $show->field('name');
            $show->field('avatar', __('admin.avatar'))->image();
            $show->field('default_fee');
            if (config('admin.permission.enable')) {
                $show->field('roles')->as(function ($roles) {
                    if (! $roles) {
                        return;
                    }

                    return collect($roles)->pluck('name');
                })->label();

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
                ///公司登入時只顯示屬於當前公司的代理
                $form->model()->where('parent_id', Admin::user()->id);
            } elseif (Admin::user()->isRole('agent')) {
                //帶入此代理為上層ID
                $form->hidden('parent_id')->value(Admin::user()->id);
            } else {
                // 其他角色，跳轉首頁
                return redirect('/');
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

            //設定角色的下拉選框內容為會員
            if (config('admin.permission.enable')) {
                $form->select('roles', trans('admin.roles'))
                ->options(function () {
                    $roleModel = config('admin.database.roles_model');
                    return $roleModel::where('id', 4)->pluck('name', 'id');
                })
                ->default(4, true)
                ->customFormat(function ($v) {
                    return array_column($v, 'id');
                });
            }

            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            if ($id == AdministratorModel::DEFAULT_ID) {
                $form->disableDeleteButton();
            }
        })->saving(function (Form $form) {
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
