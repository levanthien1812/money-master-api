<?php

namespace Tests\Unit\Services;

use App\Http\Services\UserServices;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private $userService;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $this->user = User::factory()->create();

        $this->user->assignRole('user');
        $this->userService = app(UserServices::class);

        User::factory(10)->create();
    }

    public function test_create(): void
    {
        $data = [
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
            'password' => fake()->password(),
        ];

        $this->userService->create($data);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    public function test_get_user_by_email(): void
    {
        $emails = User::all()->pluck('email')->toArray();
        $email = fake()->randomElement($emails);

        $user = $this->userService->getUserByEmail($email);

        $this->assertEquals($user->email, $email);
    }

    public function test_get_user(): void
    {
        $ids = User::all()->pluck('id')->toArray();
        $id = fake()->randomElement($ids);

        $user = $this->userService->getUser($id);

        $this->assertTrue($user instanceof User);
    }

    public function test_get_user_fail_not_found(): void
    {
        $maxId = User::select('id')->max('id');

        $user = $this->userService->getUser($maxId + 1);

        $this->assertTrue(is_null($user));
    }

    public function test_get_users(): void
    {
        $resultData = $this->userService->getUsers();

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_count_user(): void
    {
        $resultData = $this->userService->countUsers();

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_update_user(): void
    {
        $data = [
            'photo' => fake()->image(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
        ];

        $resultData = $this->userService->updateUser($this->user, $data);

        $this->assertEquals($resultData->status, 'success');
    }

    public function test_check_exist_by_email(): void
    {
        $emails = User::all()->pluck('email')->toArray();
        $email = fake()->randomElement($emails);

        $result = $this->userService->checkExistsByEmail($email);

        $this->assertTrue($result);
    }

    public function test_check_exist_by_email_fail_not_found(): void
    {
        $emails = User::all()->pluck('email')->toArray();
        do {
            $email = fake()->safeEmail();
        } while (in_array($email, $emails));

        $result = $this->userService->checkExistsByEmail($email);

        $this->assertFalse($result);
    }

    public function test_update_password_fail_user_not_found(): void
    {
        $maxId = User::select('id')->max('id');

        $resultData = $this->userService->updatePassword($maxId + 1, []);

        $this->assertEquals($resultData->message, 'User not found!');
    }

    public function test_update_password_fail_incorrect_password(): void
    {
        $resultData = $this->userService->updatePassword($this->user->id, ['password' => 'random_password_shkfj']);

        $this->assertEquals($resultData->message, 'Password is not correct!');
    }

    public function test_update_password(): void
    {
        $password = fake()->password(8, 32);

        $resultData = $this->userService->updatePassword($this->user->id, [
            'password' => 'password',
            'newPassword' => $password,
        ]);

        $this->assertEquals($resultData->status, 'success');
    }
}
