var Script = function () {
$(document).ready(function() {
        // validate the comment form when it is submitted        

		// validate signup form on keyup and submit
        $("#signin").validate({
            rules: {
                username: {
                    required: true,
					email:true,
                    minlength: 10
                },
                password: {
                    required: true,
                    minlength: 5
                }
				

            },
            messages: {
                username: {
                    required: "Enter a email",
                    minlength: "Your Username must consist of at least 2 characters"
                },
                password: {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 5 characters long"
                }
            }
        });        
        
    });
	$("#signin").on('submit',function(e) {
		var form = this;
		e.preventDefault();
		e.stopPropagation();	
		if (!$("#signin").valid()) {
		    return false;
		}		
		form.submitted = true;		
		$("#username").attr("disabled","disabled");
		$("#password").attr("disabled","disabled");
		$(this).attr("disabled","disabled");
		var ip_addr = $("#ip_addr").val();
		var fError = $("#FailError").val();
		var remember=$('input[name=remember]:Checked').val();
		$("#message").html("<p class='error' align='center' style='color:red'><img src='img/loading.gif' height='100%' /></p>");
		var token=  $("#token").val();
		var domain=  $("#domain").val();
		var form_data = {
			username: $("#username").val(),
			password: $("#password").val(),
			redirect: $("#redirect").val(),
			b: BrowserDetect.browser,
			bv:BrowserDetect.version,
			os:BrowserDetect.OS,
			ip:ip_addr,
			token:token,
			is_ajax: 1
		};		
		$.ajax({
			cache:false,
			url: root_domain+'app/login/',
			type: "POST",
			data: form_data,
			success: function(response)
			{
				console.log(response);
				var obj = jQuery.parseJSON(response);
				response=obj.msg;
				console.log(response);
				if(response == 'activate') {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					$("#message").html("<p class='error' style='color:red'>Login failed. Admin has not reviewed your account. Wait until he review - verify - authorize your account. OR You are temporary suspended.</p><BR>");	
				}
				else if(response == 'licence') {
					$("#loginusername").removeAttr("disabled","false");
					$("#login_password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					$('#companylogin_add').trigger('reset');
					
					$("#message").html("<p class='error' style='color:red'>Login failed. Trail Period is over purchase licence version.</p><BR>");	
				}
				else if(response=="success")
				{
					//alert("fd");
					var redirect = $('#redirect').val();
					//console.log(remember);
					if(remember=="1")
					{
						$.cookie("remember_me", $("#username").val(), { expires : 1, path:"/" });
						$.cookie("password", $("#password").val(), { expires : 1, path:"/" });
					}
					else
					{
						$.removeCookie('remember_me', { path: '/' });
						$.removeCookie('password', { path: '/' });
					}
					if(obj.bkp==true)//for backup url redirect
					{
						window.location.href = obj.bkp_url;
					}
					else if(redirect != null && redirect != ' ' && redirect != '')
					{
						form.submitted = false;
				        form.submit();
						window.location.href = redirect;
						console.log("window.location.href = "+redirect);
					}
					else {
						form.submitted = false;
				        form.submit();
						window.location.href = root_domain+"dashboard";
					}
				}
				else if(response == 'employee')
				{
					form.submitted = false;
					form.submit();
					window.location.href = root_domain+"dashboard";
					
				}
				else if(response == 'invalid') {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					form.submitted = false;
					$("#message").html("<center><p class='error' style='color:red'>Invalid Email or Password</p><center><BR>");
				}
				else if(response == 'single_user') {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					form.submitted = false;
					$("#message").html("<center><p class='error' style='color:red'>Single user Licence</p><center><BR>");
				}

				else {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
				}
				//console.log(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					console.log(textStatus, errorThrown);
			}
		});
		
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

}();
