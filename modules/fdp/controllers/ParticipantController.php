<?php

declare(strict_types=1);

namespace app\modules\fdp\controllers;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpParticipant;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ParticipantController extends Controller
{
    public function actionIndex(?int $fdpId = null): string|Response
    {
        $fdpId = $fdpId ?? (int) Yii::$app->request->get('fdpId');

        if ($fdpId <= 0) {
            Yii::$app->session->setFlash('error', 'Please select an FDP first.');

            return $this->redirect(['/fdp/fdp/index']);
        }

        $fdp = Fdp::findOne($fdpId);
        if ($fdp === null) {
            throw new NotFoundHttpException('The requested FDP does not exist.');
        }

        $query = FdpParticipant::find()->where(['fdp_id' => $fdpId])->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', ['fdp' => $fdp, 'dataProvider' => $dataProvider]);
    }

    public function actionCreate(?int $fdpId = null): string|Response
    {
        $fdpId = $fdpId ?? (int) Yii::$app->request->get('fdpId');

        if ($fdpId <= 0) {
            Yii::$app->session->setFlash('error', 'Please select an FDP first.');

            return $this->redirect(['/fdp/fdp/index']);
        }

        $fdp = Fdp::findOne($fdpId);
        if ($fdp === null) {
            throw new NotFoundHttpException('The requested FDP does not exist.');
        }

        $model = new FdpParticipant();
        $model->fdp_id = $fdpId;

        if ($model->load(Yii::$app->request->post())) {
            $email = FdpParticipant::normalizeEmail((string) $model->faculty_email);
            if ($email !== '' && FdpParticipant::emailExistsForFdp($fdpId, $email)) {
                $model->addError('faculty_email', 'This participant already exists for this FDP.');
            } elseif ($model->save()) {
                Yii::$app->session->setFlash('success', 'Participant saved successfully.');

                return $this->redirect(['index', 'fdpId' => $fdpId]);
            }
        }

        return $this->render('create', ['fdp' => $fdp, 'model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = FdpParticipant::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested participant does not exist.');
        }

        $fdpId = $model->fdp_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Participant deleted successfully.');

        return $this->redirect(['index', 'fdpId' => $fdpId]);
    }

    public function actionUpload(?int $fdpId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fdpId = $fdpId ?? (int) Yii::$app->request->post('fdpId');
        if ($fdpId <= 0) {
            return ['success' => false, 'message' => 'Invalid FDP id'];
        }

        $fdp = Fdp::findOne($fdpId);
        if ($fdp === null) {
            return ['success' => false, 'message' => 'FDP not found'];
        }

        $file = UploadedFile::getInstanceByName('file');
        if (!$file || !$file->tempName) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        $extension = strtolower((string) pathinfo($file->name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'xlsx'], true)) {
            return ['success' => false, 'message' => 'Unsupported file type. Please upload CSV or Excel (.xlsx).'];
        }

        $rows = $extension === 'csv'
            ? FdpParticipant::readCsvRows($file->tempName)
            : FdpParticipant::readXlsxRows($file->tempName);

        $filtered = FdpParticipant::filterUniqueRowsForFdp($fdpId, $rows);
        $batch = [];
        $columns = ['fdp_id', 'faculty_name', 'faculty_email', 'department', 'designation'];
        $inserted = 0;
        $errors = [];
        $skipped = $filtered['skipped'];

        foreach ($filtered['valid'] as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $email = FdpParticipant::normalizeEmail((string) ($row['email'] ?? ''));
            $department = trim((string) ($row['department'] ?? ''));
            $designation = trim((string) ($row['designation'] ?? ''));

            $rowErrors = [];
            if ($name === '') {
                $rowErrors[] = 'Name is required';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Valid email is required';
            }

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $index + 1, 'errors' => $rowErrors];
                continue;
            }

            $batch[] = [$fdpId, $name, $email, $department, $designation];
        }

        if ($batch !== []) {
            $inserted = (int) Yii::$app->db->createCommand()->batchInsert(FdpParticipant::tableName(), $columns, $batch)->execute();
        }

        return ['success' => true, 'message' => 'Uploaded', 'inserted' => $inserted, 'skipped' => count($skipped), 'errors' => $errors, 'processed' => count($filtered['valid'])];
    }
}
