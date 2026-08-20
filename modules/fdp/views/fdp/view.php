<?php
/** @var \app\modules\fdp\models\Fdp $model */

use yii\helpers\Html;

$this->title = $model->title;
?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= Html::encode($model->title) ?></h1>
                <p class="text-muted mb-0">FDP overview and actions</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Participants', ['/fdp/participant/index', 'fdpId' => $model->id], ['class' => 'btn btn-info']) ?>
                <?= Html::a('Attendance', ['/fdp/attendance/index', 'fdpId' => $model->id], ['class' => 'btn btn-warning']) ?>
                <?= Html::a('Defaulters', ['defaulters', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $model->id], ['class' => 'btn btn-outline-danger', 'data-confirm' => 'Delete this FDP?', 'data-method' => 'post']) ?>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small text-uppercase">Schedule</div>
                    <div class="mt-2"><strong>Start:</strong> <?= Html::encode($model->start_date) ?></div>
                    <div><strong>End:</strong> <?= Html::encode($model->end_date) ?></div>
                    <div><strong>Time:</strong> <?= Html::encode($model->time ?: ($model->time_start . ' - ' . $model->time_end)) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small text-uppercase">Delivery</div>
                    <div class="mt-2"><strong>Mode:</strong> <?= Html::encode($model->mode) ?></div>
                    <div><strong>Venue:</strong> <?= Html::encode($model->venue ?: $model->meeting_link) ?></div>
                    <div><strong>Coordinator:</strong> <?= Html::encode($model->coordinator_name) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
