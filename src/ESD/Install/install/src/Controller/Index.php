<?php

namespace App\Controller;

use DI\Annotation\Inject;
use ESD\Go\GoController;
use ESD\Plugins\EasyRoute\Annotation\GetMapping;
use ESD\Plugins\EasyRoute\Annotation\RestController;
use ESD\Plugins\Blade\Blade;

/**
 * @RestController()
 * Class Index
 * @package ESD\Plugins\EasyRoute
 */
class Index extends GoController
{

    /**
     * @Inject()
     * @var Blade
     */
    protected $blade;

    /**
     * @GetMapping("/")
     * @return string
     */
    public function index()
    {
        return $this->blade->render("app::welcome");
    }
}