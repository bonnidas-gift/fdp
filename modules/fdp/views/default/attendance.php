<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \app\modules\fdp\models\FdpAttendance $model */
/** @var array $records */

use yii\helpers\Html;

$this->title = 'Attendance for ' . $fdp->title;
?>
<div class="card shadow-sm border-0" style="margin-top: 20px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Attendance</h1>
                <p class="text-muted mb-0"><?= Html::encode($fdp->title) ?></p>
            </div>
            <?= Html::a('Back to FDP', ['view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="border rounded p-3 bg-light h-100">
                    <form method="post" enctype="multipart/form-data">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                        <?= Html::hiddenInput('FdpAttendance[fdp_id]', $fdp->id) ?>

                        <div class="mb-3">
                            <label class="form-label">Faculty Name</label>
                            <?= Html::textInput('FdpAttendance[faculty_name]', $model->faculty_name, ['class' => 'form-control']) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <?= Html::textInput('FdpAttendance[faculty_email]', $model->faculty_email, ['class' => 'form-control']) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attendance Status</label>
                            <?= Html::dropDownList('FdpAttendance[status]', $model->status, \app\modules\fdp\models\FdpAttendance::statusOptions(), ['class' => 'form-select']) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <?= Html::textarea('FdpAttendance[notes]', $model->notes, ['class' => 'form-control', 'rows' => 3]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bulk Upload (CSV)</label>
                            <input type="file" name="attendance_file" accept=".csv" class="form-control" />
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Attendance</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <?php if (!empty($records)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= Html::encode($record->faculty_name) ?></td>
                                        <td><?= Html::encode($record->faculty_email) ?></td>
                                        <td>
                                            <?php if ($record->status === 'Absent'): ?>
                                                <span class="badge bg-danger"><?= Html::encode($record->status) ?></span>
                                            <?php elseif ($record->status === 'Leave'): ?>
                                                <span class="badge bg-warning text-dark"><?= Html::encode($record->status) ?></span>
                                            <?php elseif ($record->status === 'On Duty'): ?>
                                                <span class="badge bg-info text-dark"><?= Html::encode($record->status) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?= Html::encode($record->status) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Html::encode($record->notes ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">No attendance records yet for this FDP.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
