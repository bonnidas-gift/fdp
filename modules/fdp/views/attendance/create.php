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
                        <?= Html::dropDownList('participant_id', null, ['' => 'Select a participant'] + array_reduce($participants, static function ($carry, $participant) {
                            $carry[(string) $participant->id] = $participant->faculty_name . ' <' . $participant->faculty_email . '>';
                            return $carry;
                        }, []), ['class' => 'form-select mb-3', 'id' => 'participant-selector']) ?>
                    </div>

                    <div id="participant-details" class="col-md-12 d-none">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-uppercase text-muted mb-2">Selected participant</div>
                            <div><strong>Name:</strong> <span id="selected-name">-</span></div>
                            <div><strong>Email:</strong> <span id="selected-email">-</span></div>
                        </div>
                    </div>

                    <div class="col-md-12 d-none">
                        <?= $form->field($model, 'faculty_name')->hiddenInput()->label(false) ?>
                    </div>
                    <div class="col-md-12 d-none">
                        <?= $form->field($model, 'faculty_email')->hiddenInput()->label(false) ?>
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
                    <p class="text-muted mb-0">Upload a CSV or Excel (.xlsx) file to insert multiple attendance rows (async).</p>
                </div>
            </div>

            <div id="csv-dropzone" class="border rounded p-4 text-center" style="background:#fbfbfd; cursor: pointer;">
                <div id="dz-message">Drag & drop CSV / Excel here, or click to select file</div>
                <input id="dz-file-input" type="file" name="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" style="display:none" />
                <div class="mt-2 text-muted">CSV / Excel should contain columns: name,email,status</div>
                <div id="dz-progress" class="mt-3" style="display:none">
                    <div class="progress">
                        <div id="dz-progress-bar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                    </div>
                    <div id="dz-status" class="small text-muted mt-1"></div>
                    <pre id="dz-report" class="mt-2 small text-muted" style="display:none; white-space:pre-wrap;"></pre>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function(){
        const participantSelector = document.getElementById('participant-selector');
        const selectedName = document.getElementById('selected-name');
        const selectedEmail = document.getElementById('selected-email');
        const selectedDetails = document.getElementById('participant-details');
        const facultyNameInput = document.getElementById('fdpattendance-faculty_name');
        const facultyEmailInput = document.getElementById('fdpattendance-faculty_email');
        const participants = <?= json_encode(array_reduce($participants, static function ($carry, $participant) {
            $carry[(string) $participant->id] = [
                'name' => $participant->faculty_name,
                'email' => $participant->faculty_email,
            ];
            return $carry;
        }, []), JSON_THROW_ON_ERROR) ?>;

        function applyParticipant(id){
            const participant = participants[id];
            if (!participant) {
                selectedDetails.classList.add('d-none');
                selectedName.textContent = '-';
                selectedEmail.textContent = '-';
                if (facultyNameInput) facultyNameInput.value = '';
                if (facultyEmailInput) facultyEmailInput.value = '';
                return;
            }

            selectedName.textContent = participant.name;
            selectedEmail.textContent = participant.email;
            selectedDetails.classList.remove('d-none');
            if (facultyNameInput) facultyNameInput.value = participant.name;
            if (facultyEmailInput) facultyEmailInput.value = participant.email;
        }

        participantSelector.addEventListener('change', function(){
            applyParticipant(this.value);
        });

        if (participantSelector.value) {
            applyParticipant(participantSelector.value);
        }

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
            const lower = file.name.toLowerCase();
            const valid = lower.endsWith('.csv') || lower.endsWith('.xlsx');
            if (!valid) {
                alert('Please upload a CSV or .xlsx file'); return;
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
                        const inserted = res.inserted || 0;
                        const skipped = res.skipped || res.not_found || 0;
                        const errors = res.errors || [];
                        statusText.textContent = 'Inserted: ' + inserted + ' rows. Skipped: ' + skipped + '. Errors: ' + errors.length;
                        const reportEl = document.getElementById('dz-report');
                        const detailLines = [];
                        if (errors.length > 0) {
                            detailLines.push(...errors.map(e => 'Row ' + e.row + ': ' + (e.errors || []).join('; ')));
                        }
                        if (skipped > 0) {
                            detailLines.push('Skipped ' + skipped + ' faculty rows not found in current FDP participant list.');
                        }
                        if (detailLines.length > 0) {
                            reportEl.style.display = 'block';
                            reportEl.textContent = detailLines.join('\n');
                        } else {
                            reportEl.style.display = 'none';
                            reportEl.textContent = '';
                        }
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
