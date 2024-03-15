<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Dcat\Admin\Layout\Menu;
use App\Admin\Extensions\CustomMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 设置数据库默认字符串长度
        Schema::defaultStringLength(191);

        // 绑定自定义的 Menu 类来替代原始的
        $this->app->bind(Menu::class, CustomMenu::class);
    }
}
