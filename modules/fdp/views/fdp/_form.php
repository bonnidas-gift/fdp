<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var \app\modules\fdp\models\Fdp $model */
?>
<?php $form = ActiveForm::begin(); ?>
    <div class="row g-3">
        <div class="col-md-12">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'start_date')->input('date') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'end_date')->input('date') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'time_start')->textInput(['type' => 'time']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'time_end')->textInput(['type' => 'time']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'mode')->dropDownList($model->modeOptions(), ['prompt' => 'Select mode']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'coordinator_name')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-12">
            <?= $form->field($model, 'venue')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-12">
            <?= $form->field($model, 'meeting_link')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-12 mt-3">
            <div class="d-flex justify-content-end gap-2">
                <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                <?= Html::submitButton($model->isNewRecord ? 'Create FDP' : 'Update FDP', ['class' => $model->isNewRecord ? 'btn btn-primary btn-sm' : 'btn btn-success btn-sm']) ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
