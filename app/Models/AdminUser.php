<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use App\Models\MemberWork;

class AdminUser extends Administrator
{
    public function memberWorks()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
