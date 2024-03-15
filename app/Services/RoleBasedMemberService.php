<?php

namespace App\Services;

use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoleBasedMemberService
{
    public function filterMembersByRole()
    {
        $userRole = Admin::user()->roles->first()->slug;
        $userID = Admin::user()->id;
        switch ($userRole) {
            case 'administrator':
                // 管理员显示所有会员
                $memberIds = Administrator::whereHas('roles', function ($query) {
                    $query->where('slug', 'member');
                })->pluck('id');

                return $memberIds;

            case 'company':
                // 公司角色显示该公司的代理和会员
                $agents = Administrator::where('parent_id', $userID)->pluck('id');
                $memberIds = Administrator::whereIn('parent_id', $agents)->pluck('id');
                return $memberIds;

            case 'agent':
                // 代理角色显示属于当前代理的会员
                return Administrator::where('parent_id', $userID)->pluck('id');

            case 'member':
                // 会员只能看到自己
                return collect($userID);

            default:
                // 其他角色，返回空集合
                return collect();
        }
    }
}