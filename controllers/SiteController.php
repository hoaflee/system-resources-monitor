<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ServerData;
use app\models\ServerTraffic;
use app\components\MyMemcached;

class SiteController extends Controller
{   
    function getRealIpAddr(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
          $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
          $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','dashboard'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionDashboard()
    {
        $sql = 'SELECT * FROM server_data';
        $datas = ServerData::findBySql($sql)->all();
        $trafficDatas = ServerTraffic::find()->all();
        $cpuRamData = [];
        $diskData =[];
        $count = 0;
        foreach ($datas as $data) {
            $ip = $data->ip;
            $datetime = gmdate('H:i:s m/d/Y', $data->timestamp+3600*7); //+7 GMT
            $cpuRamData[$count]=[   'name' => $data->hostname,
                                    'marker' => ['symbol' => 'circle'],
                                    'data' => [['x' => $data->mem_usage,'y' => $data->cpu_load, 'ps_info' => $data->ps_info, 'datetime' => $datetime, 'ip' => $ip]],
                                ];
            $diskData[$count] = [   'name' => $data->hostname,
                                    'marker' => ['symbol' => 'circle'],
                                    'data' => [['x' => $data->disk_usage,'y'=>intval($data->disk), 'disk_info' => $data->disk_info,'datetime' => $datetime, 'ip' => $ip]],
                                ];
            $count++;
        }
        
        return $this->render('dashboard',['cpuRamData' => $cpuRamData,'diskData' => $diskData,'trafficDatas' => $trafficDatas]);
    }

    public function actionCollectData()
    {
        $rs = [];
        if ((isset($_REQUEST['data'])) && (!empty($_REQUEST['data']))) {
            $data = json_decode(preg_replace('/\s+/', '',$_REQUEST['data']));



            $ServerData = ServerData::find()->where(['hostname' => $data->hostname])->one();
            //Save data to memcache
            $time_key = new \DateTime(gmdate('m/d/Y H:00:00',$data->timestamp));
            $key=$this->getRealIpAddr().'_'.$time_key->getTimestamp();
            $value=$data->cpu_load."_".$data->mem_usage;
            Yii::$app->cache->Memcache->set($key,$value,259200);
            // var_dump($key, $value);

            if(is_null($ServerData)){
                $ServerData = new ServerData();
                $ServerData->hostname = $data->hostname;
                $ServerData->cpu_load = $data->cpu_load;
                $ServerData->mem_usage = $data->mem_usage;
                $ServerData->ip = $this->getRealIpAddr();

                if (strpos($data->disk,'T') == true) {
                    $ServerData->disk = preg_replace('/\D/', '', $data->disk)*1000;
                }
                else{
                    $ServerData->disk = preg_replace('/\D/', '', $data->disk);
                }
                $ServerData->disk_usage = $data->disk_usage;
                $ServerData->disk_info = $_REQUEST['disk_info'];
                $ServerData->ps_info = $_REQUEST['ps_info'];
                $ServerData->timestamp = $data->timestamp;
                if ($ServerData->save(0)) {
                    $rs['Result'] = true;
                    $rs['Message'] = 'Save data success';
                    $rs['status'] = 'new server';
                    $rs['key'] = $key;
                    $rs['value'] = $value;
                }else{
                    $rs['Result'] = false;
                    $rs['Message'] = 'Save data false';
                }
            }else{
                $ServerData->cpu_load = $data->cpu_load;
                $ServerData->mem_usage = $data->mem_usage;
                if (strpos($data->disk,'T') == true) {
                    $ServerData->disk = preg_replace('/\D/', '', $data->disk)*1000;
                }
                else{
                    $ServerData->disk = preg_replace('/\D/', '', $data->disk);
                }
                $ServerData->disk_usage = $data->disk_usage;
                $ServerData->disk_info = $_REQUEST['disk_info'];
                $ServerData->ps_info = $_REQUEST['ps_info'];
                $ServerData->timestamp = $data->timestamp;
                if ($ServerData->save(0)) {
                    $rs['Result'] = true;
                    $rs['Message'] = 'Save data success';
                    $rs['status'] = 'update server';
                    $rs['key'] = $key;
                    $rs['value'] = $value;
                }else{
                    $rs['Result'] = false;
                    $rs['Message'] = 'Save data false';
                }
            }     
            $rs['Data'] = json_encode($data);
        } else {
            $rs['Result'] = false;
            $rs['Message'] = 'No data';
        }
        echo json_encode($rs);
    }

    public function actionListCpu(){
        $html='';
        $cpu = $_REQUEST['cpu'];
        $ram = $_REQUEST['ram'];
        $sel_time = $_REQUEST['datetime'];
        //var_dump($sel_time);
        $sql = 'SELECT hostname, ip, cpu_load,mem_usage, timestamp 
                FROM server_data
                where mem_usage >= '.$ram.' and cpu_load >= '.$cpu;
        $datas = ServerData::findBySql($sql)->all();

        // $datas = ServerData::find()
        //          ->select(['hostname', 'ip', 'cpu_load', 'mem_usage','timestamp'])
        //          ->where(['<=', 'mem_usage', $ram])
        //          ->andWhere(['<=', 'cpu_load', $cpu])
        //          ->all();
        if($sel_time == '0'){
            foreach ($datas as $data) {
                $html=$html."<tr>
                                <td>".$data->hostname."</td>
                                <td>".$data->ip."</td>
                                <td>".$data->cpu_load."</td>
                                <td>".$data->mem_usage."</td>
                                <td>".gmdate('H:i:s m/d/Y', $data->timestamp+3600*7)."</td>
                            </tr>";
            }
            $tableHtml="<table id='cpu-ram-table' width='100%' style='border-collapse: collapse' boder='1'>
                            <thead>
                                <tr>
                                    <th>Hostname</th>
                                    <th>IP</th>
                                    <th>% Cpu</th>
                                    <th>% Ram</th>
                                    <th>Date time</th>
                                </tr>
                            </thead>
                            <tbody>".$html."          
                            </tbody>
                        </table>";
        }else{            
            $datas = $ServerData = ServerData::find()->all();
            $ttp = intval(strtotime($sel_time))-3600*7;
            
            foreach ($datas as $data) {
                $key = $data->ip."_".$ttp;
                $val = \Yii::$app->cache->Memcache->get($key);
                //var_dump($key, $val);
                if($val) {
                    $val_arr = explode("_",$val);
                    $cpu_val=($val_arr[0]);
                    $ram_val=($val_arr[1]);
                    if(($cpu_val >= $cpu) && ($ram_val >= $ram)){
                        $html=$html."<tr>
                                <td>".$data->hostname."</td>
                                <td>".$data->ip."</td>
                                <td>".$cpu_val."</td>
                                <td>".$ram_val."</td>
                                <td>".gmdate('m/d/Y H:00:00', $ttp+3600*7)."</td>
                            </tr>";
                    }
                }
            }
            $tableHtml="<table id='cpu-ram-table' width='100%' style='border-collapse: collapse' boder='1'>
                            <thead>
                                <tr>
                                    <th>Hostname</th>
                                    <th>IP</th>
                                    <th>% Cpu</th>
                                    <th>% Ram</th>
                                    <th>Date time</th>
                                </tr>
                            </thead>
                            <tbody>".$html."          
                            </tbody>
                        </table>";

        }
        return $tableHtml;
        // return $sql;
    }

    public function actionListDisk(){
        $html='';
        $disk = $_REQUEST['disk'];
        $sql = 'SELECT hostname, ip, disk_info 
                FROM server_data
                where disk_usage '.$disk;
        $datas = ServerData::findBySql($sql)->all();
        // $datas = ServerData::find()
        //          ->select(['hostname', 'ip', 'disk_info'])
        //          ->where(['<=', 'disk_usage', $disk])
        //          ->all();
        foreach ($datas as $data) {
            $html=$html."<tr>
                            <td>".$data->hostname."</td>
                            <td>".$data->ip."</td>                           
                            <td><pre>".$data->disk_info."</pre></td>
                            </tr>";
            }
        $tableHtml="<table id='disk-table' width='100%' style='border-collapse: collapse' boder='1'>
                        <thead>
                            <tr>
                                <th>Hostname</th>
                                <th>IP</th>
                                <th>Disk info</th>
                            </tr>
                        </thead>
                        <tbody>".$html."          
                        </tbody>
                    </table>";
        return $tableHtml;
    }

    public function actionTraffic(){
        $in = $_REQUEST['in'];
        $out = $_REQUEST['out'];
        $time_interval = $_REQUEST['time_interval'];
        $per_ratio = $_REQUEST['per_ratio'];

        $sel_time = $_REQUEST['datetime'];

        $html='';
        if($in==0){
            $sql = 'SELECT *
                FROM server_traffic
                where Outbound >= '.intval($out*1024*1024);
        }elseif($out==0){
            $sql = 'SELECT *
                FROM server_traffic
                where Inbound >= '.intval($in*1024*1024);
        }
        else{
            $sql = 'SELECT *
                FROM server_traffic
                where Inbound >= '.intval($in*1024*1024).' or Outbound >= '.intval($out*1024*1024);
        }

        $trafficDatas = ServerTraffic::findBySql($sql)->all();
        if($sel_time!='0'){
            $ttp = intval(strtotime($sel_time))-3600*7;
            $time_round=[$ttp, $ttp+60, $ttp-60, $ttp+120, $ttp-120];
            foreach($trafficDatas as $item){
                foreach($time_round as $tr){
                    $key = $item->server_ip."_".$item->interface."_".$tr;
                    $val = \Yii::$app->cache->Memcache->get($key);
                    if($val) {
                        //$html=$html."<br>".$val;
                        $val_arr = explode("_",$val);
                        $in=round(intval($val_arr[0])/1024/1024,2);
                        $out=round(intval($val_arr[1])/1024/1024,2);
                        $html=$html."<tr>
                                <td>".$item->server_ip."</td>
                                <td>".$item->interface."</td>
                                <td>".$in."</td>
                                <td>".$out."</td>
                                <td>".gmdate('H:i m/d/Y', $tr+3600*7)."</td>
                            </tr>";
                        break;
                    }
                }
            }
            $tableHtml="<table id='traffic-table' width='100%' style='border-collapse: collapse;display:none' boder='1'>
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>Interface</th>
                                    <th>In (Mb/s)</th>
                                    <th>Out (Mb/s)</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>".$html."          
                            </tbody>
                        </table>";
            return $tableHtml;
        }else{
            if($time_interval==0){
                foreach($trafficDatas as $item){
                    $tf_in = round(intval($item->Inbound)/1024/1024,2);
                    $tf_out = round(intval($item->Outbound)/1024/1024,2);
                    $html=$html."<tr>
                                <td>".$item->server_ip."</td>
                                <td>".$item->interface."</td>
                                <td>".$tf_in."</td>
                                <td>".$tf_out."</td>
                                <td>".gmdate('H:i:s m/d/Y', $item->timestamp+3600*7)."</td>
                            </tr>";
                }; 
                    $tableHtml="<table id='traffic-table' width='100%' style='border-collapse: collapse;display:none' boder='1'>
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>Interface</th>
                                    <th>In (Mb/s)</th>
                                    <th>Out (Mb/s)</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>".$html."          
                            </tbody>
                        </table>";
            }else{
                $time_show='';
                switch ($time_interval) {
                    case 5:
                        $time_show='5 min';
                        break;
                    case 10:
                        $time_show='10 min';
                        break;
                    case 30:
                        $time_show='30 min';
                        break;
                    case 60:
                        $time_show='1 hours';
                        break;
                    case 120:
                        $time_show='2 hours';
                        break;
                    case 360:
                        $time_show='6 hours';
                        break;
                    case 720:
                        $time_show='12 hours';
                        break;
                    case 1440:
                        $time_show='1 days';
                        break;
                    case 2880:
                        $time_show='2 days';
                        break;
                }
                $time_check=[$time_interval*60,$time_interval*60+60,$time_interval*60-60];
                foreach($trafficDatas as $item){
                    $time_key = new \DateTime(gmdate('H:i m/d/Y',$item->timestamp));
                    foreach($time_check as $tc){
                        $pre_time = $time_key->getTimestamp()-$tc;
                        $key = $item->server_ip."_".$item->interface."_".$pre_time;
                        $val = \Yii::$app->cache->Memcache->get($key);
                        if($val) {
                            $val_arr = explode("_",$val);
                            if(intval($val_arr[0])!=0){
                                $per_in=round(($item->Inbound/intval($val_arr[0])*100)-100,2);
                                $pre_in=round(intval($val_arr[0])/1024/1024,2);
                            }else $per_in=0;
                            if(intval($val_arr[1])!=0){
                                $per_out=round(($item->Outbound/intval($val_arr[1])*100)-100,2);
                                $pre_out=round(intval($val_arr[1])/1024/1024,2);
                            }else $per_out=0;
                            break;
                        }
                    }
                    $tf_in = round(intval($item->Inbound)/1024/1024,2);
                    $tf_out = round(intval($item->Outbound)/1024/1024,2);
                    if(abs($per_in)>=$per_ratio || abs($per_out)>=$per_ratio){
                        $icon_in='';
                        $icon_out='';
                        if($per_in>0){
                            $icon_in = "<span class='glyphicon glyphicon-arrow-up' aria-hidden='true' style='color:red'></span>";
                        }
                        if($per_in<0){
                            $icon_in = "<span class='glyphicon glyphicon-arrow-down' aria-hidden='true' style='color:blue'></span>";
                        }
                        if($per_out>0){
                            $icon_out = "<span class='glyphicon glyphicon-arrow-up' aria-hidden='true' style='color:red'></span>";
                        }
                        if($per_out<0){
                            $icon_out = "<span class='glyphicon glyphicon-arrow-down' aria-hidden='true' style='color:blue'></span>";
                        }

                        $html=$html."<tr>
                                <td>".$item->server_ip."</td>
                                <td>".$item->interface."</td>
                                <td>".$tf_in."</td>
                                <td>".$tf_out."</td>
                                <td>".$pre_in."</td>
                                <td>".$pre_out."</td>
                                <td>".abs($per_in).$icon_in." %</td>
                                <td>".abs($per_out).$icon_out." %</td>
                                <td>".gmdate('H:i:s m/d/Y', $item->timestamp+3600*7)."</td>
                            </tr>";
                    }
                } 
                $tableHtml="<table id='traffic-table' width='100%' style='border-collapse: collapse;display:none' boder='1'>
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Interface</th>
                                <th>Cur In (Mb/s)</th>
                                <th>Cur Out (Mb/s)</th>
                                <th>Pre ".$time_show." In (Mb/s)</th>
                                <th>Pre ".$time_show." Out (Mb/s)</th>
                                <th>Ratio ".$time_show." In (%)</th>
                                <th>Ratio ".$time_show." Out (%)</th>
                                <th>Update Time</th>
                            </tr>
                        </thead>
                        <tbody>".$html."          
                        </tbody>
                    </table>";
            }
        }

        return $tableHtml;
    }
    public function actionCollectTrafficData(){
        if ((isset($_REQUEST['data'])) && (!empty($_REQUEST['data']))) {
            $data = json_decode($_REQUEST['data']);
            $ServerIp= $this->getRealIpAddr();
            if(!preg_match('/^b/', $data->interface)){
                $ServerTraffic = ServerTraffic::deleteAll(['server_ip' => $ServerIp,'interface' => $data->interface]);
                $ServerTraffic = new ServerTraffic();
                $ServerTraffic->server_ip = $ServerIp;
                $ServerTraffic->interface = $data->interface;
                $ServerTraffic->Inbound = $data->inbound;
                $ServerTraffic->Outbound = $data->outbound;
                $ServerTraffic->timestamp = $data->timestamp;
                $ServerTraffic->save(0);  
            }
        // write to memcache server
            $time_key = new \DateTime(gmdate('H:i m/d/Y',$data->timestamp));
            $key=$ServerIp.'_'.$data->interface.'_'.$time_key->getTimestamp();
            $value=$data->inbound."_".$data->outbound;
            Yii::$app->cache->Memcache->set($key,$value,259200);
        }
    }
    public function actionTest(){
        $ServerTraffic = new ServerTraffic();
        $ServerTraffic->interface = 'eth0';
        $ServerTraffic->Inbound = 12341234;
        $ServerTraffic->Outbound = 3333333;
        $ServerTraffic->timestamp = 1234124;
        $ServerTraffic->save(0);

    }
    public function actionMemcache($key,$value,$timelive){
        Yii::$app->cache->Memcache->set($key,$value,$timelive);
    }
    public function actionCheckMemcacheValue($key){
        $val=\Yii::$app->cache->Memcache->get($key);
        echo $val;
        // for($i=1;$i<10;$i++){
        //     echo $i;
        //     if($val){
        //         break;
        //     }
        // }
    }
    public function actionTest2(){
        $date_time = $_REQUEST['datetime'];
        $timestamp = strtotime($date_time);
        return $timestamp;
    }

    public function actionCheckTraffic($sel_time,$interval,$ins,$outs){
        $time_check=[$interval*60,$interval*60+60,$interval*60-60];
        $trafficDatas = ServerTraffic::find()->all();
            $ttp = intval(strtotime($sel_time))-3600*7;
            $time_round=[$ttp, $ttp+60, $ttp-60, $ttp+120, $ttp-120];
            foreach($trafficDatas as $item){
                foreach($time_round as $tr){
                    $key = $item->server_ip."_".$item->interface."_".$tr;
                    $val = \Yii::$app->cache->Memcache->get($key);
                    if($val) {
                        $val_arr = explode("_",$val);
                        $in_=round(intval($val_arr[0])/1024/1024,2);
                        $out_=round(intval($val_arr[1])/1024/1024,2);

                        $in=intval($val_arr[0]);
                        $out=intval($val_arr[1]);

                        foreach($time_check as $tc){
                            $pre_time = $tr-$tc;
                            $key2 = $item->server_ip."_".$item->interface."_".$pre_time;
                            $val2 = \Yii::$app->cache->Memcache->get($key2);
                            if($val2) {
                                $val_arr2 = explode("_",$val2);
                                if(intval($val_arr2[0])!=0){
                                    $per_in=round(($in/intval($val_arr2[0])*100)-100,2);
//                                    $pre_in=round(intval($val_arr2[0])/1024/1024,2);
                                }else $per_in=0;
                                if(intval($val_arr2[1])!=0){
                                    $per_out=round(($out/intval($val_arr2[1])*100)-100,2);
//                                    $pre_out=round(intval($val_arr2[1])/1024/1024,2);
                                }else $per_out=0;
                                if(($in_>=intval($ins))||($out_>=intval($outs))){
                                echo gmdate('H:i:s m/d/Y', $tr+3600*7).'<->'.gmdate('H:i:s m/d/Y', $pre_time+3600*7).'___'.$item->server_ip.':'.$item->interface.'===> in: '.abs($per_in).', out: '.abs($per_out).'<br>';
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
    }
}   
