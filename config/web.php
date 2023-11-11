<?php

use app\assets\MainAsset;
use app\helpers\Utils;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'it-IT',
    'sourceLanguage' => 'it-IT',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'layout' => 'mainy',
    'modules' => [
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ]
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'jFo5CTW3Lo8Kc9IcJ6daGb7Roq_VGp5a',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['auth/login'],
            'authTimeout' => 60 * 60 * 24 * 7, // 7 days
            // set 404 url
            'returnUrl' => ['/site/index'],
        ],
        /*        'errorHandler' => [
                    'errorAction' => 'site/errore',
                ],*/
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
        ],
        'db' => $db,
        /*        'assetManager' => [
                    'bundles' => [
                        MainAsset::class => [
                            'css' => [
                                'app.min.css',
                            ],
                        ],
                    ],
                ],*/

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'dd/MM/yyyy',
            'currencyCode' => 'EUR',
        ],
    ],
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['auth/login', 'site/error', 'site/non-autorizzato', 'auth/password-dimenticata'], // elenco delle azioni escluse
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            Yii::$app->response->redirect(['auth/login']);
        },
    ],
    'on beforeRequest' => function ($event) {
        if (isseT(Yii::$app->params['eseguiBackup']) && Yii::$app->params['eseguiBackup'] === true) {
            $lastBackupDate = Yii::$app->cache->get('lastBackupDate');
            if (!$lastBackupDate || (new \DateTime())->diff(new \DateTime($lastBackupDate))->days > 1) {
                Utils::dumpDb();
                Yii::$app->cache->set('lastBackupDate', date('Y-m-d H:i:s'));
            }
        }
    },
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
