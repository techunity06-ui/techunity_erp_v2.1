$(document).ready(function() {
	load_po_req_datatable();
});
function load_po_req_datatable()
{
	var po_type_status=$('input[name=status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	$("#overdue-po-req-datatable").dataTable({
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
		"sAjaxSource": root_domain+purchase_domain+'app/po_shortclose_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "branch_id", "value": branch_id },{ "name": "date", "value": date },{ "name": "po_type_status", "value": po_type_status }, );
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

function change_po_shortclose_approval_status(id,trn_id, po_approval_status, order_no) 
{
	$('#preview_shortclose_po_approval_hist_modal').modal('show');
	$('#sortclose_apprv_po_ref_no').html(order_no);
	$('#ref_ord_id').val(id);
	$('#ref_ord_trn_id').val(trn_id);
	load_purchase_hist_datatable();
	load_party_po_dtl();
}

function load_purchase_hist_datatable(){
	var po_trn_id = $('#ref_ord_trn_id').val();
	
	$("#order-po-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+purchase_domain+'app/po_shortclose_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "po_trn_id", "value": po_trn_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
}
function load_party_po_dtl(){
	var purchase_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_shortclose_list/',
		data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function add_po_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		purchase_order_id:$('#ref_ord_id').val(),
		po_trn_id: $('#ref_ord_trn_id').val(),
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_shortclose_list/',
		data: form_data,
		success: function(response)
		{
			$('#po_approve_status').select2("val","2");
			$('#po_approve_remark').val("");
			load_purchase_hist_datatable();
			//load_order_confirm_datatable();
			load_po_req_datatable();
			Unloading();
		}
	});	
}
