<?php

namespace Feature;

use App\Http\Controllers\FooBarController;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ControllerSerializationTest extends TestCase
{
    #[Test]
    public function controller_post_inject_param(): void
    {
        /**
         * @see FooBarController::read
         */
        $response = $this->post('/controller/10', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'AAA',
        ]);

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "id": 10,
              "a": "something",
              "b": "something else",
              "type": "AAA"
            }
            JSON,
            $response->content(),
        );
        $response->assertStatus(200);
    }

    #[Test]
    public function controller_invalid_inject_param(): void
    {
        /**
         * @see FooBarController::read
         */
        $response = $this->post('/controller/10', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'YYY',
        ]);

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "message": "Unable to parse value at .type",
              "expected": ["AAA", "BBB"],
              "given": "string"
            }
            JSON,
            $response->content(),
        );
        $response->assertStatus(400);
    }

    #[Test]
    public function controller_post_list_return_list(): void
    {
        /**
         * @see FooBarController::list
         */
        $response = $this->post('/controller', [
            [
                'id' => 10,
                'a' => 'aaa',
                'b' => 'list aaa',
                'type' => 'AAA',
            ],
            [
                'id' => 11,
                'a' => 'bbb',
                'b' => 'list bbb',
                'type' => 'BBB',
            ],
        ]);

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            [
                {
                  "id": 10,
                  "a": "aaa",
                  "b": "list aaa",
                  "type": "AAA"
                },
                {
                  "id": 11,
                  "a": "bbb",
                  "b": "list bbb",
                  "type": "BBB"
                }
            ]
            JSON,
            $response->content(),
        );
        $response->assertStatus(200);
    }
}
