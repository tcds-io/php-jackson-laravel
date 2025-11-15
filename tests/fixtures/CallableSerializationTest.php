<?php

namespace Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CallableSerializationTest extends TestCase
{
    #[Test]
    public function callable_list(): void
    {
        /**
         * @see routes/web.php
         */
        $response = $this->post('/callable', [
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

    /**
     * @see routes/web.php
     */
    #[Test]
    public function callable_item(): void
    {
        $response = $this->get('/callable/resource/12');

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "id": 12,
              "a": "aaa",
              "b": "get",
              "type": "AAA"
            }
            JSON,
            $response->content(),
        );
        $response->assertStatus(200);
    }

    #[Test]
    public function callable_inject_param(): void
    {
        /**
         * @see routes/web.php
         */
        $response = $this->post('/callable/resource', [
            'a' => 'aaa',
            'b' => 'post',
            'type' => 'BBB',
        ]);

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
        $response->assertStatus(200);
    }
}
