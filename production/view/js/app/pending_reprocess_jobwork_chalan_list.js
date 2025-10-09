
//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{	
	var branch_id=$('#branch_id').val();
	var jobwork_status=$('input[name=jobwork_status]:Checked').val();
	
	
	if(jobwork_status == '1'){
		$(".jwchalan").show()
	}else{
		$(".jwchalan").hide()
	}

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
	"sAjaxSource": root_domain+production_domain+'app/pending_reprocess_jobowork_chalan_list/',
	"fnServerParams": function ( aoData ) {
		aoData.push( 
			{ "name": "mode", "value": "fetch" },
			{ "name": "jobwork_status", "value": jobwork_status }
			);
	},
	"fnDrawCallback": function( oSettings ) {
		$('.ttip, [data-toggle="tooltip"]').tooltip();
		if(oSettings.aoData.length > 0){
			if($("#workorder_no").length == 0) {
				if(jobwork_status == '1'){
					$(".jwchalan").show()
					$('td:nth-child(1)').show();
				}else{
					$(".jwchalan").hide()
					$('td:nth-child(1)').hide();
				}
			}else{
				if(jobwork_status == '1'){
					$(".jwchalan").show()
					$('td:nth-child(2)').show();
				}else{
					$(".jwchalan").hide()
					$('td:nth-child(2)').hide();
				}
			}
		}
	}
}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}


function show_vendor_modal(job_work_id,jobwork_no,g_total){

	$("#g_total").val(g_total);
	$("#job_work_id").val(job_work_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_reprocess_jobowork_chalan_list/',
		data: { mode:"get_jobwork_data", job_work_id:job_work_id},
		success: function(resp){
			$("#mod_jobwork_edit_view").empty().html(resp);
			$("#lbl_jobwork_no").empty().html(jobwork_no);
			$("#preview_jobwork_rate").modal('show');
			
			$("#vender_id").select2({
				width : "100%"
			});
			Unloading();
		}
	});
	
}


function change_vandor(){
	var job_work_id = $("#job_work_id").val();
	var vender_id = $("#vender_id").val();

	if(vender_id == ""){
		toastr.info("PLEASE SELECT VENDER", "INFO")
		return false;
	}


	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_reprocess_jobowork_chalan_list/',
		data: { mode:"change_vender", job_work_id:job_work_id,vender_id:vender_id},
		success: function(resp){
			if(resp.trim() == "1"){
				toastr.success("VENDER UPDATED SUCCESSFULLY", "SUCCESS")
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			Unloading();
		}
	});
	
}

function change_rate(job_work_trn_id,qty,old_rate){
	var rate = $("#pr_rate_"+job_work_trn_id).val();
	var g_total = $("#g_total").val();
	var job_work_id = $("#job_work_id").val();

	if(rate == ""){
		toastr.warning("PLEASE ENTER JOBWORK RATE", "WARNING")
		return false;
	}

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_reprocess_jobowork_chalan_list/',
		data: { mode:"change_rate",job_work_id:job_work_id,job_work_trn_id:job_work_trn_id,rate:rate,qty:qty,g_total:g_total,old_rate:old_rate},
		success: function(resp){
			if(resp.trim() == "1"){
				toastr.success("VENDER UPDATED SUCCESSFULLY", "SUCCESS")
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			load_datatable();
			Unloading();
		}
	});
}