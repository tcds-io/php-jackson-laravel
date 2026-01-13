<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\UserSettings;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JacksonObjectCasterTest extends TestCase
{
    #[Test]
    public function given_an_object_then_convert_attribute_to_json(): void
    {
        $model = new UserModel();
        $model->first_name = 'Arthur';
        $model->last_name = 'Dent';
        $model->settings = new UserSettings(drawer: true, theme: 'light');

        $this->assertEquals(
            [
                'first_name' => 'Arthur',
                'last_name' => 'Dent',
                'settings' => '{"drawer":true,"theme":"light"}',
            ],
            $model->getAttributes(),
        );
    }

    #[Test]
    public function given_the_attributes_then_convert_to_object(): void
    {
        $model = new UserModel([
            'first_name' => 'Arthur',
            'last_name' => 'Dent',
            'settings' => '{"drawer":true,"theme":"light"}',
        ]);

        $this->assertEquals(
            [
                'first_name' => 'Arthur',
                'last_name' => 'Dent',
                'settings' => new UserSettings(drawer: true, theme: 'light'),
            ],
            $model->toArray(),
        );
    }
}
