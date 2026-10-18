<?php

use App\Http\Controllers\Api\Chat\ChatMessageController;
use App\Http\Controllers\Api\Chat\ChatRoomController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function (): void {
    // ينشئ غرفة موحدة بين المستخدم والمتجر ويعيد الغرفة القائمة عند تكرار الطلب.
    Route::post('/rooms', [ChatRoomController::class, 'store']);

    // يعرض غرف الطرف الحالي مع البحث وترتيب أحدث نشاط وتقسيم النتائج إلى صفحات.
    Route::get('/rooms', [ChatRoomController::class, 'index']);

    // يرسل رسالة نصية إلى غرفة يشارك فيها الحساب الحالي.
    Route::post('/rooms/{room}/messages', [ChatMessageController::class, 'store']);

    // يعرض رسائل الغرفة بالترتيب من الأحدث مع البحث وتقسيم النتائج إلى صفحات.
    Route::get('/rooms/{room}/messages', [ChatMessageController::class, 'index']);

    // يسجل بلاغ الطرف الحالي عن الغرفة مع سبب وتفاصيل اختيارية.
    Route::post('/rooms/{room}/reports', [ChatRoomController::class, 'report']);

    // يحظر الغرفة للطرف الحالي ويمنع إرسال رسائل جديدة من الطرفين.
    Route::post('/rooms/{room}/blocks', [ChatRoomController::class, 'block']);
});
