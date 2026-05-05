<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

if (!class_exists(BaseTestCase::class)) {
    exit('Laravel test case not found');
}
abstract class TestCase extends BaseTestCase
{
    //
}
