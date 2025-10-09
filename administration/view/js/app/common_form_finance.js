
// Cost Center Popup

function get_cost_center(check)
{
	alert(check);
	Loading(true);
	
	if(check=='yes')
	{
		$.ajax({
			
			type:'POST',
			url: root_domain+'app/common_form_finance/',
			data: { mode : "cost_center_form_show"},
			success:function(result)
			{
				//console.log(result);
				$("#ModalCostCenter").modal("show");
				//$('#cost_center_form_show').html(result);
				load_cost_center_data();
				$('#costcenter_id').focus();
				Unloading();
			}
		})
	}
	else
	{
		$('#cost_center_form_show').html('');
		Unloading();
	}
	
}

function add_cost_center()
{
	Loading(true);
	
	var costcenter_id = $('#costcenter_id').val();
	var costcenter_amount = $('#costcenter_amount').val();
	var costcenter_entry_type = $('#costcenter_entry_type').val();
	var cost_center_voucher_type = $('#cost_center_voucher_type').val();
	var cost_center_ledger_id = $('#cost_center_ledger_id').val();
	var cost_center_table = $('#cost_center_table').val();
	var cost_center_table_id = $('#cost_center_table_id').val();
	var edit_id = $('#edit_id').val();
	
	if(costcenter_id=='')
	{
		$('#cost_error_id').html('This Field Is Required');
		$('#cost_amount_id').html('');
		$('#cost_entry_id').html('');
		Unloading();
	}
	else if(costcenter_amount=='')
	{
		$('#cost_amount_id').html('This Field Is Required');
		$('#cost_error_id').html('');
		$('#cost_entry_id').html('');
		Unloading();
	}
	else if(costcenter_entry_type=='')
	{
		$('#cost_entry_id').html('This Field Is Required');
		$('#cost_error_id').html('');
		$('#cost_amount_id').html('');
		Unloading();
	}
	else
	{
		$('#cost_error_id').html('');
		$('#cost_amount_id').html('');
		$('#cost_entry_id').html('');
		
		$.ajax({
			
			type:'POST',
			
			url:root_domain+'app/common_form_finance/',
			
			data:{mode:"cost_center_form_add","costcenter_id":costcenter_id,"costcenter_amount":costcenter_amount,"costcenter_entry_type":costcenter_entry_type,"cost_center_voucher_type":cost_center_voucher_type,"cost_center_ledger_id":cost_center_ledger_id,"cost_center_table":cost_center_table,"cost_center_table_id":cost_center_table_id,"edit_id":edit_id },
			
			success:function(response)
			{
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					load_cost_center_data();
					Unloading();						
				}
				else if(response.trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response.trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				
				$('#costcenter_id').val('');
				$('#costcenter_amount').val('');
				$('#costcenter_entry_type').val('');
				$('#cost_center_ledger_id').val('');
				$('#cost_center_table_id').val('');
				
				Unloading();	
				
				$('#add_cost_center_btn').val('Add');
			}
		})
	}
}

function load_cost_center_data(){
		
		datatable = $("#cost-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/common_form_finance/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch_cost_center" }
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


function delete_cost_center(cost_center_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/common_form_finance/',
			data: { mode : "delete_cost_center", cost_center_id : cost_center_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS"); 	
					load_cost_center_data();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_cost_center(costcenter_transaction_id)
{
	Loading(true);
	//alert("edit");
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/common_form_finance/',
		data: { mode : "preedit", cost_center_id : costcenter_transaction_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#edit_id").val(obj.costcenter_transaction_id);
			$("#costcenter_id").val(obj.costcenter_id);
			$("#costcenter_amount").val(obj.costcenter_amount);
			$("#costcenter_entry_type").val(obj.costcenter_entry_type);
			
			$("#cost_center_voucher_type").val(obj.cost_center_voucher_type);
			$("#cost_center_ledger_id").val(obj.cost_center_ledger_id);
			$("#cost_center_table").val(obj.cost_center_table);
			$("#cost_center_table_id").val(obj.cost_center_table_id);
			
			$('#add_cost_center_btn').val('Update');
			
			Unloading();
		}
	});	
}



//Transportation Modal Start 


function get_ledger_transportation(check)
{
	//alert(check);
	Loading(true);
	
	if(check=='yes')
	{
		$.ajax({
			
			type:'POST',
			url: root_domain+'app/common_form_finance/',
			data: { mode : "transportation_form_show"},
			success:function(result)
			{
				console.log(result);
				$("#ModalTransportation").modal("show");
				load_transport_data();
				$('#transport_id').focus();
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				Unloading();
			}
		})
	}
	else
	{
		Unloading();
	}
	
}

function load_transport_data(){
		
		datatable = $("#transport-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/common_form_finance/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch_transport_details" }
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


function add_transport()
{
	Loading(true);
	
	var transport_id = $('#transport_id').val();
	var transport_gr_no = $('#transport_gr_no').val();
	var transport_gr_date = $('#transport_gr_date').val();
	var transport_vehicle_no = $('#transport_vehicle_no').val();
	var transport_station = $('#transport_station').val();
	var transport_voucher = $('#transport_voucher').val();
	var transport_transaction_table = $('#transport_transaction_table').val();
	var transport_transaction_table_id = $('#transport_transaction_table_id').val();

	//Eway bill details
	if($('#iseway_bill').val() == 'yes'){
		var eway_sub_type = $('#eway_sub_type').val();
		var eway_transaction_type = $('#eway_transaction_type').val();
	}else{
		var eway_sub_type = '';
		var eway_transaction_type = '';
	}
	
	var edit_id = $('#edit_id').val();
	
	if(transport_id=='')
	{
		$('#transport_id_error').html('This Field Is Required');
		Unloading();
	}
	else
	{
		
		$('#transport_id_error').html('');
		
		$.ajax({
			
			type:'POST',
			
			url:root_domain+'app/common_form_finance/',
			
			data:{mode:"transport_form_add","transport_id":transport_id,"transport_gr_no":transport_gr_no,"transport_gr_date":transport_gr_date,
			"transport_vehicle_no":transport_vehicle_no,"transport_station":transport_station,"transport_voucher":transport_voucher,
			"transport_transaction_table":transport_transaction_table,"transport_transaction_table_id":transport_transaction_table_id,
			eway_sub_type:eway_sub_type,eway_transaction_type:eway_transaction_type,"edit_id":edit_id },
			
			success:function(response)
			{
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					load_transport_data();
					Unloading();						
				}
				else if(response.trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response.trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				
				$('#transport_id').val('');
				$('#transport_gr_no').val('');
				$('#transport_gr_date').val('');
				$('#transport_vehicle_no').val('');
				$('#transport_station').val('');
				
				Unloading();	
				
				$('#add_transport_btn').val('Add');
			}
		})
	}
}

function delete_transportation(transport_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/common_form_finance/',
			data: { mode : "delete_transport", transport_id : transport_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS"); 	
					load_transport_data();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_transportation(transport_id)
{
	Loading(true);
	//alert("edit");
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/common_form_finance/',
		data: { mode : "preedit_transport", transport_id : transport_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#edit_id").val(obj.transport_transaction_id);
			$("#transport_id").val(obj.transport_id);
			$("#transport_gr_no").val(obj.transport_gr_no);
			$("#transport_gr_date").val(obj.transport_gr_date);
			$("#transport_vehicle_no").val(obj.transport_vehicle_no);
			$("#transport_station").val(obj.transport_station);
			
			$("#transport_voucher").val(obj.transport_voucher);
			$("#transport_transaction_table").val(obj.transport_transaction_table);
			$("#transport_transaction_table_id").val(obj.transport_transaction_table_id);
			
			$('#add_transport_btn').val('Update');
			
			Unloading();
		}
	});	
}


//Salesman Details Form


function get_ledger_salesman(check)
{
	//alert(check);
	Loading(true);
	
	if(check=='yes')
	{
		$.ajax({
			
			type:'POST',
			url: root_domain+'app/common_form_finance/',
			data: { mode : "sales_form_show"},
			success:function(result)
			{
				//console.log(result);
				$("#ModalSales").modal("show");
				load_transport_data();
				$('#salesman_id').focus();
				Unloading();
			}
		})
	}
	else
	{
		Unloading();
	}
	
}

