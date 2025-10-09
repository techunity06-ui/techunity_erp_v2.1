$(document).ready(function() {
	load_finish_qc_pending_datatable();
});
function load_finish_qc_pending_datatable()
{
	var qc_type = $("#grn_against").val();
	$("#finish-qc-pending-datatable").dataTable({
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
		"sAjaxSource": root_domain+'app/qc_done_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{"name": "qc_type", "value": qc_type});
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

function view_attach_document(qc_id,qc_no){
	$('#view_attach_document_modal').modal('show');
	$('#ref_no').html(qc_no);
	$('#ref_ord_id').val(qc_id);
	load_attach_document();
}

function load_attach_document(){
	var qc_id=$('#ref_ord_id').val();
	
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
		"sAjaxSource": root_domain + 'app/qc_done_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "load_attach_document"},
				{"name": "qc_id", "value": qc_id});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
