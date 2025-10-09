//var datatable;
$(document).ready(function() {
	load_task_datatable();
	// validate vendor add form on keyup and submit
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
}); 

$("#task_add").on('submit',function(e) {
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
		url: root_domain+'crm/app/task_one/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("TASK ADDED SUCCESSFULLY", "SUCCESS");
				if($('#back_link').val()){
					window.location=($('#back_link').val());
				}
				else{
					window.location=root_domain+'task_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("TASK UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain+'task_list';	
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
	var task_status=$('input[name=task_status]:Checked').val();
	var date=$('#rep_date').val();
	
	datatable = $("#task_one-table").dataTable({
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'crm/app/task_one/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"}, {"name": "date", "value": date}, {"name": "task_status", "value": task_status } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_task(task_id) 
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/task_one/',
			data: { mode : "delete",  task_id : task_id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("TASK DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain+'crm/app/task_one/',
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
	}
	else if(task_rel_id=='4'){//Company
		$('#company_rel_div').show();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
		$('#inq_rel_div').hide();
	}
	else if(task_rel_id=='5'){//Inquiry
		$('#inq_rel_div').show();
		$('#company_rel_div').hide();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
	}
	else{
		$('#gen_rel_div').show();
		$('#person_rel_div').hide();
		$('#company_rel_div').hide();
		$('#inq_rel_div').hide();
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
			url: root_domain+'crm/app/task_one/',
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
		url: root_domain+'app/task_one/',
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
		"sAjaxSource": root_domain+'crm/app/task_one/',
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
			url: root_domain+'crm/app/task_one/',
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
/* function load_pend_task(){
	var fil_due_date = $('#fil_due_date').val();
	var fil_task_type_id = $('#fil_task_type_id').val();
	var log_user_id=$('#log_user_id').val();
	var c_user_id=$('#crm_tree_user1').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/task_one/',
		data: { mode : "load_pend_task", fil_task_type_id:fil_task_type_id, log_user_id:log_user_id, fil_due_date:fil_due_date,c_user_id:c_user_id },
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = JSON.parse(resnse);
			$('#pend_task_tbody').html(resp.resp_html);
			$('.ttip, [data-toggle="tooltip"]').tooltip();
			Unloading();					
		}
	});
} */
function load_pend_task()
{
	var fil_due_date = $('#fil_due_date').val();
	var fil_task_type_id = $('#fil_task_type_id').val();
	var log_user_id=$('#log_user_id').val();
	var c_user_id=$('#crm_tree_user1').val();
	var is_general = true;

	datatable = $("#dynamic-table").dataTable({
		/* "bAutoWidth" : false,
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
        }, */
			
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
			"sAjaxSource": root_domain+'crm/app/task_one/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_pend_task" },{ "name": "fil_task_type_id", "value": fil_task_type_id },{ "name": "log_user_id", "value": log_user_id },{ "name": "fil_due_date", "value": fil_due_date },{ "name": "c_user_id", "value": c_user_id }, {"name": "is_general", "value": is_general } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function view_followup_hist(inquiry_id) 
{
	if(inquiry_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/inquiry/',
			data: { mode : "view_followup_hist", inquiry_id:inquiry_id },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#preview_flp_hist_modal').modal('show');
				$('#preview_flp_hist_div').html(resp.html_resp);
				Unloading();
			}
		});
	}
}

function load_inq_stage_prob(opp_id){
	if(opp_id){
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/opp_mst/',
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