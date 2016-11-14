<?php
ini_set('display_errors', '1');
	
$ROOTPATH = "/data/html/resource_monitor/trafic_monitor/server_bandwidth/";
require_once($ROOTPATH."/memcached.php");
require_once($ROOTPATH."/db.php");
$mc = new MyMemcached("10.30.22.49", 11216);
$list_arr = getAllList();
$percent = empty($_GET["percent"]) ? 10 : $_GET["percent"];
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
	<title>Traffic Monitor</title>

	<meta charset="utf-8" />
	<meta name="description" content="" />
	<meta name="author" content="" />		
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	
		
	<link rel="stylesheet" href="stylesheets/all.3.css" type="text/css" />

</head>

<body>

<div id="wrapper">
	<div style="text-align: right; padding: 10px 25px 10px 10px;">Show items option: 
		<a href="http://localhost/monitor/trafic_monitor/traffic/index.php?percent=0.1">ALL</a> | 
		<a href="http://localhost/monitor/trafic_monitor/traffic/index.php?percent=5">> 5%</a> | 
		<a href="http://localhost/monitor/trafic_monitor/traffic/index.php?percent=10">> 10%</a> | 
		<a href="http://localhost/monitor/trafic_monitor/traffic/index.php?percent=15">> 15%</a> | 
		<a href="http://localhost/monitor/trafic_monitor/traffic/index.php?percent=20">> 20%</a>
	</div>	
	<div id="content">		
		<div class="container">
				
				<div class="grid-24">
								
					<br />
								
					<div class="widget widget-table">
					
						<div class="widget-header">
							<span class="icon-list"></span>
							<h3 class="icon chart">G2 Server Traffic Monitor</h3>		
						</div>
					
						<div class="widget-content">
							
							<table class="table table-bordered table-striped data-table">
						<thead>
							<tr>
								<th>Server IP</th>
								<th>Interface</th>
								<th>Date time</th>
								<th>In (Mb/s)</th>
								<th>Out (Mb/s)</th>
								<th>In 30min(%)</th>
								<th>Out 30min(%)</th>
								<th>In 1day(%)</th>
								<th>Out 1day(%)</th>
							</tr>
						</thead>
						<tbody>
						<?php
    					foreach($list_arr as $item){
					        $key = $item['server_ip']."_".$item['interface']."_".$item['update_date']."_speed";
							$val = $mc->getData($key);

							for($i=-2; $i<3; $i++){
								$tmp = $item['update_date'] - (30 + $i);
								$key_1h = $item['server_ip']."_".$item['interface']."_".$tmp."_speed";
								$val_1h = $mc->getData($key_1h);
								if(!empty($val_1h)) break;
							}

							for($i=-2; $i<3; $i++){
	                            $tmp = $item['update_date'] - (10000 + $i);
    	                        $key_1d = $item['server_ip']."_".$item['interface']."_".$tmp."_speed";
								$val_1d = $mc->getData($key_1d);
								if(!empty($val_1d)) break;
							}

					        $val_arr = explode("_",$val);
							$val_arr_1h = explode("_",$val_1h);
							$val_arr_1d = explode("_",$val_1d);

							$in_1h_percent = empty($val_arr_1h) ? 0 : round(($val_arr[0] - $val_arr_1h[0])/$val_arr_1h[0], 2);
							$out_1h_percent = empty($val_arr_1h) ? 0 : round(($val_arr[1] - $val_arr_1h[1])/$val_arr_1h[1], 2);

                            $in_1d_percent = empty($val_arr_1d) ? 0 : round(($val_arr[0] - $val_arr_1d[0])/$val_arr_1d[0], 2);
                            $out_1d_percent = empty($val_arr_1d) ? 0 : round(($val_arr[1] - $val_arr_1d[1])/$val_arr_1d[1], 2);

								if( (abs($in_1h_percent) >= $percent || abs($out_1h_percent) >= $percent || abs($in_1d_percent) >= $percent || abs($out_1d_percent) >= $percent) && (round($val_arr[0]*8 / 1024/1024, 2) > 10 || round($val_arr[1]*8 / 1024/1024, 2) > 10) ) 
								  echo '
								  <tr class="gradeA">
						            <td>'.$item['server_ip'].'</td>
						            <td>'.$item['interface'].'</td>
						           <!-- <td>'.$item['update_date'].'</td> -->
						            <td>'.substr($item['update_date'],6,2).':'.substr($item['update_date'],-2).' '.substr($item['update_date'],4,2).'/'.substr($item['update_date'],2,2).'/20'.substr($item['update_date'],0,2).'</td>
						            <td>'.round($val_arr[0]*8 / 1024/1024, 2).'</td>
						            <td>'.round($val_arr[1]*8 / 1024/1024, 2).'</td>
						            <td>'.$in_1h_percent.'</td>
						            <td>'.$out_1h_percent.'</td>
						            <td>'.$in_1d_percent.'</td>
						            <td>'.$out_1d_percent.'</td>
								  </tr>';
						}
						?>															
						</tbody>
					</table>
						</div> <!-- .widget-content -->
					
				</div> <!-- .widget -->
			
			</div> <!-- .grid -->
	
		</div> <!-- .container -->
		
	</div> <!-- #content -->
	
	
</div> <!-- #wrapper -->

<script src="javascripts/all.js"></script>
<script src="js/common.js"></script>
</body>
</html>
