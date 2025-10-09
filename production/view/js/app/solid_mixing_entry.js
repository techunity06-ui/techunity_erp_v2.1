//var datatable;
$(document).ready(function() {

	show_data();
	
});

function show_data() {
	
	//alert(st_type);
	datatable = $("#dynamic-table1").dataTable({
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
		"sAjaxSource": root_domain+production_domain+'app/solid_mixing_entry/',
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

function open_so_trn_modal(product_id,batch_size){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_mixing_entry/',
		data: { mode : "preview_solid_planning1", product_id:product_id,batch_size:batch_size},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_mixing').modal('show');
			$("#batchsiz").html(arr.batch_size_name);
			$("#tqty").html(arr.pending_qty);
			$("#pro_name").html(arr.product_name);
			$("#mixing_finish_qty").val(arr.pending_qty);
			$("#batch_size_id").val(batch_size);
			$("#product_id").val(product_id);
			
		}		 
	});
}
function save_solid_mixing_planning(){
	var mixing_finish_qty=$("#mixing_finish_qty").val();
	var batch_size_id=$("#batch_size_id").val();
	var product_id=$("#product_id").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_mixing_entry/',
		data: { mode : "save_mixing", product_id:product_id,batch_size_id:batch_size_id,mixing_finish_qty:mixing_finish_qty},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_mixing').modal('hide');
			if(arr.msg == '1') {
				toastr.success("Entry Save SUCCESSFULLY", "SUCCESS");
				show_data();
				window.location=root_domain+inventory_domain+'solid_production_sticker_common_print/'+arr.id+'/1';
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
		}		 
	});
}