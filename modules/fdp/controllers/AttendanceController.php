<?php

declare(strict_types=1);

namespace app\modules\fdp\controllers;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpAttendance;
use app\modules\fdp\models\FdpMailQueue;
use app\modules\fdp\models\FdpMailService;
use app\modules\fdp\models\FdpParticipant;
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

        if (Yii::$app->request->isPost) {
            $selectedParticipantId = (int) Yii::$app->request->post('participant_id');
            if ($selectedParticipantId > 0) {
                $participant = FdpParticipant::findOne(['id' => $selectedParticipantId, 'fdp_id' => $fdpId]);
                if ($participant !== null) {
                    $model->faculty_name = $participant->faculty_name;
                    $model->faculty_email = $participant->faculty_email;
                }
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->queueDefaulterMailIfNeeded($model);
            Yii::$app->session->setFlash('success', 'Attendance saved successfully.');

            return $this->redirect(['index', 'fdpId' => $fdpId]);
        }

        return $this->render('create', ['fdp' => $fdp, 'model' => $model, 'participants' => $fdp->getParticipants()->orderBy(['faculty_name' => SORT_ASC])->all()]);
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

        $participants = FdpParticipant::find()->where(['fdp_id' => $fdpId])->all();
        $participantIndex = [];
        foreach ($participants as $participant) {
            $email = FdpParticipant::normalizeEmail((string) $participant->faculty_email);
            if ($email !== '') {
                $participantIndex[$email] = [
                    'fdp_id' => (int) $participant->fdp_id,
                    'faculty_name' => (string) $participant->faculty_name,
                    'faculty_email' => $email,
                ];
            }
        }

        $extension = strtolower((string) pathinfo($file->name, PATHINFO_EXTENSION));
        $rows = [];
        if ($extension === 'csv') {
            $rows = FdpAttendance::readCsvRows($file->tempName);
        } elseif ($extension === 'xlsx') {
            $rows = FdpAttendance::readXlsxRows($file->tempName);
        } else {
            return ['success' => false, 'message' => 'Unsupported file type. Please upload CSV or Excel (.xlsx) files.'];
        }

        $existing = FdpAttendance::find()->select('faculty_email')->where(['fdp_id' => $fdpId])->asArray()->all();
        $existingEmails = array_map(static fn ($row) => FdpParticipant::normalizeEmail((string) ($row['faculty_email'] ?? '')), $existing);

        $splitRows = FdpAttendance::splitValidAndSkippedRowsForFdp($fdpId, $rows, $participantIndex, $existingEmails);
        $filteredRows = $splitRows['valid'];
        $skippedRows = $splitRows['skipped'];
        $notFoundCount = 0;
        $duplicateCount = 0;
        foreach ($skippedRows as $skippedRow) {
            $reason = (string) ($skippedRow['reason'] ?? '');
            if ($reason === 'Attendance already exists for this participant in this FDP') {
                $duplicateCount++;
            } elseif ($reason !== '') {
                $notFoundCount++;
            }
        }
        $inserted = 0;
        $batch = [];
        $chunkSize = 200;
        $columns = ['fdp_id', 'faculty_name', 'faculty_email', 'status'];
        $errors = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($filteredRows as $index => $row) {
                $rowErrors = [];
                if (trim((string) ($row['faculty_name'] ?? '')) === '') {
                    $rowErrors[] = 'Name is required';
                }
                if (($row['faculty_email'] ?? '') !== '' && !filter_var($row['faculty_email'], FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'Invalid email';
                }

                if (!empty($rowErrors)) {
                    $errors[] = ['row' => $index + 1, 'errors' => $rowErrors];
                    continue;
                }

                $batch[] = [$fdpId, (string) $row['faculty_name'], (string) $row['faculty_email'], (string) $row['status']];

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

            foreach ($filteredRows as $row) {
                if (FdpAttendance::normalizeStatus((string) ($row['status'] ?? 'Present')) !== 'Absent') {
                    continue;
                }

                $email = FdpParticipant::normalizeEmail((string) ($row['faculty_email'] ?? ''));
                if ($email === '') {
                    continue;
                }

                $mail = FdpMailService::buildDefaulterMail([
                    'title' => $fdp->title,
                    'date' => $fdp->start_date,
                ], ['name' => (string) ($row['faculty_name'] ?? 'Sir/Madam')]);

                FdpMailQueue::queue(
                    'defaulter',
                    (int) $fdpId,
                    $email,
                    $mail['subject'],
                    $mail['body'],
                    $mail['htmlBody']
                );
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Uploaded',
                'inserted' => $inserted,
                'skipped' => count($skippedRows),
                'not_found' => $notFoundCount,
                'duplicate' => $duplicateCount,
                'errors' => $errors,
                'processed' => count($filteredRows),
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);

            return ['success' => false, 'message' => 'Failed to process file'];
        }
    }

    private function queueDefaulterMailIfNeeded(FdpAttendance $attendance): void
    {
        if (FdpAttendance::normalizeStatus((string) $attendance->status) !== 'Absent') {
            return;
        }

        $email = FdpParticipant::normalizeEmail((string) ($attendance->faculty_email ?? ''));
        if ($email === '') {
            return;
        }

        $fdp = Fdp::findOne((int) $attendance->fdp_id);
        if ($fdp === null) {
            return;
        }

        $mail = FdpMailService::buildDefaulterMail([
            'title' => $fdp->title,
            'date' => $fdp->start_date,
        ], ['name' => (string) ($attendance->faculty_name ?: 'Sir/Madam')]);

        FdpMailQueue::queue(
            'defaulter',
            (int) $attendance->fdp_id,
            $email,
            $mail['subject'],
            $mail['body'],
            $mail['htmlBody']
        );
    }
}
