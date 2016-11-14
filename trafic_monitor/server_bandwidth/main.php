<?php
require_once("memcached.php");
require_once("db.php");

$debug	= true;

$params = array(
	'time'		=> $argv[1],
	'ip'		=> $argv[2],
	'ifName'	=> $argv[3],
	'ifIn'		=> $argv[4],
	'ifOut'		=> $argv[5]
);

$mc = new MyMemcached("10.30.22.49", 11216);

$key 		= $params['ip']."_".$params['ifName'];
$key_30min 	= $key."_30min";
$key_30day 	= $key."_30day";
$key_report 	= $key."_report";
$interval_30min	= 60 * 30;
$interval_30day	= 60 * 60 * 24 * 30;
$report		= "";

// ==== insert to db ===== //
getDataAndWriteStats($params['ip'], $params['ifName']);

if($debug){
	echo "\n\n\nSERVER_INTERFACE: ".$key."\n";
}

// ====== calculator traffic Kbits ====== //
$in = 0;
$out = 0;

$_old = $mc->getData($key);
if($_old){
	$old = explode('_', $_old);
	$tmp = round( ($params['ifIn'] - $old[1]) / ($params['time'] - $old[0]) * 8 / 1024 );
	$in = $tmp < 0 ? 0 : $tmp;
	$tmp = round( ($params['ifOut'] - $old[2]) / ($params['time'] - $old[0]) * 8 / 1024 );
	$out = $tmp < 0 ? 0 : $tmp;

	//traffic new
	$new = $params['time']."_".$in."_".$out;

	if($debug){
		echo "Traffic current: ".$new."\n";
	}

	//check and update 30 min
	$_old_30min = $mc->getData($key_30min);
	if($_old_30min){
		$old = explode('_', $_old_30min);
		if( ($params['time'] - $old[0]) > $interval_30min){
			$mc->setData($key_30min, $new);
		}
	}else{
		$mc->setData($key_30min, $new);
	}

	if($debug){
		echo "Traffic 30mins ago: ".$_old_30min."\n";
		echo "Traffic 30mins ago new: ".$mc->getData($key_30min)."\n";
	}

	//check and update 30 day
	$_old_30day = $mc->getData($key_30day);
	if($_old_30day){
		$old = explode('_', $_old_30day);
		if( ($params['time'] - $old[0]) > $interval_30day){
			$mc->setData($key_30day, $new);
		}
	}else{
		$mc->setData($key_30day, $new);
	}

	if($debug){
		echo "Traffic 30days ago: ".$_old_30day."\n";
		echo "Traffic 30days ago new: ".$mc->getData($key_30day)."\n";
	}

}

$new = $params['time']."_".$params['ifIn']."_".$params['ifOut'];
$mc->setData($key, $new);


// ====== alert 30 min ====== //
$in_percent = 0;
$out_percent = 0;

if($_old_30min){
	$old = explode('_', $_old_30min);
	$in_percent = empty($old[1]) ? 0 : round( ($in - $old[1]) / $old[1] * 100, 1 );
	$out_percent = empty($old[2]) ? 0 : round( ($out - $old[2]) / $old[2] * 100, 1 );
}

$report = $params['time']."_".$in."_".$out."_".$in_percent."_".$out_percent;

if($debug){
	echo "Percent 30mins ago in/out: ".$in_percent."/".$out_percent."\n";
}

// ====== alert 30 day ====== //
if($_old_30day){
	$old = explode('_', $_old_30day);
	$in_percent = empty($old[1]) ? 0 : round( ($in - $old[1]) / $old[1] * 100, 1 );
	$out_percent = empty($old[2]) ? 0 : round( ($out - $old[2]) / $old[2] * 100, 1 );
}

$report = $params['time']."_".$in."_".$out."_".$in_percent."_".$out_percent."_".$in_percent."_".$out_percent;
$mc->setData($key_report, $report);

if($debug){
	echo "Percent 30days ago in/out: ".$in_percent."/".$out_percent."\n";
	echo "Report time/in/out/30min_in/30_minout/30day_in/30day_out: ".$report."\n";
	echo "\n\n\n";
}
