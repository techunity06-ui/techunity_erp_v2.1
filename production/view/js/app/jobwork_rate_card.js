//var datatable;
$(document).ready(function() {
	load_purchase_card();
	$("#jobwork_rate_card_add").validate({
		rules: {
			vender_id: {
				required: function(element) {
					if($("input[name='card_type']:checked").val() == 0){
						return true;
					} else {
						return false;
					}
				}
			},
			product_id:{
				required: function(element) {
					if($("input[name='card_type']:checked").val() == 1){
						return true;
					} else {
						return false;
					}
				}
			},
			jobwork_card_no: {
				required: true			
			},
			jobwork_card_date:{
				required : true	
			},
		},
		messages: {
			vender_id: {
				required: "Select Vender"
			},
			product_id:{
				required: "Select Product"
			},
			jobwork_card_no: {
				required: "Enter Purchase Card No"			
			},
			jobwork_card_date:{
				required : "Enter Purchase Card Date"	
			},
		}
	});
});	

function reload_data()
{
	load_purchase_card();
}	
$("#jobwork_rate_card_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#jobwork_rate_card_add").valid()) {
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
		url: root_domain+production_domain+'app/jobwork_rate_card/',
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
				toastr.success("PURCHASE CARD ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+'jobwork_rate_card_list';
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
				toastr.success("PURCHASE CARD UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+production_domain+'jobwork_rate_card_list';
				
			}
			$('#jobwork_rate_card_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function ven_prod(id){
	if(id ==0){
		$('#choose_ven').show();
		$('#choose_prod').hide();
		$('.pro_vend').show();
		$('.vend_pro').hide();
		$('#first').html("Product Name");
	}else{
		$('.pro_vend').hide();
		$('.vend_pro').show();
		$('#choose_ven').hide();
		$('#choose_prod').show();
		$('#first').html("Vendor Name");
	}
}
function load_purchase_card(){
	var card_type = $("#card_type").val();
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
			"sAjaxSource": root_domain+production_domain+'app/jobwork_rate_card/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "card_type", "value": card_type },{ "name": "date", "value": date },);
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
		
		if(card_type == 0){
			$("#car_ty_l").html("Vendor Name");
		}else{
			$("#car_ty_l").html("Product Name");
		}
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
			"sAjaxSource": root_domain+production_domain+'app/jobwork_rate_card/',
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
	url: root_domain+production_domain+'app/jobwork_rate_card/',
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
	url: root_domain+production_domain+'app/jobwork_rate_card/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
		//console.log(data);
		var no = jQuery.parseJSON(data);
		$('#jobwork_card_no').val(no.invoiceno);
	}
	});
}

function add_field()
{
	if($("input[name='card_type']:checked").val()==="0"){
		if($("#vend_product_id").val()===""){
			toastr.warning("Select Product", "ERROR")
			$("#vend_product_id").select2('focus');
			return false;
		}
		if($("#vender_id").val()===""){
			toastr.warning("Select Vendor", "ERROR")
			$("#vender_id").select2('focus');
			return false;
		}
	}
	if($("input[name='card_type']:checked").val()==="1"){
		if($("#prod_id_vend").val()===""){
			toastr.warning("Select Vender", "ERROR")
			$("#prod_id_vend").select2('focus');
			return false;
		}
		if($("#product_id").val()===""){
			toastr.warning("Select Product", "ERROR")
			$("#product_id").select2('focus');
			return false;
		}
	}

	if($("#rate_tolerance").val()>100){
		toastr.warning("Please Enter Valid Tolerance", "ERROR")
		$("#rate_tolerance").focus();
		$("#rate_tolerance").val('0');
		return false;
	}
	
	if($("#valid_date").val()===""){
		toastr.warning("Select Valid Date", "ERROR")
		$("#valid_date").focus();
		return false;
	}

	if($("#affected_date").val()===""){
		toastr.warning("Select Effective Date", "ERROR")
		$("#affected_date").focus();
		return false;
	}


	if($("#price").val()===""){
		toastr.warning("Enter Price", "ERROR")
		$("#price").focus();
		return false;
	}


	if($("#discount_percentage").val()>100){
		toastr.warning("Please Enter Valid Discount", "ERROR")
		$("#discount_percentage").focus();
		$("#discount_percentage").val('0');
		return false;
	}
	if($("#process_rate_unit").val()==""){
		toastr.warning("Please Enter Unit", "ERROR")
		return false;
	}
	
	Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/jobwork_rate_card/',
			data: { 
				mode : "fieldadd",
				card_id:$("#eid").val(),
				card_type : $("input[name='card_type']:checked").val(),
				vend_product_id:$("#vend_product_id").val(),
				prod_id_vend:$("#prod_id_vend").val(),
				vender_id:$("#vender_id").val(),
				product_id:$("#product_id").val(),
				rate_tolerance:$("#rate_tolerance").val(),
				disc_per:$("#discount_percentage").val(),
				date:$("#affected_date").val(),
				valid_date: $("#valid_date").val(),
				quot_date:$("#quotation_date").val(),
				rate : $("#price").val(),
				qtn_no : $("#quotation_no").val(),
				process_id : $("#process_id").val(),
				unit_id : $("#unit_id").val(),
				edit_id:$("#edit_id").val(),
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

				if($("input[name='card_type']:checked").val()==="0"){
					$("#vend_product_id").select2("val","");
					$("#vend_product_id").select2('focus');
				}else{
					$("#prod_id_vend").select2("val","");
					$("#prod_id_vend").select2('focus');
				}
				$("#rate_tolerance").val('');
				$("#discount_percentage").val('');
				$("#affected_date").val('');
				$("#valid_date").val('');
				$("#quotation_date").val('');
				$("#price").val('');
				$("#quotation_no").val('');
				$("#quotation_date").val('');
				$("#process_id").val('');
				$("#unit_id").val('');
				$('#edit_id').val('');
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
		url: root_domain+production_domain+'app/jobwork_rate_card/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			if($("input[name='card_type']:checked").val()==="0"){
				$("#vend_product_id").select2("val",data.product_id);
			}else{
				$("#prod_id_vend").select2("val",data.vendor_id);
			}
			
			$("#rate_tolerance").val(data.rate_tolerance);
			$("#discount_percentage").val(data.discount_percentage);
			$("#affected_date").val(data.affected_date);
			$("#valid_date").val(data.valid_date);
			$("#quotation_date").val(data.quotation_date);
			$("#price").val(data.price);
			$("#quotation_no").val(data.quotation_number);
			$("#process_id").val(data.process_id);
			$("#unit_id").val(data.unit_id);
			$("#edit_id").val(id)
			$('#save').val('Update');
			
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
				url: root_domain+production_domain+'app/jobwork_rate_card/',
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

function delete_po_card(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+production_domain+'app/jobwork_rate_card/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("JOBWORK RATE CARD DELETE SUCCESSFULLY", "SUCCESS");
						load_purchase_card();
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
	$('#preview_po_card_approval').modal('show');
	$('#po_card_no').html(no);
	$('#po_card_id').val(id);
	if(type == 0){
		veder_detail_load();
	}else{
		product_detail_load();
	}
	load_po_card_hist();
}
function veder_detail_load(){
	var id = $('#po_card_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/jobwork_rate_card/',
		data: { mode : "load_pocard_vender_detail", card_id:id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#card_detail_show').html(resp.card_detail_show);
		}
	});
}
function product_detail_load(){
	var id = $('#po_card_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/jobwork_rate_card/',
		data: { mode : "load_pocard_pro_detail", card_id:id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#card_detail_show').html(resp.card_detail_show);
		}
	});
}
function load_po_card_hist(){
	var card_id = $('#po_card_id').val();

		$("#order-pocard-history-datatable").dataTable({
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
			"sAjaxSource": root_domain+production_domain+'app/jobwork_rate_card/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_pocard_hist" }, { "name": "card_id", "value": card_id }  );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_pocard_apprv_hist(){
	
	var form_data = {
		mode:"add_pocard_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		jobwork_card_id:$('#po_card_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/jobwork_rate_card/',
		data: form_data,
		success: function(response)
		{
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_po_card_hist();
			//load_order_confirm_datatable();
			load_purchase_card();
			Unloading();
		}
	});	
}
function active_status_change(id,active_status){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/jobwork_rate_card/',
		data: { mode : "active_status",  eid : id, active_status:active_status},
		success: function(response)
		{
			//console.log(response)
			var data=jQuery.parseJSON(response)
			var response=data.res;
			if(response.trim() == "1") {
				toastr.success("JOBWORK RATE CARD STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				load_purchase_card();
				Unloading();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}							
		}
	});	
}
function load_product_unit(){
	if($("input[name='card_type']:checked").val()==="0"){
		var product_id =  $("#vend_product_id").val();
	}else{
		var product_id =  $("#product_id").val();
	}
	var edit_id = $("#edit_id").val();
	if(product_id)
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ production_domain +'app/jobwork_rate_card/',
			data: { mode : "load_product_unit", product_id : product_id, edit_id:edit_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$("#unit_id").html(obj.unit_option);
			}
		});
	}
}