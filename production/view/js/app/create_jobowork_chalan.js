//var datatable;
$(document).ready(function() {
	load_jobwork_datatable();
	
	$("#create_chalan").validate({
		rules: {
			branch_id: {
				required: true,
			},	
		},
		messages: {
			branch_id: {
				required: "Select Branch"
			},
		}
	});	
});
$("#create_chalan").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/create_jobwork_chalan/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			// console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("CHALAN CREATE SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+'pending_jobowork_chalan_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			// else if(arr.msg == '-1')
			// {
			// 	toastr.info("ALREADY EXISTS", "INFO");
			// 	Unloading();
			// }
			// else if(arr.msg== 'update')
			// {	
			// 	toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
			// 	Unloading();
			// 	window.location=root_domain+production_domain+'po_list';
				
			// }
			$('#create_chalan').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_jobwork_datatable()
{
	var job_work_id = $('#job_work_id').val();
	var branch_id = $('#branch_id').val();

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/create_jobwork_chalan/',
		data: { mode : "load_jobwork_data",job_work_id:job_work_id,branch_id:branch_id},
		success: function(data){
			//console.log(data);
			$('#tbl_jobwork_data').html(data);		
			Unloading();
		}		
	});

	
}

function challan_edit_pop_up(job_work_trn_id){
	$("#job_work_challan_edit_model").modal("show");
	Loading(true);
	//alert(job_work_trn_id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/create_jobwork_chalan/',
		data: { mode : "load_jobwork_edit_data",job_work_trn_id:job_work_trn_id},
		success: function(data){
			//console.log(data);
			$('#show_challan_form').html(data);		
			Unloading();
		}		
	});
}
	
