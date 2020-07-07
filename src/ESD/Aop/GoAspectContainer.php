<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Go\Aop\Pointcut\PointcutGrammar;
use Go\Aop\Pointcut\PointcutLexer;
use Go\Aop\Pointcut\PointcutParser;
use Go\Core\AdviceMatcher;
use Go\Core\AspectLoader;
use Go\Core\Container;
use Go\Core\GeneralAspectLoaderExtension;
use Go\Core\IntroductionAspectExtension;
use Go\Core\LazyAdvisorAccessor;

/**
 * Class GoAspectContainer
 * @package ESD\Aop
 */
class GoAspectContainer extends \Go\Core\GoAspectContainer
{
    /**
     * Constructor for container
     */
    public function __construct()
    {
        // Register all services in the container
        $this->share('aspect.loader', function (Container $container) {
            $aspectLoader = new AspectLoader(
                $container,
                $container->get('aspect.annotation.reader')
            );
            $lexer = $container->get('aspect.pointcut.lexer');
            $parser = $container->get('aspect.pointcut.parser');

            // Register general aspect loader extension
            $aspectLoader->registerLoaderExtension(new GeneralAspectLoaderExtension($lexer, $parser));
            $aspectLoader->registerLoaderExtension(new IntroductionAspectExtension($lexer, $parser));

            return $aspectLoader;
        });

        $this->share('aspect.cached.loader', function (Container $container) {
            $options = $container->get('kernel.options');
            if (!empty($options['cacheDir'])) {
                $loader = new MemCachedAspectLoader(
                    $container,
                    'aspect.loader',
                    $container->get('kernel.options')
                );
            } else {
                $loader = $container->get('aspect.loader');
            }

            return $loader;
        });

        $this->share('aspect.advisor.accessor', function (Container $container) {
            return new LazyAdvisorAccessor(
                $container,
                $container->get('aspect.cached.loader')
            );
        });

        $this->share('aspect.advice_matcher', function (Container $container) {
            return new AdviceMatcher(
                $container->get('aspect.loader'),
                $container->get('kernel.interceptFunctions')
            );
        });

        $this->share('aspect.annotation.cache', function (Container $container) {
            $options = $container->get('kernel.options');

            if (!empty($options['annotationCache'])) {
                return $options['annotationCache'];
            }
            return new DoctrineCache\ArrayCache();
        });

        $this->share('aspect.annotation.reader', function (Container $container) {
            $options = $container->get('kernel.options');
            $options['debug'] = isset($options['debug']) ? $options['debug'] : false;

            return new CachedReader(
                new AnnotationReader(),
                $container->get('aspect.annotation.cache'),
                $options['debug']
            );
        });

        // Pointcut services
        $this->share('aspect.pointcut.lexer', function () {
            return new PointcutLexer();
        });
        $this->share('aspect.pointcut.parser', function (Container $container) {
            return new PointcutParser(
                new PointcutGrammar(
                    $container,
                    $container->get('aspect.annotation.reader')
                )
            );
        });
    }
}