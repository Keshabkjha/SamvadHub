<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\GoogleJwtVerifier;

class GoogleJwtVerifierTest extends TestCase {

    public function testVerifyReturnsNullForEmptyToken() {
        $result = GoogleJwtVerifier::verify('');
        $this->assertNull($result);
    }

    public function testVerifyReturnsNullForInvalidFormat() {
        $result = GoogleJwtVerifier::verify('invalid_token_without_dots');
        $this->assertNull($result);
        
        $result = GoogleJwtVerifier::verify('one.dot');
        $this->assertNull($result);
        
        $result = GoogleJwtVerifier::verify('three.dots.in.here.too');
        $this->assertNull($result);
    }

    public function testVerifyReturnsNullForInvalidPayload() {
        // A token with 3 parts but they aren't base64url encoded JSON strings
        $result = GoogleJwtVerifier::verify('part1.part2.part3');
        $this->assertNull($result);
    }
}
