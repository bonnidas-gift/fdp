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

        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('attendance_file');
            if ($file && $file->tempName) {
                $rows = FdpAttendance::readCsvRows($file->tempName);
                foreach ($rows as $row) {
                    $record = new FdpAttendance();
                    $record->fdp_id = $fdpId;
                    $record->faculty_name = $row['name'] ?? '';
                    $record->faculty_email = $row['email'] ?? '';
                    $record->status = FdpAttendance::normalizeStatus((string) ($row['status'] ?? 'Present'));
                    $record->save(false);
                }

                Yii::$app->session->setFlash('success', 'Attendance uploaded successfully.');

                return $this->redirect(['index', 'fdpId' => $fdpId]);
            }
        }

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
}
