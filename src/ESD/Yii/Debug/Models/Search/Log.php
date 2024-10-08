<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Debug\Models\Search;

use ESD\Yii\Data\ArrayDataProvider;
use ESD\Yii\Debug\Components\Search\Filter;

/**
 * Search model for current request log.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Log extends Base
{
    /**
     * @var string ip attribute input search value
     */
    public $level;
    /**
     * @var string method attribute input search value
     */
    public $category;
    /**
     * @var int message attribute input search value
     */
    public $message;


    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['level', 'message', 'category'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'level' => 'Level',
            'category' => 'Category',
            'message' => 'Message',
            'time_since_previous' => 'Since previous',
        ];
    }

    /**
     * Returns data provider with filled models. Filter applied if needed.
     *
     * @param array $params an array of parameter values indexed by parameter names
     * @param array $models data to return provider for
     * @return \ESD\Yii\Data\ArrayDataProvider
     */
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'time',
                    'time_since_previous' => [
                        'default' => SORT_DESC,
                    ],
                    'level',
                    'category',
                    'message'
                ],
                'defaultOrder' => [
                    'time' => SORT_ASC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'level');
        $this->addCondition($filter, 'category', true);
        $this->addCondition($filter, 'message', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
