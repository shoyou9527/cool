<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Users;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\UsersModel;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\RoleModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;

class UsersController extends AdminController
{
    public function generateRandomString($length = 12) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Users(['roles']), function (Grid $grid) {
            if(Admin::user()->inRoles(['administrator']) === FALSE){
                $grid->model()->where('role', Admin::user()->roles[0]['pivot']['role_id']);
            }

            $grid->column('id')->sortable();
            $grid->column('account');
            $grid->column('code');
            $grid->column('roles.name','社區');
            $grid->column('name');
            $grid->column('email');
            $grid->column('phone');
            $grid->column('point');
            $grid->column('address');
            $grid->column('created_at');
        
            $grid->filter(function (Grid\Filter $filter) {
                if(Admin::user()->inRoles(['administrator']) !== FALSE){
                    $filter->equal('roles.id', '社區')->select(function () {
                        $categories = RoleModel::getAllExcept()->pluck('name', 'id');
                        $options = [null => 'All'] + $categories->toArray();
                        return $options;
                    })->width(3);
                }

                $filter->where('keyword', function ($query) {
                    $query->orWhere('name', 'like', "%{$this->input}%");
                    $query->orWhere('phone', 'like', "%{$this->input}%");
                    $query->orWhere('code', 'like', "%{$this->input}%");
                    $query->orWhere('email', 'like', "%{$this->input}%");
                }, "其他")->placeholder("姓名、手機號碼、代碼、信箱")->width(3);
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
        $users = UsersModel::find($id);

        if (Admin::user()->inRoles(['administrator']) === FALSE && $users->role != Admin::user()->roles[0]['pivot']['role_id']) {
            return redirect('/admin/users');
        }
        else{
            return Show::make($id, new Users(['roles']), function (Show $show) {
                $show->field('account');
                $show->field('code');
                $show->field('barcode','條碼')->unescape()->as(function () {
                    $generator = new BarcodeGeneratorPNG();
                    $barcode = base64_encode($generator->getBarcode($this->code, $generator::TYPE_CODE_39, 1, 50));
                    return "<img src='data:image/png;base64,{$barcode}'>";
                });
                $show->field('roles.name', '社區');
                $show->field('name');
                $show->field('email');
                $show->field('gender')->using(UsersModel::GENDER_METHOD);
                $show->field('phone');
                $show->field('point');
                $show->field('address');
                $show->field('created_at');
            });
        }
    }

    public function edit($id, Content $content)
    {
        $users = UsersModel::find($id);

        if (Admin::user()->inRoles(['administrator']) === FALSE && $users->role != Admin::user()->roles[0]['pivot']['role_id']) {
            return redirect('/admin/users');
        }

        return $content
            ->header('會員管理')
            ->description('編輯')
            ->body($this->form()->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Users(), function (Form $form) {
            $form->text('account')->required();
            $form->password('password')->required();

            //代碼
            if(!empty($form->model()->code)){
                $form->display('code');
                $form->hidden('code')->value($form->model()->code);
            }
            else{
                $form->display('code')->value('系統產生');
                $form->hidden('code')->value($this->generateRandomString());
            }

            //條碼
            if(!empty($form->model()->code)){
                $generator = new BarcodeGeneratorPNG();
                $barcode = base64_encode($generator->getBarcode($form->model()->code, $generator::TYPE_CODE_39, 1, 50));

                $form->display('條碼')->value("<img src='data:image/png;base64,{$barcode}'>");
            }

            $form->select('role')->options(function () {
                return RoleModel::getAllExcept()->pluck('name', 'id');
            })->required();
            
            $form->text('name')->required();
            $form->email('email')->required();
            $form->select('gender')->options(UsersModel::GENDER_METHOD)->default('M')->required();
            $form->text('phone')->required();
            $form->text('address');

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });
        });
    }
}
