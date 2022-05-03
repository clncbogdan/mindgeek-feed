<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * Test the API main endpoint.
     *
     * @return void
     */
    public function test_the_api_returns_a_successful_response()
    {
        $response = $this->get('/api/movies');

        $response->assertStatus(200);
    }
}
