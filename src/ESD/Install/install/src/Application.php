<?php

namespace App;

use ESD\Go\GoApplication;
use ESD\Yii\PdoPlugin\PdoPlugin;

class Application
{
    /**
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public static function main()
    {
        $goApp = new GoApplication();
        $goApp->addPlug(new PdoPlugin());
        $goApp->run(Application::class);
    }
}
