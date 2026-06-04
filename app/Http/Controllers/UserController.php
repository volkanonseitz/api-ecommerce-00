<?php

namespace App\Http\Controllers;

use App\DTO\UserData;
use App\Enums\Permission;
use App\Enums\Role;
use App\Events\ProcessUserData;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Mail\ContactAdmin;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\PasswordService;
use App\Services\UserService;
use App\Services\WalletService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private UserService $userService;

    private AuthService $authService;

    private PasswordService $passwordService;

    private OtpService $otpService;

    private WalletService $walletService;

    public function __construct(
        UserService $userService,
        AuthService $authService,
        PasswordService $passwordService,
        OtpService $otpService,
        WalletService $walletService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->passwordService = $passwordService;
        $this->otpService = $otpService;
        $this->walletService = $walletService;
    }

    // ==================== EMAIL VERIFICATION ====================
    public function verifyEmail($id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        $dashboardUrl = config('shop.dashboard_url');
        $shopUrl = config('shop.shop_url');
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) || $user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return Redirect::away($dashboardUrl);
        }

        return Redirect::away($shopUrl);
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthorizationException(
                config('notice.NOT_AUTHORIZED')
            );
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verification link sent on your email id',
            'success' => true,
        ]);
    }

    // ==================== USER LISTS ====================
    public function admins(Request $request)
    {
        $limit = $request->limit ?? 15;
        $admins = User::with(['profile', 'address'])
            ->where('is_active', true)
            ->whereHas('permissions', fn ($q) => $q->where('name', Permission::SUPER_ADMIN->value))
            ->paginate($limit);

        return $admins;
    }

    public function vendors(Request $request)
    {
        $limit = $request->limit ?? 15;

        return $this->fetchVendors($request)->paginate($limit);
    }

    public function fetchVendors(Request $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        $exclude = is_numeric($request->exclude) ? (int) $request->exclude : null;
        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        $adminIds = User::whereHas('permissions', fn ($q) => $q->where('name', Permission::SUPER_ADMIN->value))->pluck('id')->toArray();

        if ($this->userService->hasPermission($user, $shopId)) {
            return User::whereHas('permissions', fn ($q) => $q->where('name', Permission::STORE_OWNER->value))
                ->where('is_active', $isActive)
                ->whereNotIn('id', $adminIds)
                ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude));
        }

        return User::where('id', -1); // empty
    }

    public function customers(Request $request)
    {
        $limit = $request->limit ?? 15;
        $excludeIds = User::whereHas('permissions', function ($q) {
            $q->whereIn('name', [Permission::SUPER_ADMIN->value, Permission::STORE_OWNER->value, Permission::STAFF->value]);
        })->pluck('id')->toArray();

        return User::with(['profile', 'address'])
            ->whereHas('permissions', fn ($q) => $q->where('name', Permission::CUSTOMER->value))
            ->whereNotIn('id', $excludeIds)
            ->paginate($limit);
    }

    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;

        return User::with(['profile', 'address'])->paginate($limit);
    }

    // ==================== CRUD ====================
    public function store(UserCreateRequest $request)
    {
        try {
            $data = UserData::fromRequest($request->validated());
            $user = $this->userService->createUser($data);

            return $user;
        } catch (\Exception $e) {
            throw new \Exception(config('notice.NOT_FOUND'));
        }
    }

    public function show($id)
    {
        try {
            return User::with(['profile', 'address', 'shops', 'managed_shop'])->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception(config('notice.NOT_FOUND'));
        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = null;
        if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            $user = User::findOrFail($id);
        } elseif ($request->user()->id == $id) {
            $user = $request->user();
        }
        if (! $user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = UserData::fromRequest($request->validated());

        return $this->userService->updateUser($user, $data);
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            throw new \Exception(config('notice.NOT_FOUND'));
        }
    }

    // ==================== PROFILE ====================
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
            }

            return $user->load(['profile', 'wallet', 'address', 'shops.balance', 'managed_shop.balance'])->loadLastOrder();
        } catch (\Exception $e) {
            throw new \Exception(config('notice.NOT_AUTHORIZED'));
        }
    }

    // ==================== AUTH ====================
    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $appValid = true; // lisensi dihapus
        $result = $this->authService->attemptLogin($request->email, $request->password, $appValid);
        if (! $result) {
            return response()->json(['token' => null, 'permissions' => []]);
        }
        event(new ProcessUserData);

        return [
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'email_verified' => $result['email_verified'],
            'role' => $result['role'],
        ];
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $this->authService->logout($user);
        }

        return response()->json(true);
    }

    public function register(UserCreateRequest $request)
    {
        $notAllowed = [Permission::SUPER_ADMIN->value];
        $permissionInput = data_get($request, 'permission.value') ?? $request->permission;
        if ($permissionInput && in_array($permissionInput, $notAllowed)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $payload = $request->validated();

        $payload['permission'] = $permissionInput === Permission::STORE_OWNER->value ? Permission::STORE_OWNER->value : null;

        $data = UserData::fromRequest($payload);

        $settings = Settings::getData();
        $mustVerify = data_get($settings, 'options.useMustVerifyEmail', true); // false untuk noaktifkan kirim email

        $result = $this->authService->register($data, $mustVerify);
        $this->userService->giveSignupPoints($result['user']->id);

        return [
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'role' => $result['role'],
        ];
    }

    // ==================== BAN/ACTIVE ====================
    public function banUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value) && $user->id != $request->id) {
                $banUser = User::find($request->id);
                if (! $banUser) {
                    throw new \Exception('User not found');
                }
                $banUser->is_active = false;
                $banUser->save();
                $this->inactiveUserShops($banUser->id);

                return $banUser;
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        } catch (\Exception $th) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    private function inactiveUserShops($userId)
    {
        $shops = Shop::where('owner_id', $userId)->get();
        foreach ($shops as $shop) {
            $shop->is_active = false;
            $shop->save();
            Product::where('shop_id', $shop->id)->update(['status' => 'draft']);
        }
    }

    public function activeUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value) && $user->id != $request->id) {
                $activeUser = User::find($request->id);
                if (! $activeUser) {
                    throw new \Exception('User not found');
                }
                $activeUser->is_active = true;
                $activeUser->save();

                return $activeUser;
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        } catch (\Exception $th) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    // ==================== PASSWORD ====================
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $result = $this->passwordService->forgetPassword($request->email);

        return response()->json($result);
    }

    public function verifyForgetPasswordToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);
        $result = $this->passwordService->verifyToken($request->email, $request->token);

        return response()->json($result);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string',
        ]);
        $result = $this->passwordService->resetPassword($request->email, $request->token, $request->password);
        if (! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();
            $result = $this->passwordService->changePassword($user, $request->oldPassword, $request->newPassword);

            return response()->json($result);
        } catch (\Exception $th) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    // ==================== CONTACT ADMIN ====================
    public function contactAdmin(Request $request)
    {
        try {
            $admins = $this->userService->getAdminUsers();
            $adminEmails = $admins->pluck('email')->toArray();
            $details = $request->only('subject', 'name', 'email', 'description');
            $emailTo = $request->emailTo ?? $adminEmails;
            Mail::to($emailTo)->send(new ContactAdmin($details));

            return ['message' => config('notice.EMAIL_SENT_SUCCESSFUL'), 'success' => true];
        } catch (\Exception $e) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    // ==================== STAFF ====================
    public function fetchStaff(Request $request)
    {
        try {
            if (! $request->shop_id) {
                throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
            }
            if ($this->userService->hasPermission($request->user(), $request->shop_id)) {
                return User::with(['profile'])->where('shop_id', $request->shop_id);
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        } catch (\Exception $e) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    public function staffs(Request $request)
    {
        $query = $this->fetchStaff($request);
        $limit = $request->limit ?? 15;

        return $query->paginate($limit);
    }

    public function myStaffs(Request $request)
    {
        $limit = $request->limit ?? 15;

        return $this->fetchMyStaffs($request)->paginate($limit);
    }

    public function fetchMyStaffs(Request $request)
    {
        $user = $request->user();
        if ($this->userService->hasPermission($user, $request->shop_id)) {
            return User::whereHas('managed_shop', fn ($q) => $q->where('owner_id', $user->id));
        }

        return User::whereHas('managed_shop', fn ($q) => $q->where('owner_id', null));
    }

    public function allStaffs(Request $request)
    {
        $user = $request->user();
        $limit = $request->limit ?? 15;
        if ($this->userService->hasPermission($user)) {
            return User::whereHas('permissions', fn ($q) => $q->where('name', Permission::STAFF->value))->paginate($limit);
        }

        return User::where('id', -1)->paginate($limit);
    }

    // ==================== SOCIAL LOGIN ====================
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'access_token' => 'required|string',
        ]);
        $result = $this->authService->socialLogin($request->provider, $request->access_token);
        event(new ProcessUserData);

        return [
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'role' => $result['role'],
        ];
    }

    // ==================== OTP ====================
    protected function verifyOtp(Request $request): bool
    {
        $id = $request->otp_id;
        $code = $request->code;
        $phoneNumber = $request->phone_number;
        try {
            $result = $this->otpService->checkVerification($id, $code, $phoneNumber);

            return $result->isValid();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function sendOtpCode(Request $request)
    {
        $phoneNumber = $request->phone_number;
        try {
            if (empty($phoneNumber)) {
                return ['message' => config('shop.app_notice_domain').'ERROR.EMPTY_MOBILE_NUMBER', 'success' => false];
            }
            $result = $this->otpService->startVerification($phoneNumber);
            if (! $result->isValid()) {
                return ['message' => config('notice.OTP_SEND_FAIL'), 'success' => false];
            }
            $profile = Profile::where('contact', $phoneNumber)->first();

            return [
                'message' => config('notice.OTP_SEND_SUCCESSFUL'),
                'success' => true,
                'provider' => config('auth.active_otp_gateway', 'twilio'),
                'id' => $result->getId(),
                'phone_number' => $phoneNumber,
                'is_contact_exist' => $profile ? true : false,
            ];
        } catch (\Exception $e) {
            throw new \Exception(config('notice.INVALID_GATEWAY'));
        }
    }

    public function verifyOtpCode(Request $request)
    {
        try {
            if ($this->verifyOtp($request)) {
                return [
                    'message' => config('notice.OTP_SEND_SUCCESSFUL'),
                    'success' => true,
                ];
            }
            throw new \Exception(config('notice.OTP_VERIFICATION_FAILED'));
        } catch (\Throwable $e) {
            throw new \Exception(config('notice.OTP_VERIFICATION_FAILED'));
        }
    }

    public function otpLogin(Request $request)
    {
        $phoneNumber = $request->phone_number;
        try {
            if ($this->verifyOtp($request)) {
                $profile = Profile::where('contact', $phoneNumber)->first();
                $user = null;
                if (! $profile) {
                    $name = $request->name;
                    $email = $request->email;
                    if ($name && $email) {
                        $userExist = User::where('email', $email)->exists();
                        $user = User::firstOrCreate(
                            ['email' => $email],
                            ['name' => $name]
                        );
                        $user->givePermissionTo(Permission::CUSTOMER->value);
                        $user->assignRole(Role::CUSTOMER->value);
                        $user->profile()->updateOrCreate(
                            ['customer_id' => $user->id],
                            ['contact' => $phoneNumber]
                        );
                        if (! $userExist) {
                            $this->userService->giveSignupPoints($user->id);
                        }
                    } else {
                        return ['message' => config('notice.REQUIRED_INFO_MISSING'), 'success' => false];
                    }
                } else {
                    $user = User::where('id', $profile->customer_id)->first();
                }
                if (! $user) {
                    return ['message' => config('notice.NOT_FOUND'), 'success' => false];
                }
                event(new ProcessUserData);

                return [
                    'token' => $user->createToken('auth_token')->plainTextToken,
                    'permissions' => $user->getPermissionNames(),
                    'role' => $user->getRoleNames()->first(),
                ];
            }

            return ['message' => config('notice.OTP_VERIFICATION_FAILED'), 'success' => false];
        } catch (\Throwable $e) {
            return response()->json(['error' => config('notice.INVALID_GATEWAY')], 422);
        }
    }

    public function updateContact(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp_id' => 'required',
            'code' => 'required',
        ]);

        $user = $request->user();

        if (! $user) {
            throw new AuthorizationException(
                config('notice.NOT_AUTHORIZED')
            );
        }

        if (! $this->verifyOtp($request)) {
            return [
                'message' => config('notice.CONTACT_UPDATE_FAILED'),
                'success' => false,
            ];
        }

        $user->profile()->updateOrCreate(
            ['customer_id' => $user->id],
            ['contact' => $request->phone_number]
        );

        return [
            'message' => config('notice.CONTACT_UPDATE_SUCCESSFUL'),
            'success' => true,
        ];
    }

    // ==================== WALLET & POINTS ====================
    public function addPoints(Request $request)
    {
        $user = $request->user();

        if (
            ! $user ||
            ! $user->hasPermissionTo(
                Permission::SUPER_ADMIN->value
            )
        ) {
            throw new AuthorizationException(
                config('notice.NOT_AUTHORIZED')
            );
        }

        $request->validate([
            'points' => 'required|numeric',
            'customer_id' => 'required|exists:users,id',
        ]);

        $this->walletService->addPoints(
            $request->customer_id,
            (int) $request->points
        );

        return response()->json([
            'success' => true,
        ]);
    }

    // ==================== PERMISSIONS ====================
    public function makeOrRevokeAdmin(Request $request)
    {
        $user = $request->user();
        if ($this->userService->hasPermission($user)) {
            $targetId = $request->user_id;
            try {
                $targetUser = User::findOrFail($targetId);
                if ($targetUser->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                    $targetUser->revokePermissionTo(Permission::SUPER_ADMIN->value);
                    $targetUser->removeRole(Role::SUPER_ADMIN->value);

                    return response()->json(true);
                } else {
                    $targetUser->givePermissionTo(Permission::SUPER_ADMIN->value);
                    $targetUser->assignRole(Role::SUPER_ADMIN->value);
                    Cache::forget('cached_admin');

                    return response()->json(true);
                }
            } catch (\Exception $e) {
                throw new \Exception(config('notice.USER_NOT_FOUND'));
            }
        }
        throw new \Exception(config('notice.NOT_AUTHORIZED'));
    }

    // ==================== NEWSLETTER ====================
    public function subscribeToNewsletter(Request $request)
    {
        try {
            $email = $request->email;

            // Newsletter::subscribeOrUpdate($email); // optional, jika paket Spatie Newsletter diinstall
            return response()->json(true);
        } catch (\Exception $th) {
            throw new \Exception(config('notice.SOMETHING_WENT_WRONG'));
        }
    }

    // ==================== UPDATE EMAIL ====================
    public function updateUserEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $this->userService->updateEmail($request->user(), $request->email);
    }

    // ==================== FETCH BY PERMISSION ====================
    public function fetchUsersByPermission(Request $request)
    {
        $user = $request->user();
        $permission = strtolower($request->permission ?? '');
        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        $query = User::where('is_active', $isActive);

        if (! $this->userService->hasPermission($user, $request->shop_id)) {
            return $query->where('id', -1);
        }

        switch ($permission) {
            case Permission::SUPER_ADMIN->value:
                $query->whereHas('permissions', fn ($q) => $q->where('name', Permission::SUPER_ADMIN->value));
                break;
            case Permission::STORE_OWNER->value:
                $excludeUsers = User::whereHas('permissions', fn ($q) => $q->where('name', Permission::SUPER_ADMIN->value))->pluck('id')->toArray();
                if ($request->exclude) {
                    $excludeUsers[] = $request->exclude;
                }
                $query->whereHas('permissions', fn ($q) => $q->where('name', Permission::STORE_OWNER->value))
                    ->whereNotIn('id', $excludeUsers);
                break;
            case Permission::STAFF->value:
                $query->whereHas('permissions', fn ($q) => $q->where('name', Permission::STAFF->value));
                break;
            case Permission::CUSTOMER->value:
                $excludeUsers = User::whereHas('permissions', function ($q) {
                    $q->whereIn('name', [Permission::SUPER_ADMIN->value, Permission::STORE_OWNER->value, Permission::STAFF->value]);
                })->pluck('id')->toArray();
                $query->whereHas('permissions', fn ($q) => $q->where('name', Permission::CUSTOMER->value))
                    ->whereNotIn('id', $excludeUsers);
                break;
            default:
                $query->where('id', -1);
                break;
        }

        return $query;
    }
}
