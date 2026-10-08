<?php

namespace App\Services\Sai;

use App\Models\Sai\ContactUs;
use App\Repositories\Sai\ContactUsRepository;

class ContactUsService
{
    public function __construct(private readonly ContactUsRepository $repository) {}

    /** @param array{name: string, email: string, subject: string, message: string} $data */
    public function createMessage(array $data): ContactUs
    {
        return $this->repository->createMessage($data);
    }
}
