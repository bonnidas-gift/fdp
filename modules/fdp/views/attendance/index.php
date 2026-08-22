<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var int $recordCount */
/** @var int $presentCount */
/** @var int $absentCount */
/** @var int $onDutyCount */
/** @var int $leaveCount */
/** @var int $participantCount */
/** @var int $attendanceRate */
/** @var int $coverageRate */

use yii\helpers\Html;

$this->title = 'Attendance - ' . $fdp->title;
?>
<style>
    .fdp-page-shell {
        background: linear-gradient(180deg, #f5f8fc 0%, #eef3f9 100%);
        border-radius: 24px;
        padding: 24px;
    }

    .fdp-page-hero {
        background: linear-gradient(135deg, #17324f 0%, #255971 52%, #4d7c7b 100%);
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
        color: #17324f !important;
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75) !important;
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

    .metric-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .75rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 600;
    }

    .metric-primary { background: rgba(33, 90, 113, 0.1); color: #215a71; }
    .metric-success { background: rgba(25, 135, 84, 0.1); color: #198754; }
    .metric-danger { background: rgba(220, 53, 69, 0.1); color: #b02a37; }
    .metric-info { background: rgba(13, 202, 240, 0.12); color: #087990; }
</style>

<div class="fdp-page-shell">
    <div class="card border-0 shadow-sm fdp-page-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 hero-copy">
                    <div class="soft-label mb-2 text-white-50">Attendance Register</div>
                    <h1 class="display-6 fw-semibold mb-3"><?= Html::encode($fdp->title) ?></h1>
                    <p class="mb-4 text-white-75">Track attendance uploads, present and absent status, and the overall coverage against the FDP participant base.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill bg-light text-dark">Participants: <?= number_format($participantCount) ?></span>
                        <span class="badge rounded-pill bg-light text-dark">Records: <?= number_format($recordCount) ?></span>
                        <span class="badge rounded-pill bg-light text-dark">Coverage: <?= number_format($coverageRate) ?>%</span>
                        <span class="badge rounded-pill bg-light text-dark">Present rate: <?= number_format($attendanceRate) ?>%</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-sm h-100">
                        <div class="soft-label mb-3">Quick Actions</div>
                        <div class="d-grid gap-2">
                            <?= Html::a('Back to FDP', ['/fdp/fdp/view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
                            <?= Html::a('Add Attendance', ['create', 'fdpId' => $fdp->id], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card fdp-summary-card h-100">
                <div class="card-body p-4">
                    <div class="metric-pill metric-primary mb-3">Total Records</div>
                    <div class="display-6 fw-semibold mb-2"><?= number_format($recordCount) ?></div>
                    <div class="text-muted small">Attendance entries captured</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card fdp-summary-card h-100">
                <div class="card-body p-4">
                    <div class="metric-pill metric-success mb-3">Present</div>
                    <div class="display-6 fw-semibold mb-2 text-success"><?= number_format($presentCount) ?></div>
                    <div class="text-muted small">Marked as present</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card fdp-summary-card h-100">
                <div class="card-body p-4">
                    <div class="metric-pill metric-danger mb-3">Absent</div>
                    <div class="display-6 fw-semibold mb-2 text-danger"><?= number_format($absentCount) ?></div>
                    <div class="text-muted small">Defaulters recorded</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card fdp-summary-card h-100">
                <div class="card-body p-4">
                    <div class="metric-pill metric-info mb-3">On Duty / Leave</div>
                    <div class="display-6 fw-semibold mb-2 text-info"><?= number_format($onDutyCount + $leaveCount) ?></div>
                    <div class="text-muted small"><?= number_format($onDutyCount) ?> on duty, <?= number_format($leaveCount) ?> on leave</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fdp-table-card mt-4">
        <div class="card-body p-4">
            <form method="get" class="row g-2 mb-4">
                <input type="hidden" name="fdpId" value="<?= Html::encode((string) $fdp->id) ?>" />
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode(Yii::$app->request->get('search', '')) ?>" placeholder="Search by name, email or status" />
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </div>
            </form>

            <?php $records = $dataProvider->getModels(); ?>
            <?php if (!empty($records)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td class="ps-4"><?= Html::encode($record->id) ?></td>
                                    <td><?= Html::encode($record->faculty_name) ?></td>
                                    <td><?= Html::encode($record->faculty_email) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $record->status === 'Present' ? 'success' : ($record->status === 'Absent' ? 'danger' : ($record->status === 'On Duty' ? 'info' : 'secondary'));
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= Html::encode($record->status) ?></span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?= Html::a('Delete', ['delete', 'id' => $record->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this attendance record?', 'data-method' => 'post']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">No attendance records found for this FDP.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
