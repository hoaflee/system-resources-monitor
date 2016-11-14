<?php

// define STATS db
$STATS_HOST = "10.30.12.102";
$STATS_PORT = "3313";
$STATS_USER = "nhiennt";
$STATS_PASS = "12345ok@";
$STATS_NAME = "stats_so";

$STATS_CONNECTION = null;

function getSTATSConnection() {
	$mysqli = $GLOBALS["STATS_CONNECTION"];
	if($mysqli !=null && $mysqli->ping())
		return $mysqli;

	$mysqli = new mysqli($GLOBALS["STATS_HOST"], $GLOBALS["STATS_USER"],$GLOBALS["STATS_PASS"], $GLOBALS["STATS_NAME"],$GLOBALS["STATS_PORT"]);
	if (mysqli_connect_errno()) {
		printf("Connect STATS DB failed: %s\n", mysqli_connect_error());
		exit();
	}
	return $mysqli;
}

// write STATS
function getDataAndWriteStats($server_ip, $interface) {
	$queryOK = true;

	$connSTATS = getSTATSConnection();

	$insert_query = "REPLACE INTO server_traffic(server_ip, interface)
					VALUE('".$server_ip."', '".$interface."')";
	$i=0;
	do{
		$rsSTATS = $connSTATS->query($insert_query);
		sleep(2);
		$i++;
	}while($rsSTATS===FALSE && $i<5);
}


function getAllList() {
		$ret = array();
        $conn = getSTATSConnection();
		$rs = $conn->query("select * from server_traffic where 1;");
        if (!$rs) {
        	return array();
        } else {
			while($row = $rs->fetch_array(MYSQLI_ASSOC)) {
				if($row) $ret[] = $row;
			}
			return $ret;
        }
}
