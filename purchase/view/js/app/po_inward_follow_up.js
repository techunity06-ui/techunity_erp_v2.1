//var datatable;
$(document).ready(function() {
	load_po_req_datatable();
});
function reload_data()
{
	load_po_req_datatable();
}	
function load_po_req_datatable()
{
	//var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	//alert(po_type_status);
	datatable = $("#over-inward-table").dataTable({
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
			"sAjaxSource": root_domain+purchase_domain+'app/po_inward_follow_up/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					//{ "name": "po_type_status", "value": po_type_status },
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
function open_followup(id,pono,deliver_id){
	$('#over_due_followup').modal('show');
	$('#po_no').html(pono);
	$('#po_id').val(id);
	$('#delever_id').val(deliver_id);
	
	load_party_po_dtl();
	po_followup_datatable();
	
}
function load_party_po_dtl(){
	var purchase_order_id = $('#po_id').val();
	var delever_id = $('#delever_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_inward_follow_up/',
		data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id,delever_id:delever_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}

function po_follow_up_add(){
	if($("#po_follow_remark").val()=="")
	{
		toastr.warning("Enter Remark", "ERROR")
		$("#po_follow_remark").focus();
		return false;
	}
	var form_data = {
		mode:"followup_add",
		followup_remark:$('#po_follow_remark').val(),
		folloup_date:$("#folloup_date").val(),
		purchase_order_id:$('#po_id').val(),
		delever_id:$('#delever_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/over_due_inward/',
		data: form_data,
		success: function(response)
		{
			$('#po_follow_remark').val("");
			po_followup_datatable();
			Unloading();
		}
	});	
}

function po_followup_datatable(){
	var delever_id = $('#delever_id').val();
	
	$("#po-followup-datatable").dataTable({
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
		"sAjaxSource": root_domain+purchase_domain+'app/po_inward_follow_up/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "po_folloup_fetch" }, { "name": "delever_id", "value": delever_id }  );
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