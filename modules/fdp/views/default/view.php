<?php
/** @var \app\modules\fdp\models\Fdp $model */

use yii\helpers\Html;

$this->title = $model->title;
?>
<div class="card shadow-sm border-0" style="margin-top: 20px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= Html::encode($model->title) ?></h1>
                <p class="text-muted mb-0">FDP overview and workflow actions</p>
            </div>
            <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <?= Html::a('Send Reminder', ['send-reminder', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('Attendance', ['attendance', 'id' => $model->id], ['class' => 'btn btn-warning text-white']) ?>
            <?= Html::a('Defaulters', ['defaulters', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], ['class' => 'btn btn-outline-danger', 'data-confirm'=>'Are you sure you want to delete this FDP?', 'data-method'=>'post']) ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr>
                        <th style="width: 25%;">Title</th>
                        <td><?= Html::encode($model->title) ?></td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td><?= Html::encode($model->start_date) ?></td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td><?= Html::encode($model->end_date) ?></td>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td><?= Html::encode($model->time) ?></td>
                    </tr>
                    <tr>
                        <th>Mode</th>
                        <td><span class="badge bg-secondary"><?= Html::encode($model->mode) ?></span></td>
                    </tr>
                    <tr>
                        <th>Venue / Link</th>
                        <td><?= Html::encode($model->venue ?: $model->meeting_link) ?></td>
                    </tr>
                    <tr>
                        <th>Coordinator</th>
                        <td><?= Html::encode($model->coordinator_name) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
