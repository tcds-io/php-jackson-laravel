<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tcds\Io\Jackson\Laravel\Model\JacksonCasts;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property UserSettings $settings
 */
class UserModel extends Model
{
    use JacksonCasts;

    protected $fillable = [
        'first_name',
        'last_name',
        'settings',
    ];

    protected $casts = [
        'settings' => UserSettings::class,
    ];
}
