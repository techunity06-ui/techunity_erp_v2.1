//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	var branch_id=$('#branch_id').val();
	var ac_status=$('#ac_status').val();
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+ crm_domain +'app/order_acceptance/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "ac_status", "value": ac_status },
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][5]*1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

	function open_po_approv_payment(sales_order_id,sales_order_no){
		$('#preview_po_approval_hist_modal').modal('show');
		$('#apprv_po_ref_no').html(sales_order_no);
		$('#ref_ord_id').val(sales_order_id);
		$('#eid').val(sales_order_id);
		load_po_hist_datatable();
		load_party_po_dtl();
		show_document_attach();
		$(".add_so_apprv_hist").css("display","none");
		$(".add_oa_apprv_hist").css("display","block");
	}
	function load_po_hist_datatable(){
		var sales_order_id = $('#ref_ord_id').val();
		
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
				"sEmptyTable": "NO DATA ADDED YET !"
			},
			"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
			"iDisplayLength": 5,
			"sAjaxSource": root_domain+crm_domain +'app/order_acceptance/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_po_hist_datatable" }, { "name": "sales_order_id", "value": sales_order_id }  );
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
	var sales_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/order_acceptance/',
		data: { mode : "load_party_po_dtl", sales_order_id:sales_order_id },
		success: function(resp){
			var resp=JSON.parse(resp);
			$('#mod_so_comp_div_sec').html(resp.mod_so_comp_div_sec);
			$('#mod_so_pro_div_sec').html(resp.mod_so_pro_div_sec);
		}		 
	});
}

function add_po_apprv_hists(){
	
	var form_data = {
		mode:"add_po_apprv_hists",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		sales_order_id:$('#ref_ord_id').val()
	};
	var status = 'Approved';
	if($('#po_approve_status').val() === '0'){
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/order_acceptance/',
		data: form_data,
		success: function(response)
		{
			if(response){
				$('#po_approve_status').select2("val","0");
				$('#po_approve_remark').val("");
				load_po_hist_datatable();
				load_datatable();
			} else {
				toastr.warning("You have already "+ status, "ERROR");
				$('#po_approve_status').select2("val","0");
				$('#po_approve_remark').val("");
			}
			$('#preview_po_approval_hist_modal').modal('hide');
			Unloading();
		}
	});	
}
function load_transport_detail_party_wise(){
	var cust_id=$("#cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain + 'app/sales_order/',
		data: { mode : "load_transport_detail_party_wise", cust_id : cust_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$('#transport_id').select2("val","");
			$('#transport_id').html(obj.trans_detail);
		}
	});
}
function delete_approve_log(sales_order_id,approve_id,approve_status,type){
	var r= confirm(" Are you want to delete this log?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain +'app/order_acceptance/',
			data: { mode : "delete_approve_log",  sales_order_id : sales_order_id, approve_id: approve_id,approve_status:approve_status,type:type },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
					if(type==1){
						load_po_hist_datatables();
					}else{
						load_po_hist_datatable();
					}
						Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				$('#preview_po_approval_hist_modal').modal('hide');
				load_datatable();							
			}
		});	
	}
}

function show_document_attach() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/sales_order/',
		data: { mode : "show_document_attach", sales_order_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#po_doc_list').html(resp);
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
			url: root_domain+'crm/app/sales_order/',
			data: { mode:"delete_document_attach", attach_id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_document_attach();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

function add_document_attach()
{
	var ext = $('#doc_attach').val().split('.').pop().toLowerCase();
	// if($.inArray(ext, ['pdf','doc','docx']) === -1) {
	// 	toastr.warning("Only image type pdf/doc/docx is allowed", "ERROR");
	// 	$("#doc_attach").focus();
	// 	return false;
	// }

	if(!$("#doc_attach").val()){
		toastr.warning("Choose File", "ERROR");
		$("#doc_attach").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_document_attach");
	form_data.append('doc_name', $("#doc_name").val());
	form_data.append('sales_order_id', $("#eid").val());
	form_data.append("doc_attach", document.getElementById('doc_attach').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/sales_order/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#doc_name").val("").focus();
			$("#doc_attach").val("").focus();
			$('#dfd_attch_btn').val('Add');
			Unloading();
			show_document_attach();
			var cnt = $('#po_document_count').val();
			cnt = parseInt(cnt) + parseInt(1);
			$('#po_document_count').val(cnt);
		}
	});
}
