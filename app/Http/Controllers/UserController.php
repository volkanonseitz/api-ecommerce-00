<?php

namespace App\Http\Controllers;

use App\DTO\UserData;
use App\Enums\Permission;
use App\Enums\Role;
use App\Events\ProcessUserData;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SocialLoginRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\ContactAdmin;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuthService;
use App\Services\Otp\OtpService;
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

class UserController extends BaseController
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
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(null, 'Email verification link sent on your email id');
    }

    // ==================== USER LISTS ====================
    public function admins(Request $request)
    {
        $limit = $request->limit ?? 15;
        $admins = User::with(['profile', 'address'])
            ->where('is_active', true)
            ->whereHas('permissions', fn ($q) => $q->where('name', Permission::SUPER_ADMIN->value))
            ->paginate($limit);

        return $this->sendPaginated(
            $admins,
            UserResource::collection($admins->getCollection()),
            'Daftar admin berhasil diambil.'
        );
    }

    public function vendors(Request $request)
    {
        $limit = $request->limit ?? 15;
        $vendors = $this->fetchVendors($request)->paginate($limit);

        return $this->sendPaginated(
            $vendors,
            UserResource::collection($vendors->getCollection()),
            'Daftar vendor berhasil diambil.'
        );
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

        $customers = User::with(['profile', 'address'])
            ->whereHas('permissions', fn ($q) => $q->where('name', Permission::CUSTOMER->value))
            ->whereNotIn('id', $excludeIds)
            ->paginate($limit);

        return $this->sendPaginated(
            $customers,
            UserResource::collection($customers->getCollection()),
            'Daftar laporan berhasil diambil.'
        );
    }

    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        // Hanya super admin yang bisa melihat semua user? Sesuai asumsi, kita batasi.
        // Tapi di kode asli tidak dibatasi, kita tambahkan pengecekan.
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $users = User::with(['profile', 'address'])->paginate($limit);

        return $this->sendPaginated(
            $users,
            UserResource::collection($users->getCollection()),
            'Daftar user berhasil diambil.'
        );
    }

    // ==================== CRUD ====================
    public function store(UserCreateRequest $request)
    {
        try {
            $data = UserData::fromRequest($request->validated());
            $user = $this->userService->createUser($data);

            return $this->sendSuccess($user, 'User created', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with(['profile', 'address', 'shops', 'managed_shop'])->findOrFail($id);

            return $this->sendSuccess($user, 'User detail');
        } catch (\Exception $e) {
            return $this->sendError('User not found', 404);
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
        $updated = $this->userService->updateUser($user, $data);

        return $this->sendSuccess($updated, 'User updated');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return $this->sendSuccess(null, 'User deleted');
        } catch (\Exception $e) {
            return $this->sendError('User not found', 404);
        }
    }

    // ==================== PROFILE ====================
    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $user->load(['profile', 'wallet', 'address', 'shops.balance', 'managed_shop.balance'])->loadLastOrder();

        return $this->sendSuccess($user, 'Profile data');
    }

    // ==================== AUTH ====================
    public function token(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->attemptLogin(
            $request->email,
            $request->password,
            $request,
            true
        );

        if (! $result) {
            return $this->sendError('Email atau password tidak valid.', 401);
        }

        if (! empty($result['locked'])) {
            return $this->sendError('Akun dikunci sementara.', 423, [
                'locked_until' => $result['locked_until'],
            ]);
        }

        if (! ($result['email_verified'] ?? true)) {
            return $this->sendError('Silakan verifikasi email Anda terlebih dahulu.', 403);
        }

        event(new ProcessUserData);

        return $this->sendSuccess([
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'email_verified' => true,
            'role' => $result['role'],
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $this->authService->logout($user);
        }

        return $this->sendSuccess(true, 'Logged out');
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
        $mustVerify = data_get($settings, 'options.useMustVerifyEmail', true);

        $result = $this->authService->register($data, $mustVerify);
        $this->userService->giveSignupPoints($result['user']->id);

        return $this->sendSuccess([
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'role' => $result['role'],
        ], 'Registration successful', 201);
    }

    // ==================== BAN/ACTIVE ====================
    public function banUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value) && $user->id != $request->id) {
                $banUser = User::find($request->id);
                if (! $banUser) {
                    return $this->sendError('User not found', 404);
                }
                $banUser->is_active = false;
                $banUser->save();
                $this->inactiveUserShops($banUser->id);

                return $this->sendSuccess($banUser, 'User banned');
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        } catch (\Exception $th) {
            return $this->sendError($th->getMessage(), 400);
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
                    return $this->sendError('User not found', 404);
                }
                $activeUser->is_active = true;
                $activeUser->save();

                return $this->sendSuccess($activeUser, 'User activated');
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        } catch (\Exception $th) {
            return $this->sendError($th->getMessage(), 400);
        }
    }

    // ==================== PASSWORD ====================
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $result = $this->passwordService->forgetPassword($request->email);

        return $this->sendSuccess($result, 'Password reset link sent');
    }

    public function verifyForgetPasswordToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);
        $result = $this->passwordService->verifyToken($request->email, $request->token);

        return $this->sendSuccess($result, 'Token verification result');
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
            return $this->sendError($result['message'] ?? 'Reset failed', 400);
        }

        return $this->sendSuccess($result, 'Password reset successful');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();
            $result = $this->passwordService->changePassword($user, $request->oldPassword, $request->newPassword);

            return $this->sendSuccess($result, 'Password changed');
        } catch (\Exception $th) {
            return $this->sendError($th->getMessage(), 400);
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

            return $this->sendSuccess(null, 'Email sent successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
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
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function staffs(Request $request)
    {
        $query = $this->fetchStaff($request);
        $limit = $request->limit ?? 15;
        $staffs = $query->paginate($limit);

        return $this->sendPaginated(
            $staffs,
            UserResource::collection($staffs->getCollection()),
            'Daftar staff berhasil diambil.'
        );
    }

    public function myStaffs(Request $request)
    {
        $limit = $request->limit ?? 15;
        $staffs = $this->fetchMyStaffs($request)->paginate($limit);

        return $this->sendPaginated(
            $staffs,
            UserResource::collection($staffs->getCollection()),
            'Daftar staff saya berhasil diambil.'
        );
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
            $staffs = User::whereHas('permissions', fn ($q) => $q->where('name', Permission::STAFF->value))->paginate($limit);

            return $this->sendPaginated(
                $staffs,
                UserResource::collection($staffs->getCollection()),
                'Daftar semua staff berhasil diambil.'
            );
        }

        return $this->sendPaginated(User::where('id', -1)->paginate($limit), 'No staffs'); // {urgent cek}
    }

    // ==================== SOCIAL LOGIN ====================
    public function socialLogin(SocialLoginRequest $request)
    {
        $result = $this->authService->socialLogin(
            $request->validated('provider'),
            $request->validated('access_token')
        );

        event(new ProcessUserData);

        return $this->sendSuccess([
            'token' => $result['token'],
            'permissions' => $result['permissions'],
            'role' => $result['role'],
        ], 'Social login successful');
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
                return $this->sendError('Mobile number is required', 400);
            }
            $result = $this->otpService->startVerification($phoneNumber);
            if (! $result->isValid()) {
                return $this->sendError('OTP send failed', 400);
            }
            $profile = Profile::where('contact', $phoneNumber)->first();

            return $this->sendSuccess([
                'provider' => config('auth.active_otp_gateway', 'twilio'),
                'id' => $result->getId(),
                'phone_number' => $phoneNumber,
                'is_contact_exist' => $profile ? true : false,
            ], 'OTP sent');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function verifyOtpCode(Request $request)
    {
        try {
            if ($this->verifyOtp($request)) {
                return $this->sendSuccess(null, 'OTP verified');
            }

            return $this->sendError('OTP verification failed', 400);
        } catch (\Throwable $e) {
            return $this->sendError('OTP verification failed', 400);
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
                        return $this->sendError('Name and email are required', 400);
                    }
                } else {
                    $user = User::where('id', $profile->customer_id)->first();
                }
                if (! $user) {
                    return $this->sendError('User not found', 404);
                }
                event(new ProcessUserData);

                return $this->sendSuccess([
                    'token' => $user->createToken('auth_token')->plainTextToken,
                    'permissions' => $user->getPermissionNames(),
                    'role' => $user->getRoleNames()->first(),
                ], 'OTP login successful');
            }

            return $this->sendError('OTP verification failed', 400);
        } catch (\Throwable $e) {
            return $this->sendError('OTP login failed', 422);
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
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        if (! $this->verifyOtp($request)) {
            return $this->sendError('OTP verification failed', 400);
        }

        $user->profile()->updateOrCreate(
            ['customer_id' => $user->id],
            ['contact' => $request->phone_number]
        );

        return $this->sendSuccess(null, 'Contact updated');
    }

    // ==================== WALLET & POINTS ====================
    public function addPoints(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $request->validate([
            'points' => 'required|numeric',
            'customer_id' => 'required|exists:users,id',
        ]);

        $this->walletService->addPoints($request->customer_id, (int) $request->points);

        return $this->sendSuccess(null, 'Points added');
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
                    Cache::forget('cached_admin');

                    return $this->sendSuccess(true, 'Admin revoked');
                } else {
                    $targetUser->givePermissionTo(Permission::SUPER_ADMIN->value);
                    $targetUser->assignRole(Role::SUPER_ADMIN->value);
                    Cache::forget('cached_admin');

                    return $this->sendSuccess(true, 'Admin granted');
                }
            } catch (\Exception $e) {
                return $this->sendError('User not found', 404);
            }
        }

        return $this->sendError('Unauthorized', 403);
    }

    // ==================== NEWSLETTER ====================
    public function subscribeToNewsletter(Request $request)
    {
        try {
            $email = $request->email;

            // Newsletter::subscribeOrUpdate($email);
            return $this->sendSuccess(true, 'Subscribed');
        } catch (\Exception $th) {
            return $this->sendError($th->getMessage(), 500);
        }
    }

    // ==================== UPDATE EMAIL ====================
    public function updateUserEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation failed', 422, $validator->errors());
        }

        $result = $this->userService->updateEmail($request->user(), $request->email);

        return $this->sendSuccess($result, 'Email updated');
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

        return $this->sendSuccess($query->get(), 'Users fetched by permission');
    }
}
