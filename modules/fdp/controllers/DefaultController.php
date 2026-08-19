<?php

declare(strict_types=1);

namespace app\modules\fdp\controllers;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpAttendance;
use app\modules\fdp\models\FdpMailService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class DefaultController extends Controller
{
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Fdp::find()->orderBy(['start_date' => SORT_DESC]),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate(): string|Response
    {
        $model = new Fdp();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'FDP created successfully.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionUpdate(int $id): string|Response
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'FDP updated successfully.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'FDP deleted successfully.');

        return $this->redirect(['index']);
    }

    public function actionAttendance(int $id): string|Response
    {
        $fdp = $this->findModel($id);
        $attendance = new FdpAttendance();
        $attendance->fdp_id = $id;

        if (Yii::$app->request->isPost) {
            $attendance->load(Yii::$app->request->post());
            $file = UploadedFile::getInstanceByName('attendance_file');

            if ($file && $file->tempName) {
                $rows = FdpAttendance::readCsvRows($file->tempName);
                foreach ($rows as $row) {
                    $record = new FdpAttendance();
                    $record->fdp_id = $id;
                    $record->faculty_name = $row['name'] ?? '';
                    $record->faculty_email = $row['email'] ?? '';
                    $record->status = FdpAttendance::normalizeStatus((string) ($row['status'] ?? 'Present'));
                    $record->save(false);
                }

                Yii::$app->session->setFlash('success', 'Attendance uploaded successfully.');

                return $this->redirect(['view', 'id' => $id]);
            }

            if ($attendance->save()) {
                Yii::$app->session->setFlash('success', 'Attendance updated successfully.');

                return $this->redirect(['view', 'id' => $id]);
            }
        }

        $records = $fdp->getAttendanceRecords()->all();

        return $this->render('attendance', ['fdp' => $fdp, 'model' => $attendance, 'records' => $records]);
    }

    public function actionDefaulters(int $id): string
    {
        $fdp = $this->findModel($id);
        $records = $fdp->getAttendanceRecords()->where(['status' => 'Absent'])->all();

        return $this->render('defaulters', ['fdp' => $fdp, 'defaulters' => $records]);
    }

    public function actionSendReminder(int $id): Response
    {
        $fdp = $this->findModel($id);
        $mail = FdpMailService::buildReminderMail([
            'title' => $fdp->title,
            'start_date' => $fdp->start_date,
            'end_date' => $fdp->end_date,
            'time' => $fdp->time,
            'mode' => $fdp->mode,
            'venue' => $fdp->venue ?: $fdp->meeting_link,
            'coordinator' => $fdp->coordinator_name,
        ]);

        Yii::$app->session->setFlash('success', 'Reminder mail prepared: ' . $mail['subject']);

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionSendDefaulterMail(int $id): Response
    {
        $fdp = $this->findModel($id);
        $defaulters = $fdp->getAttendanceRecords()->where(['status' => 'Absent'])->all();

        if (empty($defaulters)) {
            Yii::$app->session->setFlash('info', 'No defaulters found for this FDP.');

            return $this->redirect(['defaulters', 'id' => $id]);
        }

        foreach ($defaulters as $person) {
            $mail = FdpMailService::buildDefaulterMail([
                'title' => $fdp->title,
                'date' => $fdp->start_date,
            ], [
                'name' => $person->faculty_name,
            ]);

            Yii::$app->session->setFlash('success', 'Defaulter mails prepared: ' . $mail['subject']);
        }

        return $this->redirect(['defaulters', 'id' => $id]);
    }

    protected function findModel(int $id): Fdp
    {
        $model = Fdp::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('The requested FDP does not exist.');
        }

        return $model;
    }
}
