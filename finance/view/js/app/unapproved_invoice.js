//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	load_datatable();
}

function open_approv_invoice(invoice_id,invoice_no){
	$('#preview_approval_hist_modal').modal('show');
	$('#apprv_ref_no').html(invoice_no);
	$('#invoice_id').val(invoice_id);
	load_invoice_hist_datatable();
}
function load_invoice_hist_datatable(){
	var invoice_id = $('#invoice_id').val();

	$("#invoice-apprv-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+finance_root_domain+'app/unapproved_invoice/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_invoice_hist_datatable" }, { "name": "invoice_id", "value": invoice_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

$("#invoice_approve_add").on('submit',function(e) {

	if($('#apprv_attachment').val()) {
		var ext = $('#apprv_attachment').val().split('.').pop().toLowerCase();
		if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg', 'pdf']) === -1) {
			toastr.warning("Only image type jpg/png/jpeg/gif/pdf is allowed", "ERROR");
			$("#apprv_attachment").focus();
			return false;
		}
	}

	var status = 'Approved';
	if($('#approve_status').val() === '2'){
		status = 'Rejected';
	}

	var form = this;
	e.preventDefault();
	e.stopPropagation();
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");
	$("#submit_btn").attr("disabled","disabled");

	var form_data=new FormData(this);

	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/unapproved_invoice/',
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			if(response){
				$('#approve_status').select2("val","0");
				$('#approve_remark').val("");
				load_invoice_hist_datatable();
                load_datatable();
                $('#preview_approval_hist_modal').modal('hide');
                toastr.success("Status Changed successfully","SUCCESS")
			} else {
				toastr.warning("You have already "+ status, "ERROR");
				$('#approve_status').select2("val","0");
				$('#approve_remark').val("");
			}
			$('#invoice_approve_add').trigger('reset');
			$("#apprv_btn").removeAttr("disabled");
		}
	});
	Unloading();
});


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
			"sAjaxSource": root_domain+finance_root_domain+'app/unapproved_invoice/',
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

function show_image_list(invoice_aprv_log_id, invoice_id){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/unapproved_invoice/',
		data: { mode:"show_invoice_aprv_image", invoice_aprv_log_id:invoice_aprv_log_id, invoice_id: invoice_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#invoice_aprv_image_modal').modal('show');
			$('#image-table').html(resp.html);
		}
	});
	Unloading();
}