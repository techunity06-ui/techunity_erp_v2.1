//var datatable;
$(document).ready(function() {
	load_jobwork_datatable();
	reload_working_data();
});

function reload_data()
{
	// var sel_action = $('input[name=jobwork_status]:checked').val();
	// if(sel_action == "total_pending"){
	// 	$('.th_vandor').hide();
	// }else{
	// 	$('.th_vandor').show();
	// }

	$('.jobwork-table').show();

	$('.jobwork-done-table').hide();
	load_jobwork_datatable();
}

function reload_complete_data()
{
	$('.jobwork-table').hide();
	$('.jobwork-done-table').show();
	load_jobwork_complete_datatable();
}

$("#jobwork_add").validate({
	rules: {
		vender_id: {
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
		
	}
}); 	
$("#jobwork_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#jobwork_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/pending_jobwork_list/',
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
				window.location=root_domain+production_domain+"pending_job_work_list";
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
function load_jobwork_datatable()
{
	var jobwork_status=$('input[name=jobwork_status]:Checked').val();
	var vender_id = $('#vender_id').val();
	var branch_id = $('#branch_id').val();
	//var date=$('#rep_date').val();
	//alert(jobwork_status);
	if(jobwork_status!="3"){
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
			"sAjaxSource": root_domain+production_domain+'app/pending_jobwork_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "jobwork_status", "value": jobwork_status },{ "name": "vender_id", "value": vender_id },{ "name": "branch_id", "value": branch_id });
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}else{
		reload_complete_data();
	}
}

function load_jobwork_complete_datatable()
{
	var jobwork_status=$('input[name=jobwork_status]:Checked').val();
	var vender_id = $('#vender_id').val();
	//var date=$('#rep_date').val();
	//alert(jobwork_status);
	datatable = $("#jobwork-done-table").dataTable({
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
			"sAjaxSource": root_domain+production_domain+'app/pending_jobwork_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch_done" },{ "name": "jobwork_status", "value": jobwork_status },{ "name": "vender_id", "value": vender_id });
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
		url: root_domain+production_domain+'app/pending_jobwork_list/',
		data: { mode : "get_series_no_jobwork" },
		success: function(resp){
			load_jobcard_no_jobwork(resp);
		}		
	});	
}

/*
Code By Umair: 29/12/2020
Comment: Get the jobwork no dynamic from tbl_invoicetype table
*/
function load_jobcard_no_jobwork(id)
{
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list/',
		data: { mode : "load_invoiceno_jobwork", typeid : id},
		success: function(data){
			var no = jQuery.parseJSON(data);
			$('#jobwork_no').val(no.invoiceno);
		}
	});
}

/*
Code By Umair: 29/12/2020
Comment: Fetch the product list which need to create a jobwork
*/
function reload_working_data(){
	Loading(true);	
	var vendor_id = $('#vender_id').val();
	var process_id = $('#process_id').val();
	var product_id = $('#product_id').val();
	var branch_id = $('#branch_id').val();

	var type = '';
	if(process_id!='' && product_id!=''){
		type = 0; // single selection item wise
	}else{
		type = 1; // all slection vendor wise
	}
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list/',
		data: { mode : "show_product_list", vendor_id : vendor_id, product_id : product_id, process_id : process_id, type : type,branch_id:branch_id },
		success: function(data){
			$('#sub_row_mat').html(data);
		}
	});
	Unloading();
}

function load_po(vendor_id){
	var product_id = $("#product_id").val();
	var process_id = $("#process_id").val();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list/',
		data: { 
			mode : "load_po_no", 
			vendor_id : vendor_id, 
			product_id : product_id, 
			process_id : process_id 
		},
		success: function(data){
			$("#purchase_id").empty().html(data);
		}
	});
}


function load_po_rate(po_trn_id){

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_jobwork_list/',
		data: { 
			mode : "load_po_rate", 
			po_trn_id : po_trn_id 
		},
		success: function(data){
			$(".vendor_rate").val(data).trigger('change');

		}
	});
}