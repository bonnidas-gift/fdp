<?php
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \app\modules\fdp\models\Fdp $searchModel */

use yii\helpers\Html;

$this->title = 'FDP List';

$fdps = $dataProvider->getModels();
$totalCount = (int) $dataProvider->getTotalCount();
$visibleCount = count($fdps);
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
                    <div class="soft-label mb-2 text-white-50">FDP Registry</div>
                    <h1 class="display-6 fw-semibold mb-3">FDP List</h1>
                    <p class="mb-4 text-white-75">Manage all faculty development programmes in one table, with quick access to the full record, participants, attendance, and defaulters.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill bg-light text-dark">Total FDPs: <?= number_format($totalCount) ?></span>
                        <span class="badge rounded-pill bg-light text-dark">Visible on page: <?= number_format($visibleCount) ?></span>
                       
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-sm h-100">
                        <div class="soft-label mb-3">Quick Action</div>
                        <div class="d-grid gap-2">
                            <?= Html::a('Create FDP', ['create'], ['class' => 'btn btn-primary btn-md']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <div class="card fdp-table-card">
        <div class="card-body p-4">
            <form method="get" class="row g-2 mb-4">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode(Yii::$app->request->get('search', '')) ?>" placeholder="Search by title, coordinator or mode" />
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </div>
            </form>

            <?php if (!empty($fdps)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Dates</th>
                                <th>Time</th>
                                <th>Mode</th>
                                <th>Venue / Link</th>
                                <th>Coordinator</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fdps as $fdp): ?>
                                <tr>
                                    <td class="ps-4"><strong><?= Html::encode($fdp->title) ?></strong></td>
                                    <td><?= Html::encode($fdp->start_date . ' - ' . $fdp->end_date) ?></td>
                                    <td><?= Html::encode($fdp->time) ?></td>
                                    <td><span class="badge bg-secondary"><?= Html::encode($fdp->mode) ?></span></td>
                                    <td><?= Html::encode($fdp->venue ?: $fdp->meeting_link) ?></td>
                                    <td><?= Html::encode($fdp->coordinator_name) ?></td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            <?= Html::a('View', ['view', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                            <?= Html::a('Edit', ['update', 'id' => $fdp->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                            <?= Html::a('Participants', ['/fdp/participant/index', 'fdpId' => $fdp->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
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
</div>
