<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Sanctum's EnsureFrontendRequestsAreStateful only turns on the
     * session/CSRF middleware for requests whose Referer matches a
     * configured stateful domain — mirror that here as a real SPA
     * request (the Next.js frontend) would send it.
     */
    protected function fromSpa()
    {
        return $this->withHeader('Referer', 'http://localhost:3000');
    }
}
