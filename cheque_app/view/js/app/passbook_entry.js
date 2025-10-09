	var reply;var range = -9;
		$(document).ready(function() {
			
			fetch_list(); //fetch list
			$('#date_range').daterangepicker();
			$(".datetime").datepicker({
				format: 'dd-mm-yyyy',
				startDate: '-3d',
				autoclose: true,
				todayHighlight: true,
				calendarWeeks: true
			});
			/*Filter List Start*/
			$('#date_range').on('apply.daterangepicker', function(ev, picker) {
				range = picker.startDate.format('YYYY-MM-DD')+','+picker.endDate.format('YYYY-MM-DD');
				bankTable.fnClearTable();
				fetch_list();
			});
			$('#date_range').on('cancel.daterangepicker', function(ev, picker) {
				$('#date_range').val('');
				range = -9;
				bankTable.fnClearTable();
				fetch_list();
			});
			$('#ps_amount').on('click', function(e) {
				e.preventDefault();
				bankTable.fnClearTable();
				fetch_list();
			});
			
			$("#ps_custid").change(function(e) {
				e.preventDefault();
				bankTable.fnClearTable();
				fetch_list();
			});
			$("#ps_accid").change(function(e) {
				e.preventDefault();
				bankTable.fnClearTable();
				fetch_list();
			});
			$("#ps_pmodeid").change(function(e) {
				e.preventDefault();
				bankTable.fnClearTable();
				fetch_list();
			});
			$("#ps_typeid").change(function(e) {
				e.preventDefault();
				bankTable.fnClearTable();
				fetch_list();
			});
			/*Filter List End*/
			
			$(".delete-row").click(function(e){
				e.preventDefault();
				bootbox.confirm("Are you sure ?", function(r) {
					if(r) {
					
					}
					else {
					
					}
				});
			});
			
			$("input[name='pay_mode']").change(function(){
				var mode = $(this).val();
				if(mode == 1) {
					$("#img_ac_e").addClass("hidden");
					$("#img_ac_f").addClass("hidden");
					$("#img_nn").addClass("hidden");
					$("#bearer").addClass("hidden");
					
				}
				
			});
			
			//add passbook entry
			$("#FormPassbookEntry").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						typeid : $("input[name='typeid']:checked").val(),
						acc_id : $("#acc_id").val(),
						paymentmodeid : $("#paymentmodeid").val(),
						amount : $("#amount").val(),
						entry_date : $("#entry_date").val(),
						customer_id : $("#customer_id").val(),
						passbook_note : $("#passbook_note").val()
						/*city : $("#city").val(),
						branch : $("#branch").val(),*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/passbook_entry/',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							if(response == "1") {
								$.gritter.add({
									text: 'TRANSACTION ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								$("#FormPassbookEntry").reset();
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
		
			$("#FormEditPassbook").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						token :  $("#token").val(),
						edit_id :  $("#edit_id").val(),
						typeid : $("input[name='edit_typeid']:checked").val(),
						acc_id : $("#edit_acc_id").val(),
						paymentmodeid : $("#edit_paymentmodeid").val(),
						amount : $("#edit_amount").val(),
						entry_date : $("#edit_entry_date").val(),
						customer_id : $("#edit_customer_id").val(),
						passbook_note : $("#edit_passbook_note").val()
						/*city : $("#city").val(),
						branch : $("#branch").val(),*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/passbook_entry/',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							$("#ModalEditPassbook").modal("hide");
							if(response == "1") {
								$.gritter.add({
									text: 'TRANSACTION UPDATE SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});
								$("#ModalEditPassbook").modal("hide");
								bankTable.fnReloadAjax();
								$("#FormEditPassbook").reset();
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
		
		});
		function show_hide_cheque()
		{
			var typeid=$("input[name='typeid']:checked").val();//1.debit 2 credit
			console.log(typeid);
			if(typeid==1)
			{	
				$("#paymentmodeid option[value='2']").attr("disabled", "disabled");
				$('#CHEQUE').attr('disabled');
				$('#paymentmodeid').select2(width:100%);
				//$("#paymentmodeid option[value=2]").attr("disabled", "disabled");
			}
			else if(typeid==2)
			{
				$("#paymentmodeid option[value=2]").show();
			}
		}
		function fetch_list()
		{
			var ch_amnt = $("#ps_amount").val();
			if (ch_amnt == null || ch_amnt == "") {
				ch_amnt = -9; //not considered
			}
			bankTable = $("#datatable").dataTable({
				"bDestroy":true,
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO DATA ADDED YET !",
				},
				"aLengthMenu": [[50, 100, 250, 500,-1], [50, 100, 250, 500,'All']],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/passbook_entry/',
				"fnServerParams": function ( aoData ) {
					aoData.push( { "name": "type", "value": "fetch" },
								{ "name": "typeid", "value": $("#ps_typeid").val()  },
								{ "name": "account", "value": $("#ps_accid").val() },
								{ "name": "pmode", "value": $("#ps_pmodeid").val() },
								{ "name": "payee", "value": $("#ps_custid").val() },
							   	{ "name": "date", "value": range },
								{ "name": "amount", "value": ch_amnt }
							   );
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				}
			}).fnSetFilteringDelay();
			
			
			//Search input style
			$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
			$('.dataTables_length select').addClass('form-control');
			
		}
		
		function add_customer()
		{
			$('#customer_add').modal('show');
		}
		$("#FormNewCustomer").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						name : $("#name").val(),
						city : $("#city").val(),
						phone : $("#phone").val(),
						mail : $("#mail").val(),
						pannumber : $("#pannumber").val(),
						modaladd:'1'
						//panphotoid : $("#panphotoid").val()
					};
					
					//console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/customer/',
						data: form_data,
						success: function(response)
						{
							response=response.trim();
							var arr=response.split('@@');
							console.log(arr);
							if(arr[0] == "1") {
								$.gritter.add({
									text: 'CUSTOMER ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1500,
								});						
								
								$('#payee_select').append("<option value='"+arr[1]+"'>"+$("#name").val().toUpperCase()+"</option>");
								$('#payee_select').trigger("change");
								$('#payee_select').select2("val",arr[1]);
								Unloading();
								$("#FormNewCustomer").reset();
								$('#customer_add').modal('hide');
								$("#payee_select").change();
							}
							else if(arr[0] == "-1") {
								$.gritter.add({
									text: 'Customer already exists in the system.',
									class_name: 'info',
									time: 1500,
								});
							}
							else if(arr[0] == "0") {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1500,
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
		var editReq = null;
		function edit_entry(id) {
			Loading(true);
			if(editReq != null)
				editReq.abort();
			
			editReq = $.ajax({
				type: "POST",
				url: root_domain+'app/passbook_entry/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditPassbook").modal("show");
					$("#edit_id").val(id);
					$("#edit_typeid1").prop( "checked", false );
					$("#edit_typeid2").prop( "checked", false );
					if(obj.typeid=="1")
					{
						$( "#edit_typeid1" ).prop( "checked", true );
						}
					else if(obj.typeid=="2")
					{
						$("#edit_typeid2").prop( "checked", true );
					}
					$("#edit_acc_id").select2('val',obj.acc_id);
					$("#edit_paymentmodeid").select2('val',obj.paymentmodeid);
					$("#edit_amount").val(obj.amount);
					$("#edit_entry_date").val(obj.edate);;
					$("#edit_customer_id").select2('val',obj.customer_id);
					$("#edit_passbook_note").val(obj.passbook_note);
					$(".datetime").datepicker({
					format: 'dd-mm-yyyy',
					startDate: '-3d',
					autoclose: true,
					todayHighlight: true,
					calendarWeeks: true
				});
					
					Unloading();
				}
			});	
		}
	function delete_entry(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/passbook_entry/',
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