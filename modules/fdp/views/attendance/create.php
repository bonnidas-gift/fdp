<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \app\modules\fdp\models\FdpAttendance $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Add Attendance';
?>
<div class="container" style="max-width: 900px; margin: 24px auto 0;">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">Add Attendance</h1>
                    <p class="text-muted mb-0">Add a single attendance record for <?= Html::encode($fdp->title) ?>.</p>
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
                    <div class="col-md-12 mt-3">
                        <div class="d-flex justify-content-end gap-2">
                            <?= Html::a('Cancel', ['index', 'fdpId' => $fdp->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= Html::submitButton('Save Attendance', ['class' => 'btn btn-primary btn-sm']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Bulk Upload Attendance</h2>
                    <p class="text-muted mb-0">Upload a CSV to insert multiple attendance rows (async).</p>
                </div>
            </div>

            <div id="csv-dropzone" class="border rounded p-4 text-center" style="background:#fbfbfd; cursor: pointer;">
                <div id="dz-message">Drag & drop CSV here, or click to select file</div>
                <input id="dz-file-input" type="file" name="file" accept=".csv,text/csv" style="display:none" />
                <div class="mt-2 text-muted">CSV should contain columns: name,email,status</div>
                <div id="dz-progress" class="mt-3" style="display:none">
                    <div class="progress">
                        <div id="dz-progress-bar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                    </div>
                    <div id="dz-status" class="small text-muted mt-1"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function(){
        const dropzone = document.getElementById('csv-dropzone');
        const input = document.getElementById('dz-file-input');
        const progressWrap = document.getElementById('dz-progress');
        const progressBar = document.getElementById('dz-progress-bar');
        const statusText = document.getElementById('dz-status');

        const csrfParam = '<?= Yii::$app->request->csrfParam ?>';
        const csrfToken = '<?= Yii::$app->request->getCsrfToken() ?>';

        dropzone.addEventListener('click', () => input.click());

        ['dragenter','dragover'].forEach(e => dropzone.addEventListener(e, (ev) => {
            ev.preventDefault(); ev.stopPropagation(); dropzone.classList.add('border-primary');
        }));
        ['dragleave','drop'].forEach(e => dropzone.addEventListener(e, (ev) => {
            ev.preventDefault(); ev.stopPropagation(); dropzone.classList.remove('border-primary');
        }));

        dropzone.addEventListener('drop', (ev) => {
            const f = ev.dataTransfer.files && ev.dataTransfer.files[0];
            if (f) uploadFile(f);
        });

        input.addEventListener('change', (e) => {
            const f = e.target.files && e.target.files[0];
            if (f) uploadFile(f);
        });

        function uploadFile(file) {
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('Please upload a CSV file'); return;
            }

            const xhr = new XMLHttpRequest();
            const form = new FormData();
            form.append('file', file);
            form.append('fdpId', '<?= (int)$fdp->id ?>');
            form.append(csrfParam, csrfToken);

            xhr.open('POST', '<?= \yii\helpers\Url::to(['upload']) ?>', true);
            xhr.setRequestHeader('X-CSRF-Token', csrfToken);
            xhr.responseType = 'json';

            xhr.upload.onprogress = function(e){
                if(e.lengthComputable){
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressWrap.style.display = 'block';
                    progressBar.style.width = pct + '%';
                    progressBar.textContent = pct + '%';
                }
            };

            xhr.onload = function(){
                try{
                    const res = xhr.response;
                    if(res && res.success){
                        statusText.textContent = 'Uploaded: ' + (res.inserted || 0) + ' rows';
                    } else {
                        statusText.textContent = 'Upload failed: ' + (res && res.message ? res.message : 'Unknown error');
                    }
                } catch(err){
                    statusText.textContent = 'Upload failed';
                }
            };

            xhr.onerror = function(){ statusText.textContent = 'Upload error'; };

            xhr.send(form);
        }
    })();
    </script>
</div>
