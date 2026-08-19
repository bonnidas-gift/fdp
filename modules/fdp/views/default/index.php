<?php
/** @var \yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;

$this->title = 'FDP Management';
$fdps = $dataProvider->getModels();
?>
<div class="card shadow-sm border-0" style="margin-top: 16px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">FDP Management</h1>
                <p class="text-muted mb-0">Create, review, and track faculty development programmes.</p>
            </div>
            <?= Html::a('Create FDP', ['create'], ['class' => 'btn btn-primary btn-lg']) ?>
        </div>

        <?php if (!empty($fdps)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-3">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">Title</th>
                            <th style="width: 12%;">Start</th>
                            <th style="width: 12%;">End</th>
                            <th style="width: 10%;">Mode</th>
                            <th style="width: 18%;">Venue / Link</th>
                            <th style="width: 12%;">Coordinator</th>
                            <th style="width: 16%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fdps as $fdp): ?>
                            <tr>
                                <td><strong><?= Html::encode($fdp->title) ?></strong></td>
                                <td><?= Html::encode($fdp->start_date) ?></td>
                                <td><?= Html::encode($fdp->end_date) ?></td>
                                <td><span class="badge bg-secondary"><?= Html::encode($fdp->mode) ?></span></td>
                                <td><?= Html::encode($fdp->venue ?: $fdp->meeting_link) ?></td>
                                <td><?= Html::encode($fdp->coordinator_name) ?></td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <?= Html::a('View', ['view', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a('Edit', ['update', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                        <?= Html::a('Attendance', ['attendance', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-warning']) ?>
                                        <?= Html::a('Delete', ['delete', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Are you sure you want to delete this FDP?', 'data-method' => 'post']) ?>
                                    </div>
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
            <div class="alert alert-light border mb-0" role="alert">
                No FDPs created yet. Click <strong>Create FDP</strong> to add the first programme.
            </div>
        <?php endif; ?>
    </div>
</div>
