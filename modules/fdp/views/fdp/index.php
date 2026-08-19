<?php
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \app\modules\fdp\models\Fdp $searchModel */

use yii\helpers\Html;

$this->title = 'FDP List';
?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">FDP List</h1>
                <p class="text-muted mb-0">Manage all faculty development programmes.</p>
            </div>
            <?= Html::a('Create FDP', ['create'], ['class' => 'btn btn-primary btn-md']) ?>
        </div>

        <form method="get" class="row g-2 mb-4">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" value="<?= Html::encode(Yii::$app->request->get('search', '')) ?>" placeholder="Search by title, coordinator or mode" />
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </div>
        </form>

        <?php $fdps = $dataProvider->getModels(); ?>
        <?php if (!empty($fdps)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Dates</th>
                            <th>Time</th>
                            <th>Mode</th>
                            <th>Venue / Link</th>
                            <th>Coordinator</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fdps as $fdp): ?>
                            <tr>
                                <td><strong><?= Html::encode($fdp->title) ?></strong></td>
                                <td><?= Html::encode($fdp->start_date . ' - ' . $fdp->end_date) ?></td>
                                <td><?= Html::encode($fdp->time) ?></td>
                                <td><span class="badge bg-secondary"><?= Html::encode($fdp->mode) ?></span></td>
                                <td><?= Html::encode($fdp->venue ?: $fdp->meeting_link) ?></td>
                                <td><?= Html::encode($fdp->coordinator_name) ?></td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <?= Html::a('View', ['view', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a('Edit', ['update', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                        <?= Html::a('Attendance', ['/fdp/attendance/index', 'fdpId' => $fdp->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                        <?= Html::a('Delete', ['delete', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this FDP?', 'data-method' => 'post']) ?>
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
            <div class="alert alert-light border mb-0">No FDP records found.</div>
        <?php endif; ?>
    </div>
</div>
