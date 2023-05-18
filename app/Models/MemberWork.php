<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class MemberWork extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'member_work';
    
}
