<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Bonus;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Models\Administrator;

class BonusController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Bonus(['company']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('company.name', '公司名稱');
            $grid->column('hours_threshold');
            $grid->column('bonus_amount');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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
        return Show::make($id, new Bonus(), function (Show $show) {
            $show->field('id');
            $show->field('company_id');
            $show->field('hours_threshold');
            $show->field('bonus_amount');
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
        return Form::make(new Bonus(), function (Form $form) {

            // 检查用户是否是总管理员
            if (Admin::user()->isAdministrator()) {
                // 總管理員登入時的FORM表單顯示所有上層公司
                $companyOptions = Administrator::whereHas('roles', function ($query) {
                    $query->where('slug', 'company');
                })->pluck('name', 'id');
                $form->select('company_id', '選擇公司')->options($companyOptions);
            } elseif (Admin::user()->isRole('company')) {
                // 帶入此公司為上層ID
                $form->hidden('company_id')->value(Admin::user()->id);
            } else {
                // 其他角色
                return redirect('/admin');
                // abort(403, 'Unauthorized access.');
            }

            $form->display('id');
            $form->text('hours_threshold');
            $form->text('bonus_amount');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
