<?php

declare(strict_types=1);

namespace app\modules\fdp\controllers;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpAttendance;
use app\modules\fdp\models\FdpMailService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FdpController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new Fdp();
        $query = Fdp::find()->orderBy(['start_date' => SORT_DESC]);

        $search = Yii::$app->request->get('search');
        if (is_string($search) && trim($search) !== '') {
            $keyword = trim($search);
            $query->andFilterWhere([
                'or',
                ['like', 'title', $keyword],
                ['like', 'coordinator_name', $keyword],
                ['like', 'mode', $keyword],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
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

    public function actionDefaulters(int $id): string
    {
        $fdp = $this->findModel($id);
        $records = $fdp->getAttendanceRecords()->where(['status' => 'Absent'])->all();

        return $this->render('defaulters', ['fdp' => $fdp, 'defaulters' => $records]);
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
