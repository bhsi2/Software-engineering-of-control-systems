<?php

namespace Tests\Feature;

class ExampleTest
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $this->assertTrue(true);
    }
}
