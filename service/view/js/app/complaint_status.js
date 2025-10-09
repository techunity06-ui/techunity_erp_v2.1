//var datatable;
$(document).ready(function() {
	 show_data_complain();
	 show_close_part();
});

$(".btn_close").click(function() {
	$("label.error").hide();
});

function openStatusForm(x)
{
	if(x=='4')
	{
		$('#cust_fb_id, #f_remark').attr('required', true);
	} else {
		$('#cust_fb_id, #f_remark').attr('required', false);
	}

	if(x=='5')
	{
		if ($("#bom_first_id").val()) {
			$('#ns_part_id').show();
		} else {
			$('input[name=n_spart][value=4]').attr('checked', true); 
		}
		
		$('#cust_fb_id_div').hide();
		$('#close_form').hide();
		
		$('#product_close_detail').hide();
		 $('#c_amount').attr('readonly', false);
		 $('#remrk_hdn_divs').show();

		 $('#file').attr('required', false);
		 $('.attach_file').text('Attach File');

		//hide payment status
		 $('#pay_status').val('0');
		 show_hide_payment_field('0');
	}
	else if(x=='8')
	{
		$('#remrk_hdn_divs').hide();
		$('#ns_part_id').hide();
		$('#cust_fb_id_div').hide();
		$('#close_form').hide();
		
		$('#product_close_detail').hide();
		 $('#c_amount').attr('readonly', false);
	}
	else
	{
		$('#remrk_hdn_divs').show();
		$('#ns_part_id').hide();
		$('#cust_fb_id_div').show();
		$('#close_form').show();
		
		$('#spare_part_form').hide();
		$('#req_sp_count').val('0');
		
		$('#product_close_detail').show();
		$('#c_amount').attr('readonly', false);

		$('#file').attr('required', false);
		$('.attach_file').text('Attach File ');

		//hide payment status
		$('#pay_status').val('');
		show_hide_payment_field('1');
	}
	
}

function openCloseForm(x)
{
	var old_sp_part_status=$('#old_sp_part_status').val();
	
	if(x==4 && old_sp_part_status=='yes')
	{
		$('#close_spare_part_form').show();
	}
	else
	{
		$('#close_spare_part_form').hide();
	}
}

function showSparePartForm(x)
{
	if(x!='4')
	{
		$('#spare_part_form').show();
		if($('#req_sp_count').val()>0)
		{
			
		}
		else
		{
			$('#req_sp_count').val('');
		}
		
	}
	else
	{
		$('#spare_part_form').hide();
		$('#req_sp_count').val('0');
	}
}
function add_field_sp(){
	if(!$("#comp_product_id").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#comp_product_id').select2('focus');
		return false;
	}
	
	if(!$("#product_id").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#product_id').select2('focus');
		return false;
	}
	
	if(!$("#product_qty").val()) {
		toastr.warning("Enter Quantity", "ERROR");
		$('#product_id').focus();
		return false;
	}
	
	var conf_form = {
		mode:'spare_part_add',
		comp_product_id:$("#comp_product_id").val(),
		product_id:$("#product_id").val(),
		product_qty:$("#product_qty").val(),
		cust_id_hid:$("#cust_id_hid").val(),
		comp_id_hid:$("#comp_id_hid").val(),
		edit_id:$("#edit_id").val()
	};
	
	$('#addrow').prop("disabled",true);
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: conf_form, 
		success: function(response)
		{
			$("#product_id").select2("val","");
			$('#product_qty').val('');
			$('#addrow').val('Add');
			$('#req_sp_count').val('1');
			Unloading();
			$('#addrow').prop("disabled",false);
			show_data_complain();
		}
	});
} 
function show_data_complain() {
	
	var comp_id = $('#comp_id_hid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: { mode:"load_complain_data", complaint_id:comp_id },
		success: function(resp){
			$('#complaint_pro_data_c').html(resp);				
			$('#complaint_pro_data1').html(resp);				
			Unloading();
		}	
	});
}

function edit_data_complain(complaint_trn_id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: { mode : "preedit", complaint_trn_id:complaint_trn_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response);
			$("#product_id").html(resp.pro_resp_html);
			$("#comp_product_id").select2("val",resp.comp_product_id);
			$("#product_id").select2("val",resp.s_product);
			$("#product_qty").val(resp.s_qty);
			$("#edit_id").val(complaint_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_data_complain(complaint_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+service_domain+'app/complaint_status/',
			data: { mode:"delete_data", complaint_trn_id:complaint_trn_id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data_complain();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function add_old_spare(){
	if(!$("#comp_product_id_old").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#comp_product_id_old').select2('focus');
		return false;
	}
	
	if(!$("#product_id_old").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#product_id_old').select2('focus');
		return false;
	}
	
	if(!$("#product_qty_old").val()) {
		toastr.warning("Enter Quantity", "ERROR");
		$('#product_qty_old').focus();
		return false;
	}
	
	if(!$("#product_rate_old").val()) {
		toastr.warning("Enter Rate", "ERROR");
		$('#product_rate_old').focus();
		return false;
	}
	
	if(!$("#product_amt_old").val()) {
		toastr.warning("Enter Amount", "ERROR");
		$('#product_amt_old').focus();
		return false;
	}

	var conf_form = {
		mode:'spare_part_add_old',
		comp_product_id:$("#comp_product_id_old").val(),
		product_id:$("#product_id_old").val(),
		product_qty:$("#product_qty_old").val(),
		product_rate:$("#product_rate_old").val(),
		product_amt:$("#product_amt_old").val(),
		courier_name:$("#courier_name").val(),
		courier_no:$("#courier_no").val(),
		courier_del_date:$("#courier_del_date").val(),
		cust_id_hid:$("#cust_id_hid").val(),
		comp_id_hid:$("#comp_id_hid").val(),
		product_remark_old:$("#product_remark_old").val(),
		edit_id:$("#edit_id_old").val()
	};
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: conf_form, 
		success: function(response)
		{
			$("#product_id_old").select2("val","");
			$('#product_qty_old').val('');
			$('#product_rate_old').val('');
			$('#product_amt_old').val('');
			$('#product_remark_old').val('');
			$('#addrow_old').val('Add');
			$('#old_sp_count').val('1');
			Unloading();
			show_close_part();
		}
	});
} 

function show_close_part() {
	var comp_id = $('#comp_id_hid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: { mode:"load_close_spare_part_data", complaint_id:comp_id },
		success: function(resp){
			$('#complaint_pro_close_data').html(resp);				
			Unloading();
		}	
	});
}

function edit_data_close_part(complaint_trn_id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_status/',
		data: { mode : "preedit_close", complaint_trn_id:complaint_trn_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response);
			$("#product_id_old").html(resp.pro_resp_html);
			$("#comp_product_id_old").select2("val",resp.sc_comp_product_id);
			$("#product_id_old").select2("val",resp.sc_product);
			$("#product_qty_old").val(resp.sc_qty);
			$("#product_rate_old").val(resp.sc_rate);
			$("#product_amt_old").val(resp.sc_amount);
			$("#courier_name").val(resp.courier_name);
			$("#courier_no").val(resp.courier_no);
			$("#courier_del_date").val(resp.courier_del_date);
			$("#product_remark_old").val(resp.sc_remark);
			$("#edit_id_old").val(complaint_trn_id);
			$('#addrow_old').val('Update');
			Unloading();
		}
	});
}

function delete_data_close_part(complaint_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+service_domain+'app/complaint_status/',
			data: { mode:"delete_data_close_part", complaint_trn_id:complaint_trn_id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_close_part();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function get_spare_part_complain(complaint_id)
{
	if(!complaint_id){
		toastr.info("Please Select Complain First !!!", "INFO");
		return false;
	}
	$('#modal-complain-history-spare-part').modal('show');
	$('#comp_id').val(complaint_id);
	show_complain_history_spare_part_datatable();
}

function show_complain_history_spare_part_datatable(){
	
	var comp_id=$('#comp_id').val();
	datatable = $("#table_complain_history_spare_part").dataTable({
		"bAutoWidth" : true,
		"bFilter" : false,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, 30, 50], [5, 10, 20, 30, 50]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+service_domain+'app/complaint_reassign/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name":"mode", "value":"show_complain_history_spare_part" },{ "name":"complain_id", "value":comp_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function getProductAmount_old()
{
	var product_qty_old=Number($('#product_qty_old').val());
	var product_rate_old=Number($('#product_rate_old').val());
	var product_amt_old=product_qty_old*product_rate_old;
	$('#product_amt_old').val(product_amt_old);
}

function getProductRate(pr)
{
	$.ajax({
		
		type:'POST',
		url: root_domain+service_domain+'app/complaint_reassign/',
		data: { mode:"get_product_rate", product_id:pr },
		success: function(resp){
			$('#product_rate_old').val(resp);				
			Unloading();
		}	
	})
}

function get_final_close_pay()
{
	var co_status=$('#change_status').val();
	if(co_status=='4')
	{
		var c_amount_old=Number($('#c_amount_old').val());
		var c_amount=Number($('#c_amount').val());
	}
}

function changeStatus(sid,s_status)
{
	var complaint_date=$('#complaint_date').val();
	var comp_id = $('#comp_id_hid').val();
	$.ajax({
		
		type:'POST',
		url: root_domain+service_domain+'app/complaint_status/',
		data: { mode:"change_complaint_status", sid:sid,s_status:s_status,date:complaint_date,com_id:comp_id },
		success: function(resp){
			
			toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
			show_close_part();		
			Unloading();
		}	
	})
}

function get_bom_product(com_product)
{
	$.ajax({
		type:'POST',
		url: root_domain+service_domain+'app/complaint/',
		data: { mode:"get_bom_product",com_product:com_product},
		success: function(resp){
			$('#product_id').html(resp);
			Unloading();
		}	
	})
}
