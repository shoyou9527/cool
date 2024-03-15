<?php

namespace App\Admin\Extensions;

use Dcat\Admin\Layout\Menu as DcatMenu;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\Auth;

class CustomMenu extends DcatMenu
{
    public function register()
    {
        if (! admin_has_default_section(Admin::SECTION['LEFT_SIDEBAR_MENU'])) {
            admin_inject_default_section(Admin::SECTION['LEFT_SIDEBAR_MENU'], function () {
                $menuModel = config('admin.database.menu_model');

                return $this->toHtml((new $menuModel())->allNodes()->toArray());
            });
        }

        // 检查用户是否已经登录
        if (Admin::user()) {
            // 然后检查用户是否是总管理员
            if (Admin::user()->isAdministrator()) {
                $this->add(static::$helperNodes, 20);
            }
        }
    }
}
