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
 * Search model for current request database queries.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Db extends Base
{
    /**
     * @var string type of the input search value
     */
    public $type;
    /**
     * @var int query attribute input search value
     */
    public $query;


    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type', 'query'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'type' => 'Type',
            'query' => 'Query',
        ];
    }

    /**
     * Returns data provider with filled models. Filter applied if needed.
     *
     * @param array $models data to return provider for
     * @return \ESD\Yii\Data\ArrayDataProvider
     */
    public function search($models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['duration', 'seq', 'type', 'query', 'duplicate'],
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'type', true);
        $this->addCondition($filter, 'query', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
