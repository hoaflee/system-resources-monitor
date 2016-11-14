<?php
use miloschuman\highcharts\Highcharts;
use yii\web\JsExpression;
use yii\bootstrap\Modal;
use app\models\ServerTraffic;

$this->registerJsFile('//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('//cdn.datatables.net/1.10.7/css/jquery.dataTables.css', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('/css/bootstrap-datetimepicker.min.css', ['depends' => [\yii\web\JqueryAsset::className()]]);
//date picker
$this->registerJsFile('/js/moment.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/bootstrap-datetimepicker.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/collapse.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/transition.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

foreach ($cpuRamData as $data) {
    Modal::begin([
        'header' => '<h3>'.$data['name'] .' Process list </h3>',
        'size' => 'modal-lg',
        'id'    => str_replace('.', '_', $data['name']).'_ps_info',
    ]);
    echo '<pre>'.$data['data']['0']['ps_info'].'</pre>';
    Modal::end();
}

foreach ($diskData as $data) {
    Modal::begin([
        'header' => '<h3>'.$data['name'] .' Disk info</h3>',
        // 'size' => 'modal-lg',
        'id'    => str_replace('.', '_', $data['name']).'_disk_info',
    ]);
    echo '<pre>'.$data['data']['0']['disk_info'].'</pre>';
    Modal::end();
}
?>

<div id="cpu-ram-modal" class="fade modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="panel panel-primary">     
                    <div class="panel-heading">Server List
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>             
                  <div class="panel-body" id='server-cpuram'>
                    <!-- table insert here -->                    
                  </div>
                </div>
            </div> 
        </div>
    </div>
</div>

<div id="disk-modal" class="fade modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="panel panel-primary">     
                    <div class="panel-heading">Server List
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>             
                  <div class="panel-body" id='server-disk'>
                  </div>
                </div>
            </div> 
        </div>
    </div>
</div>

<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-xs-2">                
                <div class="form-group">
                  <label for="sel1">CPU usage: </label>
                  <select class="form-control" id="cpu-sel">
                    <option value="0">Select all</option>
                    <!-- <option disabled="disabled">───────</option>
                    <option value="<= 10"><= 10%</option>
                    <option value="<= 25"><= 25%</option>
                    <option value="<= 40"><= 40%</option>
                    <option value="<= 60"><= 60%</option>
                    <option value="<= 90"><= 90%</option>
                    <option disabled="disabled">───────</option>   -->  
                    <option value="10">>= 10%</option>
                    <option value="25">>= 25%</option>
                    <option value="40">>= 40%</option>
                    <option value="60">>= 60%</option>
                    <option value="90">>= 90%</option>                
                  </select>
                </div>         
                <div class="form-group">
                  <label for="sel1">RAM used: </label>
                  <select class="form-control" id="ram-sel">
                    <option value="0">Select all</option>
                    <!-- <option disabled="disabled">───────</option>
                    <option value="<= 10"><= 10%</option>
                    <option value="<= 25"><= 25%</option>
                    <option value="<= 40"><= 40%</option>
                    <option value="<= 60"><= 60%</option>
                    <option value="<= 90"><= 90%</option>
                    <option disabled="disabled">───────</option> -->    
                    <option value="10">>= 10%</option>
                    <option value="25">>= 25%</option>
                    <option value="40">>= 40%</option>
                    <option value="60">>= 60%</option>
                    <option value="90">>= 90%</option> 
                  </select>
                </div>
                <div class="form-group">
                <label for="sel1">Date time: </label>
                <div class='input-group date' id='datetime_resource'>
                    <input type='text' class="form-control" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
                </div>

                <button type="button" class="btn btn-primary" id="cpu-ram-smbt" data-toggle="modal" data-target="#cpu-ram-modal">Submit</button>
            </div>
        <div class="col-xs-10">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">CPU - Memory usage Chart</h3>
                </div>
                <div class="panel-body">
<?=\dosamigos\highcharts\HighCharts::widget([
    'clientOptions' => [
        'chart' => [
            'type' => 'scatter',
            'zoomType' => 'xy'
        ],
        'title' => [
            'text' => 'CPU load and Memory usage statistics'
        ],
        'xAxis' => [
            'title' => [
                'enabled' => true,
                'text' => 'Memory usage (%)'
            ],
            'startOnTick'=> true,
            'endOnTick' => true,
            'showLastLabel'=> true,        
        ],
        'yAxis' => [
            'title' => [
                'text'=> 'CPU usage (%)'
            ]
        ],
        'legend' => [
            'enabled' => false
        ],
        'plotOptions' => [
            'scatter' => [
                'marker' => [
                    'radius' => 5,
                    'states' => [
                        'hover'=> [
                            'enabled' => true,
                            'lineColor' => 'rgb(100,100,100)'
                        ]
                    ]
                ],
                'states' => [
                    'hover' => [
                        'marker' => [
                            'enabled' => false
                        ]
                    ]
                ],
                'tooltip' => [
                    'headerFormat' => '<b>{series.name}</b><br>',
                    'pointFormat' => 'IP: {point.ip}<br>Ram used: {point.x}%, CPU usage: {point.y}%<br>DateTime: {point.datetime}'
                ],        
                'point' => [
                    'events' => [
                        'click' => new JsExpression('function (e) { var id = "#"+this.series.name.replace(/\./g,"_")+"_ps_info"; $(id).modal("show");}'),
                    ]
                ],             
            ]
        ],
        'series' => $cpuRamData,
    ]]);
?>
                </div>
            </div>                
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-2">                
            <div class="form-group">
                <label for="sel1">Traffic In: </label>
                <select class="form-control" id="in-sel">
                    <option value="0">Select all</option>
                    <option value="50">>50 Mb/s</option>
                    <option value="100">>100 Mb/s</option>
                    <option value="150">>150 Mb/s</option>
                    <option value="200">>200 Mb/s</option>
                    <option value="300">>300 Mb/s</option>
                    <option value="400">>400 Mb/s</option>
                    <option value="500">>500 Mb/s</option>            
                    <option value="1000">>1 Gb/s</option> 
                </select>
            </div>         
            <div class="form-group">
                <label for="sel1">Traffic Out: </label>
                <select class="form-control" id="out-sel">
                    <option value="0">Select all</option>
                    <option value="50">>50 Mb/s</option>
                    <option value="100">>100 Mb/s</option>
                    <option value="150">>150 Mb/s</option>
                    <option value="200">>200 Mb/s</option>
                    <option value="300">>300 Mb/s</option>
                    <option value="400">>400 Mb/s</option>
                    <option value="500">>500 Mb/s</option> 
                    <option value="1000">>1 Gb/s</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sel1">Time Interval: </label>
                <select class="form-control" id="time-interval-sel">
                    <option value="0">None</option>
                    <option value="5">5 min</option>
                    <option value="30">30 min</option>
                    <option value="60">1 hours</option>
                    <option value="1440">1 days</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sel1">Percent Ratio: </label>
                <select class="form-control" id="per-ratio-sel">
                    <option value="0">Select all</option>
                    <option value="5">>5%</option>
                    <option value="15">>15%</option>
                    <option value="20">>20%</option>
                    <option value="40">>40%</option>
                    <option value="60">>60%</option>
                    <option value="80">>80%</option>
                    <option value="100">>100%</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sel1">Date time: </label>
                <div class='input-group date' id='datetimepicker'>
                    <input type='text' class="form-control" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="traffic-smbt">Submit</button>
           <!-- <button type="button" class="btn btn-danger" id="date-smbt">Only for test</button>-->
        </div>
        <div class="col-xs-10">
            <div id="date-div"></div>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Traffic Stats</h3>
                </div>
                <div class="panel-body" id="traffic-main-div">
                    <table id='traffic-table' width='100%' style='border-collapse: collapse;display: none' boder='1'>
                        <thead> 
                            <tr>    
                                <th>IP</th>
                                <th>Interface</th>
                                <th>In (Mb/s)</th>
                                <th>Out (Mb/s)</th>
                                <th>Date Time</th>
                            </tr>   
                        </thead>
                        <tbody>
<?php
    foreach($trafficDatas as $item){
        $tf_in = round($item->Inbound/1024/1024,2);
        $tf_out = round($item->Outbound/1024/1024,2);
        echo "<tr>
                            <td>".$item->server_ip."</td>
                            <td>".$item->interface."</td>
                            <td>".$tf_in."</td>
                            <td>".$tf_out."</td>
                            <td>".gmdate('H:i:s m/d/Y', $item->timestamp+3600*7)."</td>
                        </tr>";
        };
?>        
                        </tbody>
                    </table>
                </div>
            </div>
         </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-xs-2">                
            <div class="form-group">
              <label for="sel1">Disk usage: </label>
              <select class="form-control" id="disk-sel">
                    <option value=">= 0">Select all</option>
                    <option disabled="disabled">───────</option>
                    <option value="<= 10"><= 10%</option>
                    <option value="<= 25"><= 25%</option>
                    <option value="<= 40"><= 40%</option>
                    <option value="<= 60"><= 60%</option>
                    <option value="<= 90"><= 90%</option>
                    <option disabled="disabled">───────</option>    
                    <option value=">= 10">>= 10%</option>
                    <option value=">= 25">>= 25%</option>
                    <option value=">= 40">>= 40%</option>
                    <option value=">= 60">>= 60%</option>
                    <option value=">= 90">>= 90%</option> 
              </select>
            </div>         
            <button type="button" class="btn btn-primary" id="disk-smbt" data-toggle="modal" data-target="#disk-modal">Submit</button>
        </div>
        <div class="col-xs-10">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Disk Chart</h3>
                </div>
                <div class="panel-body">
<?=\dosamigos\highcharts\HighCharts::widget([
    'clientOptions' => [
        'chart' => [
            'type' => 'scatter',
            'zoomType' => 'xy'
        ],
        'title' => [
            'text' => 'Disk usage statistics'
        ],
        'xAxis' => [
            'title' => [
                'enabled' => true,
                'text' => 'Disk usage (%)'
            ],
            'startOnTick'=> true,
            'endOnTick' => true,
            'showLastLabel'=> true,
        ],
        'yAxis' => [
            'title' => [
                'text'=> 'Disk (GB)'
            ],
        ],
        'legend' => [
            'enabled' => false
        ],
        'plotOptions' => [
            'scatter' => [
                'marker' => [
                    'radius' => 5,
                    'states' => [
                        'hover'=> [
                            'enabled' => true,
                            'lineColor' => 'rgb(100,100,100)'
                        ]
                    ]
                ],
                'states' => [
                    'hover' => [
                        'marker' => [
                            'enabled' => false
                        ]
                    ]
                ],
                'tooltip' => [
                    'headerFormat' => '<b>{series.name}</b><br>',
                    'pointFormat' => 'IP: {point.ip}<br>Disk Used: {point.x}%, Disk size: {point.y}GB<br>DateTime: {point.datetime}'
                ],                
                'point' => [
                    'events' => [
                        'click' => new JsExpression('function (e) { var id = "#"+this.series.name.replace(/\./g,"_")+"_disk_info"; $(id).modal("show");}'),
                    ]
                ],             
            ]
        ],
        'series' => $diskData,
    ]]);
?>
                </div>
            </div>
        </div>
    </div> 
    </div>
</div>

<?php
    $this->registerJs("
        $('#datetimepicker').datetimepicker();
        $('#datetime_resource').datetimepicker({
            format: 'MM/DD/YYYY HH:00:00'
        });
        $('#traffic-table').dataTable({'DisplayLength': 50});
        $('#traffic-table').show();
        $('#cpu-ram-smbt').click(function(){
            //alert($('#datetime_resource').data('date'));
            var date_time_rs = 0;
            if($('#datetime_resource').data('date')){
                date_time_rs = $('#datetime_resource').data('date');
            }
            $.ajax({type: 'POST',
                    // url: '/rs_monitor/site/list-cpu',
                    url: '/site/list-cpu',
                    data: { cpu: $('#cpu-sel').val(),
                            ram: $('#ram-sel').val(),
                            datetime: date_time_rs },
                    success:function(result){
                        $('#server-cpuram').html(result);
                        $('#cpu-ram-table').dataTable();
                        jQuery('#cpu-ram-modal').modal({'show':false});}
                });
        });

        $('#disk-smbt').click(function(){
            $.ajax({type: 'POST',
                    // url: '/rs_monitor/site/list-disk',
                    url: '/site/list-disk',
                    data: { disk: $('#disk-sel').val() },
                    success:function(result){
                        $('#server-disk').html(result);
                        $('#disk-table').dataTable();
                        jQuery('#disk-modal').modal({'show':false});}
                });
        });
        $('#traffic-smbt').click(function(){
            var date_time = 0;
            if($('#datetimepicker').data('date')){
                date_time = $('#datetimepicker').data('date');
            }
            $.ajax({type: 'POST',
                    url: '/site/traffic',
                    data:   {
                            in: $('#in-sel').val(), 
                            out: $('#out-sel').val(),
                            time_interval: $('#time-interval-sel').val(), 
                            per_ratio: $('#per-ratio-sel').val(),
                            datetime: date_time,
                            },
                    success:function(result){
                        $('#traffic-main-div').html(result);
                        $('#traffic-table').dataTable({'iDisplayLength': 50});
                        $('#traffic-table').show();}
                });
        });
       // setTimeout('location.reload()', 300000);

        $('#date-smbt').click(function(){
            var date_time = 0;
            if($('#datetimepicker').data('date')){
                date_time = $('#datetimepicker').data('date');
            }
            alert(date_time); 
        });
    ")
?> 
