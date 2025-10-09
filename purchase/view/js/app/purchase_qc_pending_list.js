$(document).ready(function() {
	load_purchase_qc_pending_datatable();
});
function load_purchase_qc_pending_datatable()
{
	var branch_id=$("#branch_id").val();
	$("#purchase-qc-pending-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		'aoColumnDefs': [{
	        'bSortable': false,
	        'aTargets': ['nosort']
    	}],
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+purchase_domain+'app/purchase_qc_pending_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "branch_id", "value": branch_id } );
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



function checkAll()
{
	var checkboxes = document.getElementsByTagName('input'), val = null;    
	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].type == 'checkbox')
		{
			if (val === null) val = checkboxes[i].checked;
			checkboxes[i].checked = val;
		}
	}
}

function qc_all(){

	var checbox_checked_len = $('input:checkbox:checked').length;
	if($('#checkAll').is(':checked')){
		checbox_checked_len = checbox_checked_len - 1;
	}

	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 checkbox ", "ERROR")
		return false;
	}else if(checbox_checked_len > 10)
	{
		toastr.warning("YOU CAN'T SELECT MORE THAN 10 QC", "ERROR")
		return false;
	}
	else
	{
		Loading();
		var i = 1;
		var batch_id = ""
		$("input:checkbox").each(function () {
			if ($(this).is(":checked")) {
				if(typeof $(this).attr("value") != 'undefined')
				{
					if(i == 1){
						batch_id = $(this).attr("value");
					}else{
						batch_id += "," + $(this).attr("value");
					}
					
					if(i == checbox_checked_len){
						$("#qc_all_batch_id").val(batch_id);
						setTimeout(function(){
							$("#qc_all_add").submit();
						},1500)
					}else{
						i++;	
					}
				}
			}
		});  
	}
}
