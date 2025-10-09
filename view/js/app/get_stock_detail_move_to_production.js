//var datatable;
$(document).ready(function() {

	show_data();
	
});

function show_data() {
	var st_type = $('#st_type').val();
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
		"sAjaxSource": root_domain+'app/get_stock_detail/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min" },{ "name": "st_type", "value": st_type },{ "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	/*$.ajax({
			type: "POST",
			url: root_domain+'app/get_stock_detail/',
			data: { mode:"generate_report_min", st_type:st_type },
			success: function(response)
			{
				var jsonObject = $.parseJSON(response); 
				
				$('#dynamic-table').dataTable( {
					data : jsonObject,
					//data : response,
					columns: [
							  {"data" : "product_name"},
							  {"data" : "product_min_stock"},
							  {"data" : "cl_stock"}              
							  ],
					searching : false
				});
				//alert(response);
				//$('#data_table').html(response);						
			}
		});	*/
}
