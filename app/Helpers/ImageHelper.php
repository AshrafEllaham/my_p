<?php

namespace App\Helpers; // تأكد من ضبط الـ Namespace حسب مشروعك

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // أو الـ Driver اللي بتستخدمه

class ImageHelper
{
    /**
     * رفع أو تحديث الملفات - نسخة الـ Helper الذكية
     */
    public static function upload($file, string $folder_name, $is_updated_file = null, $type = null): ?string
    {
        // 1. حذف الملف القديم تلقائياً في حالة التحديث (Update)
        if ($file && $is_updated_file) {
            self::delete($is_updated_file);
        }

        // 2. إذا لم يتم رفع ملف جديد، احتفظ بالملف القديم
        if (!$file) {
            return $is_updated_file;
        }

        // إذا كان الممرر نصاً وليس ملفاً (حماية إضافية)
        if (is_string($file)) {
            return $file;
        }

        $fileName_original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileNameWithExt = $file->getClientOriginalExtension();

        if ($fileNameWithExt == 'mp3') {
            $fileNameWithExt = 'm4a';
        }

        // 3. هندلة الفيديوهات أو الملفات العامة
        if ($type == 'video') {
            $fileNameToStore = $folder_name . '/' . time() . '_' . uniqid() . '.' . $fileNameWithExt;
            $file->store($fileNameToStore, 'public');
            return $fileNameToStore;
        }

        // 4. هندلة الصور وتحويلها لـ webp والضغط لـ 70%
        $fileNameToStore = $folder_name . '/' . base64_encode($fileName_original) . '_' . time() . '.webp';

        try {
            // الطريقة الرسمية لـ Intervention Image V4
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->orient(); // ضبط اتجاه الصورة

            // حفظ الصورة مباشرة
            Storage::disk('public')->put($fileNameToStore, (string) $image->toWebp(70));
        } catch (\Exception $exception) {
            // الـ Fallback الآمن في حال حدوث أي مشكلة بالسيرفر أو المكتبة
            $fileNameToStore = $folder_name . '/' . base64_encode($fileName_original) . '_' . time() . '.' . $fileNameWithExt;
            $file->store($fileNameToStore, 'public');
        }

        return $fileNameToStore;
    }

    /**
     * حذف الملف من السيرفر
     */
    public static function delete(?string $filePath): bool
    {
        if ($filePath && Storage::disk('public')->exists('uploads/' . $filePath)) {
            return Storage::disk('public')->delete('uploads/' . $filePath);
        }
        return false;
    }


    /**
     * Get the file URL, returning a default image if it doesn't exist.
     */
    public static function getFile(?string $path, string $default = 'assets/user/login/imgs/logo.png'): string
    {
        if ($path) {
            return asset($path);
        }
        return asset($default);
    }
}
