<?php

namespace Tests;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property User $user
 * @property Goal $goal
 */
abstract class TestCase extends BaseTestCase {}
