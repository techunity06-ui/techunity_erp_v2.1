//var datatable;
$(document).ready(function() {

	generate_report();

});




function generate_report() 
{
	
	var date=$("#rep_date").val();
	var so_id=$("#so_id").val();
	var cust_id=$("#cust_id").val();
	
	Loading();
	

	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		'columnDefs': [
			{
				"targets": 0, // your case first column
				"className": "text-center",
				
		   },
		   {
				"targets": 2,
				"className": "text-right",
		   }
		 ],
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+production_domain+'app/salesorder_invoice_report/',
		"fnServerParams": function ( aoData ) {
			aoData.push(
				{"name": "mode", "value": "generate_report" },
				{ "name": "date", "value": date },
				{ "name": "cust_id", "value": cust_id },
				{ "name": "so_id", "value": so_id }
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');

	Unloading();
	
}

