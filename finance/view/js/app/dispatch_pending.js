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
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	var branch_id = $('#branch_id').val();
        
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
			"sAjaxSource": root_domain+finance_root_domain+'app/dispatch_pending/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "type_id", "value": type },{ "name": "date", "value": date },{ "name": "branch_id", "value": branch_id } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function transport_detail(id,invoice_no){
	$("#transport_detail_modal").modal("show");
	$("#apprv_po_ref_no").html(invoice_no);
	$("#invoice_id").val(id);

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/dispatch_pending/',
		data: { mode:"transport_data", invoice_id: id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			//$('#image-table').html(resp.html);
			$('#transport_transaction_id').val(resp.transport_transaction_id);
			$('#transport_id').val(resp.transport_id);
			$('#transport_gr_no').val(resp.transport_gr_no);
			$('#transport_gr_date').val(resp.transport_gr_date);
			$('#distance_km').val(resp.distance_km);
			$('#transport_mode').val(resp.transport_mode);
			$('#transport_vehicle_no').val(resp.transport_vehicle_no);
			$('#transport_station').val(resp.transport_station);
			$('#transport_pincode').val(resp.transport_pincode);
			$('#transport_doc_no').val(resp.transport_doc_no);
			$('#transport_doc_date').val(resp.transport_doc_date);
			$('#transport_voucher').val(resp.transport_voucher);
		}
	});
	Unloading();
}


$("#transport_detail_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+ finance_root_domain+'app/dispatch_pending/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);	
			if(arr.msg == '1') {
				Unloading();
				toastr.success("DISPATCH ADDED SUCCESSFULLY", "SUCCESS");
				$("#transport_detail_modal").modal("hide");
				load_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				Unloading();		
				toastr.success("DISPATCH UPDATED SUCCESSFULLY", "SUCCESS");	
				$("#transport_detail_modal").modal("hide");
				load_datatable();	
			}
			$('#transport_detail_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function finally_dispatch_detail(id,invoice_no){
	$("#final_dispatch_modal").modal("show");
	$("#apprv_ref_no").html(invoice_no);
	$("#invoi_id").val(id);
	load_final_dispatch_history(id);
}

$("#final_dispatch_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+ finance_root_domain+'app/dispatch_pending/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);	
			if(arr.msg == '1') {
				Unloading();
				toastr.success("FINAL DISPATCH ADDED SUCCESSFULLY", "SUCCESS");
				$("#final_dispatch_modal").modal("hide");
				load_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				Unloading();		
				toastr.success("FINAL DISPATCH UPDATED SUCCESSFULLY", "SUCCESS");	
				$("#final_dispatch_modal").modal("hide");
				load_datatable();	
			}
			$('#final_dispatch_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function load_final_dispatch_history(id){
	datatable = $("#dispatch-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+finance_root_domain+'app/dispatch_pending/',
		
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_dispatch_history" }, { "name": "invoice_id", "value": id } );
		},

		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}