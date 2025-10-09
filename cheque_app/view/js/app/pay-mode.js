var bankTable;
		$(document).ready(function(){
			//initialize the javascript
			//Basic Instance
			bankTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO MODE ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/pay-mode/',
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
			
			$(".delete-row").click(function(e){
				e.preventDefault();
				bootbox.confirm("Are you sure ?", function(r) {
					if(r) {
					
					}
					else {
					
					}
				});
			});
			$("#FormNewBank").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						name : $("#mode_name").val(),
						/*city : $("#city").val(),
						branch : $("#branch").val(),*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/pay-mode/',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							if(response == "1") {
								$.gritter.add({
									text: 'MODE ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								$("#FormNewBank").reset();
								/*$("#PanPreview").attr("src","");
								$("#PANPrevew_Large").attr("href","");*/
								Unloading();
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
							else {
								console.log(response);
							}
							Unloading();
						}
					});
				}
			});
			
			$("#EditBank").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						token :  $("#edit_token").val(),
						id : $("#edit_id").val(),
						name : $("#edit_name").val(),
						/*city : $("#edit_city").val(),
						branch : $("#edit_branch").val()*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/pay-mode/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'UPDATED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								$("#EditBank").reset();
								$("#ModalEditBank").modal("hide");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'Bank already exists in the system.',
									class_name: 'info',
									time: 1000,
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1000,
								});
							}
							else {
								console.log(response);
							}
							Unloading();
						}
					});
				}
			});
		});
		
		var editReq = null;
		function edit_bank(id) {
			Loading(true);
			if(editReq != null)
				editReq.abort();
			
			editReq = $.ajax({
				type: "POST",
				url: root_domain+'app/pay-mode/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditBank").modal("show");
					$("#edit_id").val(id);
					$("#edit_name").val(obj.mode_name);
					/*$("#edit_city").val(obj.bank_city);
					$("#edit_branch").val(obj.bank_branch);*/
					Unloading();
				}
			});	
		}
		
		function delete_bank(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/pay-mode/',
						data: { type : "delete", token :  $("#token").val(), id : id },
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'REMOVED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								Unloading();
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 1000,
								});
								Unloading();
							}
							else {
								$.gritter.add({
									text: 'OOPS, SERVER ERROR !',
									class_name: 'danger',
									time: 1000,
								});
								Unloading();
								console.log(response);
							}
						}
					});	
				}
			});
		}