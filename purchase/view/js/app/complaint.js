//var datatable;
$(document).ready(function() {
	load_complaint_datatable();
	refresh_complaint_data();
	get_first_bom();
	//get_product_tree();
	show_data();
	$('#sp_part_status').val('4');
	// validate vendor add form on keyup and submit
	$("#complaint_add").validate({
		rules: {
			complaint_no: {
				required: true			
			},
			complaint_date: {
				required: true			
			},
			cust_id: {
				required: true			
			},
			complaint_type_id: {
				required: true			
			}
		},
		messages: {
			complaint_no: {
				required: "Enter Complaint No"
			},
			complaint_date: {
				required: "Enter Complaint Date"
			},
			cust_id: {
				required: "Choose Customer"
			},
			complaint_type_id: {
				required: "Choose Complaint Type"
			}
		}
	}); 
});

$(".btn_close").click(function() {
	$("label.error").hide();
});
$("#complaint_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	

//Amish Soni 22-09-2020
var isValidSp = false;
$('#basic .form-control').each(function() {
	var attrReq = $(this).attr('required');
	var attrId = $(this).attr('id');
	var appendEl = '';
	if ( $(this).val() == '' && (typeof attrReq !== typeof undefined && attrReq !== false)) {
		$('#'+attrId).focus();
		isValidSp = true;
	}
});

if(isValidSp) {
	return false;
}
//
	
var test_qty = 0
$("input[name^='cntrow[]']").each(function() { 
    test_qty +=parseInt($(this).val(), 10)  
});
if(test_qty=='0'){
    toastr.warning("Choose at least One Product", "WARNING");
    return false;
}
	
	if (!$("#complaint_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#submit').prop("disabled",true);
	
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/complaint/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			//alert(response);
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("COMPLAINT ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'complaint_list';
			}
			else if(responsevalue.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
				$("#bs-example-modal-lg").modal("hide");
				$('#complaint_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update') {
				toastr.success("COMPLAINT UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'complaint_list';		
			}
			//Amish Soni 04-09-2020
			else if(responsevalue.trim() == '2') {
				toastr.error("Close all previous complaints to create new complaint for this same client and same product", "ERROR");
				$("#bs-example-modal-lg").modal("hide");
				$('#complaint_add').trigger('reset');
				Unloading();
			}
			$('#complaint_add').trigger('reset');	
			$('#submit').prop("disabled",false);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_complaint(id) 
{
	
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("COMPLAINT DELETE SUCCESSFULLY", "SUCCESS");
					load_complaint_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function add_field(){
	if(!$("#product_id").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#product_id').select2('focus');
		return false;
	}
	else if($("#comp_pro_sts").val()=='2') {
		if($("#comp_amount").val()=='' || parseFloat($("#comp_amount").val())=='0'){
			toastr.warning("Enter Amount", "ERROR");
			$('#comp_amount').focus();
			return false;
		}
	}
	else if($("#paid_to_free").val()=='1' && $("#comp_pro_sts").val()=='1') {
		if($("#comp_remark").val()==''){
			toastr.warning("Enter Remark", "ERROR");
			$('#comp_remark').focus();
			return false;
		}
	}
	/*else if(!$("#model_id").val()) {
		toastr.warning("Select Model", "ERROR");
		$('#model_id').select2('focus');
		return false;
	}*/
	
	var conf_form = {
		mode:'fieldadd',
		product_id:$("#product_id").val(),
		comp_pro_sts:$("#comp_pro_sts").val(),
		comp_remark:$("#comp_remark").val(),
		comp_amount:$("#comp_amount").val(),
		complaint_id:$("#eid").val(),
		edit_id:$("#edit_id").val()
	};
	
	$('#addrow').prop("disabled",true);
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: conf_form, 
		success: function(response)
		{
			//console.log(response);
			$("#product_id").select2("val","");
			$("#model_id").select2("val","");
			//$("#comp_pro_sts").select2("val","");
			$('#edit_id').val('');
			$('#comp_amount').val('');
			$('#addrow').val('Add');
			$("#addrow").attr("disabled", true);
			$("#comp_remark_div").hide();
			$("#comp_remark").val('');
			Unloading();
			show_data();
			get_first_bom();
		}
	});
} 

function get_first_bom()
{
	var comp_id=$('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"get_fist_bom",comp_id:comp_id },
		success: function(resp){
			//console.log(resp);
			var rel=JSON.parse(resp);
			$('#bom_first_id').val(rel.bom);				
			$('#product_first_id').val(rel.comp);				
			Unloading();
		}	
	});
}

function show_data() {
	var eid = $('#eid').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"load_product_trn_data", complaint_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#complaint_pro_data').html(resp);
			get_first_bom();
			Unloading();
		}	
	});
}

function refresh_complaint_data()
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"refresh_complaint_data"},
		success: function(resp){
			//console.log(resp);
			//$('#complaint_pro_data').html(resp);
			Unloading();
			show_data();
		}	
	});
}

function edit_data(complaint_trn_id)
{
	var cust_id= $('#cust_id').val();
	if(!cust_id){
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
		return false; 
	}
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "preedit", complaint_trn_id:complaint_trn_id, cust_id:cust_id },
		success: function(response)
		{
			//console.log(response);
			var resp = jQuery.parseJSON(response);
			$("#product_id").html(resp.pro_resp_html);
			$("#product_id").select2("val",resp.product_id);
			$("#model_id").html(resp.model_resp_html);
			$("#model_id").select2("val",resp.model_id);
			$("#comp_pro_sts").select2("val",resp.comp_pro_sts);
			$("#edit_id").val(complaint_trn_id);
			$("#comp_amount").val(resp.comp_amount);
			$('#addrow').val('Update');
			$("#addrow").attr("disabled",false);
			$("#comp_remark").val(resp.comp_remark);
			if(resp.comp_pro_sts == '1')
			{
				$("#comp_remark_div").show();
			}
			Unloading();
		}
	});
}

function delete_data(complaint_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode:"delete_data", complaint_trn_id:complaint_trn_id },
			success: function(response)
			{
				console.log(response)
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					$("#addrow").attr("disabled",false);
					show_data();
					setTimeout(function(){ $('#radioBtn_no').click()}, 10);
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function load_complaint_datatable(){
	
	var date= $('#rep_date').val();
	var fol_status= $('#follow_id').val();
	var f_type= $('#f_type').val();
	var fil_followup_status= $('#fil_followup_status').val();
	var fil_followup_type= $('#fil_followup_type').val(); // -ametr
	var emp_id= $('#emp_id').val();
	
	datatable = $("#complaint-table").dataTable({
		//Amish Soni 04-09-2020
		"bStateSave": true,
		
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
		"aLengthMenu": [[10, 30, 50, 250], [10, 30, 50, 250]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/complaint/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "fol_status", "value": fol_status },{ "name": "f_type", "value": f_type }, { "name": "fil_followup_status", "value": fil_followup_status }, { "name": "fil_followup_type", "value": fil_followup_type }, { "name": "emp_id", "value": emp_id } ); // -ametr
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}	 
function load_complaint_no()
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"load_complaint_no" },
		success: function(resp){
			//console.log(resp);
			var no = jQuery.parseJSON(resp);
			$('#complaint_no').val(no.invoiceno);
		}
	});
}
function load_model_service_status(){
	var cust_id= $('#cust_id').val();
	var product_id= $('#product_id').val();
	var complaint_date= $('#complaint_date').val();
	if(!cust_id){ 
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	else if(!product_id){ 
		toastr.warning("Select Product", "ERROR");
		$('#product_id').select2('focus');
		return false;
	}
	
	else if(!complaint_date){
		toastr.warning("Select Date", "ERROR");
		$('#complaint_date').focus();
		return false;
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"load_model_service_status", product_id:product_id, cust_id:cust_id , complaint_date:complaint_date },
		success: function(response){
			console.log(response); 
			var resp = JSON.parse(response);
			//alert(resp.ser_end_dt);
			if(resp.ser_sts=='2'){
				$('#comp_pro_sts').select2('val','2');
				$("#comp_amount").prop("readonly", false);
				$('#comp_remark_div').hide();
				$('#comp_remark').attr("required", "false");
				$('#comp_remark').val('');
				$('#paid_to_free').val('1');
			}
			else{
				$('#comp_pro_sts').select2('val','1');
				$("#comp_amount").prop("readonly", true);
				$('#paid_to_free').val('0');
				// $('#comp_remark_div').show();
				// $('#comp_remark').attr("required", "true");
				// $('#comp_remark').val('').focus();
			}
			Unloading();
		}
	});
} 
function load_cust_sold_pro(cust_id){
	if(cust_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode:"load_cust_sold_pro", cust_id:cust_id },
			success: function(response)
			{
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				$('#product_id').html(resp.pro_resp_html);
				$('#product_id').select2("val","");
				Unloading();
			}
		});
	}
	else{
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
	}
}
function load_cust_prowise_model(product_id){
	var cust_id= $('#cust_id').val();
	if(!cust_id){
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
		return false; 
	}
	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode:"load_cust_prowise_model", product_id:product_id, cust_id:cust_id },
			success: function(response)
			{
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				$('#model_id').html(resp.model_resp_html);
				$('#model_id').select2("val",""); 
				Unloading();
			}
		}); 
	}
}
function add_complain_status(complain_id,followup_status){
	//alert(complain_id);
	if(!complain_id){
		toastr.info("Please Select Complain First !!!", "INFO");
		return false;
	}
	//alert(followup_status);
	//var fstat = $('#complain_btn').attr('data-fstat');
	//alert(fstat);
	
	//var data='fstat='+ followup_status+'&mode=getDataComplain';
	//alert(data);
	$.ajax({
		
		type : 'POST',
		url : root_domain+'app/complaint/', //Here you will fetch records 
		data: { mode:"get_complain_data", fstat:followup_status }, //Pass $id
		success : function(response){
			//var resp = jQuery.parseJSON(response);
			//alert(response);
			$('#fstat_action').html(response);
		}
	})
	$('#modal-complain-add').modal('show');
	$('#comp_id_hid').val(complain_id);
	
}

$("#comp_status_add").validate({
	//ignore: "",
	rules: {
		change_status: {
			required: true			
		},
		n_spart:{
			required: function(element){
				
				var change_status = $.trim($('#change_status').val());
				return change_status.length > 0 && change_status=='5';
				//return $("#change_status").val().length > 0;
			}
		},
		
		req_sp_count:{
			required:true
		},
		service_charge:{
			
			required: function(element){
				
				var change_status = $.trim($('#change_status').val());
				return change_status.length > 0 && change_status=='4';
				//return $("#change_status").val().length > 0;
			}
		},
		c_amount:{
			
			required: function(element){
				
				var change_status = $.trim($('#change_status').val());
				return change_status.length > 0 && change_status=='4';
				//return $("#change_status").val().length > 0;
			}
		},

	},
	messages: {
		f_action: {
			required: "Select Status"
		},
		n_spart:{
			required: "Select Part Status"
		},
		c_amount:{
			required: "Enter Amount"
		},
		
		req_sp_count:{
			required: "Please Enter At Least One Requested Part"
		}
	}
}); 

$("#comp_status_add").on('submit',function(e) {

	//Count Required row for Operator Details
	var operator_cnt=(document.getElementsByName('operator_cnt[]'));
	var operator_cnt_len=operator_cnt.length;

	if (!$("#comp_status_add").valid()) {
		return false;
	}
	if($("#change_status").val()=='4') {
		if($("#f_remark").val()==''){
			toastr.warning("Enter Remark", "ERROR");
			$('#f_remark').focus();
			return false;
		}
		if($("#cust_fb_id").val()==''){
			toastr.warning("Select Customer Satisfaction Level", "ERROR");
			$('#cust_fb_id').focus();
			return false;
		}
	}
	if($('#change_status').val()!='8'){//check only if not remark mode
		if(operator_cnt_len<1){
			toastr.warning("Set Operator Details!!!", "ERROR");
			$('#get_operator_detail_btn').focus();
			return false;
		}
	}
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#submit').prop("disabled",true);
	
	var form_data=new FormData(this);
	
	//form_data.append('file', $('#file').prop('files')[0]);
	
	//alert(s_image);
/*	
	var form_data = {
		f_action: $("#change_status").val(),
		f_remark: $("#f_remark").val(),
		f_emp: $("#f_emp").val(),
		complain_id: $("#comp_id_hid").val(),
		cust_id_hid: $("#cust_id_hid").val(),
		c_amount: $("#c_amount").val(),
		c_amount_old: $("#c_amount_old").val(),
		complaint_date: $("#complaint_date").val(),
		pay_status: $("#pay_status").val(),
		accountid: $("#accountid").val(),
		old_sp_part_status: $("#old_sp_part_status").val(),
		sp_part_close_status: $("#sp_part_close_status").val(),
		service_charge : $('input[name=service_charge]:Checked').val(),
		n_spart : $('input[name=n_spart]:Checked').val(),
		s_image: $('#file').prop('files')[0],
		mode:'add_complain_status',
		is_ajax: 1
	};	
    alert(form_data.s_image); */
		
	$.ajax({
		cache:false,
		url: root_domain+'app/complaint/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.res;
			//alert(msg.res);
			if(msg.trim() == '1') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+'complaint_list';
				//load_complaint_datatable();
				//$("#modal-complain-add").modal("hide");
			}
			else if(msg.trim() == '2') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS");
				$("#modal-complain-add").modal("hide");
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.error("PLEASE ENTER AT LEAST ONE OLD SPARE PART", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#comp_status_add').trigger('reset'); 	
			$('#submit').prop("disabled",false);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function ShowEmployee(emp)
{
	//alert(emp);
	if(emp==2 || emp==3)
	{
		$("#emp_detail").show();
	}
	else
	{
		$("#emp_detail").hide();
	}
}

function view_complain_history(comp_id){
	if(!comp_id){
		toastr.info("Please Select Complain First !!!", "INFO");
		return false;
	}
	
	$('#modal-complain-history').modal('show');
	$('#comp_id').val(comp_id);
	show_complain_history_datatable();
	show_complain_detail_datatable();
}

function show_complain_history_datatable(){
	
	var comp_id=$('#comp_id').val();
	//alert(comp_id);
	datatable = $("#table-complain-history").dataTable({
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
		"sAjaxSource": root_domain+'app/complaint/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name":"mode", "value":"show_complain_history" },{ "name":"complain_id", "value":comp_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function show_complain_detail_datatable(){
	
	var comp_id=$('#comp_id').val();
	//alert(comp_id);
	datatable = $("#table-complain-view").dataTable({
		"bAutoWidth" : true,
		"bFilter" : false,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bPaginate": false,
		"bInfo": false,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, 30, 50], [5, 10, 20, 30, 50]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+'app/complaint/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name":"mode", "value":"show_complain_view" },{ "name":"complain_id", "value":comp_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function checkCustomerStatus(cid)
{
	$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode : "check_customer_status",  cust_id : cid },
			success: function(resnse)
			{
				//alert(resnse);
				if(resnse=='1')
				{
					$('#cust_status_show').show();
					$('#submit').prop('disabled', true);
				}
				else
				{
					$('#cust_status_show').hide();
					$('#submit').prop('disabled', false);
				}
			}
		});
}

function checkComplainPayment(cid)
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "check_complian_due",  cust_id : cid },
		success: function(resnse)
		{
			//alert(resnse);
			if(resnse>0)
			{
				$('#cust_status_due_show').show();
				$('#submit').prop('disabled', true);
				$('#addrow').prop('disabled', true);
				$('#check_due_div').show();
			}
			else
			{
				$('#cust_status_due_show').hide();
				$('#submit').prop('disabled', false);
				$('#addrow').prop('disabled', false);
				$('#check_due_div').hide();
			}
		}
	});
}

function enable_complain()
{
	if($('#check_due').is(":checked"))
	{
		$('#cust_status_due_show').hide();
		$('#submit').prop('disabled', false);
		$('#addrow').prop('disabled', false);
	}
	else
	{
		$('#cust_status_due_show').show();
		$('#submit').prop('disabled', true);
		$('#addrow').prop('disabled', true);
	}
}

function startComplain(comp_id,emp_id)
{
	//alert(comp_id);
	//alert(emp_id);
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "start_complaint", complaint_id : comp_id, employee_id:emp_id },
		success: function(resp)
		{
			if(resp.trim()=='1'){
				toastr.success("Complaint Started", "SUCCESS");
			}
			else{
				toastr.warning("Close Previous Started Complaint !!!", "WARNING");
			}
			Unloading();
			load_complaint_datatable();
			//$('#f_type').val('1');
		}
	});

}

function generate_report() 
{
	
	var product_id=$("#product_id").val();
	
//	alert(date);
//	alert(emp_id);
	
	if(product_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "generate_report",product_id:product_id},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}
}

function getTotalPayment(pay_status)
{
	//alert(pay_status);
	var comp_id=$('#comp_id_hid').val();
	//alert(comp_id);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "get_total_payment",comp_id:comp_id},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			//$('#c_amount').val(response);
			$('#c_amount_old').val(response);
										
		}
	});
}

function show_hide_payment_field(pid)
{
	if(pid=='0')
	{
		$('#s_pmode').hide();
		$('#s_damt').hide();
		$('#s_hamount').hide();
	}
	else
	{
		$('#s_pmode').show();
		$('#s_damt').show();
		$('#s_hamount').show();
	}
}

function get_product_tree(type)
{
	var product_first_id=$('#product_first_id').val();
	if(!product_first_id){
		$('#product_id').select2('focus');
		$('#basic').html('');
		if(type=='yes') {
			toastr.warning("Please Add Complaint Product!!!", "WARNING");
			setTimeout(function(){ $('#radioBtn_no').click()}, 10);
		}
	}
	else{
		if(type=='yes')
		{
			var bom_id=$('#bom_first_id').val();
			var eid=$('#eid').val();
			
			//alert(product_first_id);
			
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/complaint/',
				data: { mode : "get_complaint_tree",bom_id:bom_id,product:product_first_id,eid:eid },
				success: function(response)
				{
					$('#basic').html(response);
					Unloading();
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true,
					});
				}
			});
		
		}
		else{
			$('#basic').html('');
		}
	}
	
	
	/*$("#basic").treejs({
		url      : root_domain+'treejs_data1.php?bom_id='+bom_id+'&product='+product_first_id,
		sourceType  : 'html',    // html, json
		initialState: 'open',    // open, close
		
	});*/

}

function change_amt_text(ser_status)
{
	//alert(ser_status);
	if(ser_status==2)
	{
		$("#comp_amount").prop("readonly", false);
		$('#comp_remark_div').hide();
		$('#comp_remark').attr("required", "false");
		$('#comp_remark').val('');
	}
	else
	{
		$("#comp_amount").prop("readonly", true);
		$('#comp_remark_div').show();
		$('#comp_remark').attr("required", "true");
		$('#comp_remark').val('').focus();
	}
}

function hide_ass_emp(x)
{
	//alert(x);
	if(x=='yes')
	{
		$('#show_assign_div').show();
	}
	else
	{
		$('#show_assign_div').hide();
	}
	$('#change_status').val();
	$('#f_emp').select2("val","");
}

function enable_text(chk)
{
	if($('#chk'+chk).is(":checked"))
	{
		$('#qty'+chk).attr('readonly', false);
		$('#rate'+chk).attr('readonly', false);
		$('#cname'+chk).attr('readonly', false);
		$('#cno'+chk).attr('readonly', false);
		$('#cdate'+chk).attr('readonly', false);
		
		$('#sp_free'+chk).attr("required", "true");
		$('#sp_free'+chk).val("paid");
		$('#qty'+chk).attr("required", "true");
		$('#rate'+chk).attr("required", "true");
		//$('#cname'+chk).attr("required", "true");
		//$('#cno'+chk).attr("required", "true");
		//$('#cdate'+chk).attr("required", "true");
		$('#sp_sent'+chk).attr("required", "true");
	}
	else
	{
		$('#qty'+chk).attr('readonly', true);
		$('#rate'+chk).attr('readonly', true);
		$('#cname'+chk).attr('readonly', true);
		$('#cno'+chk).attr('readonly', true);
		$('#cdate'+chk).attr('readonly', true);
		
		$('#sp_free'+chk).attr("required", false);
		$('#sp_free'+chk).val("");
		$('#qty'+chk).attr("required", false);
		$('#rate'+chk).attr("required", false);
		//$('#cname'+chk).attr("required", "false");
		//$('#cno'+chk).attr("required", "false");
		//$('#cdate'+chk).attr("required", "false");
		$('#sp_sent'+chk).attr("required", false);
	}
}

function get_amount_spare(chk)
{
	//alert(chk);
	var qty=Number($('#qty'+chk).val());
	var rate=Number($('#rate'+chk).val());

	var amt=qty*rate;
	$('#amt'+chk).val(amt);
	
	//alert(amt);
}

$("#spare_part_update").on('submit',function(e) {
	
	var c_name=$('#c_name').val();
	var c_no=$('#c_no').val();
	var c_date=$('#c_date').val();
	var c_type=$('#c_type').val();
	var c_remark=$('#c_remark').val();
	var s_id=$('#s_id').val();
	
	Loading();
	$('#save').prop("disabled",true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "update_spare_part",c_name:c_name,c_no:c_no,c_date:c_date,s_id:s_id,c_type:c_type,c_remark:c_remark },
		success: function(response)
		{
			window.location=root_domain+'spare_list_pending';
			Unloading();
			$('#save').prop("disabled",false);
		}
	});
	
});


$("#old_spare_part_update").on('submit',function(e) {
	
	var sc_name=$('#sc_name').val();
	var sc_no=$('#sc_no').val();
	var sc_date=$('#sc_date').val();
	var sc_id=$('#sc_id').val();
	var c_type1=$('#c_type1').val();
	var c_remark1=$('#c_remark1').val();
	
	Loading();
	$('#save').prop("disabled",true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "update_spare_part_old",sc_name:sc_name,sc_no:sc_no,sc_date:sc_date,sc_id:sc_id,c_type1:c_type1,c_remark1:c_remark1 },
		success: function(response)
		{
			window.location=root_domain+'return_old_spare';
			Unloading();
			$('#save').prop("disabled",false);
		}
	});
	
});


function get_operator_detail(comp_id,cust_id)
{
	//alert(cust_id);
	//alert(comp_id);
	
	$.ajax({
		
		type:'POST',
		url: root_domain+'app/complaint_status/',
		data: { mode:"get_operator_detail", comp_id:comp_id,cust_id:cust_id },
		success: function(resp){
			
			$('#modal-operator-detail').modal('show');
			$('#op_comp_id').val(comp_id);
			$('#op_cust_id').val(cust_id);
			show_operator_detail();		
			Unloading();
		}	
	})
}

function show_operator_detail(){
	
	var cust_id=$("#op_cust_id").val(); 
	var comp_id=$("#op_comp_id").val(); 
	
	$.ajax({
		
		type:'POST',
		url: root_domain+'app/complaint_status/',
		data: { mode:"show_operator_detail", comp_id:comp_id,cust_id:cust_id },
		success: function(resp){
			//alert(resp);
			$('#operator_table').html(resp);
		}	
	})
}

function add_operator()
{
	var op_name=$('#op_name').val();
	var op_mobile=$('#op_mobile').val();
	var op_comp_id=$('#op_comp_id').val();
	var op_cust_id=$('#op_cust_id').val();
	//alert($("#edit_id2").val());
	if($("#op_name").val()==""){
		toastr.warning("Enter Operator Name", "ERROR");
		$("#op_name").focus();
		return false;
	}
	if($("#op_mobile").val()==""){
		toastr.warning("Enter Operator Mobile", "ERROR");
		$("#op_mobile").focus();
		return false;
	}
	
	$('#add_operator_btn').prop("disabled",true);
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint_status/',
		data: { mode : "add_operator",edit_id:$("#edit_id2").val(),op_name:op_name,op_mobile:op_mobile,op_comp_id:op_comp_id,op_cust_id:op_cust_id },
		success: function(response)
		{
			
			console.log(response);
			toastr.success("DATA INSERTED SUCCESSFULLY", "SUCCESS");
			$("#op_name").val("");
			$("#op_mobile").val("");
			$("#edit_id2").val("");
			$("#add_operator_btn").val("Add");
			$('#add_operator_btn').prop("disabled",false);
			Unloading();
			show_operator_detail();
			
		}
	});
	
}

function edit_operator(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint_status/',
		data: { mode : "preedit_operator",  id : id },
		success: function(response)
		{
			//alert(response);
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#op_name').val(data.op_name);
			$('#op_mobile').val(data.op_mobile);
			
			$("#edit_id2").val(id);
			$("#add_operator_btn").val("Update");
		
			show_operator_detail();
			Unloading();
		}
	});
}


function delete_operator(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint_status/',
			data: { mode : "delete_data_operator",  eid : id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS");
					show_operator_detail();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				show_operator_detail();				
			}
		});	
	}
}

function showCourierDiv(type)
{
	//alert(type);
	if(type==1)
	{
		$('#c_div1').hide();
		$('#c_div2').hide();
	}
	else
	{
		$('#c_div1').show();
		$('#c_div2').show();
	}
}

function showCourierDiv1(type)
{
	//alert(type);
	if(type==1)
	{
		$('#c_rdiv1').hide();
		$('#c_rdiv2').hide();
	}
	else
	{
		$('#c_rdiv1').show();
		$('#c_rdiv2').show();
	}
}

function load_ledger_detail(lid)
{
	//alert(lid);
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "load_ledger_detail", lid : lid },
		success: function(response)
		{
			//alert(response);	
			var data=JSON.parse(response);
			//alert(data);
			$('#ledger_mobile').val(data.mobile);
			$('#ledger_address').val(data.address);
		}
	});	
}

//Amish Soni 03-09-2020
function sortCloseComplain(comp_id,emp_id)
{
	$('#ModalSortClose #complaint_id').val(comp_id);
	$('#ModalSortClose #employee_id').val(emp_id);
	$("#ModalSortClose").modal("show");
}

//Amish Soni 03-09-2020
$("#FormSortClose").on('submit',function(e) {
	e.preventDefault();
	e.stopPropagation();	
	
	Loading(true);	
	
	var comp_id = $('#ModalSortClose #complaint_id').val();
	var emp_id = $('#ModalSortClose #employee_id').val();
	var remark = $.trim($('#ModalSortClose #remark').val());
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "sortclose_complaint", complaint_id : comp_id, employee_id:emp_id, remark:remark },
		success: function(resp)
		{
			if(resp.trim()=='1'){
				toastr.success("Complaint Sort Closed", "SUCCESS");
			}
			else{
				toastr.warning("Error Sort Closing Complaint !!!", "WARNING");
			}
			Unloading();
			load_complaint_datatable();
		},
		complete: function(resp)
		{
			$("#ModalSortClose").modal("hide");
		}
	});
});

//Amish Soni 07-09-2020
function sortCloseSpareParts(comp_id, emp_id, cust_id)
{
	$('#ModalSortCloseSP #complaint_idSP').val(comp_id);
	$('#ModalSortCloseSP #employee_idSP').val(emp_id);
	$('#ModalSortCloseSP #cust_idSP').val(cust_id);
	$("#ModalSortCloseSP").modal("show");
}

//Amish Soni 07-09-2020
$("#FormSortCloseSP").on('submit',function(e) {
	e.preventDefault();
	e.stopPropagation();	
	
	Loading(true);	
	var modalId = '#ModalSortCloseSP';
	var comp_id = $(modalId+' #complaint_idSP').val();
	var emp_id = $(modalId+' #employee_idSP').val();
	var cust_id = $(modalId+' #cust_idSP').val();
	var remark = $.trim($(modalId+' #remarkSP').val());
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode : "sortclose_spareparts", complaint_id : comp_id, employee_id:emp_id, remark:remark, cust_id:cust_id },
		success: function(resp)
		{
			if(resp.trim()=='1'){
				toastr.success("Spare Parts Sort Closed", "SUCCESS");
			}
			else{
				toastr.warning("Error Sort Closing Spare Parts !!!", "WARNING");
			}
			Unloading();
			load_complaint_datatable();
		},
		complete: function(resp)
		{
			$("#ModalSortCloseSP").modal("hide");
		}
	});
});