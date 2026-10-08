<?php

namespace App\Services\Sai;

use App\Repositories\Sai\FireBaseTokenRepository;

class FireBaseTokenService
{
    public function __construct(private FireBaseTokenRepository $fireBaseTokenRepository)
    {
    }

    public function storeToken($user_id, $data)
    {
        $data['user_id'] = $user_id;
        if($this->fireBaseTokenRepository->getWhere([
            'user_id' => $user_id,
            'token' => $data['token'],
            'type' => $data['type']
        ])->count() == 0){
            return $this->fireBaseTokenRepository->store($data);
        }
        return true;
    }

    public function deleteWhere($where)
    {
        return $this->fireBaseTokenRepository->deleteWhere($where);
    }

}
