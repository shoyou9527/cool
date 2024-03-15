<?php

namespace App\Admin\Repositories;

use App\Models\Bonus as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Bonus extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
