<?php

namespace Tests\Feature;

use App\Http\Controllers\FooBarController;
use Tests\TestCase;

class HttpSerializationTest extends TestCase
{
    /**
     * @see routes/web.php
     */
    public function testSerializeGetResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "id": 1,
              "a": "aaa",
              "b": "get",
              "type": "AAA"
            }
            JSON,
            $response->content(),
        );
    }

    /**
     * @see routes/web.php
     */
    public function testSerializeObjectInjectingCallable(): void
    {
        $response = $this->post('/', [
            'a' => 'aaa',
            'b' => 'post',
            'type' => 'BBB',
        ]);

        $response->assertStatus(200);
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "id": null,
              "a": "aaa",
              "b": "post",
              "type": "BBB"
            }
            JSON,
            $response->content(),
        );
    }

    /**
     * @see FooBarController::read
     */
    public function testSerializeObjectInjectingController(): void
    {
        $response = $this->post('/controller/10', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'AAA',
        ]);

        $response->assertStatus(200);
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
    }

    /**
     * @see FooBarController::read
     */
    public function testWhenPayloadIsInvalidThenThrowBadRequest(): void
    {
        $response = $this->post('/controller/10', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'YYY',
        ]);

        $response->assertStatus(400);
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
    }

    /**
     * @see FooBarController::list
     */
    public function testGivenAListThenReturnList(): void
    {
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

        $response->assertStatus(200);
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
    }
}
