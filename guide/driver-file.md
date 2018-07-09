File Driver
=========

The File driver uses a files to store PID action data.

Console controller example:

```php
<?php

namespace console\controllers;

use tkanstantsin\Yii2ActionLockBehavior\ActionLockBehavior;
use tkanstantsin\Yii2ActionLockBehavior\File\Source as FileSource;

/**
 * Example controller
 */
class OrdersController extends Controller
{
    // ...
    
    public function behaviors()
    {
        return [
            'filePid' => [
                'class' => ActionLockBehavior::class,
                'source' => new FileSource([
                    'basePidPath' => \Yii::getAlias('@runtime/pid'),
                ]),
            ],
        ];
    }
    
    // ...
}
```

Thats all. Check it.