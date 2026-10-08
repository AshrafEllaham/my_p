<?php

namespace App\Repositories\Sai;

use App\Repositories\MainRepository;
use App\Models\Sai\FireBaseToken;

class FireBaseTokenRepository extends MainRepository
{
    public function __construct(FireBaseToken $model)
    {
        $this->model = $model;
    }

}
