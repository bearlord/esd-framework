<?php

namespace ESD\Yii\Clickhouse;

use ESD\Yii\Yii;

/**
 * Class ActiveRecord
 * @package ESD\Yii\Clickhouse
 */
class ActiveRecord extends \ESD\Yii\Db\ActiveRecord
{
    /**
     * Returns the connection used by this AR class.
     * @return mixed|Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('clickhouse');
    }

    /**
     * @inheritdoc
     * @return \ESD\Yii\Clickhouse\ActiveQuery the newly created [[\ESD\Yii\Clickhouse\ActiveQuery]] instance.
     */
    public static function find(): \ESD\Yii\Db\ActiveQuery
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Returns the primary key **name(s)** for this AR class.
     *
     * Note that an array should be returned even when the record only has a single primary key.
     *
     * For the primary key **value** see [[getPrimaryKey()]] instead.
     *
     * @return string[] the primary key name(s) for this AR class.
     */
    public static function primaryKey(): ?array
    {
        // TODO: Implement primaryKey() method.
        return null;
    }


    /**
     * Inserts the record into the database using the attribute values of this record.
     *
     * Usage example:
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * @param array|null $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function insert(?bool $runValidation = true, ?array $attributes = null): bool
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }

        $values = $this->getDirtyAttributes($attributes);
        if ((static::getDb()->getSchema()->insert(static::tableName(), $values)) === false) {
            return false;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);
        return true;
    }


}
