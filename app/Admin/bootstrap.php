<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;
use App\Admin\Extensions\Grid\Displayers\Actions;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

// 覆盖默认配置
// config(['admin' => user_admin_config()]);
// config(['app.locale' => config('admin.lang') ?: config('app.locale')]);

Admin::style('.main-sidebar .nav-sidebar .nav-item>.nav-link {
    border-radius: .1rem;
}');

// 扩展Column
Grid\Column::extend('code', function ($v) {
    return "<code>$v</code>";
});

Grid::resolving(function (Grid $grid) {
    if (! request('_row_')) {
        $grid->tableCollapse();


//        $grid->tools(new App\Admin\Grid\Tools\SwitchGridMode());
    }
});

Filter::resolving(function (Filter $filter) {
    $filter->panel();
    $filter->expand();
});

Grid::resolving(function (Grid $grid) {
    $grid->setActionClass(Actions::class);
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableDelete();
        $actions->disableEdit();
        $actions->disableQuickEdit();
        $actions->disableView();
    });
    $grid->model()->orderBy("id", "desc");
});

//自訂全域js套件
Admin::js('/js/global_v1.js');

//自訂全域css套件
Admin::css('/css/global_v1.css');