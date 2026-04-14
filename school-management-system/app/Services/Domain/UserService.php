<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\UserRepository;

class UserService extends CrudService
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }
}
