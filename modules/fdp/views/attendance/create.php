<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \app\modules\fdp\models\FdpAttendance $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Add Attendance';
?>
<div class="card shadow-sm border-0" style="max-width: 800px; margin: 24px auto 0;">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Add Attendance</h1>
                <p class="text-muted mb-0">Add a single record or upload a CSV file for <?= Html::encode($fdp->title) ?>.</p>
            </div>
            <?= Html::a('Back to Attendance', ['index', 'fdpId' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
            <div class="row g-3">
                <div class="col-md-12">
                    <?= $form->field($model, 'faculty_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'faculty_email')->textInput(['maxlength' => true, 'type' => 'email']) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'status')->dropDownList($model::statusOptions(), ['prompt' => 'Select status']) ?>
                </div>
                <div class="col-md-12">
                    <label class="form-label">CSV Upload</label>
                    <input type="file" name="attendance_file" class="form-control" accept=".csv,text/csv" />
                    <div class="form-text">CSV should contain columns: name,email,status</div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <?= Html::a('Cancel', ['index', 'fdpId' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= Html::submitButton('Save Attendance', ['class' => 'btn btn-primary btn-lg']) ?>
                    </div>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
