<?php

use App\Enums\AdminTypeEnum;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Random\RandomException;

/**
 * @param  $data
 * @param  $msg
 * @param  $code
 * @return JsonResponse
 */
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
        ]);
    }
}

if (! function_exists('jsonApiValid')) {
    function jsonApiValid($data, $msg = '', $code = 422)
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $errors = $data->toArray();
        } elseif (is_array($data)) {
            $errors = $data;
        } else {
            $errors = ['general' => [(string) $data]];
        }

        $messages = collect($errors)->flatten()->filter()->values()->all();

        return response()->json([
            'code' => $code,
            'message' => $msg !== '' ? $msg : ($messages[0] ?? __('messages.validation_failed')),
        ], $code);
    }
}


if (! function_exists('jsonApiValidData')) {
    function jsonApiValidData($data, $msg = '', $code = 422)
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $errors = $data->toArray();
        } elseif (is_array($data)) {
            $errors = $data;
        } else {
            $errors = ['general' => [(string) $data]];
        }

        $messages = collect($errors)->flatten()->filter()->values()->all();

        return response()->json([
            'code' => $code,
            'data' => $errors,
            'message' => $msg !== '' ? $msg : ($messages[0] ?? ''),
        ]);
    }
}

/**
 * @param  $route
 * @param  $text
 * @param  $class
 * @return string
 */

/**
 * @param  $length
 * @return string
 *
 * @throws RandomException
 */
if (! function_exists('getRandomStringRandomInt')) {
    function getRandomStringRandomInt($length = 16)
    {
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = '';
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $min = ($i == 0) ? 1 : 0;
            $pieces .= $stringSpace[random_int($min, $max)];
        }

        return $pieces;
    }
}

/**
 * @param  $str
 * @return array|string|Translator|Application|null
 */
if (! function_exists('helperTrans')) {
    function helperTrans($str): array|string|Translator|Application|null
    {

        $arrayOfKeys = explode('.', $str);
        $file = $arrayOfKeys[0] ?? 'file';
        $key = $arrayOfKeys[1] ?? '';

        $local = get_api_lang();

        try {
            $lang_array = include resource_path("lang/$local/$file.php");

            $processed_key = ucfirst(str_replace('_', ' ', remove_invalid_characters($key)));

            if (! array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = '<?php return ' . var_export($lang_array, true) . ';';
                file_put_contents(resource_path("lang/$local/$file.php"), $str);
                $result = $processed_key;
            } else {
                $result = __("$file.$key");
            }
        } catch (Exception $exception) {
            $result = __("$file.$key");
        }

        return $result;
    }
}

function get_api_lang(): array|string
{
    return request()->header('lang') ?? 'ar';
}

function remove_invalid_characters($str): array|string
{
    return str_ireplace(['\'', '"', ',', ';', '<', '>'], ' ', $str);
}

/**
 * @param  $request
 * @param  $sql
 * @param  $resource
 * @param  $message
 * @return JsonResponse
 */
if (!function_exists('generalReturn')) {
    function generalReturn($request, $sql, $resource, $message = "success", $extra = [])
    {
        $jsonMsg = ["message" => $message, "code" => 200];
        $pagination_status = ($request->pagination) ? $request->pagination : "off";
        $limit_per_page = ($request->limit_per_page) ? $request->limit_per_page : "10";

        if ($pagination_status == 'on') {
            $data = $sql->paginate($limit_per_page);
            $json = array_merge($jsonMsg, $extra, ["data" => $resource::collection($data)]);
            $json = array_merge($json, collect($data)->except(["data", 'links'])->toArray());
            return response()->json($json, 200);
        }

        $json = array_merge($jsonMsg, $extra, ["data" => $resource::collection($sql->get())]);
        return response()->json($json, 200);
    }
}


if (!function_exists('price2')) {
    function price2($value)
    {
        return round((float)$value, 2);
    }
}

if (! function_exists('getAuthUser')) {
    function getAuthUser()
    {
        $user = Auth::user();
        if ($user == null) {
            $user = Auth::guard('admin')->user();
        }

        return $user;
    }
}

function showUserImage($fileFullPath)
{
    if ($fileFullPath && file_exists(public_path('/storage/' . $fileFullPath))) {
        return asset('/storage/' . $fileFullPath);
    }

    return asset(asset('/default/default-arab.svg'));
}

if (! function_exists('getRouteNameFromUrl')) {
    function getRouteNameFromUrl($url, $method = 'GET')
    {
        // // Create a request instance with the URL and method
        // $request = Illuminate\Http\Request::create($url, $method);

        // // Get all routes
        // $routes = Illuminate\Support\Facades\Route::getRoutes();

        // foreach ($routes as $route) {
        //     // Check if the route matches the request method and URL
        //     if ($route->matches($request)) {
        //         return $route->getName();
        //     }
        // }

        // return null;

        $route = app('router')->getRoutes()->match(app('request')->create($url, $method));

        return $route->getName();
    }
}

if (! function_exists('getRouteNameFromRoute')) {
    function getRouteNameFromRoute($route)
    {
        return explode('.', getRouteNameFromUrl($route))[0];
    }
}

if (! function_exists('checkIfHasPermission')) {
    function checkIfHasPermission($route, $method = 'GET')
    {
        $user = auth('admin')->user();

        if (! $user) {
            return false;
        }

        if ($user->admin_type == AdminTypeEnum::Developer->value) {
            return true;
        }

        if (in_array($route, getUserPermissions())) {
            return true;
        }

        return false;
    }
}

if (! function_exists('checkIfHasMultiplePermission')) {
    function checkIfHasMultiplePermission(array $routes)
    {
        if (auth('admin')->user()->admin_type == AdminTypeEnum::Developer->value) {
            return true;
        }

        foreach ($routes as $route) {
            if (checkIfHasPermission($route) == true) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getRandomInt')) {
    function getRandomInt($length = 8)
    {
        $stringSpace = '0123456789';
        $pieces = "";
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $min = ($i == 0) ? 1 : 0;
            $pieces .= $stringSpace[random_int($min, $max)];
        }
        return $pieces;
    }
}

if (! function_exists('getUserPermissions')) {
    function getUserPermissions()
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            return [];
        }

        // Collect permissions from all assigned roles and direct permissions.
        return $admin->roles
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->merge($admin->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();
    }
}

if (! function_exists('getByUserPermissions')) {
    function getByUserPermissions($user)
    {
        return @$user->permissions->pluck('name')->toArray();
    }
}

if (! function_exists('checkInputTypeNumber')) {
    function checkInputTypeNumber($input)
    {
        // Remove all whitespace from the input
        $cleaned_input = preg_replace('/\s+/', '', $input);

        // Define regex patterns
        $phone_pattern = '/^(\+?\d{1,4})?\d{10}$/'; // Handles +96601211975158 or 012111273155
        $email_pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        // Check if the cleaned input matches the phone pattern
        if (preg_match($phone_pattern, $cleaned_input)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('exceptedData')) {
    function exceptedData($data)
    {
        $excepted = ['_method', '_token'];
        foreach ($data as $key => $value) {
            if (($value == null || $value == '')) {
                $excepted[] = $key;
            }
        }

        return $excepted;
    }
}

// Fixed function to get all children route names recursively
function getAllChildrensRouteNames(array $childrens)
{
    $routesNames = [];
    foreach ($childrens as $child => $data) {
        if (isset($data['childrens']) && count($data['childrens']) > 0) {
            // Recursively get nested children and merge with current results
            $nestedRoutes = getAllChildrensRouteNames($data['childrens']);
            $routesNames = array_merge($routesNames, $nestedRoutes);
        } else {
            // Only add route name if it doesn't have children
            $routesNames[] = $child;
        }
    }

    return $routesNames;
}

// Fixed function to get all children route permissions recursively
function getAllChildrensRoutePermission(array $childrens)
{
    $routesPermissions = [];
    foreach ($childrens as $child => $data) {
        if (isset($data['childrens']) && count($data['childrens']) > 0) {
            // Recursively get nested permissions and merge with current results
            $nestedPermissions = getAllChildrensRoutePermission($data['childrens']);
            $routesPermissions = array_merge($routesPermissions, $nestedPermissions);
        } else {
            // Only add permission if it doesn't have children and has permission
            if (isset($data['permission'])) {
                $routesPermissions[] = $data['permission'];
            }
        }
    }

    return $routesPermissions;
}

// Recursive function to render nested menu items
function renderNestedMenu($childrens, $parentRoute = '')
{
    foreach ($childrens as $child => $child_data) {
        // For items with children, check if user has any permission on children
        if (isset($child_data['childrens']) && count($child_data['childrens']) > 0) {
            $child_resources_names = getAllChildrensRouteNames($child_data['childrens']);
            $child_resources_permissions = getAllChildrensRoutePermission($child_data['childrens']);
            $child_group_current_route_name = explode('.', Route::currentRouteName())[0];
            $child_group_active = in_array($child_group_current_route_name, $child_resources_names);

            // Show if user has permission on any children
            if (checkIfHasMultiplePermission($child_resources_permissions) && ! isset($child_data['notVisible'])) {
                echo '<li class="collapsed">';
                echo '<a class="m-link ' . ($child_group_active ? 'active' : '') . '" data-bs-toggle="collapse" data-bs-target="#' . $child . '" href="#" aria-expanded="' . ($child_group_active ? 'true' : '') . '">';
                echo '<i class="fa ' . $child_data['icon'] . '"></i>';
                echo '<span class="ms-2">' . $child_data['label'] . '</span>';
                echo '<span class="arrow fa fa-angle-left ms-auto text-end"></span>';
                echo '</a>';
                echo '<ul class="sub-menu collapse ' . ($child_group_active ? 'show' : '') . '" id="' . $child . '">';
                renderNestedMenu($child_data['childrens'], $child);
                echo '</ul>';
                echo '</li>';
            }
        } else {
            // For items without children, check their own permission
            if (isset($child_data['permission']) && checkIfHasPermission($child_data['permission']) && ! isset($child_data['notVisible'])) {
                $active = false;
                if (isset($child_data['otherActive']) && count($child_data['otherActive'])) {
                    $other_active = $child_data['otherActive'];
                    $current_route_name = explode('.', Route::currentRouteName())[0];
                    $active = in_array($current_route_name, $other_active);
                }

                echo '<li>';
                echo '<a class="m-link ' . (Str::startsWith(Route::currentRouteName(), $child . '.') || $active ? 'active' : '') . '" href="' . route($child . '.index') . '">';
                echo '<i class="fa ' . $child_data['icon'] . '"></i>';
                echo '<span class="ms-2">' . $child_data['label'] . '</span>';
                echo '</a>';
                echo '</li>';
            }
        }
    }
}
