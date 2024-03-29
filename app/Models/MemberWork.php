<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Model;

class MemberWork extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'member_work';

    public function member()
    {
        return $this->belongsTo(Administrator::class, 'admin_user_id');
    }
    
}
