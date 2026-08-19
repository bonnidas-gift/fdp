<?php
/** @var \app\modules\fdp\models\Fdp $fdp */
/** @var array $defaulters */

use yii\helpers\Html;

$this->title = 'Defaulters for ' . $fdp->title;
?>
<div class="card shadow-sm border-0" style="margin-top: 20px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Defaulter List</h1>
                <p class="text-muted mb-0"><?= Html::encode($fdp->title) ?></p>
            </div>
            <?= Html::a('Back to FDP', ['view', 'id' => $fdp->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php if (!empty($defaulters)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($defaulters as $person): ?>
                            <tr>
                                <td><?= Html::encode($person->faculty_name) ?></td>
                                <td><?= Html::encode($person->faculty_email) ?></td>
                                <td><span class="badge bg-danger"><?= Html::encode($person->status) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <?= Html::a('Send Defaulter Mail', ['send-defaulter-mail', 'id' => $fdp->id], ['class' => 'btn btn-danger']) ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border mb-0">No absentees found for the current FDP.</div>
        <?php endif; ?>
    </div>
</div>
