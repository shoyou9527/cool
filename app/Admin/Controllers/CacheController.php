<?php

namespace App\Admin\Controllers;
use Illuminate\Support\Facades\Log;
use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Artisan;

class CacheController extends AdminController
{
       public function clear(Content $content){
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            // 记录日志
            Log::info('快取清除紀錄');

            $content->row(function (Row $row){
                Admin::script(
                    <<<JS
                        Dcat.success("快取清除成功", null, {
                            timeOut:9000, /*9000毫秒后自动消失=9秒后自动消失*/
                        });
                    JS
                );
            });
            return $content->header('快取清除');
       }
}