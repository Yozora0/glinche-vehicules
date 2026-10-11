<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function fixture(string $name): array
    {
        return json_decode(file_get_contents(__DIR__."/Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
    }
}
