<?php
return [
    'admin' => [
        'type' => 1,
        'ruleName' => 'userGroup',
    ],
    'florist' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'inSite',
        ],
    ],
    'inSite' => [
        'type' => 2,
        'description' => 'In site',
        'ruleName' => 'inSite',
    ],
];
