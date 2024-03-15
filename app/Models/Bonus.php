<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
	use HasDateTimeFormatter;

	public function company()
    {
        return $this->belongsTo(Administrator::class, 'company_id');
    }
}
