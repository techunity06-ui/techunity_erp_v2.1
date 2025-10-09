$(document).ready(function() {

	$("#FormForgetPassword").on('submit',function(e) {
		var form = this;
		e.preventDefault();
		e.stopPropagation();	
		if (form.submitted) {
		    return;
		}
		form.submitted = true;		
		$("#usernameF").attr("disabled","disabled");
		$(this).attr("disabled","disabled");
		var ip_addr = $("#ip_addrF").val();
		var fError = $("#FailErrorF").val();

		$("#messageF").html("<p class='error' align='center' style='color:red'><img src='images/loading.gif' height='100%' /></p>");
		var token=  $("#tokenF").val();
		var domain=  $("#domainF").val();
		var form_data = {
			usernameF: $("#usernameF").val(),
			redirect: $("#redirectF").val(),
			b: BrowserDetect.browser,
			bv:BrowserDetect.version,
			os:BrowserDetect.OS,
			ip:ip_addr,
			token:token,
			is_ajax: 1
		};
		
		//console.log(root_domain+'app/ForgetPassword/');
		$.ajax({
			type: "POST",
			cache:false,
			url: root_domain+'app/ForgetPassword/',
			data: form_data,
			success: function(response)
			{
				console.log(response);
				//alert(response);
				if(response == 'activate') {
					$("#usernameF").removeAttr("disabled","false");
					$("#btnForgetPassword").removeAttr("disabled","false");
					$("#messageF").html("<p class='error' style='color:red'>Please Check your mail inbox/spam for change password link.</p><BR>");	
				}
				else if(response == 'licence') {
					$("#usernameF").removeAttr("disabled","false");
					$("#btnForgetPassword").removeAttr("disabled","false");
					$('#FormForgetPassword').trigger('reset');
					$("#messageF").html("<p class='error' style='color:red'>Login failed. Trail Period is over purchase licence version.</p><BR>");	
				}
				else if(response == 'success')
				{
					//console.log(response);
					var redirect = $('#redirectF').val();
					if(redirect != null && redirect != ' ' && redirect != '')
					{
						form.submitted = false;
				                form.submit();
						window.location.href = redirect;
						//console.log("window.location.href = "+redirect);
					}
					else {
						form.submitted = false;
				                form.submit();
						window.location.href = domain+"home";
						//console.log("window.location.href = "+domain+"dashboard");
					}
				}
				else if(response == 'invalid') {
					$("#usernameF").removeAttr("disabled","false");
					$("#btnForgetPassword").removeAttr("disabled","false");
					form.submitted = false;
					$("#messageF").html("<center><p class='error' style='color:red'>"+fError+"</p><center><BR>");
				}

				else {
					$("#usernameF").removeAttr("disabled","false");
					$("#btnForgetPassword").removeAttr("disabled","false");
				}
				//console.log(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
					$("#usernameF").removeAttr("disabled","false");
					$("#btnForgetPassword").removeAttr("disabled","false");
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
});