<?php


namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
if (!class_exists(\Illuminate\Foundation\Testing\TestCase::class)) {
    die('Laravel test case not found');
}
abstract class TestCase extends BaseTestCase
{
    //
}