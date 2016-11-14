function redirect_url(request){
	location.href = "/" + request;
}

function zalert(title,msg){
	$.alert ({ 
		type: 'confirm'
		, title: title
		, text: '<p>' + msg + '</p>'
		, callback: function () { /*alert('Callback');*/ }	
	});
}

function all_hide() {
        var left_account = new Array ("#navDashboard", "#navTables", "#navGrid", "#navType", "#navButtons", "#navPages");
        for ( var i=0; i<6; i++ ){
                if ($(left_account[i]).hasClass('active')) {
                        $(left_account[i]).removeClass('active');
                }
        }
}

//export total sms
function ajx_export_total(template_id, date, time, gid){
	var wk_data = {
		"template_id"	: template_id
		,"date"			: date
		,"time"			: time
		,"group_id"		: gid
	};

	$.ajax({
		type: "POST",
		url: "/dashboard/export",
		data:wk_data,
		cache: false,
		dataType: "json",
		success: function(data){
			if ( data.status == 0 ){
				//TODO
			}else{
				//TODO
			}
		}
	});
}


function ajx_creat_task(template_id, date, time, gid){
	var wk_data = {
		"template_id"	: template_id
		,"date"			: date
		,"time"			: time
		,"group_id"		: gid
	};

	$.ajax({
		type: "POST",
		url: "/task/ajaxcreate",
		data:wk_data,
		cache: false,
		dataType: "json",
		success: function(data){
			if ( data.status == 0 ){
			//	$("#login_err").show();
			}else{
			//	$("#login_err").hide();
				var url_request = "task/list/"; 
				setTimeout("redirect_url(\'" + url_request + "\')", 1000);
			}
		}
	});
}



$(document).ready(function() {
	//active left menu
	all_hide();
	var str = location.pathname;
    if (str.search("dashboard") >= 0){
        $("#navDashboard").addClass("active");
    }else if (str.search("/sms") >= 0){
        $("#navTables").addClass("active");
    }else if (str.search("/price") >= 0){
        $("#navGrid").addClass("active");
    }
    //end
 
    $("#export_total").click(function(){
    	//var gid = $("#gid").val();
    	document.location.href = '/dashboard/export';
    });
    
    //create task
    $("#task_create_btn").click(function(){
    	var gid = $("#gid").val();
    	var time = $("#timepicker").val();
    	var date = $("#datepicker").val();
    	var template_id = $("#mail_template").val();
    	
    	ajx_creat_task(template_id,date,time,gid);
    });
    //end
    
    
    
    $("#import_contact_btn").click(function(){
    	var filename = $("#myfile").val();
    	if (filename != ""){
	    	$("#myfile").upload(
				'/contact/import',
				{"form" : "1"},
				function(data){
					if(data.status == 1){
						var url_request = "group/list/"; 
						setTimeout("redirect_url(\'" + url_request + "\')", 1000);
					} else if (data.status == 2){
						zalert("Error", "File is not csv format.");
					} else if (data.status == 3){
						zalert("Error", "All the contacts are exits on system.");
					}else{
						zalert("Error", "Can't upload.");
					}
				},
				'json'
	    	);
	    }
    });
    
});