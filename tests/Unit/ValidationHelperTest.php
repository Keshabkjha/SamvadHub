<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\ValidationHelper;

class ValidationHelperTest extends TestCase {
    
    public function testValidateSignupMissingFirstName() {
        $data = [
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123'
        ];
        $result = ValidationHelper::validateSignup($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('First name is required.', $result['msg']);
        $this->assertEquals('first_name', $result['field']);
    }

    public function testValidateSignupInvalidEmail() {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'username' => 'johndoe',
            'password' => 'password123'
        ];
        $result = ValidationHelper::validateSignup($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Please enter a valid email address.', $result['msg']);
        $this->assertEquals('email', $result['field']);
    }

    public function testValidateSignupShortPassword() {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => '123'
        ];
        $result = ValidationHelper::validateSignup($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Password must be at least 6 characters.', $result['msg']);
        $this->assertEquals('password', $result['field']);
    }

    public function testValidateSignupInvalidUsername() {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'username' => 'jd!', // Invalid character !
            'password' => 'password123'
        ];
        $result = ValidationHelper::validateSignup($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Username must be 3–30 characters (letters, numbers, underscore only).', $result['msg']);
        $this->assertEquals('username', $result['field']);
    }

    public function testValidateLoginMissingFields() {
        $data = [
            'password' => 'password123'
        ];
        $result = ValidationHelper::validateLogin($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Please enter your username or email.', $result['msg']);
        $this->assertEquals('username_email', $result['field']);
        
        $data = [
            'username_email' => 'john@example.com'
        ];
        $result = ValidationHelper::validateLogin($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Please enter your password.', $result['msg']);
        $this->assertEquals('password', $result['field']);
    }

    public function testValidateUpdateInvalidWebsite() {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'website' => 'not-a-url'
        ];
        $result = ValidationHelper::validateUpdate($data);
        $this->assertFalse($result['status']);
        $this->assertEquals('Please enter a valid website URL.', $result['msg']);
        $this->assertEquals('website', $result['field']);
    }
}
