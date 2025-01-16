<?php

namespace App\Http\Controllers\Auth;


use App\Enums\Role;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Libraries\AppLibrary;
use App\Models\User;
use App\Services\DefaultAccessService;
use App\Services\MenuService;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Smartisan\Settings\Facades\Settings;
use App\Enums\Ask;
class LoginController extends Controller
{
    public string $token;
    public DefaultAccessService $defaultAccessService;
    public PermissionService $permissionService;
    public MenuService $menuService;

    public function __construct(
        MenuService $menuService,
        PermissionService $permissionService,
        DefaultAccessService $defaultAccessService
    ) {
        $this->menuService          = $menuService;
        $this->permissionService    = $permissionService;
        $this->defaultAccessService = $defaultAccessService;
    }

    /**
     * @throws \Exception
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors()
            ], 422);
        }

        $request->merge(['status' => Status::ACTIVE]);

        if (!Auth::guard('web')->attempt($request->only('email', 'password', 'status'))) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.credentials_invalid')]
            ], 400);
        }

        $branchId = Auth::user()->branch_id;
        if (Auth::user()->branch_id == 0) {
            $branchId = Settings::group('site')->get('site_default_branch');
        }
        $this->defaultAccessService->storeOrUpdate(['branch_id' => $branchId]);
        $user        = User::where('email', $request['email'])->first();
        if (!isset($user->roles[0])) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.role_exist')]
            ], 400);
        }
        if ($user->roles[0]->id == 2) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.permission_denied')]
            ], 400);
        }
        $this->token = $user->createToken('auth_token')->plainTextToken;


        $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
        $defaultPermission = AppLibrary::defaultPermission($permission);

        return new JsonResponse([
            'message'           => trans('all.message.login_success'),
            'token'             => $this->token,
            'branch_id'         => (int)$user->branch_id,
            'user'              => new UserResource($user),
            'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
            'permission'        => $permission,
            'defaultPermission' => $defaultPermission,
        ], 201);
    }
    public function clientLogin(Request $request): JsonResponse{
        $validator = Validator::make($request->all(), [
            'phone'    => ['nullable', 'string'],
            'email'    => ['nullable', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        $validator->after(function ($validator) use ($request) {
            if (empty($request->input('phone')) && empty($request->input('email'))) {
                $validator->errors()->add('login', trans('all.message.phone_or_email_required'));
            }
        });
        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors()
            ], 422);
        }
        $credentials = [
            $request->has('phone') ? 'phone' : 'email' => $request->input('phone') ?? $request->input('email'),
            'password'                                      => $request->input('password'),
            'status'                                        => Status::ACTIVE,
        ];
        if (!Auth::guard('client-session')->attempt($credentials)) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.credentials_invalid')]
            ], 400);
        }
        $user = Auth::guard('client-session')->user();
        if (!isset($user->roles[0])) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.role_exist')]
            ], 400);
        }

        if ($user->roles[0]->id !== 2) { // ID role khách hàng
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.permission_denied')]
            ], 400);
        }
        $branchId = $user->branch_id ?? 0;

        if ($branchId == 0) {
            $branchId = Settings::group('site')->get('site_default_branch');
        }
        $this->defaultAccessService->storeOrUpdate(['branch_id' => $branchId],'client-session');

        $this->token = $user->createToken('auth_token')->plainTextToken;
        $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
        $defaultPermission = AppLibrary::defaultPermission($permission);
        return new JsonResponse([
            'message'           => trans('all.message.login_success'),
            'token'             => $this->token,
            'branch_id'         => (int)$branchId,
            'user'              => new UserResource($user),
            'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
            'permission'        => $permission,
            'defaultPermission' => $defaultPermission,
        ], 201);
    }

    public function clientGoogleRedirect(Request $request): JsonResponse
    {
        try {

            $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();;
            return new JsonResponse([
                'redirectUrl' => $redirectUrl,
            ],201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function clientLoginGoogle(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $userData = [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'username' => Str::slug($googleUser->getName()) . time(),
                    'is_guest' => false,
                    'branch_id' => 0,
                    'email_verified_at' => now(),
                ];

                $user = User::create($userData);
                $user->assignRole(Role::CUSTOMER);
            }
            Auth::guard('client-session')->login($user);
            $token = $user->createToken('auth_token')->plainTextToken;
            if (!$user->password) {
                return redirect(env('APP_URL') . '/client/google/callback?token=' . $token . '&update_password=true');
            }
            return redirect(env('APP_URL') . '/client/google/callback?token=' . $token);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect(env('APP_URL') . '/client/google/callback?error=google_login_failed');
        }
    }
    public function clientSSOCheck(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('sanctum-client')->user();
            $token = $request['token']??"";
            if (!$user || !$token) {
                return new JsonResponse([
                    'errors' => ['validation' => trans('all.message.token_invalid')]
                ], 401);
            }
            if (!isset($user->roles[0]) || $user->roles[0]->id !== 2) {
                return new JsonResponse([
                    'errors' => ['validation' => trans('all.message.permission_denied')]
                ], 403);
            }
            $branchId = $user->branch_id ?? 0;

            if ($branchId == 0) {
                $branchId = Settings::group('site')->get('site_default_branch');
            }
            $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
            $defaultPermission = AppLibrary::defaultPermission($permission);
            return new JsonResponse([
                'message'           => trans('all.message.login_success'),
                'token'             => $token,
                'branch_id'         => (int)$branchId,
                'user'              => new UserResource($user),
                'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
                'permission'        => $permission,
                'defaultPermission' => $defaultPermission,
            ], 201);
        }catch (\Exception $e) {
            return new JsonResponse([
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }
    public function clientSSOUpdatePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return new JsonResponse([
                    'errors' => $validator->errors(),
                ], 422);
            }
            $user = Auth::guard('sanctum-client')->user();
            $token = $request['token'] ?? "";

            if (!$user || !$token) {
                return new JsonResponse([
                    'errors' => ['validation' => trans('all.message.token_invalid')],
                ], 401);
            }
            $user->password = Hash::make($request['password']);
            $user->save();
            if (!isset($user->roles[0]) || $user->roles[0]->id !== 2) {
                return new JsonResponse([
                    'errors' => ['validation' => trans('all.message.permission_denied')],
                ], 403);
            }
            $branchId = $user->branch_id ?? 0;

            if ($branchId == 0) {
                $branchId = Settings::group('site')->get('site_default_branch');
            }

            $permission = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
            $defaultPermission = AppLibrary::defaultPermission($permission);

            return new JsonResponse([
                'message' => trans('all.message.password_update_success'),
                'token' => $token,
                'branch_id' => (int)$branchId,
                'user' => new UserResource($user),
                'menu' => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
                'permission' => $permission,
                'defaultPermission' => $defaultPermission,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')->currentAccessToken()->delete();
        return new JsonResponse([
            'message' => trans('all.message.logout_success')
        ], 200);
    }
    public function clientLogout(Request $request): JsonResponse
    {
        $request->user('sanctum-client')->currentAccessToken()->delete();
        return new JsonResponse([
            'message' => trans('all.message.logout_success')
        ], 200);
    }
}
