<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\SuccessfulData;
use App\Mail\EmailVerification;
use App\Mail\PasswordReset;
use App\Models\User;
use Exception;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService extends BaseService
{
    protected $verificationCodeServices;

    protected $userServices;

    public function __construct(
        VerificationCodeServices $verificationCodeServices,
        UserServices $userServices
    ) {
        parent::__construct(User::class);
        $this->verificationCodeServices = $verificationCodeServices;
        $this->userServices = $userServices;
    }

    public function _generateVerificationCode(int $length = 6): string
    {
        $characters = '0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    public function register(array $data): object
    {
        try {
            $data['password'] = Hash::make($data['password']);

            $newUser = $this->model::create($data);

            if (! $newUser) {
                return new FailedData('Register user fail!');
            }

            $newUser->assignRole('user');

            return new SuccessfulData('Register user successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to register user!');
        }
    }

    public function sendVerificationCode(string $email): object
    {
        try {
            $verificationCode = $this->_generateVerificationCode();

            $this->verificationCodeServices->createOrUpdate($email, $verificationCode);

            Mail::to($email)->queue(new EmailVerification($verificationCode));

            return new SuccessfulData('Email verification code was sent!');
        } catch (Exception $error) {
            return new FailedData('Failed to send email verification code!');
        }
    }

    public function verify(string $email, string $verificationCode): object
    {
        try {
            $isCorrectCode = $this->verificationCodeServices->checkExists($email, $verificationCode);

            if ($isCorrectCode) {
                return new SuccessfulData('Verification code is correct!');
            } else {
                return new FailedData('Verification code is incorrect!');
            }
        } catch (Exception $error) {
            return new FailedData('Failed to verify verification code!');
        }
    }

    public function login(array $data): object
    {
        try {
            $user = $this->userServices->getUserByEmail($data['email']);

            if (! $user) {
                return new FailedData('User not found!');
            }

            if (! Hash::check($data['password'], $user->password)) {
                return new FailedData('Password is not correct!', ['password' => 'Password is not correct!']);
            }

            $token = $user->createToken(config('constants.auth_token'))->plainTextToken;
            $roles = $user->getRoleNames();

            return new SuccessfulData(
                'Login successfully!',
                [
                    'user' => $user,
                    'token' => $token,
                    'roles' => $roles,
                ]
            );
        } catch (Exception $error) {
            return new FailedData('Something went wrong when login user!');
        }
    }

    public function resetPassword(array $data): object
    {
        try {
            $user = $this->userServices->getUserByEmail($data['email']);

            if (! $user) {
                $message = 'User with this email does not exists!';
                return new FailedData($message, ['email' => $message]);
            }

            $tokenExists = app(PasswordBroker::class)->tokenExists($user, $data['token']);

            if ($tokenExists) {
                $user->password = Hash::make($data['newPassword']);
                $user->save();

                return new SuccessfulData('Password is updated!');
            } else {
                return new FailedData('Token is invalid!');
            }
        } catch (Exception $error) {
            return new FailedData('Failed to reset password!');
        }
    }

    public function forgetPassword(string $email): object
    {
        try {
            if (! $this->userServices->checkExistsByEmail($email)) {
                return new FailedData('User with this email does not exist!');
            }

            $user = $this->userServices->getUserByEmail($email);

            app(PasswordBroker::class)->deleteToken($user);
            $token = app(PasswordBroker::class)->createToken($user);

            $resetLink = config('constants.app_fe_url').'/reset-password/'.$token;

            Mail::to($email)->queue(new PasswordReset($resetLink));

            return new SuccessfulData('Password reset link was sent!');
        } catch (Exception $error) {
            return new FailedData('Fail to send password reset link!');
        }
    }

    public function logout(User $user): object
    {
        try {
            $user->tokens()->delete();

            return new SuccessfulData('Logged out successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to logout!');
        }
    }
}
