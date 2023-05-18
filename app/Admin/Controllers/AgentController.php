<?php

namespace App\Admin\Controllers;

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

class AgentController extends UserController
{

    public function title()
    {
        return trans('admin.agent');
    }

    protected function grid()
    {
        return Grid::make(Administrator::with(['roles']), function (Grid $grid) {
            // 根據User裝置顯示對應的view，電腦版為預設view，手機版為客製view
            $agent = new Agent();
            if ($agent->isMobile()) {
                $grid->view('admin.grid.agents');
            }

            $grid->column('id', 'ID')->sortable();

            if (Admin::user()->isAdministrator()) {
                // 顯示所有代理
                $grid->model()->whereIn('id', function ($query) {
                    $query->select('user_id')->from('admin_role_users')->where('role_id', 3);
                });

                $grid->column('parent_id', '上層公司')->display(function ($parentId) {
                    $parent = Administrator::find($parentId);
                    return $parent ? $parent->name : '';
                });

            } elseif (Admin::user()->isRole('company')) {
                // 只顯示屬於當前公司的代理
                $grid->model()->where('parent_id', Admin::user()->id);
            } else {
                // 其他角色，跳轉首頁
                return redirect('/');
            }

            $grid->column('username');
            $grid->column('name');
            $grid->column('avatar', '頭像')->image();

            if (config('admin.permission.enable')) {
                $grid->column('roles')->pluck('name')->label('primary', 3);

                $permissionModel = config('admin.database.permissions_model');
                $roleModel = config('admin.database.roles_model');
                $nodes = (new $permissionModel())->allNodes();
            }

            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->quickSearch(['id', 'name', 'username']);

            $grid->showQuickEditButton();
            // $grid->enableDialogCreate();
            $grid->showColumnSelector();
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
                //總管理員登入時的FORM表單顯示所有上層公司
                $companyOptions = Administrator::whereHas('roles', function ($query) {
                    $query->where('id', 2);
                })->pluck('name', 'id');
                
                $form->select('parent_id', '上層公司')->options($companyOptions);

            } elseif (Admin::user()->isRole('company')) {
                //公司登入時只顯示屬於當前公司的代理
                $form->model()->where('parent_id', Admin::user()->id);
                //帶入此公司為上層ID
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

            //設定角色的下拉選框內容為代理
            if (config('admin.permission.enable')) {
                $form->select('roles', trans('admin.roles'))
                ->options(function () {
                    $roleModel = config('admin.database.roles_model');
                    return $roleModel::where('id', 3)->pluck('name', 'id');
                })
                ->default(3, true)
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
