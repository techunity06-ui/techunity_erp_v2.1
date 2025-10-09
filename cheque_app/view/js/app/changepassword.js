$(document).ready(function() {

	$("#FormChangepass").on('submit',function(e) {
		var form = this;
		e.preventDefault();
		e.stopPropagation();
				
		if (form.submitted) {
		    return;
		}
		else if($("#new_pass").val()!=$("#retype_pass").val())
		{
			$("#message").html("<p class='error' style='color:red'>Enter Same Password </p><BR>");
			return false;
		}		
		form.submitted = true;		
		$("#new_pass").attr("disabled","disabled");
		$(this).attr("disabled","disabled");
		var ip_addr = $("#ip_addrF").val();
		var fError = $("#FailErrorF").val();

		$("#message").html("<p class='error' align='center' style='color:red'><img src='images/loading.gif' height='100%' /></p>");
		var token=  $("#token").val();
		var domain=  $("#domainF").val();
		var form_data = {
			key:$("#emailid").val(),						
			newpass:$("#new_pass").val(),						
			token:token,
			is_ajax: 1
		};
		
		//console.log(root_domain+'app/ForgetPassword/');
		$.ajax({
			type: "POST",
			cache:false,
			url: root_domain+'app/changepassword/',
			data: form_data,
			success: function(response)
			{
				//console.log(response);
				if(response == 'success') {
					$('#FormChangepass').trigger('reset');
					$("#new_pass").removeAttr("disabled","false");
					$("#btnChangepass").removeAttr("disabled","false");
					$("#message").html("<p class='error' style='color:green	'>Success your password is changed.</p><BR>");	
					setTimeout(function() {
						  window.location.href = 'login';
					}, 5000);
				}
				else if(response == 'invalid') {
					$("#new_pass").removeAttr("disabled","false");
					$("#btnChangepass").removeAttr("disabled","false");
					$('#FormChangepass').trigger('reset');
					$("#message").html("<p class='error' style='color:red'>Some thing wrong.</p><BR>");	
				}	
				else {
					$("#new_pass").removeAttr("disabled","false");
					$("#btnChangepass").removeAttr("disabled","false");
				}
				//console.log(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
					$("#new_pass").removeAttr("disabled","false");
					$("#btnChangepass").removeAttr("disabled","false");
					console.log(textStatus, errorThrown);
			}
		});
		
	});	
	
});