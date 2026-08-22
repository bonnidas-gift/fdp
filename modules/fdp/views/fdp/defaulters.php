<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var array $defaulters */
/** @var int $participantCount */
/** @var int $defaulterCount */
/** @var int $attendanceCount */
/** @var int $presentCount */
/** @var int $coverageRate */
/** @var int $defaulterRate */

use yii\helpers\Html;

$this->title = 'Defaulters for ' . $fdp->title;
?>
<style>
    .fdp-page-shell {
        background: linear-gradient(180deg, #f5f8fc 0%, #eef3f9 100%);
        border-radius: 24px;
        padding: 24px;
    }

    .fdp-page-hero {
        background: linear-gradient(135deg, #4a1d2f 0%, #8a2d3d 55%, #c24f3c 100%);
        color: #fff;
        border-radius: 24px;
        overflow: hidden;
    }

    .fdp-page-hero .hero-copy .text-muted,
    .fdp-page-hero .hero-copy .soft-label,
    .fdp-page-hero .hero-copy .badge {
        color: rgba(255, 255, 255, 0.85) !important;
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

    .metric-danger { background: rgba(220, 53, 69, 0.12); color: #b02a37; }
    .metric-primary { background: rgba(33, 90, 113, 0.1); color: #215a71; }
    .metric-success { background: rgba(25, 135, 84, 0.1); color: #198754; }
    .metric-info { background: rgba(13, 202, 240, 0.12); color: #087990; }
</style>

<div class="fdp-page-shell">
    <div class="card border-0 shadow-sm fdp-page-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 hero-copy">
                    <div class="soft-label mb-2 text-white-50">Defaulter Report</div>
                    <h1 class="display-6 fw-semibold mb-3"><?= Html::encode($fdp->title) ?></h1>
                    <p class="mb-4 text-white-75">A focused list of absent participants with quick visibility into the size of the defaulter pool and overall attendance coverage.</p>
                    
                </div>
                <div class="col-lg-4">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-sm h-100">
                        <div class="soft-label mb-3">Quick Action</div>
                        <?= Html::a('Back to FDP', ['view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary w-100']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    <?php if (!empty($defaulters)): ?>
        <div class="card fdp-table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($defaulters as $person): ?>
                                <tr>
                                    <td class="ps-4"><?= Html::encode($person->faculty_name) ?></td>
                                    <td><?= Html::encode($person->faculty_email) ?></td>
                                    <td><span class="badge bg-danger"><?= Html::encode($person->status) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mb-0">No absentees found for the current FDP.</div>
    <?php endif; ?>
</div>
