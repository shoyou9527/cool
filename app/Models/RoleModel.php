<?php

/*
 * // +----------------------------------------------------------------------
 * // | erp
 * // +----------------------------------------------------------------------
 * // | Copyright (c) 2006~2020 erp All rights reserved.
 * // +----------------------------------------------------------------------
 * // | Licensed ( LICENSE-1.0.0 )
 * // +----------------------------------------------------------------------
 * // | Author: yxx <1365831278@qq.com>
 * // +----------------------------------------------------------------------
 */

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RoleModel
 *
 * @property int $id
 * @property string $name 屬性名稱
 * @property string $link 聯繫人
 * @property int $pay_method 支付方式
 * @property string $phone 手機號碼
 * @property string $other 備註
 * @property int $user_id 建立使用者
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CustomerAddressModel[] $address
 * @property-read int|null $address_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DraweeModel[] $drawee
 * @property-read int|null $drawee_count
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel newQuery()
 * @method static \Illuminate\Database\Query\Builder|RoleModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel wherePayMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|RoleModel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RoleModel withoutTrashed()
 * @mixin \Eloquent
 */
class RoleModel extends Model
{
    protected $table = 'admin_roles';

    // 排除特定 ID
    public static function getAllExcept($id = 1)
    {
        return self::where('id', '<>', $id)->get();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

}
