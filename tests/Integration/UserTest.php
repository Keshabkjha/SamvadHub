<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;
use App\Models\User;

class UserTest extends DatabaseTestCase {

    public function testUserCreationAndRetrieval() {
        $email = 'testuser' . rand(1000, 9999) . '@example.com';
        $username = 'testusername' . rand(1000, 9999);
        $password = 'mypassword123';

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 1,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        // Create the user
        $created = User::create($data);
        $this->assertTrue($created);

        // Verify registration checks
        $this->assertTrue(User::isEmailRegistered($email));
        $this->assertTrue(User::isUsernameRegistered($username));

        // Retrieve user by email
        $user = User::getByEmail($email);
        $this->assertNotEmpty($user);
        $this->assertEquals('Jane', $user['first_name']);
        $this->assertEquals('Doe', $user['last_name']);
        $this->assertEquals($username, $user['username']);
        $this->assertEquals(0, (int)$user['ac_status']); // Status defaults to 0 (unverified)

        // Retrieve user by username
        $userByUsername = User::getByUsername($username);
        $this->assertEquals($user['id'], $userByUsername['id']);
    }

    public function testUserAuthentication() {
        $email = 'authuser' . rand(1000, 9999) . '@example.com';
        $username = 'authusername' . rand(1000, 9999);
        $password = 'securepwd789';

        $data = [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'gender' => 0,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        User::create($data);

        // Verify successful auth with email
        $authEmailResult = User::authenticate([
            'username_email' => $email,
            'password' => $password
        ]);
        $this->assertTrue($authEmailResult['status']);
        $this->assertEquals($username, $authEmailResult['user']['username']);

        // Verify successful auth with username
        $authUsernameResult = User::authenticate([
            'username_email' => $username,
            'password' => $password
        ]);
        $this->assertTrue($authUsernameResult['status']);

        // Verify failed auth with wrong password
        $authWrongPwdResult = User::authenticate([
            'username_email' => $username,
            'password' => 'wrong_password'
        ]);
        $this->assertFalse($authWrongPwdResult['status']);
    }

    public function testVerifyEmailAndResetPassword() {
        $email = 'verifyuser' . rand(1000, 9999) . '@example.com';
        $username = 'verifyusername' . rand(1000, 9999);
        $password = 'pwdpwd123';

        $data = [
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'gender' => 1,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        User::create($data);

        // Verify status is 0 initially
        $user = User::getByEmail($email);
        $this->assertEquals(0, (int)$user['ac_status']);

        // Verify email
        $verified = User::verifyEmail($email);
        $this->assertTrue($verified);

        // Verify status updated to 1
        $user = User::getByEmail($email);
        $this->assertEquals(1, (int)$user['ac_status']);

        // Reset password
        $resetResult = User::resetPassword($email, 'new_password_xyz');
        $this->assertTrue($resetResult);

        // Authenticate with new password
        $authResult = User::authenticate([
            'username_email' => $email,
            'password' => 'new_password_xyz'
        ]);
        $this->assertTrue($authResult['status']);
    }
}
