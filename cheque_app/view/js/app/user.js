$(document).ready(function() {
	accountTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO USER ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/user/',
				"fnServerParams": function ( aoData ) {
					aoData.push( { "name": "type", "value": "fetch" } );
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				}
			}).fnSetFilteringDelay();

			//Search input style
			$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
			$('.dataTables_length select').addClass('form-control');
			
	$("#FrmUser").on('submit',function(e) {
		e.preventDefault();
		e.stopPropagation();
	var check = $(this).parsley('validate');
	if(check) {	        
		var form = this;		
		if (form.submitted) {
		    return;
		}		
		//form.submitted = true;		
		var form_data=new FormData(this);		
		Loading();
		var token=  $("#token").val();
		var domain=  $("#domain").val();
		$.ajax({
			type: "POST",
			cache:false,
			url: root_domain+'app/user/',
			data: form_data,
			contentType: false,
			processData:false,
			success: function(response)
			{
				console.log(response);
				if(response == '1') {
					$.gritter.add({
									text: 'USER ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});					
					$("#FrmUser").trigger('reset');					
				}
				else if(response == "-1") {
					$.gritter.add({
						text: 'ALREADY EXISTS !',
						class_name: 'info',
						time: 1000,
					});
				}
				else if(response == "0") {
					$.gritter.add({
						text: 'ERROR, TRY AGAIN !',
						class_name: 'danger',
						time: 1000,
					});
				}
				Unloading();		
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
	$("#EditCustomer").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						token :  $("#edit_token").val(),
						id : $("#edit_id").val(),
						company_name : $("#edit_name").val(),
						address : $("#edit_address").val(),
						contact_no: $("#edit_phone").val(),
						email: $("#edit_email").val(),
						password : $("#edit_password").val()
					};

					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/user/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'UPDATED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								accountTable.fnReloadAjax();
								$("#EditCustomer").reset();
								$("#ModalEditUser").modal("hide");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'ALREADY EXISTS !',
									class_name: 'danger',
									time: 1000,
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 1000,
								});
							}
							else {
								$.gritter.add({
									text: 'SERVER ERROR, CONTACT ADMIN !',
									class_name: 'danger',
									time: 1000,
								});
								console.log(response);
							}
							Unloading();
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
	
});
function edit_user(id) {
			Loading(true);			
			 $.ajax({
				type: "POST",
				url: root_domain+'app/user/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditUser").modal("show");
					$("#edit_id").val(id);
					$("#edit_name").val(obj.company_name);
					$("#edit_address").val(obj.user_address);
					$("#edit_email").val(obj.user_mail);					
					$("#edit_phone").val(obj.user_phone);
					Unloading();
				}
			});	
		}
function change_status(id,status) {
	
	bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
Loading(true);						
	 $.ajax({
		type: "POST",
		url: root_domain+'app/user/',
		data: { type : "status_update", id : id, status : status },
		success: function(response)
		{
			console.log(response);
			if(response == "1") 
			{
				$.gritter.add({
					text: 'UPDATED SUCCESSFULLY !',
					class_name: 'success',
					time: 1000,
				});
				accountTable.fnReloadAjax();				
				Unloading();
			}
			else {
				$.gritter.add({
					text: 'SERVER ERROR, CONTACT ADMIN !',
					class_name: 'danger',
					time: 1000,
				});
				console.log(response);
			}
			Unloading();
		}
	});	
	}
});
}
function delete_user(id) {
	bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
	Loading(true);			
	 $.ajax({
		type: "POST",
		url: root_domain+'app/user/',
		data: { type : "delete", id : id },
		success: function(response)
		{
			console.log(response);
			if(response == "1") 
			{
				$.gritter.add({
					text: 'UPDATED SUCCESSFULLY !',
					class_name: 'success',
					time: 1000,
				});
				accountTable.fnReloadAjax();				
				Unloading();
			}
			else {
				$.gritter.add({
					text: 'SERVER ERROR, CONTACT ADMIN !',
					class_name: 'danger',
					time: 1000,
				});
				console.log(response);
			}
			Unloading();
		}
	});	
	}
});
}	
