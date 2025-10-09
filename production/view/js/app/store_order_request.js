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
		"sAjaxSource": root_domain+production_domain+'app/store_order_request/',
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

}



	function open_create_workorder_modal(product_id,order_id,pending_qty){
		

		$("#pending_qty").val(pending_qty);
		$("#indent_qty").val(pending_qty);
		$("#so_product_id").val(product_id);
		$("#sales_ordertrn_id").val(order_id);
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/store_order_request/',
			data: { 
				mode : "get_product_name",
				product_id:product_id,
				
			},
			success: function(data){
				$("#product_name").val(data);
				$("#preview_workorder_indent").modal("show");
				Unloading();
			}		
			
		});
		
	}


$("#create_wo_indent").on('submit',function(e) {

		var pending_qty = parseFloat($("#pending_qty").val());
		var indent_qty = parseFloat($("#indent_qty").val());

		if(indent_qty > pending_qty){
			toastr.warning("QUANTITY MUST BE LESS THAN OR EQUAL TO " + pending_qty, "WARNING");
			return false;
		}

		var form = this;
		e.preventDefault();
		e.stopPropagation();	
		
		
		form.submitted = true;	
		Loading(true);	
		$(this).attr("disabled","disabled");		
		
		var form_data=new FormData(this);	
		$.ajax({
			cache:false,
			url: root_domain+production_domain+'app/store_order_request/',
			type: "POST",
			data: form_data,
			contentType: false,
			processData:false,
			success: function(response)
			{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("INDED CREATED SUCCESSFULLY", "SUCCESS");
				show_data();
				
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
				show_data();
			}
			$("#preview_workorder_indent").modal("hide");
			$('#create_wo_indent').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
		
	});