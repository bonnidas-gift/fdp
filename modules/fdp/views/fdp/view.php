<?php
/** @var \app\modules\fdp\models\Fdp $model */
/** @var int $participantCount */
/** @var int $attendanceCount */
/** @var int $attendedCount */
/** @var int $absentCount */
/** @var int $onDutyCount */
/** @var int $leaveCount */
/** @var int $attendanceCoverage */
/** @var int $attendanceRate */

use yii\helpers\Html;

$this->title = $model->title;

$venue = trim((string) ($model->venue ?: $model->meeting_link));
if ($venue === '') {
    $venue = 'Not provided';
}

$timeRange = trim((string) $model->time);
if ($timeRange === '') {
    $timeRange = trim((string) ($model->time_start . ' - ' . $model->time_end));
}
if ($timeRange === '' || $timeRange === '-') {
    $timeRange = 'Not provided';
}

$metricCards = [
    [
        'label' => 'Total Participants',
        'value' => $participantCount,
        'hint' => 'Registered faculty members',
        'class' => 'metric-primary',
    ],
    [
        'label' => 'Attendance Marked',
        'value' => $attendanceCount,
        'hint' => 'Records uploaded so far',
        'class' => 'metric-success',
    ],
    [
        'label' => 'Attended',
        'value' => $attendedCount,
        'hint' => 'Present participants',
        'class' => 'metric-info',
    ],
    [
        'label' => 'Defaulters',
        'value' => $absentCount,
        'hint' => 'Absent participants',
        'class' => 'metric-danger',
    ],
];
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
    .fdp-page-hero .hero-copy .text-white-75,
    .fdp-page-hero .hero-copy .badge {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .fdp-page-hero .hero-copy .badge.bg-light,
    .fdp-page-hero .hero-copy .fdp-hero-pill {
        background: #edf4fb !important;
        color: #16324f !important;
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

</style>

<div class="fdp-page-shell">
    <div class="card border-0 shadow-sm fdp-page-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 hero-copy">
                    <div class="soft-label mb-2 text-white-50">FDP Overview</div>
                    <h1 class="display-6 fw-semibold mb-3"><?= Html::encode($model->title) ?></h1>
                    <p class="mb-4 text-white-75">Overview of the programme, participant volume, and attendance status.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill fdp-hero-pill">Mode: <?= Html::encode($model->mode) ?></span>
                        <span class="badge rounded-pill fdp-hero-pill">Participants: <?= number_format($participantCount) ?></span>
                        <span class="badge rounded-pill fdp-hero-pill">Attendance rate: <?= number_format($attendanceRate) ?>%</span>
                        <span class="badge rounded-pill fdp-hero-pill">Coverage: <?= number_format($attendanceCoverage) ?>%</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-sm h-100">
                        <div class="soft-label mb-3">Quick Actions</div>
                        <div class="d-grid gap-2">
                            <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                            <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('Participants', ['/fdp/participant/index', 'fdpId' => $model->id], ['class' => 'btn btn-info text-white']) ?>
                            <?= Html::a('Attendance', ['/fdp/attendance/index', 'fdpId' => $model->id], ['class' => 'btn btn-warning']) ?>
                            <?= Html::a('Defaulters', ['defaulters', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($metricCards as $metric): ?>
            <div class="col-md-6 col-xl-3">
                <div class="card fdp-summary-card h-100">
                    <div class="card-body p-4">
                        <div class="metric-pill <?= Html::encode($metric['class']) ?> mb-3"><?= Html::encode($metric['label']) ?></div>
                        <div class="display-6 fw-semibold mb-2"><?= number_format((int) $metric['value']) ?></div>
                        <div class="text-muted small"><?= Html::encode($metric['hint']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card fdp-table-card mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="border rounded-4 p-4 h-100 bg-light">
                        <div class="soft-label mb-2">Core Details</div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Schedule</span>
                            <strong><?= Html::encode($model->start_date . ' to ' . $model->end_date) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Time</span>
                            <strong><?= Html::encode($timeRange) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Mode</span>
                            <strong><span class="badge bg-secondary"><?= Html::encode($model->mode) ?></span></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Venue / Link</span>
                            <strong><?= Html::encode($venue) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span>Coordinator</span>
                            <strong><?= Html::encode($model->coordinator_name) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-4 p-4 h-100 bg-light">
                        <div class="soft-label mb-2">Attendance Snapshot</div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Attendance coverage</span>
                            <strong><?= number_format($attendanceCoverage) ?>%</strong>
                        </div>
                        <div class="progress mb-4" style="height: 12px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= (int) $attendanceCoverage ?>%" aria-valuenow="<?= (int) $attendanceCoverage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Present</div>
                                    <div class="h4 mb-0 text-success"><?= number_format($attendedCount) ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Absent</div>
                                    <div class="h4 mb-0 text-danger"><?= number_format($absentCount) ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 h-100">
                                    <div class="text-muted small">On Duty</div>
                                    <div class="h4 mb-0 text-info"><?= number_format($onDutyCount) ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Leave</div>
                                    <div class="h4 mb-0 text-secondary"><?= number_format($leaveCount) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
