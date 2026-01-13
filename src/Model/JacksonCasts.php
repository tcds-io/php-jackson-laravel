<?php

namespace Tcds\Io\Jackson\Laravel\Model;

use Override;

trait JacksonCasts
{
    /**
     * @return array<string, string>
     */
    #[Override] public function getCasts(): array
    {
        $mappers = config('jackson.mappers', []);
        $casts = parent::getCasts();

        foreach ($casts as $column => $cast) {
            if (!is_string($cast)) {
                continue;
            }

            if (!array_key_exists($cast, $mappers)) {
                continue;
            }

            $casts[$column] = sprintf('%s:%s', JacksonObjectCaster::class, $cast);
        }

        return $casts;
    }
}
