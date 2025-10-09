//var datatable;
$(document).ready(function() {
	load_task_datatable();
	
	// validate vendor add form on keyup and submit
	$("#task_add").validate({
		rules: {
			old_user_id: {
				required: true			
			},
			new_user_id:{
				required: true
			}
		},
		messages: {
			old_user_id: {
				required: "Choose Old User"
			},
			new_user_id:{
				required: "Choose New User"
			}
		}
	}); 
}); 

$("#task_add").on('submit',function(e) {
	var path = $('#back_link').val();
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#task_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/task_transfer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location= path;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=path;	
			}
			Unloading();
			$('#task_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_task_datatable(){
	//var task_status=$('input[name=task_status]:Checked').val();
//	var date=$('#rep_date').val();
	//var branch_id=$('#branch_id').val();
	datatable = $("#task-table").dataTable({
		"bStateSave": true,
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
		"sAjaxSource": root_domain + crm_domain + 'app/task_transfer/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"}
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

function task_transfer(task_trasfer_id) 
{
	var r= confirm(" Are you sure want to Transfer Inquiry ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain+'app/task_transfer/',
			data: { mode : "task_transfer",  task_trasfer_id : task_trasfer_id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("SUCCESSFULLY", "SUCCESS");
					load_task_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	} 
}

function change_task_status(task_id,task_status) 
{
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/task/',
		data: { mode : "change_task_status",  task_id:task_id, task_status:task_status },
		success: function(response)
		{
			console.log(response);
			if(response.trim() == "1") {
				toastr.success("TASK CHANGED SUCCESSFULLY", "SUCCESS");
				load_task_datatable();
				load_pend_task();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}	
			Unloading();						
		}
	});		
}
function get_rel_task_divs(task_rel_id){
	if(task_rel_id=='3'){//Person
		$('#person_rel_div').show();
		$('#gen_rel_div').hide();
		$('#company_rel_div').hide();
		$('#inq_rel_div').hide();
		$("#task_stage_div").hide();
		$("#task_sales_stage_div").hide();
	}
	else if(task_rel_id=='4'){//Company
		$('#company_rel_div').show();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
		$('#inq_rel_div').hide();
		$("#task_stage_div").hide();
		$("#task_sales_stage_div").hide();
	}
	else if(task_rel_id=='5'){//Inquiry
		$('#inq_rel_div').show();
		$('#company_rel_div').hide();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
		//$("#task_stage_div").show();
		//$("#task_sales_stage_div").show();
	}
	else{
		$('#gen_rel_div').show();
		$('#person_rel_div').hide();
		$('#company_rel_div').hide();
		$('#inq_rel_div').hide();
		$("#task_stage_div").hide();
		$("#task_sales_stage_div").hide();
	}
}
function validateDueDate(task_type, due_date){
	var opp_id = $("#opp_id").val();
	if(task_type === '16' && opp_id !== '13'){
		$("#task_due_date").val('');
		$("#task_add").validate({
			rules: {
				task_type_id: {
					required: true			
				}
			},
			messages: {
				task_type_id: {
					required: "Choose Task Type"
				}
			}
		});
	} else {
		$("#task_due_date").val(due_date);
	}
}
function preview_rel_types() 
{
	var task_rel_id=$('#task_rel_id').val();
	var c_con_id=$('#c_con_id').val();
	var cust_id=$('#cust_id').val();
	var inquiry_id=$('#inquiry_id').val();
	if(task_rel_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/task/',
			data: { mode : "preview_rel_types", task_rel_id:task_rel_id, c_con_id:c_con_id, cust_id:cust_id, inquiry_id:inquiry_id },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#preview_rel_details_modal').modal('show');
				$('#preview_rel_details_div').html(resp.html_resp);
				Unloading();
			}
		});
	}
}
function open_follow_up(task_id,inq_name) {
	$('#add_flp_hist_modal').modal('show');
	$('#flp_task_id').val(task_id);
	$('#flp_modal_inq_name').html(inq_name);
	$('#task_flp_remark').focus();
	show_flp_hist();
}
function add_flp_hist_field()
{
	if(!$("#task_flp_remark").val()) {		
		toastr.warning("Enter Remark", "ERROR");
		$("#task_flp_remark").focus();
		return false;
	}
	
	var form_data = {
		mode : "add_flp_hist_field",
		flp_id:$("#flp_id").val(),
		task_flp_remark:$("#task_flp_remark").val(),
		task_id:$("#flp_task_id").val() 
	};
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/task/',
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			$("#task_flp_remark").val("");
			$("#flp_id").val('');
			$("#addhistrow").val("Add");
			Unloading();
			show_flp_hist();
		}
	});
}
function show_flp_hist(){
	var task_id = $("#flp_task_id").val();
	
	$("#flp-hist-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain + 'app/task/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name":"mode", "value":"show_flp_hist" }, { "name":"task_id", "value":task_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function delete_task_flp(flp_id) 
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/task/',
			data: { mode : "delete_task_flp", flp_id:flp_id },
			success: function(resnse)
			{
				//console.log(resnse);
				if(resnse.trim() == "1") {
					toastr.success("REMARK DELETED SUCCESSFULLY", "SUCCESS");
					show_flp_hist();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}		
				Unloading();					
			}
		});	
	}
}
function load_pend_task(){
	var fil_due_date = $('#fil_due_date').val();
	var fil_task_type_id = $('#fil_task_type_id').val();
	var log_user_id=$('#log_user_id').val();
	var c_user_id=$('#crm_tree_user1').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": true,
		"fnStateSave": function (oSettings, oData) {
			localStorage.setItem('offersDataTables', JSON.stringify(oData));
		},
		"fnStateLoad": function (oSettings) {
			return JSON.parse(localStorage.getItem('offersDataTables'));
		},
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/task/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_pend_task" },
				{ "name": "fil_task_type_id", "value": fil_task_type_id },
				{ "name": "log_user_id", "value": log_user_id },
				{ "name": "fil_due_date", "value": fil_due_date },
				{ "name": "c_user_id", "value": c_user_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function show_lost_reason(){
	var oid=$("#opp_id").val();

	if(oid==="13"){
		$(".lost_reasons").show();
        //$("lost_reason_div").show();
        $('#task_add').validate({
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
    }else{
    	$(".lost_reasons").hide();
       	// $("#lost_reason_div").show();
       }
   }

   function add_reason_div(){
   	var counter = $("#counter").val();
   	$.ajax({
   		type: "POST",
   		url: root_domain + crm_domain + 'app/task/',
   		data: { mode : "add_lost_reason", counter: counter },
   		success: function(response)
   		{
   			var resp=JSON.parse(response);
                    //$(".add_remove_reason").removeClass('fa-plus');
                    //$(".add_remove_reason").addClass('fa-minus');
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

   function load_inquiry_stage(inq_id){
   	$.ajax({
   		type: "POST",
   		url: root_domain + crm_domain + 'app/task/',
   		data: { mode : "load_inquiry_stage", inq_id: inq_id},
   		success: function(response)
   		{
            //console.log(response);
            var arr = jQuery.parseJSON(response);
            
            $('#opp_id').select2("val",arr.opp_id);
            $('#sales_stage_id').select2("val",arr.sales_stage_id);
            
            $("#task_stage_div").show();
            $("#task_sales_stage_div").show();
        }
    });
   }
   function load_opp_stage_prob(opp_id){
   	if(opp_id){
   		var task_type = $("#task_type_id").val();
   		if(task_type === '16' && opp_id === '13'){
   			$("#task_due_date").val('');
   		}
   		$.ajax({
   			type: "POST",
   			url: root_domain + crm_domain + 'app/opp_mst/',
   			data: { mode:"load_opp_stage_prob", opp_id:opp_id },
   			success: function(response)
   			{
				//console.log(response);
				var resp=jQuery.parseJSON(response);
				$('#stage_prob').val(resp.opp_probability);
			}
		});


   	}

   }
   function change_inquiry_stage(opp_id){
   	if(opp_id){
   		if(opp_id === '12'){
   			var inquiry_id = $("#inquiry_id").val();
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
   								validate_close_reason(opp_id);
   							}
   						});
   					} else {
   						$('#opp_id').val(5);
   						$('#stage_prob').val(10);
   					}
   				}
   			} else {
   				load_opp_stage_prob(opp_id);
   			}
   		}
   		else {
   			load_opp_stage_prob(opp_id);
   		}
   	}

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

   function check_assign_user(task_type_id){
   	if(task_type_id === '15' || task_type_id === '20'){
   		$('#assign_user_ids').removeAttr('multiple');
   		$('#assign_user_ids').select2({width: '100%'});
   	} else {
   		$('#assign_user_ids').attr('multiple','true');
   		$('#assign_user_ids').select2({width: '100%'});
   	}
   }

   function add_task_attch_field() {
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
	form_data.append('mode', "add_task_attch_field");
	form_data.append('task_id', $("#eid").val());
	form_data.append('inquiry_id', $("#inquiry_id").val());
	form_data.append('inq_attch_doc_name', $("#inq_attch_doc_name").val());
	form_data.append("inq_attch_file", document.getElementById('inq_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/task/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#inq_attch_doc_name").val("").focus();
			$("#inq_attch_file").val("");
			$('#task_attch_btn').val('Add');
			Unloading();
			show_task_attach_data();
		}
	});
}
function show_task_attach_data() {
	var eid = $('#inquiry_id').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/task/',
		data: { mode : "show_task_attach_data", task_id:eid,modee:chkmode },
		success: function(resp){
			//console.log(resp);
			$('#task_attch_trn_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_task_attach_data(task_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/task/',
			data: { mode:"delete_task_attach_data", task_attach_id:task_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_task_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
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
function unlock_inquiry(inquiry_id){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/task/',
		data: { mode : "unlock_inquiry", inquiry_id:inquiry_id },
		success: function(response)
		{
			Unloading();
			load_pend_task();
		}
	});
}