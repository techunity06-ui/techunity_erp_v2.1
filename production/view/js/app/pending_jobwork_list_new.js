var datatable;
var datatable1;
$(document).ready(function() {
	load_jobwork_pending_datatable();
	if($("#jobwork_edit_id").val()!=""){
		show_edit_data();
	}else{
		show_data();
	}


	$("#jobwork_add").validate({
	rules: {
		vender_id: {
			required: true,
		},
		branch_id: {
			required: true,
		},
		vehicle_no : {
			required: true,
		},
		
	},
	messages: {
		vender_id: {
			required: "Select Vendor"
		},
		vehicle_no: {
			required: "Enter Vehicle No."
		},
		branch_id: {
			required: "Select Branch"
		},
		
	}
});
	
});


function reload_data()
{
	var type = $("input[name='workorder_status']:checked").val();
	if(type == '1'){
		load_jobwork_done_datatable()
	}else{
		load_jobwork_pending_datatable();	
	}
	
}

function load_jobwork_pending_datatable()
{
	$("#jobwork_done").hide();
	$("#jobwork_pending").show();
	// var vender_id = $('#vender_id').val();
	var branch_id = $('#branch_id').val();
	
	datatable = $("#jobwork-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": false,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+production_domain+'app/pending_jobwork_list_new/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "branch_id", "value": branch_id });
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			},
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	
}



function load_jobwork_done_datatable()
{
	$("#jobwork_done").show();
	$("#jobwork_pending").hide();
	// var vender_id = $('#vender_id').val();
	var branch_id = $('#branch_id').val();
	
	datatable1 = $("#jobwork-done-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": false,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+production_domain+'app/pending_jobwork_list_new/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch_jobwork" },{ "name": "branch_id", "value": branch_id });
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	
}


/*
Code By Umair: 29/12/2020
Comment: Get unique id of the relevant invoice type no from tbl_invoicetype table
*/
function get_series_no_jobwork(){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "get_series_no_jobwork" },
		success: function(resp){
			load_jobcard_no_jobwork(resp.trim());
		}		
	});	
}

function load_jobcard_no_jobwork(id)
{
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "load_invoiceno_jobwork", typeid : id},
		success: function(data){
			var no = jQuery.parseJSON(data);
			$('#jobwork_no').val(no.invoiceno);
		}
	});
}

/*
Code By Umair: 29/12/2020
Comment: Get the jobwork no dynamic from tbl_invoicetype table
*/

function get_process_list(product_id){
	if(product_id == ""){
		return false;
	}
	if($("#vender_id").val()==""){

		toastr.warning("Select Vendor", "ERROR");
		$("#product_id").select2("val","").trigger('change');
		return false;
	}
	if($("#branch_id").val()=="" || $("#branch_id").val()=="1000"){

		toastr.warning("Select Branch", "ERROR");
		$("#product_id").select2("val","").trigger('change');
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "get_process_list", 
			product_id : product_id,
			branch_id : $("#branch_id").val() 
		},
		success: function(data){
			$("#process_id").empty().html(data);
			$("#process_id").val('').trigger('change');
		}
	});
}

function show_jobwork_detail_data(process_id){
	
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "get_jobwork_detail_data", 
			product_id : product_id,
			process_id:process_id,
			edit_id : $("#edit_id").val(),
			branch_id : $("#branch_id").val() 
		},
		success: function(response){
			var data = jQuery.parseJSON(response);	
			
			$("#base_qty").html(data.working_qty);
			$("#product_base_unit_name").html(data.unit_name);
			$("#product_base_unit").val(data.base_unit);
			$("#product_conv_unit").val(data.conv_unit);
			$('#product_working_qty_hide').val(data.working_qty);
			$('#description').val(data.description);
			$('#p_id').val(data.p_id);
		}
	});
}


function product_convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		if(resultb===results){
			return false;
		}
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	var process_id=$("#process_id").val();
	
	if(product_id){
		if(process_id==""){

		toastr.warning("Select Process First", "WARNING");
		$("#product_base_qty").val("");
		$("#product_base_qty_hide").val("");
		$("#product_conv_qty").val("");
		$("#product_conv_qty_hide").val("");
	}
		
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/pending_jobwork_list_new/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
			
				if(type===1){
					$("#product_base_qty_hide").val(base_qty.trim());
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty.trim());
				}
				
				if(type===1){
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);				
					
				}else{
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);
				}
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_base_qty").val("");
		$("#product_base_qty_hide").val("");
		$("#product_conv_qty").val("");
		$("#product_conv_qty_hide").val("");
	}
}
function calculate_total_amount(qty){

	if($("#rate").val() == ""){
		return;
	}else{
		calculate_total($("#rate").val())
	}
}
function calculate_total(rate){

	var qty=$("#product_base_qty").val();
	var material_qty=$("#material_qty").val();
	var rate=$("#rate").val();
	if(material_qty == ""){
		
		if(qty == ""){
			toastr.warning("Enter Quantity First", "WARNING");
			return;
		}
	}
	

	var total = 0;
	if(material_qty == ""){
		total = qty * rate;
	}else{
		total = material_qty * rate;
		//alert(material_qty);
		//alert(rate);
	}
	//alert(total);
	$("#total_amount").val(total);

}



function add_field()
{
	var qty = parseInt($("#product_base_qty").val());
	var working_qty =   parseInt($("#product_working_qty_hide").val());
	if($("#product_id").val()==""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if($("#process_id").val()==""){
		toastr.warning("Select Process Name", "ERROR");
		$("#process_id").select2('focus');
		return false;
	}
	else if($("#product_base_qty").val()==""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#rate").val()==""){
		toastr.warning("Enter Rate", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}else if($("#vender_id").val()==""){
		toastr.warning("Select Vendor", "ERROR");
		return false;
	}else if($("#branch_id").val()==""){
		toastr.warning("Select Branch", "ERROR");
		return false;
	}
	else if($("#branch_id").val()=="1000"){
		toastr.warning("Select any one Branch", "ERROR");
		return false;
	}
	else if(qty > working_qty){
		toastr.warning("Quantity not greater than working qty", "ERROR");
		return false;
	}

	if($("#branch_id").val() == "1000"){
		toastr.warning("Select Branch", "ERROR");
		return false;
	}

	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "fieldadd",
			edit_id:$("#edit_id").val(),
			product_id:$("#product_id").val(),
			process_id:$("#process_id").val(),
			product_base_unit:$("#product_base_unit").val(),
			product_base_qty:$("#product_base_qty").val(),
			product_conv_unit:$("#product_conv_unit").val(),
			product_conv_qty:$("#product_conv_qty").val(),
			working_qty:$("#product_working_qty_hide").val(),
			rate : $("#rate").val(),
			material_unit : $("#material_unit").val(),
			material_qty : $("#material_qty").val(),
			p_id : $("#p_id").val(),
			total_amount : $("#total_amount").val(),
			branch_id:$("#branch_id").val(),
			description:$("#description").val()
		},
		success: function(response)
		{

			if(response=='-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			else
			{
				$('#workorder_wise_qty_modal').modal('hide');
				reset_add_field_from();
				Unloading();
				
				if($("#jobwork_edit_id").val()!=""){
					show_edit_data();
				}else{
					show_data();
				}
			}
			
		}
	});
}


function reset_add_field_from(){
	$("#product_id").select2("val","");
				$("#product_id").select2('focus');
				$("#process_id").val("");
				$("#edit_id").val('');
				$('#addrow').val('Add');
				$("#product_base_unit").val("");
				$("#product_conv_unit").val("");
				$("#product_base_qty").val("");
				$("#product_conv_qty").val("");
				$("#product_base_unit_name").val("");
				$("#material_unit").val("");
				$("#material_qty").val("");
				
				$("#product_base_qty_hide").val("");
				$("#product_conv_qty_hide").val("");
				$("#product_working_qty_hide").val("");
				$("#product_base_unit_name").html("NOS");
				$("#product_base_unit_name").html("NOS");
				$("#rate").val("");
				$("#base_qty").html("0");
				$("#total_amount").val("");
				
				$("#process_id").select2("val", "");
}

function show_data(){
	var branch_id = $("#branch_id").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "load_tempoutward", branch_id:branch_id},
		success: function(data){
			//console.log(data);
			$('#tbl_jobwork_data').html(data);		
			Unloading();
		}		
	});
}


function show_edit_data(){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "load_tempoutward_edit",
			job_work_id : $("#jobwork_edit_id").val()
		},
		success: function(data){
			//console.log(data);
			$('#tbl_jobwork_data').html(data);		
			Unloading();
		}		
	});
}


function delete_data(id,table,whereid)
{

	Swal.fire({
			title: 'Are you want to delete ?',
		  // text: "You won't be able to revert this!",
		  icon: 'question',
		  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
		  customClass: {
		    icon: 'no-border'
		  }*/
		  showCancelButton: true,
		  confirmButtonColor: '#5cb85c',
		  cancelButtonColor: '#d9534f',
		  cancelButtonText: 'No',
		  confirmButtonText: 'Yes',
		  allowOutsideClick: false,
		  allowEscapeKey : false,
		  
		}).then((result) => {
			if (result.isConfirmed) {
				Loading();
				$.ajax({
					type: "POST",
					url: root_domain+production_domain+'app/pending_jobwork_list_new/',
					data: { mode : "delete_data", eid:id, table:table, whereid:whereid},
					success: function(response)
					{
						
						var data=jQuery.parseJSON(response);
						var response=data.res;
						if(response.trim() == "1") {
							toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
							
							if($("#jobwork_edit_id").val()!=""){
								show_edit_data();
							}else{
								show_data();
							}
							reset_add_field_from();
							Unloading();
						}
						else if(response.trim() == "0") {
							toastr.warning("SOMETHING WRONG", "WARNING");
						}							
					}
				});	
			}
		});

}

function edit_data(id,p_id)
{

	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "preedit",  id : id,p_id:p_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);

			$("#product_id").select2("val",data.product_id).trigger('change');
			$("#product_id").select2('focus');
			$("#edit_id").val(id);
				$('#addrow').val('Update');
			setTimeout(function(){
				$("#process_id").select2("val",data.process_id).trigger('change');
				
				$("#product_base_unit").val(data.product_base_unit);
				$("#product_conv_unit").val(data.product_conv_unit);
				$("#product_base_qty").val(data.product_base_qty);
				$("#product_conv_qty").val(data.product_conv_qty);
				
				$("#material_unit").val(data.material_unit);
				$("#material_qty").val(data.material_qty);
				
				$("#base_qty").html(data.working_qty);
				$("#product_base_unit_name").html(data.unit_name);
				$('#product_working_qty_hide').val(data.working_qty);
				$('#p_id').val(data.p_id);
				
				$("#product_base_qty_hide").val(data.product_base_qty);
				$("#product_conv_qty_hide").val(data.product_con_qty);
				CKEDITOR.instances['description'].setData(data.description);
				
				Unloading();
			},500);
			
			setTimeout(function(){
				$("#rate").val(data.pr_rate);
				if(data.material_qty != "" && data.material_qty != 0){
					$("#total_amount").val(data.pr_rate * data.material_qty);
				}else{
					$("#total_amount").val(data.pr_rate * data.product_base_qty);	
				}
				
			},800);
			
			
		}
	});
}

$("#jobwork_add").on('submit',function(e) {

	var jobwork_qty = $("#jobwork_total_qty").val();



	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#jobwork_add").valid()) {
		return false;
	}

	if(jobwork_qty == 0){
		toastr.warning("PLEASE ENTER ANY ONE", "ERROR");
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("JOBWORK CREATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+"pending_job_work_list_new";
			}else if(arr.msg == 'update'){
				toastr.success("JOBWORK UPDATE SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+"pending_job_card_new";
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
			$('#jobwork_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function delete_temp_data(){
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/pending_jobwork_list_new/',
			data: { mode : "delete_temp_data"},
			success: function(data){

			}		
		});
}

function auto_add_temp_data(product_id,process_id,vendor_id,branch_id,p_id){ 
	var type = '';
	if(process_id!='' && product_id!=''){
		type = 0; // single selection item wise
	}else{
		type = 1; // all slection vendor wise
	}
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/pending_jobwork_list_new/',
			data: { mode : "auto_add_temp_data",vendor_id : vendor_id, product_id : product_id, process_id : process_id, type : type,branch_id:branch_id,p_id:p_id },
			success: function(data){
				setTimeout(function(){
					show_data();
				},800);
			}		
		});
}

function get_jobwork_rate(){

	var vendor_id = $("#vender_id").val();
	var product_id = $("#product_id").val();
	var process_id = $("#process_id").val();
	var material_unit = $("#material_unit").val();
	var branch_id = $("#branch_id").val();

/*	console.log(vendor_id)
	console.log(product_id)
	console.log(process_id)
	console.log(material_unit)*/
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/pending_jobwork_list_new/',
			data: { mode : "get_jobwork_rate",vendor_id : vendor_id, product_id : product_id, process_id : process_id,material_unit:material_unit,branch_id:branch_id},
			success: function(data){
				//console.log(data);
				var rate=data.trim();
				$("#rate").val(rate);
				/*if(product_id != "" && process_id !=""){
					calculate_total();
				}*/
				Unloading();
			}		
		});
}


function load_jobwork_product(branch_id){
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "load_jobwork_product", branch_id:branch_id},
		success: function(data){
			//console.log(data);

			$('#product_id').empty().html(data);	
			$("#product_id").select2({
				placeholder:"Select Product",
				width : "100%",  
				allowClear: true
			});	
			// $("#product_id").select2("val","");	
			Unloading();
		}		
	});
}


function open_workorder_wise_jobwork_qty(){
	var qty = parseInt($("#product_base_qty").val());
	var working_qty =   parseInt($("#product_working_qty_hide").val());
	var p_id = $("#p_id").val();
	var product_id = $("#product_id").val();
	var process_id = $("#process_id").val();
	if($("#product_id").val()==""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if($("#process_id").val()==""){
		toastr.warning("Select Process Name", "ERROR");
		$("#process_id").select2('focus');
		return false;
	}
	else if($("#product_base_qty").val()==""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#rate").val()==""){
		toastr.warning("Enter Rate", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}else if($("#vender_id").val()==""){
		toastr.warning("Select Vendor", "ERROR");
		return false;
	}else if($("#branch_id").val()==""){
		toastr.warning("Select Branch", "ERROR");
		return false;
	}
	else if($("#branch_id").val()=="1000"){
		toastr.warning("Select any one Branch", "ERROR");
		return false;
	}
	else if(qty > working_qty){
		toastr.warning("Quantity not greater than working qty", "ERROR");
		return false;
	}

	if($("#branch_id").val() == "1000"){
		toastr.warning("Select Branch", "ERROR");
		return false;
	}	
	
	
	$.ajax({
		type: "POST",
		url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "wo_jobwork_model_open",
			qty:qty,
			product_id:product_id,
			process_id:process_id,
			p_id:p_id
		},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#workorder_wise_qty_modal').modal('show');
			$("#workorder_data").html(data.html_data);	
			$(".wo_select2").select2({
				width: '100%',
			//minimumInputLength: 3
		});	
			load_wo_jobwork_datatable();
			validate_qty(0);	
		}
	});
}

function load_wo_jobwork_datatable()
{
	var p_id=$('#p_id').val();
	var edit_id = $("#edit_id").val();
	
	datatable = $("#wo_jobwork_table").dataTable({
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
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + production_domain +'app/pending_jobwork_list_new/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_wo_jobwork_qty" },
				{"name":"edit_id","value":edit_id},
				{"name":"p_id","value":p_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_wo_jobwork_qty(){
	
	if($("#wo_p_id").val()==="")
	{		
		toastr.warning("Select Workorder", "ERROR")
		$("#wo_p_id").select2('focus')
		return false;
	}
	else if($("#qtyforwo").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#qtyforwo").focus();
		return false;
	}
	else if(parseFloat($("#qtyforwo").val()) > parseFloat($("#wo_qty").val()))
	{		
		toastr.warning("Quantity can not greater WORKORDER quantity", "ERROR")
		$("#qtyforwo").focus();
		return false;
	}

	var wo_p_id = $("#wo_p_id").val();
	var qty = $("#qtyforwo").val();
	var product_id =  $("#product_id").val();
	var process_id =  $("#process_id").val();
	var edit_id = $("#edit_id").val();
	var unit_id = $("#product_base_unit").val();
	var branch_id = $("#branch_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "add_wo_jobwork_qty",
			qty:qty,
			product_id:product_id,
			process_id:process_id,
			p_id:wo_p_id,
			edit_id:edit_id,
			unit_id:unit_id,
			branch_id:branch_id
		},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;
			if(response1.trim() == "1") {
				
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$("#wo_p_id").select2("val","");
				$("#qtyforwo").val("");
				$("#wo_qty").val("");
				load_wo_jobwork_datatable();
				validate_qty(0);
				$('#workorder_wise_qty_modal').modal('show');
				
			}else if(response1.trim() == "-1") {
				toastr.warning("ALREADY EXISTS", "WARNING");
				return false;
			}
			else if(response1.trim() == "0") {
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
				return false;
			}
		}
	});
}
function validate_qty(qtyforwo){

	var product_qty =  $("#product_base_qty").val();
	var product_id =  $("#product_id").val();
	var p_id =  $("#p_id").val();
	var edit_id = $("#edit_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
		data: { mode : "validate_qty",product_qty:product_qty,product_id:product_id,
		qtyforwo:qtyforwo,edit_id:edit_id,p_id:p_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;

			if(response1.trim() == "0") {
				$("#qtyforwo").val('0')
				toastr.warning("Jobwork Quantity can not greater WORKORDER quantity", "WARNING");
				$(".addbutton").hide();
				return false;
			}else if(response1.trim() == "1") {
				$(".addbutton").show();
			}else{
				$(".addbutton").hide();
			}
		}
	});
}
function get_wo_jobwork_qty(p_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "get_wo_jobwork_qty",
			p_id:p_id
		},
		success: function(response)
		{
			var stock = response.trim();
			$("#wo_qty").val(response);
			Unloading();
			validate_qty(0);
		}
	});
}

function delete_wo_jobwork_entry(job_work_sub_trn_id){

	$.ajax({
		type: "POST",
		url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
		data: { 
			mode : "delete_wo_jobwork_entry",
			job_work_sub_trn_id:job_work_sub_trn_id
		},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;
			if(response1.trim() == "1") {
				toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
				load_wo_jobwork_datatable();			
				open_workorder_wise_jobwork_qty();	
			}
			else if(response1.trim() == "0") {
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
				return false;
			}
			validate_qty(0);
		}
	});
}


function jobwork_shortclose(job_work_id){
	Swal.fire({
			title: 'Are you want to short close jobwork ?',
		  // text: "You won't be able to revert this!",
		  icon: 'question',
		  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
		  customClass: {
		    icon: 'no-border'
		  }*/
		  showCancelButton: true,
		  confirmButtonColor: '#5cb85c',
		  cancelButtonColor: '#d9534f',
		  cancelButtonText: 'No',
		  confirmButtonText: 'Yes',
		  allowOutsideClick: false,
		  allowEscapeKey : false,
		  
		}).then((result) => {
			if (result.isConfirmed) {
				Loading();
					$.ajax({
						type: "POST",
						url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
						data: { 
							mode : "jobwork_shortclose",
							job_work_id:job_work_id
						},
						success: function(response)
						{
							if(response.trim() == '1'){
								toastr.success("JOBWORK SUCCESSFULLY SHORT CLOSED!", "SUCCESS");
							}else{
								toastr.warning("SOMETHING WENT WRONG", "WARNING");
							}	
							Unloading();
							datatable1.fnReloadAjax();
						}
					});
			}
		});
	
}



function revert_jobwork_shortclose(job_work_id){
	Swal.fire({
			title: 'Are you want to revert jobwork ?',
		  // text: "You won't be able to revert this!",
		  icon: 'question',
		  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
		  customClass: {
		    icon: 'no-border'
		  }*/
		  showCancelButton: true,
		  confirmButtonColor: '#5cb85c',
		  cancelButtonColor: '#d9534f',
		  cancelButtonText: 'No',
		  confirmButtonText: 'Yes',
		  allowOutsideClick: false,
		  allowEscapeKey : false,
		  
		}).then((result) => {
			if (result.isConfirmed) {
				Loading();
					$.ajax({
						type: "POST",
						url: root_domain+ production_domain+'app/pending_jobwork_list_new/',
						data: { 
							mode : "revert_jobwork_shortclose",
							job_work_id:job_work_id
						},
						success: function(response)
						{
							if(response.trim() == '1'){
								toastr.success("JOBWORK SUCCESSFULLY SHORT CLOSED!", "SUCCESS");
							}else{
								toastr.warning("SOMETHING WENT WRONG", "WARNING");
							}	
							Unloading();
							datatable1.fnReloadAjax();
						}
					});
			}
		});
	
}