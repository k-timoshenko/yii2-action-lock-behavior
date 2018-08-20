# Yii2 Action lock behavior

Yii2 widget

[![Latest Stable Version](https://poser.pugx.org/t-kanstantsin/yii2-action-lock-behavior/v/stable.png)](https://packagist.org/packages/t-kanstantsin/yii2-action-lock-behavior)
[![Total Downloads](https://poser.pugx.org/t-kanstantsin/yii2-action-lock-behavior/downloads.png)](https://packagist.org/packages/t-kanstantsin/yii2-action-lock-behavior)

## Basic

Behavior allow deny multiple runs of same console application action (e.g. long time executing task initiated with cron).

Lock source should be chosen carefully noting following:

- [__File source__](guide/driver-db.md) simple but can be used _only with one docker container instance_ because its not possible determine if process still running or ended in another container. Requires only writable directory

- [__Db source__](guide/driver-file.md) requires mysql connection. May be used only with single database instance.