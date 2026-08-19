<?php
/** @var \app\modules\fdp\models\Fdp $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Update FDP';
?>
<div class="card shadow-sm border-0" style="max-width: 900px; margin: 24px auto 0;">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Update FDP</h1>
                <p class="text-muted mb-0">Edit the selected faculty development programme details.</p>
            </div>
            <?= Html::a('Back to FDP', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label fw-semibold'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ],
        ]); ?>

        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'start_date')->input('date') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'end_date')->input('date') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'time')->textInput(['placeholder' => '09:30 AM - 04:30 PM']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'mode')->dropDownList(['Online' => 'Online', 'Offline' => 'Offline']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'venue')->textInput(['placeholder' => 'Seminar Hall A']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'meeting_link')->textInput(['placeholder' => 'https://meet.example.com/fdp']) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'coordinator_name')->textInput(['placeholder' => 'Dr. Smith']) ?>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::submitButton('Save Changes', ['class' => 'btn btn-primary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
