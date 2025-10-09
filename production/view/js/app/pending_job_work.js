//var datatable;
$(document).ready(function() {

	show_data();
	
});

/*function show_data() {
	var product_id = $('#product_id').val();
	var vender_id = $('#vender_id').val();
	//alert(st_type);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_job_work/',
		data: { mode : "generate_report",product_id:product_id,vender_id:vender_id},
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_data').html(data);		 
			Unloading();
		}		 
	}); 
}*/



function show_data()
{
	var product_id = $('#product_id').val();
	var vender_id = $('#vender_id').val();
	
	
	datatable1 = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": false,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+production_domain+'app/pending_job_work/',
			"fnServerParams": function ( aoData ) {
				aoData.push(
					{ "name": "mode", "value": "generate_report" },
					{ "name": "product_id", "value": product_id },
					{ "name": "vender_id", "value": vender_id }
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
