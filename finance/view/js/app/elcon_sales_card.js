//var datatable;
$(document).ready(function() {
	load_sales_card();
	$("#elcon_salescard_add").validate({
		rules: {
			sales_card_no: {
				required: true			
			},
			sales_card_date:{
				required : true	
			}
		},
		messages: {
			sales_card_no: {
				required: "Enter Sales Card No"			
			},
			sales_card_date:{
				required : "Enter Sales Card Date"	
			}
		}
	});
});	

function reload_data()
{
	load_sales_card();
}	
$("#elcon_salescard_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#elcon_salescard_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("SALES CARD ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+finance_root_domain+'elcon_sales_card_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("SALES CARD UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+finance_root_domain+'elcon_sales_card_list';
				
			}
			$('#elcon_salescard_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_sales_card(){
	var date  = $('#rep_date').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/elcon_sales_card/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function item_detail_data(){
	var card_id = $('#eid').val();
	var card_type = $("input[name='card_type']:checked").val();
	datatable = $("#item_data_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/elcon_sales_card/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_item" },{ "name": "card_id", "value": card_id },{ "name": "card_type", "value": card_type });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function get_series_no(type_id){

	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "get_series_no", type_id:type_id},
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_pono(resp)	
		}		
	});	
}
function load_pono(id)
{
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "load_invoiceno", typeid : id},
		success: function(data){
		//console.log(data);
		var no = jQuery.parseJSON(data);
		$('#sales_card_no').val(no.invoiceno);
	}
});
}

function add_field()
{
	if($("input[name='card_type']:checked").val()==="1"){
		if($("#product_cat_id").val()===""){
			toastr.warning("Select Product", "ERROR")
			$("#product_cat_id").select2('focus');
			return false;
		}
	}
	if($("#price").val()===""){
		toastr.warning("Enter Price", "ERROR")
		$("#price").focus();
		return false;
	}
	if($("#unit_id").val()===""){
		toastr.warning("Select Unit", "ERROR")
		$("#unit_id").focus();
		return false;
	}
	if($("#valid_date").val()===""){
		toastr.warning("Select Valid Date", "ERROR")
		$("#valid_date").focus();
		return false;
	}
	if($("#effected_date").val()===""){
		toastr.warning("Select Effective Date", "ERROR")
		$("#effected_date").focus();
		return false;
	}
	if($("#effected_date").val()>=$("#valid_date").val()){
		toastr.warning("Valid Date Must be greater than Effective Date", "ERROR")
		$("#valid_date").focus();
		return false;
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { 
			mode : "fieldadd",
			card_id:$("#eid").val(),
			card_type : $("input[name='card_type']:checked").val(),
			product_cat_id:$("#product_cat_id").val(),
			currency_id:$("#currency_id").val(),
			rate1:$("#rate1").val(),
			rate2:$("#rate2").val(),
			rate3: $("#rate3").val(),
			rate : $("#price").val(),
			edit_id:$("#edit_id").val(),
			effected_date:$("#effected_date").val(),
			valid_date:$("#valid_date").val(),
			unit_id:$("#unit_id").val()
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
			}
			$("#product_cat_id").select2("val","");
			$("#product_cat_id").select2('focus');
			$("#currency_id").val('');
			$("#rate1").val('');
			$("#rate2").val('');
			$("#rate3").val('');
			$("#price").val('');
			$("#unit_id").select2('val','');
			$('#edit_id').val('');
			$('#effected_date').val('');
			$('#valid_date').val('');
			$('#save').val('save');
			Unloading();

			item_detail_data();
		}
	});
}
function edit_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);

			$("#product_cat_id").select2("val",data.product_cat_id);
			$("#currency_id").select2("val",data.currency_id);
			$("#rate3").val(data.rate3);
			$("#rate1").val(data.rate1);
			$("#rate2").val(data.rate2);
			$("#price").val(data.price);
			$("#unit_id").select2("val",data.unit_id);
			$("#effected_date").val(data.effected_date);
			$("#valid_date").val(data.valid_date);
			$("#edit_id").val(id)
			$('#save').val('Update');
			// load_product_unit();
			Unloading();
		}
	});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/elcon_sales_card/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					item_detail_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}

}

function delete_so_card(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/elcon_sales_card/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				//console.log(response)
				if(response.trim() == "1") {
					toastr.success("SALES CARD DELETE SUCCESSFULLY", "SUCCESS");
					load_sales_card();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
		Unloading();
	}
}
function card_aprooval_status(id,type,no){
	$('#preview_so_card_approval').modal('show');
	$('#so_card_no').html(no);
	$('#so_card_id').val(id);
	veder_detail_load();
	// product_detail_load();
	load_so_card_hist();
}
function veder_detail_load(){
	var id = $('#so_card_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "load_socard_vender_detail", card_id:id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#card_detail_show').html(resp.card_detail_show);
	}
});
}
function product_detail_load(){
	var id = $('#so_card_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "load_socard_pro_detail", card_id:id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#card_detail_show').html(resp.card_detail_show);
	}
});
}
function load_so_card_hist(){
	var card_id = $('#so_card_id').val();

	$("#order-socard-history-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+finance_root_domain+'app/elcon_sales_card/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_socard_hist" }, { "name": "card_id", "value": card_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_socard_apprv_hist(){
	
	var form_data = {
		mode:"add_socard_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		elcon_sales_id:$('#so_card_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: form_data,
		success: function(response)
		{
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_so_card_hist();
			$('#preview_so_card_approval').modal('hide');
			//load_order_confirm_datatable();
			load_sales_card();
			Unloading();
		}
	});	
}
function active_status_change(id,active_status){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/elcon_sales_card/',
		data: { mode : "active_status",  eid : id, active_status:active_status},
		success: function(response)
		{
			//console.log(response)
			var data=jQuery.parseJSON(response)
			var response=data.res;
			if(response.trim() == "1") {
				toastr.success("POCARD STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				load_sales_card();
				Unloading();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}							
		}
	});	
}
// Maulik Start
function load_product_unit(){
	if($("input[name='card_type']:checked").val()==="0"){
		var product_cat_id =  $("#vend_product_cat_id").val();
	}else{
		var product_cat_id =  $("#product_cat_id").val();
	}
	var edit_id = $("#edit_id").val();
	if(product_cat_id)
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ finance_root_domain +'app/elcon_sales_card/',
			data: { mode : "load_product_unit", product_cat_id : product_cat_id, edit_id:edit_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$("#unit_id").html(obj.unit_option);
			}
		});
	}
}