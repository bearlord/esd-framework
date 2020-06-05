<?php

namespace App\Model;

use ESD\Yii\Yii;

/**
 * This is the model class for table "n_admin_rule".
 *
 * @property int $id
 * @property int $types 0系统设置1工作台2客户管理3项目管理4人力资源5财务管理6商业智能(客戶)7商业智能(办公)
 * @property string $title 名称
 * @property string $name 定义
 * @property int $level 级别。1模块,2控制器,3操作
 * @property int|null $pid 父id，默认0
 * @property int|null $status 状态，1启用，0禁用
 */
class AdminRule3 extends \ESD\Yii\Db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'n_admin_rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['types', 'level', 'pid', 'status'], 'integer'],
            [['title', 'name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'types' => 'Types',
            'title' => 'Title',
            'name' => 'Name',
            'level' => 'Level',
            'pid' => 'Pid',
            'status' => 'Status',
        ];
    }
}
