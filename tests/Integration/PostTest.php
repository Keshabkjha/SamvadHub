<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;
use App\Models\User;
use App\Models\Post;

class PostTest extends DatabaseTestCase {
    private int $testUserId;

    protected function setUp(): void {
        parent::setUp();

        // Create a test user to associate posts with
        $email = 'posttest' . rand(1000, 9999) . '@example.com';
        $username = 'posttestuser' . rand(1000, 9999);
        User::create([
            'first_name' => 'Alice',
            'last_name' => 'Post',
            'gender' => 0,
            'email' => $email,
            'username' => $username,
            'password' => 'pwd123456'
        ]);

        $user = User::getByEmail($email);
        $this->testUserId = (int)$user['id'];
        
        // Active status must be 1 to show in explore/search
        \App\Core\Database::execute("UPDATE users SET ac_status = 1 WHERE id = ?", 'i', $this->testUserId);
        $user = User::getById($this->testUserId);

        // Mock current user login session
        $_SESSION['userdata'] = $user;
        $_SESSION['Auth'] = true;
    }

    protected function tearDown(): void {
        unset($_SESSION['userdata'], $_SESSION['Auth']);
        parent::tearDown();
    }

    public function testPostCreationAndRetrieval() {
        $data = [
            'post_text' => 'Hello this is my first test #hashtag post!'
        ];

        // Create the post
        $created = Post::create($data, null);
        $this->assertTrue($created);

        // Retrieve posts for the user
        $userPosts = Post::getByUserId($this->testUserId);
        $this->assertCount(1, $userPosts);
        $this->assertEquals('Hello this is my first test #hashtag post!', $userPosts[0]['post_text']);

        // Verify total posts count
        $this->assertGreaterThan(0, Post::getTotalCount());
    }

    public function testSearchPosts() {
        // Create posts with specific keywords
        Post::create(['post_text' => 'We are learning PHPUnit today!'], null);
        Post::create(['post_text' => 'Writing integration tests in PHP'], null);
        Post::create(['post_text' => 'Another completely unrelated status updates'], null);

        // Search for "PHP"
        $results = Post::searchPosts('PHP', 0, 10);
        $this->assertCount(2, $results);
        
        // Verify result contents
        $texts = array_column($results, 'post_text');
        $this->assertContains('We are learning PHPUnit today!', $texts);
        $this->assertContains('Writing integration tests in PHP', $texts);
        $this->assertNotContains('Another completely unrelated status updates', $texts);
    }
}
