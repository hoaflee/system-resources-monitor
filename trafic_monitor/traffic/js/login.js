function redirect_url(request){
	location.href = "/" + request;
}

function ajx_login(username, password){
	var wk_data = {
		"username"		: username
		,"password"		: password
	};

	$.ajax({
		type: "POST",
		url: "/user/login",
		data:wk_data,
		cache: false,
		dataType: "json",
		success: function(data){
			if ( data.status == 0 ){
				$("#login_err").show();
			}else{
				$("#login_err").hide();
				var url_request = "sms/list/"; 
				setTimeout("redirect_url(\'" + url_request + "\')", 1000);
			}
		}
	});
}

$(document).ready(function() {
	$("#login_btn").click(function(){
		var username = $("#username").val();
		var password = $("#password").val();
		if(username == '' || password == ''){
			$("#login_err").show();	
		}else{
			$("#login_err").hide();
			ajx_login(username, password);
		}
		
	});
	
	
		
});