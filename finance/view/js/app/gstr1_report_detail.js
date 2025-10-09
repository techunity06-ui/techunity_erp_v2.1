$(document).ready(function() {
	  //	generate_report();
		
});

//,typeid:typeid
function generate_report() 
{
	Loading();
	//alert("hii");
	var start_date=$('#start_date').val();
	var end_date=$('#end_date').val();
	var type=$('#type').val();
        
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
			"sAjaxSource": root_domain + finance_root_domain +'app/gstr1_report/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "gst_report_details" },{ "name": "type_id", "value": type },{ "name": "start_date", "value": start_date },{ "name": "end_date", "value": end_date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');

}
