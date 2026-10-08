<?php

namespace App\Repositories\Sai;

use App\Models\Sai\ContactUs;
use App\Repositories\MainRepository;

class ContactUsRepository extends MainRepository
{
    public function __construct(ContactUs $model)
    {
        $this->model = $model;
    }

    /** @param array{name: string, email: string, subject: string, message: string} $data */
    public function createMessage(array $data): ContactUs
    {
        return $this->getModel()->newQuery()->create($data);
    }
}
