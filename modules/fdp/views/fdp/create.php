<?php
/** @var \app\modules\fdp\models\Fdp $model */

use yii\helpers\Html;

$this->title = 'Create FDP';
?>
<div class="card shadow-sm border-0" style="max-width: 900px; margin: 24px auto 0;">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Create FDP</h1>
                <p class="text-muted mb-0">Add a new faculty development programme.</p>
            </div>
            <?= Html::a('Back to FDP List', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
