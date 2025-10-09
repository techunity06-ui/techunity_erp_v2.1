//var datatable;
$(document).ready(function() {
	load_po_datatable();
	});

function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	
	var po_approval_status=$('input[name=po_approval_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
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
			"sAjaxSource": root_domain+purchase_domain+'app/po_approoval_finance/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_approval_status", "value": po_approval_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
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
function change_po_finance_approval_status(id, po_approval_status, order_no) 
{
	$('#preview_po_finance_approval_hist_modal').modal('show');
	$('#fin_apprv_po_ref_no').html(order_no);
	$('#fin_ref_ord_id').val(id);
	load_finance_purchase_hist_datatable();
	load_finance_party_po_dtl();
	load_finance_pro_po_dtl();
	show_document_fin_attach();
}
function load_finance_purchase_hist_datatable(){
	var purchase_order_id = $('#fin_ref_ord_id').val();
	
	$("#order-finance-po-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+purchase_domain+'app/po_approoval_finance/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "purchase_order_id", "value": purchase_order_id }  );
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
function add_finance_po_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#finance_po_approve_status').val(),
		approve_remark:$('#finance_po_approve_remark').val(),
		purchase_order_id:$('#fin_ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_approoval_finance/',
		data: form_data,
		success: function(response)
		{
			$('#preview_po_finance_approval_hist_modal').modal('hide');
			$('#finance_po_approve_status').select2("val","4");
			$('#finance_po_approve_remark').val("");
			load_finance_purchase_hist_datatable();
			//load_order_confirm_datatable();
			load_po_datatable();
			Unloading();
		}
	});	
}
function load_finance_party_po_dtl(){
	var purchase_order_id = $('#fin_ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_order/',
		data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_fin_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function load_finance_pro_po_dtl(){
	var purchase_order_id = $('#fin_ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_order/',
		data: { mode : "load_pro_purchase_dtl", purchase_order_id:purchase_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_fin_po_pro_div_sec').html(resp.mod_po_pro_div_sec);
		}		 
	});
}
function delivery_detail(po_trn_id){
	$('#delivery_detail').modal('show');
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain +'app/purchase_order/',
		data: { mode : "delivery_detail",  po_trn_id : po_trn_id},
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$('#pr_na').html(data.pro_name);
			$('#delivery_schedule').html(data.delivery_schedule);
		}
	});
}


function add_fin_document_attach()
{
	var ext = $('#fin_doc_attach').val().split('.').pop().toLowerCase();
	// if($.inArray(ext, ['pdf','doc','docx']) === -1) {
	// 	toastr.warning("Only image type pdf/doc/docx is allowed", "ERROR");
	// 	$("#doc_attach").focus();
	// 	return false;
	// }

	if(!$("#fin_doc_attach").val()){
		toastr.warning("Choose File", "ERROR");
		$("#fin_doc_attach").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_document_attach");
	form_data.append('doc_name', $("#fin_doc_name").val());
	form_data.append('purchaseorder_id', $("#fin_ref_ord_id").val());
	form_data.append("doc_attach", document.getElementById('fin_doc_attach').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_order/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#fin_doc_name").val("").focus();
			$("#fin_doc_attach").val("").focus();
			$('#fin_dfd_attch_btn').val('Add');
			Unloading();
			show_document_fin_attach();
			var cnt = $('#po_document_count').val();
			cnt = parseInt(cnt) + parseInt(1);
			$('#po_document_count').val(cnt);
		}
	});
}

function show_document_fin_attach() {
	var eid = $('#fin_ref_ord_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_order/',
		data: { mode : "show_document_attach", purchaseorder_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#fin_po_doc_list').html(resp);
			Unloading();
		}		 
	}); 
}

function delete_document_attach(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_order/',
			data: { mode:"delete_document_attach", attach_id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_document_fin_attach();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}