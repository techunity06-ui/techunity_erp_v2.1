var datatable;
$(document).ready(function() {
	load_pro_tbl()
});


function load_pro_tbl(product_type){	
	var branch_id = $('#branch_id').val();
	var fil_product_type = $('#fil_product_type').val();
	var datatable = $("#product-table").dataTable({
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
		"sAjaxSource": root_domain+'app/rb_stock_generate_cron/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "product_type", "value": product_type },
				{ "name": "fil_product_type", "value": fil_product_type },
				{"name": "branch_id", "value": branch_id }
				);
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


function run_cron(product_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/rb_stock_generate_cron/',
		data: { mode : "stock_cron_run",  product_id : product_id },
		success: function(response)
		{
			//console.log(response)
			if(response.trim() == "1") {
				toastr.success("STOCK GENERATE SUCCESSFULLY", "SUCCESS");
				load_pro_tbl();
				Unloading();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
				Unloading();
			}
		}
	});
}