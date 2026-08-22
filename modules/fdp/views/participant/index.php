<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var int $participantCount */

use yii\helpers\Html;

$this->title = 'Participants - ' . $fdp->title;
?>
<style>
    .fdp-page-shell {
        background: linear-gradient(180deg, #f5f8fc 0%, #eef3f9 100%);
        border-radius: 24px;
        padding: 24px;
    }

    .fdp-page-hero {
        background: linear-gradient(135deg, #16324f 0%, #215a71 55%, #2e7d7f 100%);
        color: #fff;
        border-radius: 24px;
        overflow: hidden;
    }

    .fdp-page-hero .hero-copy .text-muted,
    .fdp-page-hero .hero-copy .soft-label,
    .fdp-page-hero .hero-copy .badge {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .fdp-page-hero .hero-copy .badge.bg-light {
        color: #16324f !important;
    }

    .fdp-summary-card,
    .fdp-table-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 14px 40px rgba(17, 41, 71, 0.08);
    }

    .soft-label {
        letter-spacing: .08em;
        font-size: .75rem;
        text-transform: uppercase;
        color: #6c7a89;
    }
</style>

<div class="fdp-page-shell">
    <div class="card border-0 shadow-sm fdp-page-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 hero-copy">
                    <div class="soft-label mb-2 text-white-50">Participant Register</div>
                    <h1 class="display-6 fw-semibold mb-3"><?= Html::encode($fdp->title) ?></h1>
                    <p class="mb-4 text-white-75">Manage the participant list and upload bulk participant files in one place.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill bg-light text-dark">Total participants: <?= number_format($participantCount) ?></span>
                        
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-sm h-100">
                        <div class="soft-label mb-3">Quick Actions</div>
                        <div class="d-grid gap-2">
                            <?= Html::a('Back to FDP', ['/fdp/fdp/view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
                            <?= Html::a('Add Participant', ['create', 'fdpId' => $fdp->id], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fdp-summary-card mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-2">Bulk Upload Participants</h2>
            <p class="text-muted mb-3">Upload CSV or .xlsx files. Only valid staff/faculty rows are inserted.</p>
            <div id="participant-dropzone" class="border border-dashed rounded-4 p-4 text-center bg-white" style="cursor:pointer;">
                <div id="participant-dz-message">Drag & drop CSV / Excel here, or click to select file</div>
                <input id="participant-dz-file-input" type="file" name="file" accept=".csv,.xlsx" style="display:none" />
                <div id="participant-dz-progress" class="mt-3" style="display:none">
                    <div class="progress">
                        <div id="participant-dz-progress-bar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                    </div>
                    <div id="participant-dz-status" class="small text-muted mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    <?php $records = $dataProvider->getModels(); ?>
    <?php if (!empty($records)): ?>
        <div class="card fdp-table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td class="ps-4"><?= Html::encode((string) $record->id) ?></td>
                                    <td><?= Html::encode($record->faculty_name) ?></td>
                                    <td><?= Html::encode($record->faculty_email) ?></td>
                                    <td><?= Html::encode($record->department ?: '-') ?></td>
                                    <td><?= Html::encode($record->designation ?: '-') ?></td>
                                    <td class="text-center pe-4">
                                        <?= Html::a('Delete', ['delete', 'id' => $record->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this participant?', 'data-method' => 'post']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mb-0">No participants added for this FDP yet.</div>
    <?php endif; ?>
</div>

<script>
(function(){
    const dropzone = document.getElementById('participant-dropzone');
    const input = document.getElementById('participant-dz-file-input');
    const progressWrap = document.getElementById('participant-dz-progress');
    const progressBar = document.getElementById('participant-dz-progress-bar');
    const statusText = document.getElementById('participant-dz-status');
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
        const file = ev.dataTransfer.files && ev.dataTransfer.files[0];
        if (file) uploadFile(file);
    });

    input.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (file) uploadFile(file);
    });

    function uploadFile(file) {
        const valid = ['.csv', '.xlsx'];
        const ext = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
        if (!valid.includes(ext)) {
            alert('Please upload a CSV or .xlsx file');
            return;
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
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                progressWrap.style.display = 'block';
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';
            }
        };

        xhr.onload = function(){
            const res = xhr.response || {};
            if (res.success) {
                statusText.textContent = 'Inserted: ' + (res.inserted || 0) + ' rows';
                if (res.errors && res.errors.length > 0) {
                    console.warn(res.errors);
                }
                setTimeout(() => window.location.reload(), 700);
            } else {
                statusText.textContent = 'Upload failed: ' + (res.message || 'Unknown error');
            }
        };

        xhr.onerror = function(){
            statusText.textContent = 'Upload error';
        };

        xhr.send(form);
    }
})();
</script>
