//var datatable;
$(document).ready(function() {

	show_data();
	
});

function open_stock_allocation_so(sales_order_trn_id){
	//alert(sales_order_trn_id);
	$('#preview_so_branch_allocate_modal').modal('show');
	$('#ref_sales_order_trn_id').val(sales_order_trn_id);
	//show_data();
}
function add_branch() {
	var branch_id = $('#branch_id').val();
	var ref_sales_order_trn_id = $('#ref_sales_order_trn_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/get_sales_order_details_branch/',
		data: { mode : "add_branch", ref_sales_order_trn_id:ref_sales_order_trn_id,branch_id:branch_id },
		success: function(resp){
			//console.log(resp);
			if(resp.trim()==1){
				toastr.success("BRANCH ALLOCATION SUCCESSFULLY", "SUCCESS");
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			$('#preview_so_branch_allocate_modal').modal('hide');
			show_data();
			Unloading();
		}		 
	}); 
}

function show_data() {
	//var st_type = $('#st_type').val();
	//var branch_id = $('#branch_id').val();
	
	//alert(st_type);
	datatable = $("#dynamic-table1").dataTable({
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
		"sAjaxSource": root_domain+production_domain+'app/get_sales_order_details_branch/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min_new" });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
} 
function open_so_trn_modal(so_trn_id){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/get_sales_order_details_branch/',
		data: { mode : "preview_so_trn_pro_description", so_trn_id:so_trn_id},
		success: function(response){
			$('#preview_so_trn_pro_description').modal('show');
			$('#preview_so_trn_pro_description_div').html(response);
		}		 
	});
}