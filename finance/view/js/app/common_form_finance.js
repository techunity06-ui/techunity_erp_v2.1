
// Cost Center Popup

function get_cost_center(check)
{
	//alert(check);
	Loading(true);
	
	if(check=='yes')
	{		
		$("#ModalCostCenter").modal("show");
		load_cost_center_data();
		$('#costcenter_id').focus();
		Unloading();
		$('#cost_center_link').show();
	}
	else
	{
		//$('#cost_center_form_show').html('');
		$('#cost_center_link').hide();
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
			
			url:root_domain+finance_root_domain+'app/common_form_finance/',
			
			data:{mode:"cost_center_form_add","costcenter_id":costcenter_id,"costcenter_amount":costcenter_amount,"costcenter_entry_type":costcenter_entry_type,"cost_center_voucher_type":cost_center_voucher_type,"cost_center_ledger_id":cost_center_ledger_id,"cost_center_table":cost_center_table,"cost_center_table_id":cost_center_table_id,"edit_id":edit_id },
			
			success:function(response)
			{
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					load_cost_center_data();
					Unloading();						
				}
				else if(response.trim() == '2') {
					toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
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
				$('#edit_id').val('');
				Unloading();	
				
				$('#add_cost_center_btn').val('Add');
			}
		})
	}
}

function load_cost_center_data(){

		var cost_center_table = $('#cost_center_table').val();
		var edit_id = $('#eid').val();
		
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
			"sAjaxSource": root_domain+finance_root_domain+'app/common_form_finance/',
			"fnServerParams": function ( aoData ) {
				aoData.push({ "name": "mode", "value": "fetch_cost_center" });
				aoData.push({ "name": "cost_center_table", "value": cost_center_table });
				aoData.push({ "name": "cost_center_table_id", "value": edit_id });
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
			url: root_domain+finance_root_domain+'app/common_form_finance/',
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
		url: root_domain+finance_root_domain+'app/common_form_finance/',
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
				//console.log(result);
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
	var transport_mode = $('#transport_mode').val();
	var distance_km = $('#distance_km').val();
	var transport_vehicle_no = $('#transport_vehicle_no').val();
	var transport_station = $('#transport_station').val();
	var transport_pincode = $('#transport_pincode').val();

	var transport_doc_no = $('#transport_doc_no').val();
	var transport_doc_date = $('#transport_doc_date').val();

	var transport_voucher = $('#transport_voucher').val();
	var transport_transaction_table = $('#transport_transaction_table').val();
	var eway_sub_type = $('#eway_sub_type').val();
	var eway_transaction_type = $('#eway_transaction_type').val();
	var eway_bill_voucher_type = $('#eway_bill_voucher_type').val();
	var eway_bill_voucher_table = $('#eway_bill_voucher_table').val();
	var iseinvoice_bill = $('#iseinvoice_bill').val();
	var iseway_bill = $('#iseway_bill').val();
	var transport_transaction_table_id = $('#transport_transaction_table_id').val();

	var edit_id = $('#edit_eway_id').val();
	
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
			
			url:root_domain+finance_root_domain+'app/common_form_finance/',
			
			data:{mode:"transport_form_add","transport_id":transport_id,"transport_gr_no":transport_gr_no,"transport_gr_date":transport_gr_date,"transport_mode":transport_mode,
			"distance_km":distance_km,"transport_vehicle_no":transport_vehicle_no,"transport_station":transport_station,"transport_pincode":transport_pincode,
			"transport_doc_no":transport_doc_no,"transport_doc_date":transport_doc_date,"transport_voucher":transport_voucher,
			"transport_transaction_table":transport_transaction_table,"edit_id":edit_id,"eway_sub_type":eway_sub_type,"eway_transaction_type":eway_transaction_type,
			 "eway_bill_voucher_type":eway_bill_voucher_type,"eway_bill_voucher_table":eway_bill_voucher_table,"iseway_bill":iseway_bill,"iseinvoice_bill":iseinvoice_bill,"transport_transaction_table_id":transport_transaction_table_id },
			
			success:function(response)
			{
				
				//console.log(response);
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$("#add_cost_center_btn").val('Update');
					//load_transport_data();
					Unloading();						
				}
				else if(response.trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
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
				$("#ModalEwayBill").modal("hide");
				Unloading();	
				
				//$('#add_transport_btn').val('Add');
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


function get_ledger_salesman(check,total_id)
{

	Loading(true);

	if(check=='yes')
	{
		
		var salesman_voucher_type = $('#salesman_voucher_type').val();
		var salesman_voucher_id = $('#eid').val();
		var bill_amount = $('#'+total_id).val();
		//alert(total_id);
		$.ajax({
			
			type:'POST',
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "sales_form_show",salesman_voucher_type:salesman_voucher_type,salesman_voucher_id:salesman_voucher_id,bill_amount:bill_amount},
			success:function(result)
			{
				//console.log(result);
				
				var obj = JSON.parse(result);
				
				$("#ModalSalesman").modal("show");
				$("#salesman_link").show();
				//alert(obj.salesman_id);
				$('#sales_bill_amt').val(bill_amount);
				$('#salesman_id').select2("val",obj.salesman_id);
				get_salesman_detail(obj.salesman_id);
				$('#sales_tot_qty').val(obj.sales_tot_qty);

				$('#sales_comm_type').val(obj.sales_comm_type);
				$('#sales_comm_percentage').val(obj.sales_commision_per);
				$('#sales_comm_bag').val(obj.sales_comm_bag);
				$('#sales_comm_amount').val(obj.sales_commision);
				$('#salesman_popup_id').val(obj.salesman_trans_id);
				
				Unloading();
			}
		})
	}
	else
	{
		$("#salesman_link").hide();
		Unloading();
	}
	Unloading();
}

function set_salesman_percentage()
{
	var sales_bill_amt = Number($('#sales_bill_amt').val());
	var percentage = Number($('#sales_comm_percentage').val());
	
	var total = (sales_bill_amt*Number(percentage))/100;
	
	$('#sales_comm_amount').val(total);
	
}

function set_salesman_bag()
{
	var sales_tot_qty = Number($('#sales_tot_qty').val());
	var bags = Number($('#sales_comm_bag').val());
	
	var total = (sales_tot_qty*Number(bags));
	
	$('#sales_comm_amount').val(total);
}

function add_salesman_transaction()
{
	var salesman_voucher_type = $('#salesman_voucher_type').val();
	var salesman_voucher_table = $('#salesman_voucher_table').val();
	var salesman_voucher_id = $('#salesman_voucher_id').val();
	var eid = $('#eid').val();
	var salesman_id = $('#salesman_id').val();
	var sales_bill_amt = $('#sales_bill_amt').val();
	var sales_comm_type = $('#sales_comm_type').val();
	var sales_comm_percentage = $('#sales_comm_percentage').val();
	var sales_comm_bag = $('#sales_comm_bag').val();
	var sales_comm_amount = $('#sales_comm_amount').val();
	var salesman_popup_id = $('#salesman_popup_id').val();
	var sales_tot_qty = $('#sales_tot_qty').val();
	
	$.ajax({
		
		type:'post',
		url:root_domain+finance_root_domain+'app/common_form_finance/',
		data:{mode:'add_salesman_transaction',salesman_voucher_type:salesman_voucher_type,salesman_voucher_table:salesman_voucher_table,
		salesman_voucher_id:salesman_voucher_id,eid:eid,salesman_id:salesman_id,sales_bill_amt:sales_bill_amt,sales_comm_type:sales_comm_type,
		sales_comm_percentage:sales_comm_percentage,sales_comm_amount:sales_comm_amount,salesman_popup_id:salesman_popup_id,sales_comm_bag:sales_comm_bag,
		sales_tot_qty:sales_tot_qty},
		success:function(response)
		{
			//alert(response);
			
			if(response == '1') {
				toastr.success("SALESMAN DETAILS INSERTED SUCCESSFULLY", "SUCCESS");
				$("#ModalSalesman").modal("hide");
				Unloading();						
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
		}
	})
	
	
	
}


function get_salesman_detail(salesman_id)
{
	
	var sales_bill_amt = $('#sales_bill_amt').val();
	var tot_qty=0;
	$('input[name^="trn_pro_stk"]').each(function() {
		//alert($(this).val());
		tot_qty+=($(this).val());
	});
	
	//alert(tot_qty);
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/common_form_finance/',
		data:{mode:'get_salesman_detail',salesman_id:salesman_id},
		success:function(result)
		{
			//console.log(result);
			var obj = JSON.parse(result);
			$('#sales_tot_qty').val(tot_qty);
			//var commision_mode = obj.salesman_commision_mode;
			
			if(obj.salesman_commision_mode==0)
			{
				$('#comm_per_div').show();
				$('#comm_bag_div').hide();
				
				var commision_mode = "Percentage";
				var sale_commision = (sales_bill_amt*Number(obj.salesman_commision_percentage))/100;
				var total = sale_commision;
			}
			else if(obj.salesman_commision_mode==1)
			{
				var commision_mode = "Lumpsum Amount";
				var total = Number(obj.salesman_commision_percentage);
				
				$('#comm_per_div').hide();
				$('#comm_bag_div').hide();
			}
			else
			{
				var commision_mode = "Per Qty";
				var total = Number(obj.salesman_commision_percentage)*Number(tot_qty);
				$('#sales_comm_bag').val(obj.salesman_commision_percentage);
				
				$('#comm_per_div').hide();
				$('#comm_bag_div').show();
			}
			
			$('#sales_comm_type').val(commision_mode);
			$('#sales_comm_percentage').val(obj.salesman_commision_percentage);
			$('#sales_comm_amount').val(total);
			
		}
	})
	
}


//Bill Adjustment Form


function get_bill_show(check,bill_type,entry_amount,vender_id,ledger_id="",ledger_name="")
{
	//alert(bill_type);
	Loading(true);
	
	if(check=='yes')
	{
		if(ledger_id=="")
		{
			var cus_id = $("#"+vender_id).val();	
		}
		else
		{
			var cus_id = ledger_id;
		}
		

		if(ledger_name=="")
		{
			var cus_name = $("#"+vender_id+" option:selected").text();	
		}
		else
		{
			var cus_name = ledger_name;
		}
		
		$.ajax({
			
			type:'POST',
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "bill_form_show",cus_id:cus_id,cus_name:cus_name,bill_type:bill_type },
			success:function(result)
			{
				//alert(result);
				//alert(bill_type);
				console.log(result);
				var obj = JSON.parse(result);
				$("#ModalBillAdjustment").modal("show");
				if(bill_type=='purchase')
				{
					$('#bill_type_hid').val(2);	
				}
				else
				{
					$('#bill_type_hid').val(0);		
				}
				if(bill_type=='jv')
				{
					$('#jv_hid').val(1);
				}
				$(".cust_name").text('('+obj.cus_name+')');
				$("#billby_bill_link").show();
				$("#bill_ref").html("");
				$("#bill_ref").append(obj.due_invoice_list);
				$("#bill_ref").val("0");

				//alert(obj.cus_id);
				load_bill_by_bill_data(obj.cus_id);
				$('#paid_amt').val($('#'+entry_amount).val());
				$('#bill_ref_manual').val($('#receipt_no_reference').val());
				$('#bill_method_id').focus();
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				$('#cust_ledger_id').val(obj.cus_id);
				Unloading();
			}
		})
	}
	else
	{
		Unloading();
	}
	
}

function get_due_amount(id){
	Loading(true);
	//var due_amount = $("#bill_ref").find(":selected").data("dueamount");
	var ref_id = id;
	var bill_type = $("#bill_ref").find(":selected").data("type");
	//$("#bill_amt").val(due_amount);

	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "get_billby_bill_due","ref_id":id,"bill_type":bill_type},
		success:function(result)
		{
			//alert(result);
			//var obj = JSON.parse(result);
			$("#bill_amt").val(result);
			Unloading();
		}
	})

}




function add_bill_show()
{
	Loading(true);
	
	var bill_method = $('#bill_method').val();
	var bill_ref = $('#bill_ref').val();
	var bill_ref_manual = $('#bill_ref_manual').val();
	var bill_type = $("#bill_ref").find(":selected").data("type");
	if($('#jv_hid').val()=='1')
	{
		
		var bill_type_original = $('#new_ref_type').val();	
	}
	else
	{
		var bill_type_original = $('#bill_type_hid').val();		
	}
	var due_amt = parseFloat($('#bill_amt').val());
	var bill_amt = parseFloat($('#paid_amt').val());
	var bill_due_date = $('#bill_due_date').val();
	var bill_entry_type = $('#bill_entry_type').val();
	var bill_adjust_voucher_type = $('#bill_adjust_voucher_type').val();
	var bill_adjust_ledger_id = $('#bill_adjust_ledger_id').val();
	if($('#jv_hid').val()=='1')
	{
		var bill_adjust_table = 'tbl_journal';
	}
	else
	{
		var bill_adjust_table = $('#bill_adjust_table').val();
	}
	var bill_adjust_table_id = $("#receiptid").val();
	var due_date = $('#bill_due_date').val();
	var edit_id = $('#edit_id_bill').val();
	var cust_ledger_id = $('#cust_ledger_id').val();
	//alert(bill_entry_type);
	if($('#paid_amt').val()=='')
	{
		$('#billpaid_error_id').html('This Field Is Required');
		$('#billmethod_error_id').html('');
		$('#billentry_error_id').html('');
		Unloading();
	}
	else if(bill_amt > due_amt){
		toastr.warning("Paid Amount Can not be greater than due amount", "WARNING");
		$('#paid_amt').val('0');
		Unloading();
		return false;
	}else if(due_date == ''){
		$('#bill_due_date_error').html('This Field is Required');
		Unloading();
	}
	else
	{
		
		$('#billmethod_error_id').html('');
		$('#billentry_error_id').html('');
		$('#billamt_error_id').html('');
		
		$.ajax({
			
			type:'POST',
			
			url:root_domain+finance_root_domain+'app/common_form_finance/',
			
			data:{mode:"bill_form_add","bill_ref":bill_ref,"bill_type":bill_type,"bill_due_date":bill_due_date,"bill_amt":bill_amt,"bill_entry_type":bill_entry_type,"bill_adjust_voucher_type":bill_adjust_voucher_type,"bill_adjust_ledger_id":bill_adjust_ledger_id,"bill_adjust_table":bill_adjust_table,"bill_adjust_table_id":bill_adjust_table_id,"edit_id":edit_id,"cust_ledger_id":cust_ledger_id,bill_method:bill_method,bill_ref_manual:bill_ref_manual,bill_type_original:bill_type_original },
			
			success:function(response)
			{
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					load_bill_by_bill_data(cust_ledger_id);
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
					
				//$('#bill_method_id').val('');
				$('#bill_ref').val('');
				$('#bill_amt').val('');
				$('#paid_amt').val('');
				$("#bill_ref").val("0");
				$('#bill_entry_type').val('');
				$('#bill_due_date').val('');
				$('#edit_id_bill').val('');
				
				Unloading();	
				
				$('#add_bill_adjustment_btn').val('Add');
				
				
			}
		})
	}
}


function load_bill_by_bill_data(cust_id=''){

	//alert(cust_id);
	var bill_adjust_voucher_type = $('#bill_adjust_voucher_type').val();
	var receiptid = $("#receiptid").val();

	datatable = $("#billbybill-table").dataTable({
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
			"sAjaxSource": root_domain+finance_root_domain+'app/common_form_finance/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch_bill_by_bill_details" },
					{ "name": "bill_adjust_voucher_type", "value": bill_adjust_voucher_type },
					{"name":"ledger_id","value":cust_id},{"name":"receiptid","value":receiptid}
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


function delete_bill_by_bill(bill_id,bill_cust_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "delete_bill_by_bill", bill_id : bill_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS"); 
					if($("#edit_id_bill").val() != ''){
						$("#edit_id_bill").val('');
					}
					load_bill_by_bill_data(bill_cust_id);
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
					Unloading();
				}							
			}
		});	
	}
	
}

function edit_bill_by_bill(bill_id)
{
	Loading(true);
	//alert("edit");
	editReq = $.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "preedit_billedit", bill_id : bill_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$('#bill_method').val(obj.bill_method);
			get_bill_by_method(obj.bill_method);
			$("#edit_id_bill").val(obj.bill_transaction_id);
			$("#bill_method_id").val(obj.bill_method);
			$("#bill_ref").val(obj.bill_ref);
			var due_amount = $("#bill_ref").find(":selected").data("dueamount");
			$("#bill_amt").val(due_amount);
			$("#paid_amt").val(obj.bill_amount);
			$("#bill_due_date").val(obj.bill_due_date);
			$("#bill_entry_type").val(obj.bill_entry_type);
			
			$("#bill_voucher_type").val(obj.bill_voucher_type);
			$("#bill_table").val(obj.bill_table);
			$("#bill_table_id").val(obj.bill_table_id);
			
			$('#add_bill_adjustment_btn').val('Update');
			
			Unloading();
		}
	});	
}

function show_eway_field(id) {
	if(id==1){
		$(".eway_other_field").show();
	}else{
		$(".eway_other_field").hide();
	}
}

function get_eway_bill(check,popuptype,so_voucher_type="")
{
	Loading(true);
	
	//alert(voucher_type);
	//alert(popuptype);
	if(so_voucher_type!="")
	{
		var transport_voucher = so_voucher_type;
	}
	else
	{
		var transport_voucher = $('#transport_voucher').val();
	}

	var company_trans = $('#company_trans').val();
	// alert(transport_voucher);
	
	//alert(company_trans);
	if(check=='yes')
	{
		$('#eway_bill_link').show();

		if(popuptype == 'transport'){
			$(".eway_bill_class").hide();
			$('#transport_link').show();
			$('#eway_bill_link').hide();
			$('#eway_bill_no').prop('readonly',false);
			$('#eway_bill_date').prop('readonly',false);
		}else{
			$('#tran_div').hide();
			$(".eway_bill_class").show();
			$('#eway_bill_no').prop('readonly',true);
			$('#eway_bill_date').prop('readonly',true);
		}

		$("#ModalEwayBill").modal("show");
		load_eway_bill_data(transport_voucher,so_voucher_type);
		$('#transport_id').focus();
		
		$(".eway_other_field").hide();
		
		$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
	}
	else
	{
		//$('#cost_center_form_show').html('');
		$('#eway_bill_link').hide();
		$('#eway_bill_no').prop('readonly',false);
		$('#eway_bill_date').prop('readonly',false);
		if(company_trans == 1){
			$('#tran_div').show();
			$('#transport_link').hide();
		}

		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
	}
	Unloading();
}

function load_eway_bill_data(voucher_type,so_voucher_type="")
{
	//alert(voucher_type);
	Loading(true);
	//alert(voucher_type);
	var edit_id_transport = $('#edit_id_transport').val();
	//alert(edit_id_transport);
	// alert(voucher_type);
	$.ajax({
		
		type:'POST',
		data:'voucher_type='+voucher_type+'&mode=load_eway_bill_data'+'&voucher_id='+edit_id_transport,
		url:root_domain+finance_root_domain+'app/common_form_finance/',
		success:function(result)
		{
			//console.log(result);
			//alert(result);
			var obj = JSON.parse(result);
			//alert(obj);
			if(obj != null){
				$('#transport_id').val(obj.transport_id);
				$('#transport_gr_no').val(obj.transport_gr_no);
				$('#transport_gr_date').val(obj.transport_gr_date);
				$('#transport_mode').val(obj.transport_mode);
				$('#distance_km').val(obj.distance_km);
				$('#transport_vehicle_no').val(obj.transport_vehicle_no);
				$('#transport_station').val(obj.transport_station);
				$('#transport_pincode').val(obj.transport_pincode);
				$('#transport_doc_no').val(obj.transport_doc_no);
				$('#transport_doc_date').val(obj.transport_doc_date);
				$('#iseway_bill').val(obj.transport_eway_bill_status);
				$('#iseinvoice_bill').val(obj.trasport_einvoice_status);
				$('#eway_sub_type').val(obj.eway_sub_type);
				$('#eway_transaction_type').val(obj.eway_transaction_type);
				$('#edit_eway_id').val(obj.transport_transaction_id);

				if(so_voucher_type=="")
				{
					$('#add_transport_btn').val('UPDATE');
				}
				else
				{
					$('#add_transport_btn').val('ADD');	
					$('#edit_eway_id').val('');
				}

			}
			else
			{
				$('#add_transport_btn').val('ADD');
			}
			
		}
		
	})
	Unloading();
}

function is_advance_payment(venderid,amount,entrytype){	
	
	//alert(amount);
	var isadvance = get_totaldue();
	var vender_id = $("#"+venderid).val();
	var ledger_tds = led_tds_permis(vender_id);
	var company_tds_per = $("#company_tds_per").val();
	var amount = $('#'+amount).val();
	var entry_type = $("#entry_type").val();
	var gst_nature = $("#gst_nature").val();
	
	if(ledger_tds ==1 && company_tds_per ==1  && (gst_nature==96 || gst_nature==69) ){
		if(amount > isadvance){
			var r= confirm(" Do you want to apply TDS on this ?");
			if(r){
				var cus_id = $("#vender_id").val();
				$.ajax({
				
					type:'POST',
					url: root_domain+finance_root_domain+'app/common_form_finance/',
					data: { mode : "advance_payment_tds",cus_id:vender_id},
					success:function(result)
					{
						//alert($('#entry_amount').val());
						//var obj = JSON.parse(result);

						$("#ModalAdvancePymentTds").modal("show");

						// $("#billby_bill_link").show();
						$("#tds_cat").html("");
						$("#tds_cat").append(result);
						$("#entrytype").val(entrytype);
						$("#tds_cat").val("0");
						$('#paid_amount_tds').val(amount);
						$('#paid_amount_cust').val(vender_id);

						// load_bill_by_bill_data();
						// $('#bill_method_id').focus();
						// $('.default-date-picker').datepicker({
						// 	format: 'dd-mm-yyyy',
						// 	autoclose: true
						// });
						//Unloading();
					}
				})
			}
		}
	}
}

function get_totaldue(){
	return 5000;
}
function led_tds_permis(id){
	var ledger_tds="";
	$.ajax({
		async: false,
		type:'POST',
		data:{ mode : "led_tds_permis",id:id},
		url:root_domain+finance_root_domain+'app/common_form_finance/',
		success:function(result)
		{
			var obj = JSON.parse(result);
			ledger_tds = obj.enable_tds;
		}
	});
	return (ledger_tds);
}
function get_details(id,paid_amount="",vender_id=""){

	var due_amt = 0; //As discussed with dhaval bhai - will develop in future by dhruv 21-12-2021
	var entry_type = $("#entrytype").val();
	if(vender_id=="")
	{
		if(entry_type==1){
			var cus_id = $("#vender_id").val();
		}else if(entry_type==2){
			var cus_id = $("#ledger_id").val();
		}
		
	}
	else{
		var cus_id = vender_id
	}

	if(paid_amount=='')
	{
		if(entry_type==1){
			var paid_amount = $("#paid_amount").val();	
		}else if(entry_type==2){
			var paid_amount = $("#amount").val();	
		}
		
	}
	else{
		var paid_amount = paid_amount;
	}

	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "get_tds_details",cus_id:cus_id,"tds_cat_id":id,"paid_amount":paid_amount,"due_amt":due_amt},
		success:function(result)
		{
			//alert(result);
			$(".it_act").html('');
			//$(".it_act").remove();
			$('.it_act tr').each(function(){
				$(this).remove();
			});	
			//$('').insertAfter("table .it_act_trns");
			$(result).insertAfter("table .it_act_trns");
			
		}
	})

}

//Added by Dhruv
function get_adv_receipt_popup(id,vender_id){

	if(id == 99){

		if($("#paid_amount").val() == 0 || $("#paid_amount").val() == ''){
			toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
			$("#gst_nature").select2("val","");
			$("#paid_amount").focus();			
			return false;
		}

		var party_ledger_id = $("#"+vender_id).val();
		var receipt_voucher = $("#receipt_voucher").val();
		var receipt_adv_pay_table = $("#receipt_adv_pay_table").val();
		var recieptid = $("#receiptid").val();

		//alert(recieptid);

		$.ajax({
			
			type:'POST',
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "get_adv_receipt_details","party_ledger_id":party_ledger_id,"receipt_voucher":receipt_voucher,"receipt_adv_pay_table":receipt_adv_pay_table,"recieptid":recieptid},
			success:function(result)
			{
				//alert(result);
				//console.log(result);
				var obj = JSON.parse(result);
				$("#ModalAdvancePayment").modal("show");

				$(".party_name").text(obj.cust_name);
				$(".party_region").text(obj.region);
				$(".party_state").text(obj.state_name);
				$(".party_state_code").text(obj.state_code);

				$(".gst").html(obj.gst);

				$("#isinterstate").val(obj.isinterstate);
				$("#trn_ref").val($("#receipt_no").val());
				$(".adv_pay").text($("#paid_amount").val());
				$("#trn_amount").val($("#paid_amount").val());

				$("#trn_gst").val('');
				$("#taxable_amt").val('');
				if(obj.trn_gst != ''){
					$('#add_adv_payment_btn').val('Update');
					$('#trn_gst').val(obj.trn_gst);
					$('#adv_payment_id').val(obj.transaction_id);
					calculate_tax(obj.trn_gst);
				}
				else
				{
					$('#add_adv_payment_btn').val('ADD');
				}

			}
		})
	}
}

//added by dhruv
function get_adv_payment_ref(id,vender_id){
	if(id == 72){
		// if($("#adv_refund_payment_id").val() == '' ){
		// 	$("#ref_no").select2("val","0");
		// 	$(".ref_details").html('');
		// }else{
		// 	$('#add_adv_refund_payment_btn').val('Update');
		// }
		var party_ledger_id = $("#"+vender_id).val();
		var adv_refund_payment_id = $("#adv_refund_payment_id").val();
		var receiptid = $("#receiptid").val();

		$.ajax({
			
			type:'POST',
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "get_adv_payment_ref","party_ledger_id":party_ledger_id,
			"adv_refund_payment_id":adv_refund_payment_id,"receiptid":receiptid},
			success:function(result)
			{
				var obj = JSON.parse(result);
				$("#ModalAdvanceRefundPayment").modal("show");
				$(".party_name").text(obj.cust_name);
				$(".party_region").text(obj.region);
				$(".party_state").text(obj.state_name);
				$(".party_state_code").text(obj.state_code);	
				$("#isinterstate").val(obj.isinterstate);			
				$("#ref_no").html(obj.str_ref);
				if(obj.transaction_id != null){
					$('#add_adv_refund_payment_btn').val('Update');
					$("#adv_refund_payment_id").val(obj.transaction_id);
					//$('#ref_no').trigger('change');
					$('#ref_no').trigger('change');
					get_adv_refund_payment_details(obj.trn_ref,obj.trn_amount);
					//alert(obj.trn_amount); return false
					
				}

			}
		})

	}
}



function get_adv_refund_payment_details(id,trn_amount){

	var party_ledger_id = $("#vender_id").val();
	var payment_voucher = $("#payment_voucher").val();
	var receipt_adv_pay_table = $("#receipt_adv_pay_table").val();

	$.ajax({
		
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "get_adv_refund_payment_details","party_ledger_id":party_ledger_id,"payment_voucher":payment_voucher,"transaction_id":id,
		"receipt_adv_pay_table":receipt_adv_pay_table},
		success:function(result)
		{
			var obj = JSON.parse(result);
			$(".ref_details").html(obj.ref_details);
			if(trn_amount != ''){
				//alert(trn_amount);
				$("#trn_refund_amount").val(trn_amount);
				calculate_refund_tax(trn_amount);
			}			
		}
	})
	
}

function calculate_refund_tax(refund_amt){

	var tax = $("#trn_gst").val();
	var taxable_v = (Number(refund_amt) - ((refund_amt) * tax)/(100 + Number(tax)));

	$("#taxable_amt").val(taxable_v.toFixed(2));

	var isinterstate = $("#isinterstate").val();
	var gst = (Number(refund_amt) * tax)/(100 + Number(tax));
	
	if(isinterstate == 0){
		$("#cgst_rate").val((gst/2).toFixed(2));
		$("#sgst_rate").val((gst/2).toFixed(2) );
	}else if(isinterstate == 1){
		$("#igst_rate").val(gst.toFixed(2));
	}

}

function add_refund_adv_payment(){

	// alert($("#trn_refund_amount").val());

	// alert($("#trn_rem_amount").val()); return false

	if($("#trn_refund_amount").val() == 0 || $("#trn_refund_amount").val() == '' || !($.isNumeric($("#trn_refund_amount").val())) ){
		toastr.warning("PLEASE INSERT REFUND AMOUNT FIRST", "ERROR")
		$("#trn_refund_amount").focus();	
		$("#trn_refund_amount").val(0);		
		return false;
	}else if( Number($("#trn_refund_amount").val()) > Number($("#trn_rem_amount").val()) ){
		toastr.warning("REFUND AMOUNT CAN NOT BE GREATER THAN REMANING AMOUNT", "ERROR")
		$("#trn_refund_amount").val($("#trn_rem_amount").val());
		calculate_refund_tax($("#trn_rem_amount").val());			
		return false;
	}else{

		Loading(true);
		
		var trn_ref =  $("#ref_no").val();
		var trn_refund_amount = $('#trn_refund_amount').val();
		var trn_gst = $('#trn_gst').val();
		var taxable_amt = $('#taxable_amt').val();
		var receipt_voucher = $("#payment_voucher").val();
		var receipt_adv_pay_table = $("#payment_refund_adv_pay_table").val();
		var ref_no = $("#ref_no").val();
		var cust_id = $("#vender_id").val();
		var adv_refund_payment_id = $('#adv_refund_payment_id').val();
			
		$.ajax({			
			type:'POST',			
			url:root_domain+finance_root_domain+'app/common_form_finance/',			
			data:{mode:"add_refund_adv_payment","trn_gst":trn_gst,"taxable_amt":taxable_amt,"trn_refund_amount":trn_refund_amount,"adv_refund_payment_id":adv_refund_payment_id,
			"receipt_voucher":receipt_voucher,"receipt_adv_pay_table":receipt_adv_pay_table,"trn_ref":trn_ref,"cust_id":cust_id },
			
			success:function(result)
			{
				var response = result.split("-");
				if(response[0].trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$("#adv_refund_payment_id").val(response[1]);
					$('#ModalAdvanceRefundPayment').modal('toggle');
					//$("#add_cost_center_btn").val('Update');
					//load_transport_data();
					Unloading();						
				}
				else if(response[0].trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
					$('#ModalAdvanceRefundPayment').modal('toggle');
					Unloading();
				}
				else if(response[0].trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response[0].trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				//$("#ModalEwayBill").modal("hide");
				Unloading();	
				
			}
		})
	}
	
}

function calculate_tax(tax){
	
	trn_gst = $("#trn_gst").val();
	if(trn_gst>100){
		toastr.warning("PLEASE ENTER VALID GST RATE", "ERROR")
		$("#trn_gst").focus();	
		$("#trn_gst").val("");		
		return false;
	}
	
	var taxable_v = Number($("#paid_amount").val()) - ((Number($("#paid_amount").val()) * tax)/(100 + Number(tax)));
	$("#taxable_amt").val(taxable_v.toFixed(2));
	var isinterstate = $("#isinterstate").val();
	var gst = (Number($("#paid_amount").val()) * tax)/(100 + Number(tax));
	
	if(isinterstate == 0){
		$("#cgst_rate").val((gst/2).toFixed(2));
		$("#sgst_rate").val((gst/2).toFixed(2) );
	}else if(isinterstate == 1){
		$("#igst_rate").val(gst.toFixed(2));
	}

}

function add_adv_payment(){

	if($("#trn_gst").val() == 0 || $("#trn_gst").val() == ''){
		toastr.warning("PLEASE INSERT TAX VALUE FIRST", "ERROR")
		$("#trn_gst").focus();			
		return false;
	}else{

		Loading(true);
		
		var trn_gst = $('#trn_gst').val();
		var taxable_amt = $('#taxable_amt').val();
		var paid_amount = $('#paid_amount').val();
		var receipt_voucher = $("#receipt_voucher").val();
		var receipt_adv_pay_table = $("#receipt_adv_pay_table").val();
		var trn_ref = $("#trn_ref").val();
		var cust_id = $("#receiver_ledger").val();
		var adv_payment_edit_id = $('#adv_payment_id').val();
		var cgst_rate = $('#cgst_rate').val();
		var sgst_rate = $('#sgst_rate').val();
		var igst_rate = $('#igst_rate').val();
			
		$.ajax({			
			type:'POST',			
			url:root_domain+finance_root_domain+'app/common_form_finance/',			
			data:{mode:"add_adv_payment","trn_gst":trn_gst,"taxable_amt":taxable_amt,"paid_amount":paid_amount,
			"adv_payment_edit_id":adv_payment_edit_id,"receipt_voucher":receipt_voucher,"receipt_adv_pay_table":receipt_adv_pay_table,
			"trn_ref":trn_ref,"cust_id":cust_id,"cgst_rate":cgst_rate,"sgst_rate":sgst_rate,"igst_rate":igst_rate },
			
			success:function(response)
			{
				//alert(response);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					//$("#add_cost_center_btn").val('Update');
					//load_transport_data();
					Unloading();						
				}
				else if(response.trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
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
				$("#ModalAdvancePayment").modal("hide");
				Unloading();	
				
			}
		})
	}
}


//Start Code - Registered Expence related By Dhruv
function get_registered_expence_popup(id,ledgerid,amt){	
	if(id == 70 || id==94){
		var gst_nature_cat = $("#gst_nature").find('option:selected').attr('data-catid');
		var party_form = $("#vender_id").find('option:selected').attr('data-formgroup');
		
		// if($("#registered_expense_id").val() == '' ){
		// 	$("#gst_report_basis").select2("val","0");
		// 	show_hide_div(0);
		// 	$(".other_details").html('');
		// }else{
		// 	$('#add_registered_expense_btn').val('Update');
		// }

		if($("#"+ledgerid).val() == 0 ){
			toastr.warning("PLEASE SELECT PARTY LEDGER FIRST", "ERROR")
			$("#gst_nature").select2("val","");
			$("#"+ledgerid).focus();			
			return false;
		}else if($("#"+amt).val() == 0 || $("#"+amt).val() == ''){
			toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
			$("#gst_nature").select2("val","");
			$("#"+amt).focus();			
			return false;
		}
		// else if(gst_nature_cat == 29 && party_form!='expense_form'){
		// 	toastr.warning("PLEASE SELECT EXPENCE PARTY LEDGER FIRST", "ERROR")
		// 	$("#gst_nature").select2("val","");
		// 	$("#vender_id").select2("focus");
		// }
		else{
			Loading(true);
			var party_ledger_id = $("#"+ledgerid).val();
			var paid_amount = $("#"+amt).val();
			var receiptid = $("#receiptid").val();
			var payment_voucher_table = $("#payment_voucher_table").val();

			$.ajax({
					
				type:'POST',
				url: root_domain+finance_root_domain+'app/common_form_finance/',
				data: { mode : "get_register_expence_details","party_ledger_id":party_ledger_id,
				"paid_amount":paid_amount,"receiptid":receiptid,"payment_voucher_table":payment_voucher_table},
				success:function(result)
				{
					var obj = JSON.parse(result);
					//alert(obj.regd_expense_id);

					$("#ModalRegisteredExpence").modal("show");
					$(".other_details").html(obj.expence_other_details);
					$(".party_det").html(obj.party_det);
					$(".party_name").text(obj.party_name);
					//$(".adj_amount").text(paid_amount);
					$(".regd_select2").select2({
						width: '100%'
					});
					$('.date-picker').datepicker({
						defaultDate: new Date(),
						format: 'dd-mm-yyyy',
						autoclose: true,
					});

					if(obj.regd_expense_id != null && obj.regd_expense_id != ''){
						$('#add_registered_expense_btn').val('Update');
						$('#registered_expense_id').val(obj.regd_expense_id);
						show_hide_div(1);
						$('#checkRegExpLink').show();
					}else{
						$('#add_registered_expense_btn').val('Add');
						show_hide_div(1);
					}

					Unloading();
					
				}
			})
		}
	}

}

function show_hide_div(id){
	var party_id = $("#regd_party_id").val();
	var state_id = $("#regd_state").val();
	if(id==1){
		$(".party_ledger").show();
		$(".manual_party_details").hide();
		//party_wise_tax(party_id);
	}else if(id==2){
		$(".party_ledger").hide();
		$(".manual_party_details").show();
		party_state_wise(state_id);
	}else{
		$(".party_ledger").hide();
		$(".manual_party_details").hide();
	}
}

function party_wise_tax(party_id,id){
	var vender_id = id;
	var product_amount	= $("#regd_taxable_amount").val();
	$.ajax({
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "party_wise_tax",party_id:party_id,vender_id:vender_id,product_amount:product_amount},
		success:function(result)
		{
			var obj = JSON.parse(result);
			if(obj.tax_type == 1){
				$("#regd_cgst").val(obj.cgst_tax_rate.toFixed(2));
				$("#regd_sgst").val(obj.sgst_tax_rate.toFixed(2));
				$(".cgst").show();
				$(".igst").hide();
			}else{
				$("#regd_igst").val(obj.igst_tax_rate.toFixed(2));
				$(".igst").show();
				$(".cgst").hide();
			}
			$("#regd_gst").val(obj.total_gst.toFixed(2));
		}
	});
}

function party_state_wise(state_id){
	var vender_id = $("#vender_id").val();
	var product_amount	= $("#regd_taxable_amount").val();
	$.ajax({
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "party_state_wise",state_id:state_id,product_amount:product_amount,vender_id:vender_id},
		success:function(result)
		{
			var obj = JSON.parse(result);
			if(obj.tax_type == 1){
				$("#regd_cgst").val(obj.cgst_tax_rate.toFixed(2));
				$("#regd_sgst").val(obj.sgst_tax_rate);
				$(".cgst").show();
				$(".igst").hide();
			}else{
				$("#regd_igst").val(obj.igst_tax_rate.toFixed(2));
				$(".igst").show();
				$(".cgst").hide();
			}
			
			$("#regd_gst").val(obj.total_gst.toFixed(2));
		}
	});
}

function add_registered_expense(){

	var gstin = $("#regd_gstin").val();

	if($("#gst_report_basis").val() == 0){
		toastr.warning("PLEASE SELECTE GST BASIS FIRST", "ERROR")
		$("#gst_report_basis").focus();			
		return false;
	}else if($("#gst_report_basis").val() == 1 && $("#regd_party_id").val() == 0){
		//if($("#regd_party_name").val() == 0){
			toastr.warning("PLEASE SELECTE PARTY NAME", "ERROR")
			$("#regd_party_id").focus();			
			return false;
		//}
	}else if($("#gst_report_basis").val() == 2 && $("#regd_party_name").val() == 0){
		//if($("#regd_party_name").val() == 0){
			toastr.warning("PLEASE INSERT PARTY NAME", "ERROR")
			$("#regd_party_name").focus();			
			return false;
	}else if($("#gst_report_basis").val() == 2 && $("#regd_state").val() == 0){
		toastr.warning("PLEASE SELECT PARTY STATE", "ERROR")
		$("#regd_state").focus();			
		return false;
	}else if($("#gst_report_basis").val() == 2 && $("#regd_type_of_dealer").val() == 0){
		toastr.warning("PLEASE SELECT TYPE OF DEALER", "ERROR")
		$("#regd_type_of_dealer").focus();			
		return false;
	}else if($("#gst_report_basis").val() == 2 && $("#regd_gstin").val() == 0){
		toastr.warning("PLEASE INSERT PARTY GSTIN NUMBER", "ERROR")
		$("#regd_gstin").focus();			
		return false;
	}else if(($("#gst_report_basis").val() == 2) && (gstin.length != 15)){
		toastr.warning("PLEASE INSERT PARTY GSTIN NUMBER WITh LENGHT OF 15 DIGIT", "ERROR")
		$("#regd_gstin").focus();			
		return false;
	}else if($("#regd_purchase_inv_no").val() == ''){
			toastr.warning("PLEASE INSERT PURCHASE INVOICE NUMBER", "ERROR")
			$("#regd_purchase_inv_no").focus();			
			return false;
	}else if($("#regd_purchase_bill_date").val() == ''){
			toastr.warning("PLEASE SELECT BILL DATE", "ERROR")
			$("#regd_purchase_bill_date").focus();			
			return false;		
	}else{

		Loading(true);
		
		var gst_report_basis = $('#gst_report_basis').val();
		var regd_party_id = $('#regd_party_id').val();
		var regd_party_name = $('#regd_party_name').val();
		var regd_state = $("#regd_state").val();
		var regd_type_of_dealer = $("#regd_type_of_dealer").val();
		var regd_gstin = $("#regd_gstin").val();
		var regd_account = $("#vender_id").val();
		var regd_purchase_inv_no = $('#regd_purchase_inv_no').val();
		var regd_purchase_bill_date = $('#regd_purchase_bill_date').val();
		var regd_hsn = $('#regd_hsn').val();
		var regd_unit = $('#regd_unit').val();
		var regd_taxable_amount = $("#regd_taxable_amount").val();
		var regd_gst = $("#regd_gst").val();
		var regd_cgst = $("#regd_cgst").val();
		var regd_sgst = $("#regd_sgst").val();
		var regd_igst = $("#regd_igst").val();
		var regd_itc = $("#regd_itc").val();
		var registered_expense_id = $("#registered_expense_id").val();

		var voucher_type = $("#payment_voucher").val();
		var voucher_table = $("#payment_voucher_table").val();
		var receiptid = $("#receiptid").val();
		var journal_id = $("#journal_id").val();
		$.ajax({			
			type:'POST',			
			url:root_domain+finance_root_domain+'app/common_form_finance/',			
			data:{mode:"add_registered_expense","gst_report_basis":gst_report_basis,"regd_party_id":regd_party_id,"regd_party_name":regd_party_name,
			"regd_state":regd_state,"regd_type_of_dealer":regd_type_of_dealer,"regd_gstin":regd_gstin,"regd_account":regd_account,
			"regd_purchase_inv_no":regd_purchase_inv_no,"regd_purchase_bill_date":regd_purchase_bill_date,"regd_hsn":regd_hsn,
			"regd_unit":regd_unit,"regd_taxable_amount":regd_taxable_amount,"regd_gst":regd_gst,"regd_cgst":regd_cgst,"regd_cgst":regd_cgst,
			"regd_sgst":regd_sgst,"regd_igst":regd_igst,"regd_itc":regd_itc,"registered_expense_id":registered_expense_id,"voucher_type":voucher_type,
			"voucher_table":voucher_table,receiptid:receiptid,journal_id:journal_id},
			
			success:function(result)
			{
				var response = result.split("-");
				//alert(response);
				if(response[0].trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$("#registered_expense_id").val(response[1]);
					$('#ModalRegisteredExpence').modal('toggle');
					if(voucher_type == 84){
						show_data();
					}else if(voucher_type == 82){
						load_payment_entry_datatable();
					}
					Unloading();						
				}				
				else if(response[0].trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
					$('#ModalRegisteredExpence').modal('toggle');
					if(voucher_type == 84){
						show_data();
					}else if(voucher_type == 82){
						load_payment_entry_datatable();
					}
					Unloading();
				}
				else if(response[0].trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response[0].trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				Unloading();	
				
			}
		})
	}
}
//End Code - Registered Expence related By Dhruv


//Start Code - Payment to Gov popup By Dhruv
function get_payment_gov_popup(id){

	if(id == 73 || id==92){
	
		Loading(true);
		var receiptid = $("#receiptid").val();
		$.ajax({
						
			type:'POST',
			url: root_domain+finance_root_domain+'app/common_form_finance/',
			data: { mode : "get_payment_gov_details","receiptid":receiptid},
			success:function(result)
			{
				var obj = JSON.parse(result);
				$("#ModalPaymentToGov").modal("show");
				
				$(".gov_payment").html(obj.payment_gov_details);
				if(obj.payment_id != null && obj.payment_id != ''){
					$('#add_gov_payment_btn').val('Update');
					$('#gov_payment_id').val(obj.payment_id);
				}else{
					$('#add_gov_payment_btn').val('Add');
				}
				
				$(".gov_select2").select2({
					width: '100%'
				});
				$('.gov_date_picker').datepicker({
					defaultDate: new Date(),
					format: 'dd-mm-yyyy',
					autoclose: true,
				});

				Unloading();
				
			}
		})
	}
}

function add_payment_to_gov(){

	if($("#gst_payment_type").val() == 0){
		toastr.warning("PLEASE SELECTE GST PAYMENT TYPE", "ERROR")
		$("#gst_payment_type").focus();			
		return false;
	}else if($("#period_ending").val() == ''){
		toastr.warning("PLEASE SELECTE PERIOD ENDING DATE", "ERROR")
		$("#period_ending").focus();			
		return false;
	}else
	{

		var CGST = [];
		$(".CGST").each(function() {
			CGST.push($(this).val());
		});

		var SGST = [];
		$(".SGST").each(function() {
			SGST.push($(this).val());
		});

		var IGST = [];
		$(".IGST").each(function() {
			IGST.push($(this).val());
		});

		var gov_payment_id = $('#gov_payment_id').val();

		var voucher_type = $("#payment_voucher").val();
		var voucher_table = $("#payment_voucher_table").val();
		var receiptid = $("#receiptid").val();

		$.ajax({			
			type:'POST',			
			url:root_domain+finance_root_domain+'app/common_form_finance/',			
			data:{mode:"add_payment_to_gov","gst_payment_type":$("#gst_payment_type").val(),"period_ending":$("#period_ending").val()
			,"chalan_number":$("#chalan_number").val(),"chalan_date":$("#chalan_date").val(),"cheque_no":$("#cheque_no").val()
			,"cheque_date":$("#cheque_date").val(),"bank_name":$("#bank_name").val(),"bank_code":$("#bank_code").val(),"IGST":IGST,"CGST":CGST
			,"SGST":SGST,"gov_payment_id":gov_payment_id,"voucher_type":voucher_type,"voucher_table":voucher_table,"receiptid":receiptid },
			
			success:function(result)
			{
				var response = result.split("-");
				if(response[0].trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$('#ModalPaymentToGov').modal('toggle');
					Unloading();						
				}
				else if(response[0].trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
					$('#ModalPaymentToGov').modal('toggle');
					Unloading();
				}
				else if(response[0].trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response[0].trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				Unloading();	
				
			}
		})

	}
}
//End Code - Payment to Gov popup By Dhruv

function showHideLink(id){
	if(id == 70 || id == 94){
		$("#checkRegExpLink").show();
		$("#checkAdvPayLink").hide();
		$("#checkGovPayLink").hide();
	}else if(id == 72 || id==99){
		$("#checkAdvPayLink").show();
		$("#checkRegExpLink").hide();
		$("#checkGovPayLink").hide();
	}else if(id == 73 || id==92){
		$("#checkGovPayLink").show();
		$("#checkRegExpLink").hide();
		$("#checkAdvPayLink").hide();
	}else if(id == 79 || id==100 || id==101){
		$("#checkCreditLink").show();
		$("#checkDebitLink").hide();
		$("#checkGovPayLink").hide();
		$("#checkRegExpLink").hide();
		$("#checkAdvPayLink").hide();
	}else if(id == 80 || id==86 || id==87){
		$("#checkDebitLink").show();
		$("#checkCreditLink").hide();
		$("#checkGovPayLink").hide();
		$("#checkRegExpLink").hide();
		$("#checkAdvPayLink").hide();
	}else{
		$("#checkAdvPayLink").hide();
		$("#checkGovPayLink").hide();
		$("#checkRegExpLink").hide();
		$("#checkCreditLink").hide();
		$("#checkDebitLink").hide();
	}
}

/*Debit-Credit Note Start- Dhruv */	
function get_debit_credit_note_popup(id){
	
	if(id == 79 || id == 80 || id == 100 || id == 86 || id == 101 || id == 87){

		if(id == 79 || id == 100 || id == 101){
			var bill_type = 'invoice';
		}else if(id == 80 || id == 86 || id == 87){
			var bill_type = 'purchase';
		}
		//alert($("#ledger_hid_id").val());return false;

		if($("#ledger_hid_id").val() == 0 || $("#ledger_hid_id").val() == undefined ){
			toastr.warning("PLEASE SELECT PARTY LEDGER FIRST", "ERROR")
			$("#gst_nature").select2("val","");
			$("#ledger_id").focus();
			showHideLink($("#ledger_hid_id").val());			
			return false;
		}else{
			Loading(true);
			var party_ledger_id = $("#ledger_hid_id").val();
			var journal_id = $("#journal_id").val();
			var payment_voucher_table = $("#payment_voucher_table").val();
			var cr_dr_entry_type = $("#cr_dr_entry_type").val();

			$.ajax({
				
				type:'POST',
				url: root_domain+finance_root_domain+'app/common_form_finance/',
				data: { mode : "get_debit_credit_note_details","party_ledger_id":party_ledger_id,
				"bill_type":bill_type,"journal_id":journal_id,"payment_voucher_table":payment_voucher_table,"cr_dr_entry_type":cr_dr_entry_type},
				success:function(result)
				{
					//alert(result);
					var obj = JSON.parse(result);
					$("#ModalDebitCreditNote").modal("show");			
					$(".deb_cre_details").html(obj.deb_cre_details);
					$(".party_name").text(obj.party_name);
					$(".party_region").text(obj.region);
					$(".party_state").text(obj.state_name);
					$(".party_state_code").text(obj.state_code);
					$("#isinterstate").val(obj.isinterstate);
					if(obj.adjustment_id != '' && obj.adjustment_id != null){
						$("#deb_cre_adjustment_id").val(obj.adjustment_id);
						$("#add_cre_deb_note_btn").val('Update');
					}

					$(".cre_deb_select2").select2({
						width: '100%'
					});
					$('.cre_deb_date_picker').datepicker({
						defaultDate: new Date(),
						format: 'dd-mm-yyyy',
						autoclose: true,
					});
					Unloading();
				}
			})
		}
	}
}

function add_cre_deb_note(){

	if($("#adjust_reason").val() == ''){
		toastr.warning("PLEASE SELECT RETURN REASON", "ERROR")
		$("#adjust_reason").focus();			
		return false;
	}else if($("#adjust_invoice").val() == ''){
		toastr.warning("PLEASE SELECT INVOICE/PURCHASE NUMBER", "ERROR")
		$("#adjust_invoice").focus();			
		return false;
	}else if($("#adjsut_diff").val() == '' || $("#adjsut_diff").val() == 0){
		toastr.warning("PLEASE INSERT DIFFERENCE AMOUNT", "ERROR")
		$("#adjsut_diff").focus();			
		return false;
	}else if($("#adjust_gst").val() == '' || $("#adjust_gst").val() == 0){
		toastr.warning("PLEASE INSERT GST %", "ERROR")
		$("#adjust_gst").focus();			
		return false;
	}else if($("#adjust_hsn").val() == '' ){
		toastr.warning("PLEASE INSERT HSN", "ERROR")
		$("#adjust_hsn").focus();			
		return false;
	}else
	{
		var gst_nature = $("#gst_nature").val();
		if(gst_nature == 79 || gst_nature == 100 || gst_nature == 101){
			var bill_type = '1';
		}else if(gst_nature == 80 || gst_nature == 86 || gst_nature == 87){
			var bill_type = '2';
		}

		var voucher_type = $("#payment_voucher").val();
		var voucher_table = $("#payment_voucher_table").val();
		var entry_type_id = $("#entry_type_id").val();
		var deb_cre_adjustment_id = $("#deb_cre_adjustment_id").val();

		$.ajax({			
			type:'POST',			
			url:root_domain+finance_root_domain+'app/common_form_finance/',			
			data:{mode:"add_cre_deb_note","adjust_reason":$("#adjust_reason").val(),"adjust_invoice":$("#adjust_invoice").val()
			,"adjust_invoice_date":$("#adjust_invoice_date").val(),"adjust_hsn":$("#adjust_hsn").val(),"adjust_unit":$("#adjust_unit").val()
			,"adjsut_diff":$("#adjsut_diff").val(),"adjust_gst":$("#adjust_gst").val(),"adjust_cgst":$("#adjust_cgst").val(),"adjust_sgst":$("#adjust_sgst").val(),
			"adjust_igst":$("#adjust_igst").val(),"adjust_itc":$("#adjust_itc").val(),"adjust_nature_transaction":$("#adjust_nature_transaction").val(),
			"voucher_type":voucher_type,"voucher_table":voucher_table,"deb_cre_adjustment_id":deb_cre_adjustment_id,"bill_type":bill_type,"entry_type_id":entry_type_id,"adjust_date":$("#adjust_date").val() },
			
			success:function(result)
			{
				//alert(result);
				var response = result.split("-");
				if(response[0].trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$('#ModalDebitCreditNote').modal('toggle');
					Unloading();						
				}
				else if(response[0].trim() == '2') {
					toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
					$('#ModalDebitCreditNote').modal('toggle');
					Unloading();
				}
				else if(response[0].trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(response[0].trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				Unloading();	
				
			}
		})

	}
}

function calculate_cre_deb_tax(){

	var diff_amt = $("#adjsut_diff").val();
	var adjust_gst = $("#adjust_gst").val();
	
	if(adjust_gst>100){
		toastr.warning("PLEASE ENTER VALID TAX", "ERROR");
		$("#adjust_gst").val("");
	}
	// var taxable_v = Number($("#paid_amount").val()) - ((Number($("#paid_amount").val()) * tax)/(100 + Number(tax)));
	// $("#taxable_amt").val(taxable_v.toFixed(2));
	var isinterstate = $("#isinterstate").val();
	var gst = (Number(diff_amt) * adjust_gst)/(100);
	
	if(isinterstate == 0){
		$("#adjust_cgst").val((gst/2).toFixed(2));
		$("#adjust_sgst").val((gst/2).toFixed(2) );
	}else if(isinterstate == 1){
		$("#adjust_igst").val(gst.toFixed(2));
	}

}

function getInvDate(){
	var invdate = $("#adjust_invoice").find(":selected").data("bill_date");
	$("#adjust_invoice_date").val(invdate);
}
/*Debit-Credit Note End- Dhruv */	

//TCS Decustion popup and functionality-27_12_2021
function get_tcs_reference_popup(id,amount){

	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "get_tcs_reference_popup","ledgerid":id,"paidamount":amount},
		success:function(result)
		{
			$("#tcs_reference_adj").modal("show");
			$(".tcsreference").html(result);
			Unloading();
		}
	});
	
}

function cal_ref_amt(){
	var tot_amt = 0;
	var payamount = $("#payamount").val();
	$(".gen_checkbox").each(function() {
		if($(this).is(':checked')){
			tot_amt+=(parseFloat($(this).val()));
			$("#adj_amt").val(tot_amt.toFixed(2));
			if(parseFloat(payamount) == parseFloat(tot_amt) ){
				$("#add_tdsref").removeAttr("disabled");
				$("#add_tcsref").removeAttr("disabled");
			}else{
				$("#add_tdsref").attr("disabled", "disabled");
				$("#add_tcsref").attr("disabled", "disabled");
			}
		}else{
			$("#adj_amt").val(tot_amt.toFixed(2));
			if(payamount == tot_amt){
				$("#add_tdsref").removeAttr("disabled");
				$("#add_tcsref").removeAttr("disabled");
			}else{
				$("#add_tdsref").attr("disabled", "disabled");
				$("#add_tcsref").attr("disabled", "disabled");
			}
		}		
	});
}

function get_tds_reference_popup(id,amount){

	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "get_tds_reference_popup","ledgerid":id,"paidamount":amount},
		success:function(result)
		{
			//var obj = JSON.parse(result);
			$("#tds_reference_adj").modal("show");
			$(".tdsreference").html(result);
			Unloading();
		}
	});
	
}

function add_tds_ref_detail(){
	var refid = [];
	$(".gen_checkbox").each(function() {
		if($(this).is(':checked')){
			refid.push($(this).data("generalid"));
		}		
	});
	var adj_amt = $("#adj_amt").val();
	var pay_chalanno = $("#pay_chalanno").val();
	var pay_cheque_no = $("#pay_cheque_no").val();
	var payment_tds_ledger_id = $("#payment_tds_ledger_id").val();
	var payment_type = $("#payment_type").val();
	$.ajax({	
		type:'POST',
		url: root_domain+finance_root_domain+'app/common_form_finance/',
		data: { mode : "add_tds_ref_detail","refid":refid,"adj_amt":adj_amt,"pay_chalanno":pay_chalanno,
		"pay_cheque_no":pay_cheque_no,"payment_tds_ledger_id":payment_tds_ledger_id,"payment_type":payment_type},
		
		success:function(result)
		{
			//alert(result);
			if(result == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");

				if(payment_type == '1'){
					$("#tds_reference_adj").modal("toggle");
				}else{
					$("#tcs_reference_adj").modal("toggle");
				}
				Unloading();						
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		
		}
	});
}

// function add_tcs_ref_detail(){
// 	var refid = [];
// 	$(".gen_checkbox").each(function() {
// 		if($(this).is(':checked')){
// 			refid.push($(this).data("generalid"));
// 		}		
// 	});
// 	var adj_amt = $("#adj_amt").val();
// 	var pay_chalanno = $("#pay_chalanno").val();
// 	var pay_cheque_no = $("#pay_cheque_no").val();
// 	var payment_tcs_ledger_id = $("#payment_tcs_ledger_id").val();
// 	var payment_type = $("#payment_type").val();
// 	$.ajax({	
// 		type:'POST',
// 		url: root_domain+finance_root_domain+'app/common_form_finance/',
// 		data: { mode : "add_tds_ref_detail","refid":refid,"adj_amt":adj_amt,"pay_chalanno":pay_chalanno,
// 		"pay_cheque_no":pay_cheque_no,"payment_tds_ledger_id":payment_tds_ledger_id,"payment_type":payment_type},
		
// 		success:function(result)
// 		{
// 			alert(result);
// 			$("#tds_reference_adj").modal("hide");
// 			// $(".tdsreference").html(result);
// 			Unloading();
// 		}
// 	});
// }


function get_bill_by_method(method_type)
{
	//alert(method_type);
	if(method_type==1)
	{
		$('.bill_append').show();
		$('.bill_ref_no').hide();

		$('.due_amt_div').show();
	}
	else
	{
		$('.bill_ref_no').show();
		$('.bill_append').hide();

		$('.due_amt_div').hide();
		$('#bill_amt').val('');

		if($('#jv_hid').val()=='1')
		{
			$('.bill_ref_no_type').show();
		}
		else
		{
			$('.bill_ref_no_type').hide();	
		}
	}
}
function get_bill_adjsutment(check,page_type)
{
	if(check==1)
	{
		if(page_type==0)
		{
			var cust_id = $('#cust_id').val();
		}
		else
		{
			var cust_id = $('#vender_id').val();	
		}
		
		var eid = $('#eid').val();

		$.ajax({

			type:'POST',
			url:root_domain+finance_root_domain+'app/common_form_finance/',
			data:{mode:'get_bill_adjsutment',cust_id:cust_id,page_type:page_type,eid:eid},
			success:function(result)
			{
				//alert(result);
				console.log(result);
				$('#modal-bill-adjustment').modal('show');
				$('#advance_payment_table').html(result);
				get_bill_past_adjustment(cust_id,page_type,eid);
				$('.adjust_advance_link').show();
				
			}

		})
	}
	else
	{
		$('.adjust_advance_link').hide();
	}
}

function get_bill_past_adjustment(cust_id,page_type,eid)
{
	//alert(eid);
	$.ajax({

		type:'POST',
		url:root_domain+finance_root_domain+'app/common_form_finance/',
		data:{mode:'get_bill_past_adjustment',cust_id:cust_id,page_type:page_type,eid:eid},
		success:function(result)
		{
			$('.adv-table').html(result);
		}
	})
}
function unread_payment(check_id)
{
	//alert(check_id);
	if($('#advance_check'+check_id).is(':checked')==true)
	{
		$('#advance_amount'+check_id).prop('readonly', false);
	}
	else
	{
		$('#advance_amount'+check_id).prop('readonly', true);	
	}
}
function check_advance_amount(amount,cnt)
{
	var bill_amount = Number($('#bill_amount'+cnt).val());
	var amount = Number(amount);

	if(bill_amount<amount)
	{
		toastr.warning("AMOUNT MUST BE SMALLER THAN ADVANCE AMOUNT","error");
	}
}
function save_advance_payment(cust_id,page_type)
{
	if(page_type=='0'){
		var bill_type=0;
		var bill_entry_type=1;
		var bill_adjust_table = 'tbl_invoice';
		var bill_type_original = '2';
	}
	else
	{
		var bill_type=2;
		var bill_entry_type=2;
		var bill_adjust_table = 'tbl_pono';
		var bill_type_original = '1';
	}

	var bill_transaction_id= new Array();
	$("input[name='bill_transaction_id[]']").each(function(){
		//console.log($(this).val());
	    bill_transaction_id.push($(this).val());
	});

	var bill_amount= new Array();
	$("input[name='bill_amount[]']").each(function(){
		//console.log($(this).val());
	    bill_amount.push($(this).val());
	});

	var advance_amount= new Array();
	$("input[name='advance_amount[]']").each(function(){
		//console.log($(this).val());
	    advance_amount.push($(this).val());
	});

	$.ajax({

			type:'POST',
			url:root_domain+finance_root_domain+'app/common_form_finance/',
			data:{mode:"bill_form_add_by_trasaction",bill_type:bill_type,bill_amt:advance_amount,"bill_entry_type":bill_entry_type,"bill_adjust_voucher_type":$('#cost_center_voucher_type').val(),"bill_adjust_ledger_id":cust_id,"bill_adjust_table":bill_adjust_table,"bill_adjust_table_id":'0',"edit_id":'',"cust_ledger_id":cust_id,bill_method:'1',bill_type_original:bill_type_original,bill_transaction_id:bill_transaction_id,bill_amount:bill_amount },
			success:function(result)
			{
				console.log(result);
				toastr.success('Adjustment Done Successfully',"success");
				$('#modal-bill-adjustment').modal('hide');
			}

		});

	

}
function get_data_description(nature_id)
{
	//alert(nature_id);
	$.ajax({

		type:'POST',
		data:{mode:'get_data_description',nature_id:nature_id},
		url:root_domain+finance_root_domain+'app/payment_new/',
		success:function(result)
		{
			//alert(result);
			console.log(result);
			$('.gst_nature_link').attr("data-original-title",result);
			//alert(result);
		}

	})
}