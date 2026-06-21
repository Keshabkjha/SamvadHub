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

    public function testUpdateProfileWithBase64CroppedImage() {
        // Create user
        $email = 'cropuser' . rand(1000, 9999) . '@example.com';
        $username = 'cropusername' . rand(1000, 9999);
        $password = 'crop_password123';

        $data = [
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'gender' => 1,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        User::create($data);
        $createdUser = User::getByEmail($email);
        $this->assertNotEmpty($createdUser);

        // Set session user ID to mock a logged-in user
        $_SESSION['userdata'] = $createdUser;

        // Base64 transparent 1x1 PNG image
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $updateData = [
            'first_name' => 'CharlieUpdated',
            'last_name' => 'BrownUpdated',
            'username' => $username . '_up',
            'bio' => 'A happy kid.',
            'website' => 'https://charliebrown.com',
            'cropped_image_base64' => $base64Image
        ];

        // Call updateProfile
        $status = User::updateProfile($updateData, []);
        $this->assertTrue($status);

        // Fetch updated user from DB
        $updatedUser = User::getById((int)$createdUser['id']);
        $this->assertEquals('CharlieUpdated', $updatedUser['first_name']);
        $this->assertEquals('BrownUpdated', $updatedUser['last_name']);
        $this->assertEquals($username . '_up', $updatedUser['username']);
        $this->assertEquals('A happy kid.', $updatedUser['bio']);
        $this->assertEquals('https://charliebrown.com', $updatedUser['website']);

        // Check if the uploaded image file exists and delete it
        $uploadedFile = dirname(__DIR__, 2) . '/public/assets/images/profile/' . $updatedUser['profile_pic'];
        $this->assertFileExists($uploadedFile);
        
        // Clean up the uploaded image
        if (file_exists($uploadedFile)) {
            unlink($uploadedFile);
        }

        // Clean up session
        unset($_SESSION['userdata']);
    }
}
