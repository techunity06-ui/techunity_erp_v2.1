$(document).ready(function() {
	load_inquiry_datatable();
	show_data();
	show_inq_note_data();
	show_inq_attach_data();
	var pro_type = $('#pro_type').val();
	var pro_search = $('#pro_search').val();
	show_lost_reason();
	// load_inquiry_type_product(pro_type,pro_search);
	product_load();
	product_load_pro()
	product_load_pro_l()
	
	if($("#mode").val()=='Add'){
		currency_rate_c();
	}
	get_symbol();
	// validate vendor add form on keyup and submit
	$("#inquiry_add").validate({
		rules: {
			inquiry_date: {
				required: true			
			},
			cust_id: {
				required: true			
			},
			opp_id: {
				required: true			
			},
			task_due_date: {
				required: true
			}
		},
		messages: {
			inquiry_date: {
				required: "Enter Inquiry Date"
			},
			cust_id: {
				required: "Choose Customer"
			},
			opp_id: {
				required: "Choose Stage"
			},
			task_due_date: {
				required: "Choose Task Due Date"
			}
		}
	}); 
}); 

$("#inquiry_add").on('submit',function(e) {
	var inq_id = $('#eid').val();
	var inq_product_required = $('#inq_product_required').val();
	var product = check_product(inq_id);
	if(inq_product_required == '1'){
		if(product === false){		
			toastr.warning("Add Product Please!!", "ERROR");
			$("#product_id").select2('focus');
			return false;
		}
	}
	$("#currency_id").prop("disabled", false);
	$("#currency_rate").prop("disabled", false);
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#inquiry_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	//Hide Form Submit Alert
	setFormSubmitting();
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/inquiry/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("INQUIRY ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain + 'inquiry_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '2') {
				toastr.warning("Add One Product Please!!", "ERROR");
				$("#product_id").select2('focus');
				$('#save').prop('disabled', false);
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("INQUIRY UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location = root_domain + crm_domain + 'inquiry_list';	
			}
			Unloading();
			$('#inquiry_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			////console.log(textStatus, errorThrown);
		}
	});
	
});
function load_inquiry_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var stage_id = $('#stage_id').val();
	var sales_stage_id = $('#sales_stage_id').val();
	var source_id = $('#source_id').val();
	var user_id = $('#user_id').val();
	var branch_id = $('#branch_id').val();
	var country_id = $('#country_id').val();
	var state_id = $('#state_id').val();
	var assign_user_ids = $('#assign_user_id').val();
	var city_id = $('#cityid').val();
	var product_id = $('#category_product_id').val();
	var category_id = $('#category_id').val();
	var sales_stage_cat_id = $('#sales_stage_cat_id').val();

	$("#inquiry-table").dataTable({
		"bStateSave": true,
		"fixedHeader": true,
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/inquiry/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"}, 
				{"name": "user_id", "value": user_id},
				{"name": "stage_id", "value": stage_id},
				{"name": "sales_stage_id", "value": sales_stage_id},
				{"name": "source_id", "value": source_id},
				{"name": "start_date", "value": start_date},
				{"name": "end_date", "value": end_date},
				{"name": "country_id", "value": country_id},
				{"name": "state_id", "value": state_id},
				{"name": "assign_user_id", "value": assign_user_ids},
				{"name": "city_id", "value": city_id },
				{"name": "product_id", "value": product_id },
				{"name": "category_id", "value": category_id },
				{"name": "sales_stage_cat_id", "value": sales_stage_cat_id },
				{"name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_inquiry(inquiry_id,inquiry_no) {
	var r= confirm(" Are you sure, you want to delete '"+inquiry_no+"' ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode : "delete",  inquiry_id : inquiry_id },
			success: function(response)
			{
               	////console.log(response);
               	if(response.trim() == "1") {
               		toastr.success("INQUIRY DELETE SUCCESSFULLY", "SUCCESS");
               		load_inquiry_datatable();
               	}
               	else if(response.trim() == "0") {
               		toastr.warning("SOMETHING WRONG", "WARNING");
               	}	
               	Unloading();						
               }
           });	
	} 
}
function change_inquiry_stage(opp_id){
	if(opp_id){
		if(opp_id === '12'){
			var inquiry_id = $("#eid").val();
			var has_quot = has_quotation(inquiry_id);
			if(has_quot === false){
				if(confirm("Inquiry has no Quotation. Are you sure, you want to Won ?")){
					load_opp_stage_prob(opp_id);
				} else {
					if(inquiry_id){ 
						$.ajax({
							type: "POST",
							url: root_domain + crm_domain + 'app/inquiry/',
							data: { mode:"load_inquiry_data", inquiry_id : inquiry_id },
							success: function(response)
							{
								var resp=jQuery.parseJSON(response);
								$('#opp_id').select2("val",resp.opp_id);
								$('#stage_prob').val(resp.stage_prob);
								
							}
						});
					} else {
						$('#opp_id').val(5);
						$('#stage_prob').val(10);
					}
				}
			}
		}
		else {
			load_opp_stage_prob(opp_id);
		}
	}
	
}
function load_opp_stage_prob(opp_id){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/opp_mst/',
		data: { mode:"load_opp_stage_prob", opp_id:opp_id },
		success: function(response)
		{
			var resp=jQuery.parseJSON(response);
			$('#stage_prob').val(resp.opp_probability);
		}
	});
}
function has_quotation(inquiry_id){
	var has_quot = false;
	if(inquiry_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"has_quotation", inquiry_id : inquiry_id },
			success: function(response)
			{
				if(response > 0){
					has_quot = true;
				} else {
					has_quot = false;
				}
			}
		});
	} else {
		has_quot = false;
	}
	
	return has_quot;
}

function check_product(inquiry_id){
	var has_product = false;
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode:"has_product", inquiry_id : inquiry_id },
		success: function(response)
		{
                ////console.log(response);
                if(response == '0'){
                	has_product = false;
                } else {
                	has_product = true;
                }
            }
        });
	return has_product;
}
function show_lost_reason(){
	var opp_id = $("#opp_id").val();
	if(opp_id === '13'){
		$(".lost_reasons").show();
		$('#inquiry_add').validate({
			rules: {
				reason_id: {
					required: true			
				},
				lost_reason: {
					required: true
				}
			},
			messages: {
				reason_id: {
					required: "Choose Reason"
				},
				lost_reason: {
					required: "Write Reason to close."
				}
			}
		});
	} else {
		$(".lost_reasons").hide();
	}
}
function add_reason_div(){
	var counter = $("#counter").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "add_lost_reason", counter: counter },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$(".lost_reasons:last").after(resp.html);
			$("#reason_id"+counter).select2({width: '100%'});
			counter++;
			$("#counter").val(counter);
		}
	});	
}

function remove_reason_div(obj){
	$(obj).closest( ".lost_reasons" ).remove();
}
function add_inq_attch_field() {
	if(!$("#inq_attch_doc_name").val()){		
		toastr.warning("Enter Document Name", "ERROR");
		$("#inq_attch_doc_name").focus();
		return false;
	}
	if(!$("#inq_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#inq_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_inq_attch_field");
	form_data.append('inquiry_id', $("#eid").val());
	form_data.append('inq_attch_doc_name', $("#inq_attch_doc_name").val());
	form_data.append("inq_attch_file", document.getElementById('inq_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/i	nquiry/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			////console.log(response);
			$("#inq_attch_doc_name").val("").focus();
			$("#inq_attch_file").val("");
			$('#inq_attch_btn').val('Add');
			Unloading();
			show_inq_attach_data();
		}
	});
}
function show_inq_attach_data() {
	var eid = $('#eid').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "show_inq_attach_data", inquiry_id:eid,modee:chkmode },
		success: function(resp){
			////console.log(resp);
			$('#inq_attch_trn_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_inq_attach_data(inq_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"delete_inq_attach_data", inq_attach_id:inq_attach_id },
			success: function(response)
			{
				////console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_inq_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

function add_inq_note_field(){
	
	if(!$("#inq_note_title").val()){		
		toastr.warning("Enter Note Title", "ERROR");
		$("#inq_note_title").focus();
		return false;
	}
	else if(!$("#inq_note_desc").val()){
		toastr.warning("Enter Description", "ERROR");
		$("#inq_note_desc").focus();
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "add_inq_note_field", edit_inq_noteid:$("#edit_inq_noteid").val(),inq_note_title:$("#inq_note_title").val(),inq_note_desc:$("#inq_note_desc").val(),inquiry_id:$("#eid").val() },
		success: function(response)
		{
			////console.log(response);
			$("#inq_note_title").val("");
			$("#inq_note_desc").val("");
			$("#edit_inq_noteid").val("");
			$('#inq_note_btn').html('Add');
			Unloading();
			show_inq_note_data();
		}
	});
}
function show_inq_note_data() {
	var eid = $('#eid').val();
	var modee = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "show_inq_note_data", inquiry_id:eid,chkmode:modee },
		success: function(resp){
			////console.log(resp);
			$('#inq_notes_trn_div').html(resp);
			Unloading();
		}		 
	}); 
}
function edit_inq_note_data(inq_note_id){ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode:"edit_inq_note_data", inq_note_id:inq_note_id },
		success: function(response)
		{
			////console.log(response)
			var resp = jQuery.parseJSON(response);
			$("#inq_note_title").val(resp.inq_note_title);
			$("#inq_note_desc").val(resp.inq_note_desc);
			$("#edit_inq_noteid").val(inq_note_id);
			$('#inq_note_btn').html('Update');
			Unloading();
		}
	});
}
function delete_inq_note_data(inq_note_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"delete_inq_note_data", inq_note_id:inq_note_id },
			success: function(response)
			{
				////console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_inq_note_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function get_amount(){	
	var ratcalfiled=$("#pro_cal_type").val();
	var product_qty = parseFloat($("#"+ratcalfiled).val());
	var product_rate = parseFloat($("#product_rate").val());
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		$("#product_amount").val(product_amount);
	}
	else {
		$("#product_amount").val(0);
	}
	get_gtotal();
}

function get_amount_pop(){	
	var product_qty = parseFloat($("#acc_product_qty").val());
	var product_rate = parseFloat($("#acce_rate").val());
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		$("#acc_amount").val(product_amount);
	}
	else {
		$("#acc_amount").val(0);
	}
	
}

function get_gtotal(){	
	var t=0;
	var input_amount=(document.getElementsByName('amount[]'));
	var cnt=input_amount.length;
	var total=0;
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=parseFloat(total)+parseFloat(t);
	}
	//$("#total").val(parseFloat(total).toFixed(2));
	
	$("#g_total").val(parseFloat(total).toFixed(2));
}

function add_field(){
	
	if($("#aeon_permission").val()==1)
	{	
	if(!$("#cat_id").val()){		
		toastr.warning("Choose Product Category", "ERROR");
		$("#cat_id").select2('focus');
		return false;
	}
	}
	if(!$("#product_id").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val()){
		toastr.warning("Enter Quantity", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if($("#product_qty").val() <= 0){
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#product_rate").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#product_rate").focus();
		return false;
	}
	else if($("#product_rate").val() <= 0){
		toastr.warning("Rate must be greater than 0", "ERROR");
		$("#product_rate").focus();
		return false;
	}
	else if(!$("#currency_id").val()){
		toastr.warning("Choose Currency", "ERROR");
		$("#currency_id").select2('focus');
		return false;
	}
	else if(!$("#currency_rate").val()){
		toastr.warning("Enter Currency Rate", "ERROR");
		$("#currency_rate").focus();
		return false;
	}
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	}

	
	var form_data = { 
		mode : "add_field",
		edit_id:$("#edit_id").val(),
		 
		product_id:$("#product_id").val(), 
		cat_id:$("#cat_id").val(), 
		rcat_id : $("#parent_cat_id").val(),
		pg_id:$("#pg_id").val(), 
		level_id:$("#level_id").val(), 
		product_qty:$("#product_qty").val(), 
		unitid:$("#unitid").val(), 
		
		product_conv_qty : $("#product_conv_qty").val(),
		conv_unit_id : $("#conv_unitid").val(),
		rate_unit : $("#rate_unit_id").val(),
		
		product_rate:$("#product_rate").val(), 
		product_amount:$("#product_amount").val(), 
		product_desc:$("#product_desc").val(), 
		product_spec:$("#product_spec").val(), 
		specification:specification,
		inquiry_id:$("#eid").val(),
		inquiry_type:$("#inquiry_type").val(),
		old_product_id:$("#old_product_id").val(),
		currency_id : $("#currency_id").val(),
		currency_rate : $("#currency_rate").val(),
		product_attr:$('#product_id').find('option:selected').attr('data-type')
	};
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			$("#parent_cat_id").select2("val","");
			
			if(aeon_permission!=1){
				$("#cat_id").select2("val","");
			}
			$("#product_id").select2("val","");
			/*$("#cat_id").select2("val","");*/
			$("#pg_id").select2("val","");
			$("#level_id").select2("val","1");
			
			$("#rate_unit_id").val("");
			
			$("#product_qty").val("");
			$("#product_conv_qty").val("");
			$("#product_qty_hide").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$("#unit_id").val("");
			$("#unit_show").html("");
			$("#convert_unit_show").html("");
			
			$("#product_rate").val("");
			$("#product_amount").val("");
			//$("#product_desc").val("");
			CKEDITOR.instances['product_desc'].setData("");
			CKEDITOR.instances['product_spec'].setData("");
			$("#specification_id").select2("val","");
			//$("#product_spec").val("");
			$("#edit_id").val("");
			
			if(durva_permission==1)
			{
				$("#addrow1").show();
				$("#inq_trn_btn").hide();
				
			}
			else
			{
			$('#inq_trn_btn').html('Add');
			}
			$('#projectItem').css('display','none');
			$('#product_rate').attr('readonly', false);
			$('#bs-batch_wise_stock-modal1').modal('hide');
			Unloading();
			show_data();
			dataget();
		}
	});
}


function show_data() {
	var eid = $('#eid').val();
	var modee = $('#mode').val();
	var mode='';
	if(durva_permission==1){
		mode = "show_data_durva";
	}else{
		mode = "show_data";
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : mode, inquiry_id:eid,modee:modee },
		success: function(resp){
			////console.log(resp);
			$('#inq_trn_div').html(resp);
			Unloading();
			get_amount();
			get_symbol();
			currency_rate_c();
		}		 
	}); 
}
function edit_trn_data(inquiry_trn_id, project_wise){ 

$("#addrow1").hide();
$("#inq_trn_btn").show();

	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode:"edit_trn_data", inquiry_trn_id:inquiry_trn_id, project_wise : project_wise },
		success: function(response)
		{
			//console.log(response);

			if(quotation_rate_fixed=='1'){
				$('#product_rate').attr('readonly', true);
			}
			var resp = jQuery.parseJSON(response);
			
			var curr = '<?php echo $_SESSION["currency_id"]?>';
			var currency_id = $('#currency_id').val();
			//load_product_category_wise(resp.cat_id);
			$("#cat_id").select2("val",resp.cat_id);
			$("#parent_cat_id").select2("val",resp.rcat_id);
			$("#product_id").select2("val",resp.product_id);
			$("#old_product_id").val(resp.product_id);
			$("#product_id").select2('data', { id:resp.product_id, text: resp.product_name});
			$("#cat_id").select2("val",resp.cat_id);
			$("#pg_id").select2("val",resp.pg_id);
			$("#level_id").select2("val",resp.level_id);
			
			$("#unitid").select2("val",resp.unitid);
			$("#conv_unitid").val(resp.conv_unit_id);
			
			$("#product_qty").val(resp.product_qty);
			$("#product_qty_hide").val(resp.product_qty);
			$("#product_conv_qty_hide").val(resp.product_conv_qty);
			$("#product_conv_qty").val(resp.product_conv_qty);

					
			if(curr == currency_id){
				$("#product_rate").val(resp.product_rate);
				$("#product_amount").val(resp.product_amount);
			}else{
				$("#product_rate").val(resp.product_rate_conv);
				$("#product_amount").val(resp.product_amount_conv);
			}
			
			CKEDITOR.instances['product_desc'].setData(resp.product_desc);
			CKEDITOR.instances['product_spec'].setData(resp.product_spec);
			$("#edit_id").val(inquiry_trn_id);
			$('#inq_trn_btn').html('Update');
			if(project_wise=='1'){
				$('#projectItem').css('display','block');
			}
			load_product_unit(resp.product_id, resp.unitid);
			get_hsn(resp.product_id);
			dataget(resp.product_spec_id,resp.product_spec_id_id);
			Unloading();
		}
	});
}
function dataget(product_spec_id,product_spec_id_id){
	
	
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"dataget", product_spec_id:product_spec_id },
			success: function(response)
			{
				//console.log(response);
				
				var data=jQuery.parseJSON(response);
				if(product_spec_id_id)
				{
				$('#specification_id').html(data.res);
				$('#specification_id').select2("val",product_spec_id_id.split(","));
				}
				Unloading();						
			}
		});	
	
}
function delete_trn_data(inquiry_trn_id, project_wise){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"delete_trn_data", inquiry_trn_id:inquiry_trn_id },
			success: function(response)
			{
				////console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				else if(response.trim() == "2") {
					toastr.warning("Please Delete Sub Product", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function load_product_dtls(product_id){

	var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();

	
// 	if(branch_id==''){
// 		toastr.warning("Select branch", "ERROR");
// 		$('#product_id').select2("val",'');
// 		$("#branch_id").focus();
// 		return false;
// 	}

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type,cust_id : $("#cust_id").val()},
			success: function(response)
			{
				////console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				CKEDITOR.instances['product_desc'].setData(resp.product_desc);
				CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if(currency_id != curr){
					rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
				}else{
					rate = resp.product_sale_rate;
				}
				$('#product_rate').val(rate.toFixed(2));
				
				$('#current_stock').css('display', 'block');
				$('#current_stock').html('Current Stock: '+resp.current_stock);
				load_product_unit(product_id, resp.product_base_unit);
				Unloading();						
				if(inquiry_type!=2){
					$('#projectItem').css('display','none');
				}else{
					$('#projectItem').css('display','block');
					//add_project_data();
					
				}
				
				if(durva_permission==1)
				{
					add_accessories_data();
				}
			}
		});	
	}
	
	// var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	// var branch_id = $('#branch_id').val();
	// var inquiry_type = $('#inquiry_type').val();
	// var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	// var currency_id	 = $('#currency_id').val();
	// var currency_rate = $('#currency_rate').val();
	
	// if(branch_id==''){
	// 	toastr.warning("Select branch", "ERROR");
	// 	$('#product_id').select2("val",'');
	// 	$("#branch_id").focus();
	// 	return false;
	// }

	// if(product_id){
	// 	Loading();
	// 	$.ajax({
	// 		type: "POST",
	// 		url: root_domain + crm_domain + 'app/inquiry/',
	// 		data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type,cust_id : $("#cust_id").val()},
	// 		success: function(response)
	// 		{
	// 			////console.log(response);
	// 			if(quotation_rate_fixed=='1'){
	// 				$('#product_rate').attr('readonly', true);
	// 			}
	// 			var resp=jQuery.parseJSON(response);
	// 			var rate=0;
	// 			var curr = '<?php echo $_SESSION["currency_id"]?>';
	// 			////console.log(resp.product_sale_rate);
	// 			CKEDITOR.instances['product_desc'].setData(resp.product_desc);
	// 			CKEDITOR.instances['product_spec'].setData(resp.product_spec);
	// 			if(currency_id != curr){
	// 				rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
	// 			}else{
	// 				rate = resp.product_sale_rate;
	// 			}
	// 			$('#product_rate').val(rate.toFixed(2));
				
	// 			$('#current_stock').css('display', 'block');
	// 			$('#current_stock').html('Current Stock: '+resp.current_stock);
	// 			load_product_unit(product_id, resp.product_base_unit);
	// 			Unloading();						
	// 			if(inquiry_type!=2){
	// 				$('#projectItem').css('display','none');
	// 			}else{
	// 				$('#projectItem').css('display','block');
	// 				//add_project_data();
					
	// 			}
				
	// 			if(durva_permission==1)
	// 			{
	// 				add_accessories_data();
	// 			}
	// 		},
	// 		// error: function(xhr, status, error) {
	// 		// 	console.error('AJAX Error: ' + status + error);
	// 		// }
	// 	});	
	// }
}


//Maulik Start
function load_product_unit(product_id,edit_unit){
	if(product_id){

	}else{
		var product_id=$("#product_id").val();
	}
	if(edit_unit){

	}else{
		var edit_unit=$("#rate_unit_id").val();
	}
	//alert(product_id);
	if(product_id)//tax calculation on total 
	{

		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ crm_domain +'app/inquiry/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				/*console.log(response);*/
				var obj=jQuery.parseJSON(response);
				//alert(obj.qye);
				$("#rate_unit_id").html(obj.unit_option);
				//alert(edit_unit);
				if(edit_unit!="0"){
					//alert(edit_unit);
					$("#rate_unit_id").val(edit_unit);
					if(obj.product_base_unit===edit_unit){
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_unit_block").show();
							$("#convert_unit_block").show();
							$("#product_conv_qty").attr("readonly","readonly");
							$("#product_qty").removeAttr("readonly","readonly");
						}else{
							$("#convert_unit_block").hide();
						}
						$("#pro_cal_type").val("product_qty_hide");
					}else{
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_unit_block").show();
							$("#product_qty").attr("readonly","readonly");
							$("#product_conv_qty").removeAttr("readonly","readonly");
							$("#convert_unit_block").show();
						}else{
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}
				}else{
					$("#base_unit_block").show();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#product_conv_qty").removeAttr("readonly","readonly");
					$("#convert_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
				}

				$('#unitid').val(obj.product_base_unit);
				$('#conv_unitid').val(obj.product_conv_unit);
				
				$('#unit_show').html(obj.base_unit_name);
				$('#convert_unit_show').html(obj.convert_unit_name);
				get_amount();
				/*$("#convert_unit_block").show();
				if(obj.unit_status==="1"){
					$("#convert_unit_block").show();
				}else{
					$("#convert_unit_block").hide();
				}*/
			}
		});
	}
} 
//Maulik End

function copy_inq_name(inq_name_using_comapany) {
	
	if($('#cust_id').val())
	{
		if(inq_name_using_comapany=='1')
		{
			$('#inquiry_name').val("@"+$('#cust_id option:selected').text());
		}	
		
	}
	//Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "get_cust_territory", cust_id:$('#cust_id').val() },
		success: function(response)
		{
			////console.log(response);
			var resp=JSON.parse(response);
			$('#t_id').select2('val',resp.t_id);
			//Unloading();
		}
	});
}
function view_followup_hist(inquiry_id){
	if(inquiry_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode : "view_followup_hist", inquiry_id:inquiry_id },
			success: function(response)
			{
				////console.log(response);
				var resp=JSON.parse(response);
				$('#preview_flp_hist_modal').modal('show');
				$('#preview_flp_hist_div').html(resp.html_resp);
				$('#preview_flp_hist_inq_name').html(resp.inq_name);
				Unloading();
			}
		});
	}
}
function open_add_task_popup(inquiry_id, entry_type){
	if(inquiry_id){
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/add_task_popup/add_task_popup',
			data: { inquiry_id : inquiry_id, entry_type : entry_type },
			success: function(response)
			{
				$('#task_model_popup').html(response);
				$('#add_task_modal').modal('show');
			}
		});
	}
}

function open_inq_email(inquiry_id,cust_id){
	$('#send_email_modal').modal("show");
	$('#email_ref_id').val(inquiry_id);
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "open_inq_email", inquiry_id:inquiry_id, cust_id:cust_id },
		success: function(response)
		{
			////console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#to_email_id').val(obj.to_email_id);
			$('#ccemail_id').val("");
			$('#bccemail_id').val("");
			CKEDITOR.instances['email_content'].setData(obj.email_content)
			$('#email_subject').val("Thank You For Your Inquiry");
			Unloading();
		}
	});
}

$("#send_email_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#send_email_add").valid()) {
		return false;
	} 
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#send_mail_btn').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/inquiry/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			////console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("MAIL SENT SUCCESSFULLY", "SUCCESS");
				$('#send_email_modal').modal('hide');
				load_inquiry_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#send_mail_btn').prop('disabled', false);
			$('#send_email_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			//console.log(textStatus, errorThrown);
		}
	});
	
});

function preview_cust_person(){
	var cust_id = $('#cust_id').val();
	if(!cust_id){
		toastr.warning("Choose Customer!!!", "ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain + 'app/customer/',
		data: { mode : "preview_cust_person", cust_id:cust_id },
		success: function(response)
		{
			////console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#preview_cust_person_modal').modal("show");
			$('#preview_cust_person_modal_div').html(obj.html_resp);
			Unloading();
		}
	});
}
function addcustomer(){
	branch_id = $('#branch_id').val();
	if(!branch_id){
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$('#bs-example-modal-lg').modal('show');
	$('#bran').val(branch_id);
}
function preview_cust_dtls(){
	var cust_id = $('#cust_id').val();
	if(cust_id){
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + administration_domain + 'app/customer/',
			data: { mode:"preview_cust_dtls", cust_id:cust_id },
			success: function(response)
			{
				////console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#preview_cust_dtls_modal1').modal('show');
				$('#preview_cust_dtls_div').html(obj.html_resp);
				$('#preview_cust_pro_div').html(obj.row);
				Unloading();
			}
		});
	} else {
		toastr.warning("Select Company First", "ERROR");
	}
}

/*
Code By Umair : 23-06-2021
Comment: Load Product Based On the Inquiry Type
START
*/
function load_inquiry_type_product(type,pro_search){
	var inquiry_type = $('#inquiry_type').val();
	$('#projectItem').css('display','none');
	if(inquiry_type){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"load_inquiry_type_product", inquiry_type:inquiry_type, pro_type: type , pro_search:pro_search},
			success: function(response)
			{
				////console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_id').empty().append(obj.product_list);
				$("#product_id").select2({
					width: '100%'
				});
				Unloading();
			}
		});
	}	
}

function load_project_item(){
	/*var branch_id = $('#branch_id').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}*/
	$('#add_project_wise_item_modal').modal('show');
	
	/*var eid = $('#eid').val();
	if(eid==''){
		add_project_data();
	}*/
	show_project_data();
}

function load_accessories_item(){
	/*var branch_id = $('#branch_id').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}*/
	$('#add_project_wise_item_modal').modal('show');
	
	/*var eid = $('#eid').val();
	if(eid==''){
		add_project_data();
	}*/
	show_accessories_data();
}

function add_accessories_data()
{
	
	var inquiry_type = $('#inquiry_type').val();
	var product_id = $('#product_id').val();
	
	var eid = $('#eid').val();
	var branch_id = $('#branch_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "add_accessories_data",product_id:product_id, inquiry_type : inquiry_type,eid : eid, branch_id : branch_id },
		success: function(data){
		//console.log(data);
		}		
	});
}


function add_project_data()
{
	////alert("add");
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	
	var eid = $('#eid').val();
	var branch_id = $('#branch_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "add_project_data",project_assign_id:project_assign_id, inquiry_type : inquiry_type,eid : eid, branch_id : branch_id },
		success: function(data){
		//console.log(data);
		}		
	});
}

function show_project_data()
{
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "load_tempoutward",project_assign_id:project_assign_id, inquiry_type : inquiry_type,eid : eid },
		success: function(data){
			$('#sale_productdata').html(data);				
		}		
	});
}
function show_accessories_data()
{
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "load_accessories_tempoutward",project_assign_id:project_assign_id, inquiry_type : inquiry_type,eid : eid },
		success: function(data){
			$('#accessories_productdata').html(data);				
		}		
	});
}

function add_project_field(){
	if($("#project_product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#project_product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	if($("#project_product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if($("#branch_id").val()==="")
	{		
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	Loading();	
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "add_project_field",
		edit_id:$("#project_edit_id").val(),
		inquiry_trn_id:$("#edit_id").val(),
		product_id:$("#project_product_id").val(),
		product_des:$("#project_product_des").val(),
		product_spec:$("#project_product_spec").val(),
		product_hsn_code:$("#project_product_hsn_code").val(),
		product_qty:$("#project_product_qty").val(),
		product_rate:$("#project_product_rate").val(),
		project_assign_id:$("#product_id").val(),
		inquiry_type:$("#inquiry_type").val(),
		branch_id:$("#branch_id").val(),
		formulaid:$("#project_formulaid").val(),
		inquiry_id:$('#eid').val()
	},
	success: function(response)
	{
		$("#project_product_id").select2("val","")
		$("#project_product_des").val("")
		$("#project_product_spec").val("")
		$("#project_product_hsn_code").val("")
		$("#project_product_qty").val("")
		$("#project_product_rate").val('')
		$("#project_edit_id").val('')
		$('#project_addrow').val('Add');
		$("#project_formulaid").val("");
		Unloading();
		show_project_data();
	}
});
}
function load_productdetail(val) {
	/*if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}*/
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			CKEDITOR.instances['project_product_des'].setData(obj.product_desc);
			CKEDITOR.instances['project_product_spec'].setData(obj.product_spec);	
			$('#project_product_hsn_code').val(obj.product_hsn);
			$('#project_product_rate').val(obj.product_sale_rate);
			$('#formulaid').val(obj.formula_id);	
			
		}
	});
}
function edit_project_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "edit_project_data",  id : id},
		success: function(response)
		{
				////console.log(response)
				var data = jQuery.parseJSON(response);
				$("#project_product_id").select2("val",data.product_id)
				$("#project_product_hsn_code").val(data.product_hsn_code)
				$("#project_product_des").val(data.description)
				$("#project_product_qty").val(data.product_qty)
				$("#project_product_rate").val(data.product_rate)
				$("#project_formulaid").val(data.formulaid);
				$("#project_edit_id").val(id)
				$('#project_addrow').val('Update');
				CKEDITOR.instances['project_product_des'].setData(data.product_desc);
				CKEDITOR.instances['project_product_spec'].setData(data.product_spec);
				Unloading();
			}
		});
}
function delete_project_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/inquiry/',
			data: { mode : "delete_project_data",  eid : id},
			success: function(response)
			{
					////console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_project_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
	}
}

function get_project_amount()
{	
	var product_qty = parseFloat($("#project_product_qty").val());
	var product_rate = parseFloat($("#project_product_rate").val());
	
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		/*$("#product_amount").val(parseFloat(product_amount).toFixed(2));
		$("#product_total").val(parseFloat(product_amount).toFixed(2));*/
		if($("#project_formulaid").val()!="")//tax calculation
		{
			var formulaid=$("#project_formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/inquiry/',
				data: { mode : "get_project_amount", product_amount:product_amount ,formulaid:formulaid },
				success: function(response)
				{
					var obj=jQuery.parseJSON(response);
					//$('#product_total').val(obj.product_total);
				}
			});
		}
	}
	else {
		//$("#product_amount").val(0);
	}
}
function load_product_history(){
	//$('#preview_product_history_modal').modal('show');
	show_product_history_data();
}
function show_product_history_data(){
	var cust_id = $('#cust_id').val();
	var product_id = $('#product_id').val();
	if(product_id && cust_id){
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/inquiry/',
			data: { mode : "load_product_history",product_id:product_id, cust_id:cust_id },
			success: function(data){
				$('#preview_product_history_modal').modal('show');
				$('#preview_product_history_div').html(data);				
			}		
		});
	} else{
		toastr.warning("Select Company and Product First", "ERROR");
	}
}
/* END */
function product_load(){
	
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	var product_category='';
	var cat = '';

	if(comp_config.cat_wise_product_load==1){
		product_category = $("#cat_id").val();
		cat = '&product_category='+product_category;
	}

	if(inquiry_type == 2)
	{
		$('#product_rate').attr('readonly', true);
	}
	else
	{
		
		$('#product_rate').attr('readonly',false);
	}
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search&product_category='+product_category;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product('product_id', testData)	
	// return testData;
}

function load_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
	    // query with pagination
	    query: function(q) {
	    	var pageSize,
	    	results,
	    	that = this;
	      	pageSize = 20; // or whatever pagesize
	      	results = [];
	      	if (q.term && q.term !== '') {
	        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
	        	results = _.filter(that.data, function(e) {
	        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
	        	});
	        } else if (q.term === '') {
	        	results = that.data;
	        }
	        q.callback({
	        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
	        	more: results.length >= q.page * pageSize,
	        });
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}
function get_hsn(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode').text(response);
				$(".hsncode").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode").hide();
				$(".product_stock_label").hide();
				$('#product_id').select2("val","");
				return false;
			}
		}
	});
	
}
function showproduct(){
	$('#modal-add-product').modal('show');
	$("#product_add_type").val('inquiry');
	//$("#ledger_name").focus();
}

function add_hsn_invoice(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_inquiry');
	$("#hsn_name").focus();
}
function getrate(){
	var product_id = $('#product_id').val();
	var unit_id = $('#unitid').val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "getrate",product_id:product_id, unit_id:unit_id},
		success: function(response)
		{
			var data=jQuery.parseJSON(response);
			$('#product_rate').val(data.price);
			get_amount();
		}
	});
}
function no_of_inquiry(inquiry_user_id){
	var user_id = inquiry_user_id.value;
	var inquiry_id = $("#eid").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "no_of_inquiry", user_id:user_id, inquiry_id:inquiry_id },
		success: function(response)
		{
			$('#no_of_inquiry').html("Number of Inquiry = "+response);
			Unloading();
		}
	});
}
function view_acknowledgement(inquiry_id){
	$("#bs-acknowledge-modal-lg").modal('show');
	$("#ac_inq_id").val(inquiry_id);
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "acknowledge_detail", inquiry_id:inquiry_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response);
			$("#type_of_inquiry").select2("val",resp.type_of_inquiry);
			$("#inquiry_project_name").val(resp.inquiry_project_name);
			$("#end_user_details").val(resp.end_user_details);
			$("#scope_of_work").val(resp.scope_of_work);
			$("#payment_terms").val(resp.payment_terms);
			$("#delivery_time").val(resp.delivery_time);
			$("#estimated_timeline_for_closing").val(resp.estimated_timeline_for_closing);
			$("#quotation_required_date").val(resp.quotation_required_date);
			Unloading();
		}
	});
}

function close_acknowledge(){
	$("#bs-acknowledge-modal-lg").modal('hide');	
}

$("#acknowledgement_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#acknowledgement_add").valid()) {
		return false;
	} 
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#submit').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/inquiry/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			////console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("ACKNOWLEDGEMENT ADD SUCCESSFULLY", "SUCCESS");
				$('#bs-acknowledge-modal-lg').modal('hide');
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#submit').prop('disabled', false);
			$('#acknowledgement_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			//console.log(textStatus, errorThrown);
		}
	});
	
});

function view_attach_document(inquiry_id,inquiry_no){
	$('#view_attach_document_modal').modal('show');
	$('#ref_no').html(inquiry_no);
	$('#ref_ord_id').val(inquiry_id);
	load_attach_document();
}

function load_attach_document(){
	var inquiry_id=$('#ref_ord_id').val();
	
	$("#attachments-doc-datatable").dataTable({
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
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/inquiry/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "load_attach_document"},
				{"name": "inquiry_id", "value": inquiry_id});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function get_symbol(){

	$(".sp_cr").remove();
	$(".currency_icon").html('');
	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	/*var rate = $("#currency_id").find(':selected').attr("data-currency-rate");*/
	/*//alert(symbl);*/
	var textt = " ("+symbl+")"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});
	/*$('#currency_rate').val(rate);*/
}
function currency_rate_c(){
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	var inquiry_id = $("#eid").val();
	var is_umaboy = $("#is_umaboy").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "check_product_entry", inquiry_id:inquiry_id },
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.cnt>0){
				$('#currency_id').select2("val",arr.currency_id);
				$('#currency_rate').val(arr.currency_rate);
				$("#currency_id").prop("disabled", true);
				$("#currency_rate").prop("disabled", true);
			}else{
				$('#currency_rate').val(rate);
				if (!is_umaboy){
					$("#currency_id").prop("disabled", false);
				}
				$("#currency_rate").prop("disabled", false);
			}
			get_symbol();
			Unloading();
		}
	});
	
}
//////////////////////////////////////////////////Product load-harshil///////////////////////////////////////////////////////////////////////////////

function load_product_category_wise(product_category){
	////alert(product_category);
	var testData = [];
	var branch_id = $('#branch_id').val();
		
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#cat_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	}

	
	var inquiry_type=$("#inquiry_type").val();
	
	if(inquiry_type == 2)
	{
		$('#product_rate').attr('readonly', true);
	}
	else
	{
		
		$('#product_rate').attr('readonly',false);
	}
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&product_category='+product_category+'&type=crm_pro_type&search=crm_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product('product_id', testData)	
	// return testData;
	// return testData;
	
	
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////Harshil  - 19-9-2022///////////////////////////////////////////////
//Update : harshil - 19-9-2022
function open_batch_wise_qty(){
	
	
	
		load_batch_datatable();
		if($("#product_id").val()==="")
		{		
			toastr.warning("Select Product", "ERROR")
			$("#product_id").select2('focus')
			return false;
		}
		else if($("#product_qty").val()==="")
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#product_qty").focus();
			return false;
		}
		
		var qty=$("#product_qty").val();
		var product_id=$("#product_id").val();
		
		
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain +'app/inquiry/',
			data: { mode : "accessories_model_open",qty:qty,product_id:product_id},
			success: function(response)
			{
				
				var data = jQuery.parseJSON(response);
				
				$('#bs-batch_wise_stock-modal1').modal('show');
				
				$("#batch_data").html(data.html_data);	
				product_load_pro();
					
			CKEDITOR.replace('acc_product_desc', {
                enterMode: CKEDITOR.ENTER_BR
            });
			
				//validate_qty(0);	
			}
		});
	}
	
	
function open_accesorice_wise_product_list(id){
	
	
	////alert(id);
			
		
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain +'app/inquiry/',
			data: { mode : "open_accesorice_wise_product_list",product_id:id},
			success: function(response)
			{
				//alert(response);
				var data = jQuery.parseJSON(response);
				
				$('#bs-batch_wise_stock-modal2').modal('show');
				
				$("#batch_data1").html(data.html_data);	
				product_load_pro_l();
					
			CKEDITOR.replace('acc_product_desc_l', {
                enterMode: CKEDITOR.ENTER_BR
            });
			
				//validate_qty(0);	
			}
		});
	}	
	
	function load_batch_datatable()
{
	
	var product_id=$('#product_id').val();
	
	var edit_id = $("#edit_id").val();
	
	datatable = $("#batch_stock_table").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/inquiry/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_accessories_qty" },
				{ "name": "product_id", "value": product_id },
				{"name":"edit_id","value":edit_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

///////////////////////////////////////////////////////////////////////////Harshil 19-9-2022/////////////////////////////////////////////////////////

function product_load_pro(){
	
	var testData = [];
	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product('acc_product_id', testData)	
	// return testData;
}

function product_load_pro_l(){
	
	var testData = [];
	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product('acc_product_id_l', testData)	
	// return testData;
}


function load_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
	    // query with pagination
	    query: function(q) {
	    	var pageSize,
	    	results,
	    	that = this;
	      	pageSize = 20; // or whatever pagesize
	      	results = [];
	      	if (q.term && q.term !== '') {
	        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
	        	results = _.filter(that.data, function(e) {
	        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
	        	});
	        } else if (q.term === '') {
	        	results = that.data;
	        }
	        q.callback({
	        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
	        	more: results.length >= q.page * pageSize,
	        });
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}

function add_accessories_product_pop()
{
	

	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	
	if($("#acc_product_id").val()==="")
	{		
		toastr.warning("Select Product Id", "ERROR");
		$("#acc_product_id").select2("focus");
		return false;
	}
if($("#acc_product_qty").val()==="")
	{		
		toastr.warning("Enter Product Qty", "ERROR");
		$("#acc_product_qty").val("focus");
		return false;
	}

/* var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	} */

var form_data = { 
		mode : "add_accessories_product_pop",
		edit_id:$("#edit_id_accessories").val(),
		acc_product_id:$("#acc_product_id").val(), 
		pid:$("#pid").val(), 
		acc_product_qty:$("#acc_product_qty").val(), 
		acce_rate:$("#acce_rate").val(), 
		acc_amount:$("#acc_amount").val(), 
		acc_product_desc:$("#acc_product_desc").val() 
		//specification:specification
	};

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/inquiry/',
		data: form_data,
		success: function(response)
		{	
			////console.log(response)
			$("#acc_product_id").select2("val","");	
			$("#acc_product_qty").val('');	
			$("#acce_rate").val('');	
			$("#acc_amount").val('');	
			CKEDITOR.instances['acc_product_desc'].setData("");
			$("#edit_id_accessories").val('')
			$("#add_party_purchase").val("Add");
			Unloading();
			load_batch_datatable();
		}
	});
}
function edit_data_accessories_product_pop(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/inquiry/',
		data: { mode : "preedit_accessories_product",  id : id },
		success: function(response)
		{	
			//////console.log(response);
			var data = jQuery.parseJSON(response);
			$("#acc_product_id").select2('data', { id:data.product_id, text: data.product_name});
			$("#acc_product_qty").val(data.qty);
			$("#acce_rate").val(data.acce_rate);
			$("#acc_amount").val(data.acc_amount);
			$("#edit_id_accessories").val(id);
			CKEDITOR.instances['acc_product_desc'].setData(data.product_desc);
			//$("#add_alternative_btn").val("Update");
			Unloading();
			get_hsn_pop(data.product_id);
			load_product_dtls_pop(data.product_id);
		}
	});
}

function delete_data_accessories_product_pop(id)
{
	
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/inquiry/',
			data: { mode : "delete_data_alternative_product_pop",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function add_field_list(){
	
	
	if(!$("#acc_product_id_l").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#acc_product_id_l").select2('focus');
		return false;
	}
	else if(!$("#acc_product_qty_l").val()){
		toastr.warning("Enter Quantityyyyyy", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if($("#acc_product_qty_l").val() <= 0){
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if(!$("#acce_rate_l").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#acce_rate").focus();
		return false;
	}
	else if($("#acce_rate_l").val() <= 0){
		toastr.warning("Rate must be greater than 0", "ERROR");
		$("#acce_rate_l").focus();
		return false;
	}
	else if(!$("#acc_amount_l").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#acc_amount_l").focus();
		return false;
	}
	
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	
	
	var form_data = { 
		mode : "add_field_list",
		product_id:$("#acc_product_id_l").val(), 
		pid:$("#pid_l").val(), 
		product_qty:$("#acc_product_qty_l").val(), 
		product_rate:$("#acce_rate_l").val(), 
		product_amount:$("#acc_amount_l").val(), 
		product_desc:$("#acc_product_desc_l").val(), 
		edit_id : $("#eid").val()
		
	};
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: form_data,
		success: function(response)
		{
			console.log(response);
			$("#acc_product_id_l").select2("val","");
			$("#pid_l").val("");
			$("#acc_product_qty_l").val("");
			$("#acce_rate_l").val("");
			$("#acc_amount_l").val("");
			CKEDITOR.instances['acc_product_desc_l'].setData("");
			$('#bs-batch_wise_stock-modal2').modal('hide');
			Unloading();
			show_data();
			dataget();
		}
	});
}
function load_product_dtls_pop(product_id){
	
	var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	
	/* if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	} */
	

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type,cust_id : $("#cust_id").val()},
			success: function(response)
			{
				////console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				CKEDITOR.instances['acc_product_desc'].setData(resp.product_desc);
				//CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if(currency_id != curr){
					rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
				}else{
					rate = resp.product_sale_rate;
				}
				
				$('#acce_rate').val(rate.toFixed(2));
				//$('#unitid').select2("val",resp.product_base_unit);
				$('#current_stock_pop').css('display', 'block');
				$('#current_stock_pop').html('Current Stock: '+resp.current_stock);
				$('.unit_pop').css('display', 'block');
				$('#unit_pop').html('Unit: '+resp.unit_name);
				Unloading();						
				
				
					
			}
		});	
	}
}
function get_hsn_pop(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode_pop').text(response);
				$(".hsncode_pop").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode_pop").hide();
				
				$('#acc_product_id').select2("val","");
				return false;
			}
		}
	});
	
}
function load_product_dtls_pop_list(product_id){
	
	var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	
	/* if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	} */

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type,cust_id : $("#cust_id").val()},
			success: function(response)
			{
				////console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				CKEDITOR.instances['acc_product_desc_l'].setData(resp.product_desc);
				//CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if(currency_id != curr){
					rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
				}else{
					rate = resp.product_sale_rate;
				}
				
				$('#acce_rate_l').val(rate.toFixed(2));
				//$('#unitid').select2("val",resp.product_base_unit);
				$('#current_stock_pop_l').css('display', 'block');
				$('#current_stock_pop_l').html('Current Stock: '+resp.current_stock);
				$('.unit_pop_l').css('display', 'block');
				$('#unit_pop_l').html('Unit: '+resp.unit_name);
				Unloading();						
				
				
					
			}
		});	
	}
}
function get_hsn_pop_list(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode_pop_l').text(response);
				$(".hsncode_pop_l").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode_pop_l").hide();
				
				$('#acc_product_id_l').select2("val","");
				return false;
			}
		}
	});
	
}
function get_amount_pop_list(){	
	var product_qty = parseFloat($("#acc_product_qty_l").val());
	var product_rate = parseFloat($("#acce_rate_l").val());
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		$("#acc_amount_l").val(product_amount);
	}
	else {
		$("#acc_amount_l").val(0);
	}
	
}
function hard_delete_inquiry(inquiry_id,inquiry_no) {
	var r= confirm(" Are you sure, you want to Hard Delete '"+inquiry_no+"' ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode : "hard_delete",  inquiry_id : inquiry_id },
			success: function(response)
			{
               	////console.log(response);
               	if(response.trim() == "1") {
               		toastr.success("INQUIRY DELETE SUCCESSFULLY", "SUCCESS");
               		load_inquiry_datatable();
               	}
               	else if(response.trim() == "0") {
               		toastr.warning("SOMETHING WRONG", "WARNING");
               	}	
               	Unloading();						
               }
           });	
	} 
}

/*Maulik Start*/
function product_convert_qty(type){
	// console.log(type)
	if(type==2){
		var conv_qty_hide=$("#product_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var	num=$("#product_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);

		if(resultb===results){
			get_amount();
			return false;
		}
		var product_conv_qty_hide=$("#product_conv_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#product_conv_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		if(resultb===results){
			get_amount();
			return false;
		}
		var conv_qty_hide=$("#product_qty").val();
	}
	// console.log(base_qty_hide);
	// console.log(conv_qty_hide);
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/inquiry/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#product_conv_qty_hide").val(conv_qty);
				}else if(type===2){
					$("#product_qty_hide").val(base_qty);
				}
				
				if(type===1){
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					
				}else{
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);
				}
				get_amount();
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_conv_qty").val("0");
		$("#product_conv_qty_hide").val("0");
		$("#product_qty").val("0");
		$("#product_qty_hide").val("0");
	}
	
}

function exportCsv() {
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var stage_id = $('#stage_id').val();
	var sales_stage_id = $('#sales_stage_id').val();
	var source_id = $('#source_id').val();
	var user_id = $('#user_id').val();
	var branch_id = $('#branch_id').val();
	var country_id = $('#country_id').val();
	var state_id = $('#state_id').val();
	var assign_user_ids = $('#assign_user_id').val();
	var city_id = $('#cityid').val();
	var product_id = $('#product_id').val();
	var category_id = $('#category_id').val();
	var sales_stage_cat_id = $('#sales_stage_cat_id').val();
	
	var url = root_domain +'generate_export?mode=inquiry_list&start_date=' + encodeURIComponent(start_date) + "&end_date=" + encodeURIComponent(end_date) + "&stage_id=" + encodeURIComponent(stage_id) + "&sales_stage_id=" + encodeURIComponent(sales_stage_id) + "&source_id=" + encodeURIComponent(source_id) + "&user_id=" + encodeURIComponent(user_id) + "&branch_id=" + encodeURIComponent(branch_id) + "&country_id=" + encodeURIComponent(country_id) + "&state_id=" + encodeURIComponent(state_id) + "&assign_user_id=" + encodeURIComponent(assign_user_ids) + "&city_id=" + encodeURIComponent(city_id) + "&product_id=" + encodeURIComponent(product_id) + "&category_id=" + encodeURIComponent(category_id) + "&sales_stage_cat_id=" + encodeURIComponent(sales_stage_cat_id);
	window.location.href = url;
}

// JS : File import for inquiry product data
$("#product_import_file").change(function() {
	var import_file_name = "Import File : " + this.files[0]['name'];
	$("#import_filename").text(import_file_name);

	var formData = new FormData();

	// Additional parameters
	formData.append("excel_file", this.files[0]);
	formData.append("inquiry_id", $("#eid").val());
	formData.append("mode", "check_data");
	
	$.ajax({
		cache:false,
		url: root_domain+crm_domain+'app/inquiry/',
		// dataType: 'json',
		type: "POST",
		data: formData,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var response=data.res;
			$("#import_filename").text("");
			$("#product_import_file").val("");
			
			if(response == '1') {
				Unloading();
				show_data();
				dataget();	
				toastr.success("Inquiry Product Import Data ADDED SUCCESSFULLY", "SUCCESS");
				if (data.error_flag) {
					$("#direct-import-inquiry-data").html(data.error_list_data);
					$('#direct-inquiry-wrong-data-modal').modal('show');
				}
			}
			else if(response == '-1')
			{
				toastr.info("SELECT WRONG FILE", "INFO")
				Unloading();				
			}
			else if(response == '0')
			{
				toastr.warning("Coloums Does Not Match Please Check With demo File", "ERROR")
				Unloading();				
			}
			else if(response == '3')
			{
				toastr.warning("Coloums Does Not Match Please Check With demo File", "ERROR")
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

/*function load_parent_cat(){
	var parent_id = $("#parent_cat_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "load_parent_cat",parent_id :parent_id },
		success: function(response)
		{
			$("#cat_id").html(response);
		}
	});
}*/
/*Maulik End*/