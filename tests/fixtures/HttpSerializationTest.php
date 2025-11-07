<?php

namespace Tests\Feature;

use Tests\TestCase;

class HttpSerializationTest extends TestCase
{
    public function testSerializeGetResponse(): void
    {
        $response = $this->get('/');

        $response->assertContent('{"a":"aaa","b":"get","type":"AAA"}');
        $response->assertStatus(200);
    }

    public function testSerializeObjectInjectingCallable(): void
    {
        $response = $this->post('/', [
            'a' => 'aaa',
            'b' => 'post',
            'type' => 'BBB',
        ]);

        $response->assertContent('{"a":"aaa","b":"post","type":"BBB"}');
        $response->assertStatus(200);
    }

    public function testSerializeObjectInjectingController(): void
    {
        $response = $this->post('/controller', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'AAA',
        ]);

        $response->assertContent('{"a":"something","b":"something else","type":"AAA"}');
        $response->assertStatus(200);
    }

    public function testWhenPayloadIsInvalidThenThrowBadRequest(): void
    {
        $response = $this->post('/controller', [
            'a' => 'something',
            'b' => 'something else',
            'type' => 'YYY',
        ]);

        $this->assertEquals(
            [
                'message' => 'Unable to parse value at .type',
                'expected' => ['AAA', 'BBB'],
                'given' => 'string',
            ],
            $response->json(),
        );
        $response->assertStatus(400);
    }
}
