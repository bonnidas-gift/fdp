<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \app\modules\fdp\models\FdpParticipant $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Add Participant';
?>
<div class="container" style="max-width: 900px; margin: 24px auto 0;">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">Add Participant</h1>
                    <p class="text-muted mb-0">Add a single participant for <?= Html::encode($fdp->title) ?>.</p>
                </div>
                <?= Html::a('Back to Participants', ['index', 'fdpId' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?php $form = ActiveForm::begin(); ?>
                <div class="row g-3">
                    <div class="col-md-12">
                        <?= $form->field($model, 'faculty_name')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'faculty_email')->textInput(['maxlength' => true, 'type' => 'email']) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'department')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'designation')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="d-flex justify-content-end gap-2">
                            <?= Html::a('Cancel', ['index', 'fdpId' => $fdp->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= Html::submitButton('Save Participant', ['class' => 'btn btn-primary btn-sm']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
