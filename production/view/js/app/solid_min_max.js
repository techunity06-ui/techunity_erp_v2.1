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
		"sAjaxSource": root_domain+production_domain+'app/solid_min_max/',
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
			url: root_domain+production_domain+'app/get_stock_detail/',
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

function open_so_trn_modal(product_id,qty){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_min_max/',
		data: { mode : "preview_solid_planning1", product_id:product_id},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_planing').modal('show');
			//$('#solid_planning_div').html(response);
			//$("#so_no").html(arr.sales_order_no);
			//$("#so_date").html(arr.sales_order_date);
			$("#sodiv").hide();
			$("#pro_name").html(arr.product_name);
			$("#pqty").val(qty);
			//$("#sotrn").val("");
			$("#product_id").val(product_id);
			open_so_trn_modal_pro();
		}		 
	});
}
function open_so_trn_modal_pro(){
	var qty=$("#pqty").val();
	var product_id=$("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/get_sales_order_details_solid/',
		data: { mode : "preview_solid_planning", product_id:product_id,qty:qty},
		success: function(response){
			//$('#solid_planning_div').html(response);
			var arr = jQuery.parseJSON(response);
				$('#solid_planning_div').html(arr.html);
				$('#pqty').val(arr.qty);
		}		 
	});
}
function save_solid_planning(){
	
	var sales_order_trn_id=$("#sotrn").val();
	var product_id=$("#product_id").val();
	var pqty=$("#pqty").val();
	
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/get_sales_order_details_solid/',
			data: { mode : "save_solid_planning",  sales_order_trn_id : sales_order_trn_id,product_id:product_id,pqty:pqty},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				
				if(arr.msg==1){
					toastr.success("Planning SUCCESSFULLY", "SUCCESS");
					show_data();
					$('#solid_planing').modal('hide');
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
				}
			}
		});	
	
}