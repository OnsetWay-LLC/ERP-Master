<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {

            // تحقق: يسمح بالتسجيل لأول مستخدم فقط
            if (User::query()->exists()) {
                abort(403, 'Registration is allowed only for the first system admin.');
            }

            // إنشاء أول مستخدم بالنظام بدون Employee
            $user = User::create([
                'employee_id' => null,
                'name' => $data['full_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
                'failed_attempts' => 0,
                'locked_until' => null,
                'is_initial_admin' => true,
            ]);

            // إعطاء دور CFO
            $user->assignRole('CFO');

            // إرسال رسالة تفعيل البريد الإلكتروني
            $user->sendEmailVerificationNotification();

            return $user->fresh();
        });
    }
}