<?php

$file = 'tests/blog/composer.json';

$content = array_merge_recursive(
    json_decode(file_get_contents($file), true),
    [
        'autoload' => [
            'psr-4' => [
                'Tcds\\Io\\Laravel\\Jackson\\' => 'jackson/',
            ],
        ],
        'repositories' => [
            ['type' => 'git', 'url' => 'https://github.com/thiagocordeiro/laravel-serializer'],
        ],
    ],
);

$content['minimum-stability'] = 'dev';

file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
