<?php

namespace Martinoak\StatamicLinkChecker\Tests;

use Martinoak\StatamicLinkChecker\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
