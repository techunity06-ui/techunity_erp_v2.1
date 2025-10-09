//var datatable;
$(document).ready(function() {
	load_datatable();
});

function load_datatable()
{

	var datatable = $("#dynamic_table_working").dataTable({
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
		"aLengthMenu": [[-1,10, 20, 50, 100], ['ALL',10, 20, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/store_release_material_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_working" });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}



function get_material_data(p_id,req_qty,store_release_id){ 

	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_material_list/',
			data: { mode : "get_release_material_data",req_qty:req_qty,p_id:p_id,store_release_id:store_release_id},
			success: function(response)
			{
				
				$('#store_release_material_data').html(response);
				$("#store_release_material_model").modal('show');
			}
	});
}
