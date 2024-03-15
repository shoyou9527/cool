<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator as DcatAdministrator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends DcatAdministrator
{
    public function member()
    {
        return $this->hasMany(DcatAdministrator::class, 'parent_id');
    }

    
}