# Yii Action lock behavior

Yii widget

[![Latest Stable Version](https://poser.pugx.org/t-kanstantsin/yii2-action-lock-behavior/v/stable.png)](https://packagist.org/packages/t-kanstantsin/yii2-action-lock-behavior)
[![Total Downloads](https://poser.pugx.org/t-kanstantsin/yii2-action-lock-behavior/downloads.png)](https://packagist.org/packages/t-kanstantsin/yii2-action-lock-behavior)

## Basic

Behavior allow deny multiple runs of same console application action (e.g. long time executing task initiated with cron) using `yii\mutex\*` package.

Lock source should be chosen carefully noting following:

- `yii\mutex\FileMutex` simple but can be used _only with one docker container instance_ because its not possible determine if process still running or ended in another container. Requires only writable directory

- `yii\mutex\DbMutex` requires db connection. May be used only with single database instance.


## Example

Using mutex from global config:

```php

    public function behaviors(): array
    {
        return [
            'pid' => ActionLockBehavior::class,
        ];
    }

```

Define mutex on-the-fly:

```php

    public function behaviors(): array
    {
        return [
            'pid' => [
                'class' => ActionLockBehavior::class,
                'mutex' => [
                    'class' => FileMutex::class,
                    'mutexPath' => \Yii::getAlias('@runtime/pid'),
                ],
            ],
        ];
    }

```
