<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Debug\Models\Search;

use ESD\Yii\Base\Model;
use ESD\Yii\Data\ActiveDataProvider;
use ESD\Yii\Db\ActiveRecord;

/**
 * Search model for implementation of IdentityInterface
 *
 * @author Semen Dubina <yii2debug@sam002.net>
 * @since 2.0.10
 */
class User extends Model
{
    /**
     * @var Model implementation of IdentityInterface
     */
    public $identityImplement = null;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (\Yii::$app->user && \Yii::$app->user->identityClass) {
            $identityImplementation = new \Yii::$app->user->identityClass();
            if ($identityImplementation instanceof Model) {
                $this->identityImplement = $identityImplementation;
            }
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $name)
    {
        return $this->identityImplement->__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set(string $name, $value)
    {
        return $this->identityImplement->__set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [[array_keys($this->identityImplement->getAttributes()), 'safe']];
    }

    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return $this->identityImplement->attributes();
    }

    /**
     * {@inheritdoc}
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function search($params)
    {
        if ($this->identityImplement instanceof ActiveRecord) {
            return $this->searchActiveDataProvider($params);
        }

        return null;
    }

    /**
     * Search method for ActiveRecord
     * @param array $params the data array to load model.
     * @return ActiveDataProvider
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    private function searchActiveDataProvider($params)
    {
        /** @var ActiveRecord $model */
        $model = $this->identityImplement;
        $query = $model::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        foreach ($model::getTableSchema()->columns as $attribute => $column) {
            if ($column->phpType === 'string') {
                $query->andFilterWhere(['like', $attribute, $model->getAttribute($attribute)]);
            } else {
                $query->andFilterWhere([$attribute => $model->getAttribute($attribute)]);
            }
        }

        return $dataProvider;
    }
}
