<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Bootstrap4;

/**
 * \ESD\Yii\Bootstrap4\Widget is the base class for all bootstrap widgets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 */
class Widget extends \ESD\Yii\Base\Widget
{
    use BootstrapWidgetTrait;

    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \ESD\Yii\Helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
}
