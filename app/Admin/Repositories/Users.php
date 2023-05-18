<?php

namespace App\Admin\Repositories;

use App\Models\UsersModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Users extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
