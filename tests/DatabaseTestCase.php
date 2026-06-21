<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Database as DB;

abstract class DatabaseTestCase extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Load the config file once to define DB constants and initialize environment
        require_once dirname(__DIR__) . '/config/config.php';

        // Begin a MySQLi database transaction before each test runs
        DB::getConnection()->begin_transaction();
    }

    protected function tearDown(): void {
        // Roll back transaction to keep the test environment clean and isolate data
        DB::getConnection()->rollback();
        
        parent::tearDown();
    }
}
