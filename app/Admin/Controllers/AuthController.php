<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Http\Repositories\Administrator;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{
    protected function settingForm()
    {
        return new Form(new Administrator(), function (Form $form) {
            $form->action(admin_url('auth/setting'));

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
                $tools->disableDelete();
            });

            $form->display('username', trans('admin.username'));
            //擴展修改會員不可編輯名稱
            if (Admin::user()->isRole('member')) {
                $form->display('name', trans('admin.name'));
            } else {
                $form->text('name', trans('admin.name'))->required();
            }
            // $form->text('name', trans('admin.name'))->required();
            $form->image('avatar', trans('admin.avatar'))->autoUpload();

            $form->password('old_password', trans('admin.old_password'));

            $form->password('password', trans('admin.password'))
                ->minLength(5)
                ->maxLength(20)
                ->customFormat(function ($v) {
                    if ($v == $this->password) {
                        return;
                    }

                    return $v;
                });
            $form->password('password_confirmation', trans('admin.password_confirmation'))->same('password');

            $form->ignore(['password_confirmation', 'old_password']);

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }

                if (! $form->password) {
                    $form->deleteInput('password');
                }
            });

            $form->saved(function (Form $form) {
                return $form
                    ->response()
                    ->success(trans('admin.update_succeeded'))
                    ->redirect('auth/setting');
            });
        });
    }

    protected function getRedirectPath()
    {
        if (Admin::user()->isRole('member')) {
            return admin_url('/member_work');
        }
    
        return $this->redirectTo ?: admin_url('/');
    }
}
