<?php

declare(strict_types=1);

namespace app\modules\fdp;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\fdp\controllers';
    public $defaultRoute = 'default';
    public $layout = '@app/views/layouts/main';
    public $name = 'FDP';
}
