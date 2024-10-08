<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Filters;

use ESD\Yii\Base\Action;
use ESD\Yii\Yii;
use ESD\Yii\Base\ActionFilter;
use ESD\Yii\Web\BadRequestHttpException;
use ESD\Yii\Web\Request;

/**
 * AjaxFilter allow to limit access only for ajax requests.
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => 'yii\filters\AjaxFilter',
 *             'only' => ['index']
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Dmitry Dorogin <dmirogin@ya.ru>
 * @since 2.0.13
 */
class AjaxFilter extends ActionFilter
{
    /**
     * @var string the message to be displayed when request isn't ajax
     */
    public $errorMessage = 'Request must be XMLHttpRequest.';
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction(Action $action)
    {
        if ($this->request->getIsAjax()) {
            return true;
        }

        throw new BadRequestHttpException($this->errorMessage);
    }
}
