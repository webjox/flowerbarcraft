<?php

use common\models\User;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-crm',
    'name' => 'Flowerbarkraft',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'crm\controllers',
    'bootstrap' => ['log', 'settings'],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-crm',
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => [User::ROLE_ADMIN, User::ROLE_FLORIST],
            'itemFile' => '@common/components/rbac/items.php',
            'assignmentFile' => '@common/components/rbac/assignments.php',
            'ruleFile' => '@common/components/rbac/rules.php'
        ],
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-crm', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-crm',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['retailcrm'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/retailcrm.log',
                    'maxLogFiles' => 1000,
                ],
            ],
        ],
        'settings' => [
            'class' => 'common\components\settings\services\Settings',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
                'login' => 'site/login',
                'users' => 'user/default/list',
                'user/<action:[a-z-]+>/<id:[0-9]+>' => 'user/default/<action>',
                'user/<action:[a-z-]+>' => 'user/default/<action>',
                'settings' => 'settings/default/index',
                'settings/status-update/<id:[0-9]+>' => 'settings/default/status-update',
                'orders' => 'order/default/list',
                'order/<action:[a-z-]+>/<id:[0-9]+>' => 'order/default/<action>',
                'order/<action:[a-z-]+>' => 'order/default/<action>',
                'webhook/<action:[a-z-]+>' => 'webhook/<action>',
            ],
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'crm\modules\user\UserModule',
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                ],
            ],
        ],
        'settings' => [
            'class' => 'crm\modules\settings\SettingsModule',
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                ],
            ],
        ],
        'order' => [
            'class' => 'crm\modules\order\OrderModule',
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_FLORIST],
                    ],
                ],
            ],
        ],
        'gridview' =>  [
            'class' => 'kartik\grid\Module',
        ],
    ],
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'actions' => ['login', 'error', 'order-changed'],
                'allow' => true,
            ],
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ],
    ],
    'params' => $params,
];
