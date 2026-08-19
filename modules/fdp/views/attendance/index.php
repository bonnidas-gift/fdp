<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var \yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;

$this->title = 'Attendance - ' . $fdp->title;
?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= Html::encode($fdp->title) ?></h1>
                <p class="text-muted mb-0">Attendance records and upload management.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a('Back to FDP', ['/fdp/fdp/view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('Add Attendance', ['create', 'fdpId' => $fdp->id], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

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
                <table class="table table-bordered table-hover align-middle mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?= Html::encode($record->id) ?></td>
                                <td><?= Html::encode($record->faculty_name) ?></td>
                                <td><?= Html::encode($record->faculty_email) ?></td>
                                <td>
                                    <?php
                                    $statusClass = $record->status === 'Present' ? 'success' : ($record->status === 'Absent' ? 'danger' : 'secondary');
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= Html::encode($record->status) ?></span>
                                </td>
                                <td class="text-center">
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
