//var datatable;
$(document).ready(function() {
	load_po_req_datatable();
	$("#FormSortClose").validate({
		rules: {
			remark: {
				required: true			
			},
		},
		messages: {
			remark: {
				required: "Please Enter Remark",
			},
		}
	});
});
function reload_data()
{
	load_po_req_datatable();
}	
$("#approve_indent_add").on('submit',function(e) {
	
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	//alert($('#product_desc').val());
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#approve_indent_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");				
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/indent_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+purchase_domain+"indent_list";
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
			else if(arr.msg== 'update')
			{	
				toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+purchase_domain+'po_list';
				
			}
			$('#purchaseorder_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	//alert(po_type_status);
	datatable = $("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+purchase_domain+'app/indent_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
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
function shortcloseindent(id,pendingqty){
	$("#ModalSortClose").modal("show");
	$("#rp_id").val(id);
	$("#pending_qty").val(pendingqty);
}

function shortclosereason(id){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/indent_list/',
		data: { mode : "load_reason_short_close", id : id },
		success: function(response)
		{
			Unloading();
			var data=JSON.parse(response);
			$("#ModalSortCloseReason").modal("show");
			$('#reason_remark').html(data.shortclose_remark);
		}
	});	
}

$("#FormSortClose").on('submit',function(e) {
	
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormSortClose").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/indent_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("INDENT SHORTCLOSE SUCCESSFULLY", "SUCCESS");
				$("#ModalSortClose").modal("hide");
				window.location=root_domain+purchase_domain+"indent_list";
			}
			else if(arr.msg == '0'){
				Unloading();
				toastr.error("SOMETHING WENT WRONG", "ERROR");
			}
			$('#FormSortClose').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function open_approve_history(rp_id,order_no){
	$('#preview_indent_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(order_no);
	$('#ref_ord_id').val(rp_id);
	load_indent_hist_datatable();
}

function load_indent_hist_datatable(){
		var rp_id = $('#ref_ord_id').val();

		$("#indent-history-datatable").dataTable({
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
			"sAjaxSource": root_domain+purchase_domain+'app/indent_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "indent_hist_datatable" }, { "name": "rp_id", "value": rp_id }  );
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
function un_approve_indent(rp_id,purchaseordertrn_id){
	Loading(true);
	

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/indent_list/',
		data: { mode : "un_approve_indent", rp_id : rp_id, purchaseordertrn_id:purchaseordertrn_id  },
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("INDENT UNAPPROVED SUCCESSFULLY", "SUCCESS");
				$("#ModalSortClose").modal("hide");
				window.location=root_domain+purchase_domain+"indent_list";
			}
			else if(arr.msg == '0'){
				Unloading();
				toastr.error("SOMETHING WENT WRONG", "ERROR");
			}
			Unloading();
		}
	});	
}

function delete_indent(id){
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/indent_list/',
			data: { mode : "indent_delete",  eid : id  },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_po_req_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function show_remark(pre_trn_id , indent_no, remark){
	$("#indent_remark_detail").modal("show");
	$("#indent_remark").html("<strong>Indent No</strong> : "+indent_no+ "<br><strong>Remark</strong> : "+remark);
}