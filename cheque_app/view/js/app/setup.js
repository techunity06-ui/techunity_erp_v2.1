$(document).ready(function() {

	$("#Frmsetup").on('submit',function(e) {
		e.preventDefault();
		e.stopPropagation();
	var check = $(this).parsley('validate');
	if(check) {	        
		var form = this;		
		if (form.submitted) {
		    return;
		}		
		form.submitted = true;		
		var form_data=new FormData(this);
		console.log(form_data);
		$("#message").html("<p class='error' align='center' style='color:red'><img src='images/loading.gif' height='100%' /></p>");
		var token=  $("#token").val();
		var domain=  $("#domain").val();
		//console.log(root_domain+'app/login/');
		$.ajax({
			type: "POST",
			cache:false,
			url: root_domain+'app/setup/',
			data: form_data,
			contentType: false,
			processData:false,
			success: function(response)
			{
				console.log(response);
				if(response == '1') {
					$("#message").html("<p class='error' style='color:green'>Setup Completed.</p><BR>");
					$("#Frmsetup").trigger('reset');
					setTimeout(function(){window.location=root_domain+'login'},5000);
				}
				else if(response == '0') {
					$("#message").html("<p class='error' style='color:red'>Setup not work. Please Try Again.</p><BR>");	
				}
						
			},
			error: function(jqXHR, textStatus, errorThrown) {
					$("#username").removeAttr("disabled","false");
					$("#password").removeAttr("disabled","false");
					$("#btnLogin").removeAttr("disabled","false");
					console.log(textStatus, errorThrown);
			}
			
		});
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
});
