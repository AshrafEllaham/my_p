<?php

use App\Models\Admin\Admin;
use App\Models\Driver;
use App\Models\Store;
use App\Models\Twenty\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

if (! function_exists('isEmail')) {
    function isEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (! function_exists('setting')) {
    function setting()
    {
        // الموديل نفسه متسجل كـ singleton في AppServiceProvider، فالاستعلام
        // بيحصل مرة واحدة في الـ request كله بدل استعلام في كل مرة.
        return app('settings');
    }
}

if (! function_exists('assetVer')) {
    /**
     * بيرجّع رابط الـ asset مع كاش-بست على شكل mtime.
     * الهدف إن المتصفح يميّز الملف لو اتعدّل، من غير ما نكتب رقم في الكود يدوي.
     */
    function assetVer(string $path): string
    {
        $url = asset($path);
        $full = public_path($path);

        // الملف مش موجود -> رجّع الرابط زي ما هو من غير query
        if (! is_file($full)) {
            return $url;
        }

        return $url . '?v=' . filemtime($full);
    }
}

if (! function_exists('admin')) {
    function admin()
    {
        return auth()->guard('admin');
    }
}

if (! function_exists('loggedAdmin')) {
    function loggedAdmin($field = null)
    {
        if ($field == null) {
            return Auth::guard('admin')->user();
        } else {
            return auth()->guard('admin')->user()->$field;
        }
    }
}

if (! function_exists('loggedUser')) {
    function loggedUser($field = null)
    {
        if ($field == null) {
            return Auth::guard('api')->user();
        } else {
            return auth()->guard('api')->user()->$field;
        }
    }
}

if (! function_exists('helperTrans')) {
    function helperTrans($key)
    {
        return __($key);
    }
}

if (! function_exists('get_file')) {
    function get_file($path, $type = null)
    {
        if ($path === 'null') {
            return null;
        }

        if ($path === 'logo') {
            $path = setting()->logo ?? 'assets/default/twenty.png';
        }

        if (! $path) {
            if ($type === 'user') {
                return asset('assets/default/user.svg');
            }

            return null;
        }

        if (is_string($path) && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) {
            return $path;
        }

        $path = ltrim((string) $path, '/');

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        if (file_exists(public_path('storage/' . $path))) {
            return asset('storage/' . $path);
        }

        $storagePath = str_starts_with($path, 'uploads/') ? $path : ('uploads/' . $path);

        if (file_exists(public_path('storage/' . $storagePath))) {
            return asset('storage/' . $storagePath);
        }

        if ($type === 'user') {
            return asset('assets/default/user.svg');
        }

        return null;
    }
}

if (! function_exists('get_file_user')) {
    function get_file_user($path)
    {
        return get_file($path, 'user');
    }
}

if (! function_exists('jsonSuccess')) {

    function jsonSuccess($data = null, $msg = null, $code = 200): JsonResponse
    {
        if ($data && is_object($data)) {
            request('up_id') ? $data->up_id = request('up_id') : null;
            request('up_name_id') ? $data->up_name_id = request('up_name_id') : null;
            $code = request('up_id') ? 202 : $code;
        } elseif ($data && is_array($data)) {
            request('up_id') ? $data['up_id'] = request('up_id') : null;
            request('up_name_id') ? $data['up_name_id'] = request('up_name_id') : null;
            $code = request('up_id') ? 202 : $code;
        }

        $msg = ($msg == null) ? __('auth.done successfully') : $msg;

        return response()->json([
            'code' => $code,
            'data' => $data,
            'message' => $msg,
        ], 200);
    }
}

if (! function_exists('extractTranslations')) {
    function extractTranslations(array $data): array
    {
        $translations = [];

        foreach (config('lexi-translate.supported_locales') as $locale) {
            if (isset($data[$locale]) && is_array($data[$locale])) {
                $translations[$locale] = $data[$locale];
                unset($data[$locale]);
            }
        }

        return [$translations, $data];
    }
}

if (! function_exists('processTranslations')) {
    function processTranslations($model, array $data): void
    {
        if (! method_exists($model, 'setTranslation')) {
            return;
        }

        foreach (config('lexi-translate.supported_locales') as $locale) {
            if (! isset($data[$locale]) || ! is_array($data[$locale])) {
                continue;
            }

            foreach ($data[$locale] as $column => $value) {
                if (! empty($value)) {
                    $model->setTranslation($column, $locale, $value);
                }
            }
        }
    }
}

if (! function_exists('getFieldLanguage')) {
    function getFieldLanguage($locale)
    {
        return match ($locale) {
            'ar' => 'عربي',
            'en' => 'English',
            default => $locale,
        };
    }
}

if (! function_exists('translationValue')) {
    function translationValue($model, string $column, string $locale): string
    {
        $translation = $model->getTranslation($column, $locale);

        if (! empty($translation?->text)) {
            return $translation->text;
        }

        return $locale === app()->getLocale() ? (string) $model->getRawOriginal($column) : '';
    }
}

if (! function_exists('addButton')) {
    function addButton($text = '', $class = 'btn-outline-info ')
    {
        $fullText = helperTrans('admin.add') . ($text ? ' ' . $text : '');

        return '<div class="d-inline-flex justify-content-end">
            <button type="button" class="addButton btn btn-glass-add ' . $class . '" title="' . helperTrans('admin.add') . '">
                <i class="ph-duotone ph-plus-circle me-1"></i>
                <span>' . $fullText . '</span>
            </button>
        </div>';
    }
}

if (! function_exists('addButtonNew')) {
    function addButtonNew($route, $text = '')
    {
        $fullText = helperTrans('admin.add') . ($text ? ' ' . $text : '');

        return '<div class="d-flex mb-3 justify-content-start">
            <button type="button" class="btn btn-glass-add addButton mx-1" data-bs-toggle="modal" data-bs-target="#createModal"
                   model-title="' . $fullText . '" model-route="' . $route . '" title="' . helperTrans('admin.add') . '">
                <i class="ph-duotone ph-plus-circle me-1"></i>
                <span>' . $fullText . '</span>
            </button>
        </div>';
    }
}

if (! function_exists('submitButton')) {
    function submitButton($route = '', $text = '')
    {
        $fullText = ($text != '') ? $text : helperTrans('admin.save');

        return '<button class="btn btn-primary text-white" type="submit">' . $fullText . '</button>';
    }
}
if (! function_exists('closeButton')) {
    function closeButton($class = '', $text = '')
    {
        $fullText = ($text != '') ? $text : helperTrans('admin.Close');

        return '<button class="btn btn-outline-secondary upCreateModalClose ' . $class . '" type="button" data-bs-dismiss="modal">' . $fullText . '</button>';
    }
}
if (! function_exists('addButton2')) {
    function addButton2($route, $text = '')
    {
        $fullText = helperTrans('admin.add') . ($text ? ' ' . $text : '');

        return '<div class="d-flex mb-3 justify-content-end">
            <button type="button" class="btn btn-glass-add btn-glass-success addButton mx-1" data-bs-toggle="modal" data-bs-target="#createModal"
                   model-title="' . $fullText . '" model-route="' . $route . '" title="' . helperTrans('admin.add') . '">
                <i class="ph-duotone ph-plus-circle me-1"></i>
                <span>' . $fullText . '</span>
            </button>
        </div>';
    }
}

if (! function_exists('editButton')) {
    function editButton($route, $title = '')
    {
        $title = $title ?: helperTrans('admin.edit');

        return '<a href="#" class="editButton btn btn-sm btn-info btn-glass-action btn-glass-edit m-1" data-bs-toggle="modal" data-bs-target="#createModal" model-route="' . $route . '" model-title="' . $title . '" title="' . $title . '">
                    <i class="ph-duotone ph-pencil-simple"></i>
                </a>';
    }
}

if (! function_exists('showButton')) {
    function showButton($route, $title = '')
    {
        $title = $title ?: helperTrans('admin.view');

        return '<a href="#" class="editButton btn btn-sm btn-primary btn-glass-action btn-glass-show m-1" data-bs-toggle="modal" data-bs-target="#createModal" model-route="' . $route . '" model-title="' . $title . '" title="' . $title . '">
                    <i class="ph-duotone ph-eye"></i>
                </a>';
    }
}

if (! function_exists('deleteButton')) {
    function deleteButton($route, $title = '')
    {
        $title = $title ?: helperTrans('admin.delete');

        return '<a href="#" class="deleteButton btn btn-sm btn-danger btn-glass-action btn-glass-delete m-1" delete-route="' . $route . '" title="' . $title . '">
                    <i class="ph-duotone ph-trash"></i>
                </a>';
    }
}

if (! function_exists('approveButton')) {
    function approveButton($route, $title = '', $extraClasses = '', $extraAttrs = '')
    {
        $title = $title ?: helperTrans('admin.approve');

        return '<button type="button" class="btn btn-sm btn-glass-action btn-glass-approve ' . $extraClasses . ' m-1" title="' . $title . '" data-route="' . $route . '" ' . $extraAttrs . '>
                    <i class="ph-duotone ph-check"></i>
                </button>';
    }
}

if (! function_exists('settingsButton')) {
    function settingsButton($route, $title = '')
    {
        $title = $title ?: helperTrans('admin.settings');

        return '<a href="#" class="editButton btn btn-sm btn-glass-action btn-glass-settings m-1" data-bs-toggle="modal" data-bs-target="#createModal" model-route="' . $route . '" model-title="' . $title . '" title="' . $title . '">
                    <i class="ph-duotone ph-toggle-right"></i>
                </a>';
    }
}

if (! function_exists('rejectButton')) {
    function rejectButton($route, $title = '', $modal = false, $extraClasses = '', $extraAttrs = '')
    {
        $title = $title ?: helperTrans('admin.reject');

        if ($modal) {
            return '<a href="#" class="editButton btn btn-sm btn-glass-action btn-glass-reject ' . $extraClasses . ' m-1" title="' . $title . '" data-bs-toggle="modal" data-bs-target="#createModal" model-route="' . $route . '" model-title="' . $title . '" ' . $extraAttrs . '>
                        <i class="ph-duotone ph-x"></i>
                    </a>';
        }

        return '<button type="button" class="btn btn-sm btn-glass-action btn-glass-reject ' . $extraClasses . ' m-1" title="' . $title . '" data-route="' . $route . '" ' . $extraAttrs . '>
                    <i class="ph-duotone ph-x"></i>
                </button>';
    }
}

if (! function_exists('getImgTag')) {
    function getImgTag($fileFullPath, $width = '40px', $height = '40px')
    {
        return '<img src="' . get_file($fileFullPath, 'logo') . '" class="img-fluid" style="max-height: 60px;cursor:pointer;" onclick="window.open(this.src, \'_blank\')">';
    }
}

if (! function_exists('renderStarRating')) {
    function renderStarRating($rate)
    {
        $rate = max(0, min(5, (float) $rate));
        $html = '<div class="d-flex align-items-center gap-1">';

        for ($i = 1; $i <= 5; $i++) {
            $icon = $rate >= $i ? 'fas fa-star' : ($rate >= $i - 0.5 ? 'fas fa-star-half-stroke' : 'far fa-star');
            $html .= '<i class="' . $icon . '" style="color:#f5a623;font-size:14px;"></i>';
        }

        $html .= '<span class="ms-1">' . number_format($rate, 1) . '</span></div>';

        return $html;
    }
}

if (! function_exists('uploadFile')) {
    function uploadFile($file, $folder)
    {
        if (! $file) {
            return null;
        }
        if (is_string($file)) {
            return $file;
        }
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($folder, $filename, 'public');

        return $folder . '/' . $filename;
    }
}

if (! function_exists('deleteFile')) {
    function deleteFile($fileFullPath)
    {
        $deletePath = storage_path('');
        $deletePath .= '/app/public/' . $fileFullPath;

        return File::delete($deletePath);
    }
}

// ----------------------------------------------------------------------------
if (! function_exists('jsonSuccess')) {

    function jsonSuccess($data = null, $msg = null, $code = 200)
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        if ($data && is_object($data)) {
            request('up_id') ? $data->up_id = request('up_id') : null;
            request('up_name_id') ? $data->up_name_id = request('up_name_id') : null;
            $code = request('up_id') ? 202 : $code;
        } elseif ($data && is_array($data)) {
            request('up_id') ? $data['up_id'] = request('up_id') : null;
            request('up_name_id') ? $data['up_name_id'] = request('up_name_id') : null;
            $code = request('up_id') ? 202 : $code;
        }

        $msg = ($msg == null) ? __('auth.done successfully') : $msg;

        return response()->json([
            'code' => $code,
            'data' => $data,
            'message' => $msg,
        ], 200);
    }
}
// ----------------------------------------------------------------------------
/**
 * @param  $data
 * @param  $msg
 * @param  $code
 * @return JsonResponse
 */
if (! function_exists('jsonValid')) {

    function jsonValid($data, $msg = '', $code = 422)
    {
        return response()->json([
            'code' => $code,
            'data' => $data,
            'message' => $msg,
        ], $code);
    }
}


if (! function_exists('banButton')) {
    function banButton(string $type, int $id, bool $isBanned): string
    {
        if ($isBanned) {
            $url   = route('admin.users.unban', ['type' => $type, 'id' => $id]);
            $title = helperTrans('admin.unban_user') ?: 'رفع الحظر';
            return '<button type="button"
                        class="btn btn-sm btn-glass-action m-1 ban-action-btn"
                        style="background:rgba(34,197,94,0.12);color:#16a34a;border:1.5px solid rgba(34,197,94,0.25);"
                        title="' . $title . '"
                        data-url="' . $url . '"
                        data-action="unban"
                        data-confirm="' . (helperTrans('admin.confirm_unban_user') ?: 'هل أنت متأكد من رفع الحظر؟') . '"
                        data-bs-toggle="tooltip">
                        <i class="ph-duotone ph-check-circle"></i>
                    </button>';
        }

        $url   = route('admin.users.ban', ['type' => $type, 'id' => $id]);
        $title = helperTrans('admin.ban_user') ?: 'حظر';
        return '<button type="button"
                    class="btn btn-sm btn-glass-action btn-glass-reject m-1 ban-action-btn"
                    title="' . $title . '"
                    data-url="' . $url . '"
                    data-action="ban"
                    data-confirm="' . (helperTrans('admin.confirm_ban_user') ?: 'هل أنت متأكد من حظر هذا المستخدم؟') . '"
                    data-bs-toggle="tooltip">
                    <i class="ph-duotone ph-prohibit"></i>
                </button>';
    }
}
