<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;
use Illuminate\Http\Request;
use Dcat\Admin\Models\Administrator;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    //路徑輸入就清快取
    Route::get('/clear-cache', 'CacheController@clear')->name('admin.clear-cache');

    $router->resource('test', 'TestController');

    //會員帳號管理
    $router->resource('users', 'UsersController');

    $router->resource('companys', 'CompanyController')->parameters(['companys' => 'id']);
    $router->resource('agents', 'AgentController')->parameters(['agents' => 'id']);
    $router->resource('members', 'MemberController')->parameters(['members' => 'id']);
    $router->resource('member_work', 'MemberWorkController')->parameters(['member_work' => 'id']);

    //結帳切換
    Route::post('/member_work/checkout/{id}', 'MemberWorkController@checkout')->name('admin.checkout');
    
    Route::get('/api/hourly_rate', 'MemberWorkController@getHourlyRate');
    Route::get('/api/work_start', 'MemberWorkController@startWork');
    Route::get('/api/work_end', 'MemberWorkController@endWork');

    $router->resource('bonus', 'BonusController');

    //報表
    $router->resource('report', 'ReportController');
    $router->resource('report-member', 'ReportMemberController');
});
