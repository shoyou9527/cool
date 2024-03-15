<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\RoleModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsersModel extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'users';
    
    const GENDER_MEN      = 'M';
    const GENDER_WOMEN    = 'F';

    const GENDER_METHOD = [
        self::GENDER_MEN    => '男',
        self::GENDER_WOMEN  => '女',
    ];

    /**
     * @return BelongsTo
     */
    public function roles(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role');
    }
}
