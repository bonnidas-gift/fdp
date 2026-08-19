<?php

declare(strict_types=1);

namespace app\modules\fdp\controllers;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpAttendance;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class AttendanceController extends Controller
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

        $query = FdpAttendance::find()->where(['fdp_id' => $fdpId])->orderBy(['id' => SORT_DESC]);

        $search = Yii::$app->request->get('search');
        if (is_string($search) && trim($search) !== '') {
            $keyword = trim($search);
            $query->andWhere(['or', ['like', 'faculty_name', $keyword], ['like', 'faculty_email', $keyword], ['like', 'status', $keyword]]);
        }

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

        $model = new FdpAttendance();
        $model->fdp_id = $fdpId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Attendance saved successfully.');

            return $this->redirect(['index', 'fdpId' => $fdpId]);
        }

        // keep create action focused on single-record form submission
        // CSV bulk uploads are handled by actionUpload below (async)

        return $this->render('create', ['fdp' => $fdp, 'model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = FdpAttendance::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested attendance record does not exist.');
        }

        $fdpId = $model->fdp_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Attendance row deleted successfully.');

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

        $handle = fopen($file->tempName, 'rb');
        if ($handle === false) {
            return ['success' => false, 'message' => 'Unable to open uploaded file'];
        }

        $inserted = 0;
        $batch = [];
        $chunkSize = 200;
        $header = null;
        $columns = ['fdp_id', 'faculty_name', 'faculty_email', 'status'];
        $errors = [];
        $line = 0; // tracking CSV line number

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // explicit fgetcsv parameters to be future-proof
            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $line++;
                if ($header === null) {
                    $header = array_map(static fn ($value) => strtolower(trim((string) $value)), $data);
                    continue;
                }

                $row = [];
                foreach ($header as $index => $key) {
                    $row[$key] = $data[$index] ?? '';
                }

                $name = trim((string) ($row['name'] ?? $row['faculty'] ?? ''));
                $email = trim((string) ($row['email'] ?? $row['faculty_email'] ?? ''));
                $status = FdpAttendance::normalizeStatus((string) ($row['status'] ?? $row['attendance'] ?? 'Present'));

                // per-row validation
                $rowErrors = [];
                if ($name === '') {
                    $rowErrors[] = 'Name is required';
                }
                if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'Invalid email';
                }

                if (!empty($rowErrors)) {
                    $errors[] = ['row' => $line, 'errors' => $rowErrors];
                    // skip adding to batch
                } else {
                    $batch[] = [$fdpId, $name, $email, $status];
                }

                if (count($batch) >= $chunkSize) {
                    $count = Yii::$app->db->createCommand()->batchInsert(FdpAttendance::tableName(), $columns, $batch)->execute();
                    $inserted += (int) $count;
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                $count = Yii::$app->db->createCommand()->batchInsert(FdpAttendance::tableName(), $columns, $batch)->execute();
                $inserted += (int) $count;
            }

            $transaction->commit();
            fclose($handle);

            return ['success' => true, 'message' => 'Uploaded', 'inserted' => $inserted, 'errors' => $errors, 'processed' => $line - 1];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            fclose($handle);
            Yii::error($e->getMessage(), __METHOD__);

            return ['success' => false, 'message' => 'Failed to process file'];
        }
    }
}
