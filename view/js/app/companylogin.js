//var datatable;
$(document).ready(function() {
$("#companylogin_add").validate({
	rules: {
		
		login_password:{
			required:true,
			minlength:5
		},
		attendance:{
			required: function(element){
				
				var loginusername = $.trim($('#loginusertype_id').val());
				if(loginusername=='3'){
					return loginusername.length > 0 && loginusername=='3';
				}
				else if(loginusername=='5'){
					return loginusername.length > 0 && loginusername=='5';
				}
				//return $("#change_status").val().length > 0;
			}
		},
	},
	messages: {
		
		login_password:{
			required:"Enter Password",
			minlength:"Password Must be More than 5 character"
		},
		attendance:{
			
			required:"Please Fill The Attendance"
		}
	
	}
}); 
$('#password').keypress(function(e) { 
		var s = String.fromCharCode( e.which );
		if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey ) {
			$('#message').html('<center><font color="orange">ALERT : CAPS LOCK IS ON</font></center><BR>');
		}
		else {
			$('#message').html('');
		}
	});
	
	var BrowserDetect = {
		init: function () {
			this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
			this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
			this.OS = this.searchString(this.dataOS) || "an unknown OS";
		},
		searchString: function (data) {
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1)
						return data[i].identity;
				}
				else if (dataProp)
					return data[i].identity;
			}
		},
		searchVersion: function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1) return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		},
		dataBrowser: [
			{
				string: navigator.userAgent,
				subString: "Chrome",
				identity: "Chrome"
			},
			{ 	string: navigator.userAgent,
				subString: "OmniWeb",
				versionSearch: "OmniWeb/",
				identity: "OmniWeb"
			},
			{
				string: navigator.vendor,
				subString: "Apple",
				identity: "Safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera",
				versionSearch: "Version"
			},
			{
				string: navigator.vendor,
				subString: "iCab",
				identity: "iCab"
			},
			{
				string: navigator.vendor,
				subString: "KDE",
				identity: "Konqueror"
			},
			{
				string: navigator.userAgent,
				subString: "Firefox",
				identity: "Firefox"
			},
			{
				string: navigator.vendor,
				subString: "Camino",
				identity: "Camino"
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent,
				subString: "Netscape",
				identity: "Netscape"
			},
			{
				string: navigator.userAgent,
				subString: "MSIE",
				identity: "Explorer",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent,
				subString: "Gecko",
				identity: "Mozilla",
				versionSearch: "rv"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent,
				subString: "Mozilla",
				identity: "Netscape",
				versionSearch: "Mozilla"
			}
		],
		dataOS : [
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			{
				   string: navigator.userAgent,
				   subString: "iPhone",
				   identity: "iPhone/iPod"
			},
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}
		]

	};
	BrowserDetect.init();
	
	$("#companylogin_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#companylogin_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var ip_addr = $("#ip_addr").val();
	var form_data=new FormData(this);
	//alert(form_data.loginusername);
	var browser=BrowserDetect.browser;
	form_data.append('b', browser);
	form_data.append('bv', BrowserDetect.version);
	form_data.append('os', BrowserDetect.OS);
	form_data.append('ip', ip_addr);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/companylogin/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var res=jQuery.parseJSON(response);
			responsevalue=res.msg;
			if(responsevalue  == '1') {
				Unloading();
				$('#companylogin_add').trigger('reset');
				$("#company_modal").modal("hide");
				$("#companylogin_modal").modal("hide");
				window.location=root_domain+'dashboard';
			}
			else if(responsevalue == 'licence') {
					$("#loginusername").removeAttr("disabled","false");
					$("#login_password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					$('#companylogin_add').trigger('reset');
					
					$("#message").html("<p class='error' style='color:red'>Login failed. Trail Period is over purchase licence version.</p><BR>");
					window.location=root_domain+'licence/'+res.company_id;
				}
			else if(responsevalue == 'ip_error') {
					$("#loginusername").removeAttr("disabled","false");
					$("#login_password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					$('#companylogin_add').trigger('reset');
					var mms="<p class='error' style='color:red'>Login failed. IP Address Not Match "+res.ip_add+"</p><BR>";
					$("#message").html(mms);
					//window.location=root_domain+'licence/'+res.company_id;
				}
			else if(responsevalue == '3') {
				$("#loginusername").removeAttr("disabled","false");
				$("#login_password").removeAttr("disabled","false");
				$("#btnLogin").removeAttr("disabled","false");
				$('#companylogin_add').trigger('reset');
				
				$("#message").html("<p class='error' style='color:red'>Login failed. Single User Licence Version.</p><BR>");	
			}
			else if(responsevalue  == '-1')
			{
				$("#message").html("<center><p class='error' style='color:red'>Invalid Email or Password</p><center><BR>");
			}
			else
			{
				$("#message").html("<center><p class='error' style='color:red'>Invalid Email or Password</p><center><BR>");
			}
			Unloading();				
				
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
});

$(".btn_close").click(function() {
    $("label.error").hide();
});



function open_forgetpass()
{
	$("#company_modal").modal("hide");
	$("#companylogin_modal").modal("hide");
	$("#forgot_companyid").val($("#logincompany_id").val())
	$("#forgot_usertype").val($("#loginusertype_id").val())
	$("#myModal").modal("show");
	
}
function close_forgetpass()
{
	pass_session($("#login_company").html(),$("#logincompany_id").val())
}
function check_forgotpass()
{
	var question= $("#forgotquestion_id").val();
	var answer= $("#forgotgive_answer").val();
	var companyid= $("#forgot_companyid").val();
	var usertype= $("#forgot_usertype").val();
	if(question=='')
	{
		$("#error_companyid").html("Select Question")
	}
	else if(answer=="")
	{
		$("#error_companyid").html("")
		$("#error_answer").html("Enter Answer")
	}
	else
	{
		$("#error_companyid").html("")
		$("#error_answer").html("")
		$.ajax({
		cache:false,
		url: root_domain+'app/company/',
		type: "POST",
		data: { mode : "forgot_pass", question:question,answer:answer,companyid:companyid,usertype:usertype },
		success: function(response)
		{	
			console.log(response);
			var res=jQuery.parseJSON(response);
			responsevalue=res.msg;
			if(responsevalue  == '1') {
				Unloading();
				$('#companylogin_add').trigger('reset');
				$("#company_modal").modal("hide");
				$("#companylogin_modal").modal("hide");
				$("#myModal").modal("hide");
				window.location=root_domain+'changepassword/'+res.user_id;
			}
			else if(responsevalue  == '-1')
			{
				$("#forgot_message").html("<center><p class='error' style='color:red'>Question & Answer is Wrong</p><center><BR>");
			}
			else
			{
				$("#forgot_message").html("<center><p class='error' style='color:red'>Question & Answer is Wrong</p><center><BR>");
			}
			Unloading();				
				
			}
		});
	
	}
}