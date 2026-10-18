<?php

use App\Models\Sai\User;
use Illuminate\Support\Facades\Broadcast;

// يتيح لكل حساب الاستماع إلى صندوق محادثاته فقط.
Broadcast::channel('chat.inboxes.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === $userId;
}, ['guards' => ['api']]);
