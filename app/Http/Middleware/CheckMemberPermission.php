<?php

namespace App\Http\Middleware;

use Closure;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Admin;
use App\Models\MemberWork;

class CheckMemberPermission
{
    public function handle($request, Closure $next, $fromWorkLog = false)
    {
        
        $user = Admin::user();

        //總管理員直接通過
        if ($user->isAdministrator()) {
            return $next($request);
        }
        
        // 工作日誌使用 先從工作日誌獲取會員ID 如果非工作日誌就直接使用目標ID
        $targetId = $fromWorkLog ? MemberWork::findOrFail($request->route('id'))->admin_user_id : $request->route('id');

        $targetUser = Administrator::find($targetId);
        
        //找不到目標或無上層
        if (!$targetUser || !$targetUser->parent_id) {
            abort(403, '無權限查看或編輯該資料');
        }

        //比對目標的上層與上上層是否為自己
        while ($targetUser) {
            if ($targetUser->parent_id === $user->id) {
                return $next($request);
            }
            $targetUser = Administrator::find($targetUser->parent_id);
        }
        
        // 循环结束后未找到匹配的上层用户，没有权限
        abort(403, '無權限查看或編輯該資料');
    }
}
