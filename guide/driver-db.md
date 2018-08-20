DB Driver
=========

The DB driver uses a database to store PID action data.

Configuration example:

```php
return [
    'components' => [
        'lockDb' => [
            'class' => yii\db\Connection::class,
            'dsn' => 'mysql:host=__MYSQL_HOST__;dbname=__MYSQL_DATABASE__',
            'username' => '__MYSQL_USERNAME__',
            'password' => '__MYSQL_PASSWORD__',
            'charset' => 'utf8',
            'enableQueryCache' => YII_ENV_PROD && !YII_DEBUG,
            'enableSchemaCache' => YII_ENV_PROD && !YII_DEBUG,
            'attributes' => [
                // Permanent mysql connection
                PDO::ATTR_PERSISTENT => true,
                // Or 
                // PDO::ATTR_TIMEOUT => 36000,
            ],
        ],
    ],
];
```

Console controller example:

```php
<?php

namespace console\controllers;

use tkanstantsin\Yii2ActionLockBehavior\ActionLockBehavior;
use tkanstantsin\Yii2ActionLockBehavior\Db\Source as DbSource;

/**
 * Example controller
 */
class OrdersController extends Controller
{
    // ...
    
    public function behaviors()
    {
        return [
            'dbPid' => [
                'class' => ActionLockBehavior::class,
                'source' => new DbSource([
                    // Use exist component (recommended)
                    'connection' => 'db',
                    
                    // Use new component
                    // 'connection'     => \Yii::$app->lockDb,
                    // 'connectionCopy' => false,
                ]),
            ],
        ];
    }
    
    // ...
}
```

Run:
```bash
php yii migrate
```

Thats all. Check it.