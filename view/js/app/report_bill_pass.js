$(document).ready(function() {
    reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() {
	var date = $("#rep_date").val();
        var cust_id = '0';
        var bill_type = $('input[name=cust_type]:Checked').val();
        var pay_terms = $("#pay_terms").val();
        
        if(bill_type > 0){
            cust_id = $("#cust_id").val();
        }
        
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain + 'app/report_bill_pass/',
		data: { mode : "generate_report", date : date,cust_id:cust_id,pay_terms:pay_terms},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
}

function open_approv_model(po_id,po_no){
	$('#preview_approval_hist_modal').modal('show');
	$('#apprv_ref_no').html(po_no);
	$('#ref_quotation_id').val(po_id);
	load_purchase_hist_datatable();
}
function add_apprv_hist(){
	
	var form_data = {
		mode:"add_apprv_hist",
		assign_user_ids:$('#assign_user_ids').val(),
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		po_id:$('#ref_quotation_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
		data: form_data,
		success: function(response)
		{
			$('#assign_user_ids').select2("val","");
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_purchase_hist_datatable();
                        generate_report();
			Unloading();
		}
	});	
}

function load_purchase_hist_datatable(){
	var po_id = $('#ref_quotation_id').val();
	
	$("#sales-order-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+'app/purchase/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "po_id", "value": po_id }  );
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