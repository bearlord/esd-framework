<?php
/* @var $this ESD\Yii\Web\View */
/* @var $form ESD\Yii\Widgets\ActiveForm */
/* @var $generator ESD\Yii\Clickhouse\gii\model\Generator */
echo $form->field($generator, 'collectionName');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');