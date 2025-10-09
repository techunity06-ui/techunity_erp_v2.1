//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{	
	var release_status=$('input[name=release_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
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
		"sAjaxSource": root_domain+inventory_domain+'app/production_stock_return_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}
	// function change_stock_status (unitname,grn_trn_id,total_qty,unit_id,godwn,product_name,grn_no,grn_date,product_id,batch_id,batch_no,reprocess_qc) 
	function change_stock_status (return_id,total_qty,product_id,godown_id) 
	{
		
		$("#total_qty").val(total_qty);
		$("#tqty").html(total_qty);
		$("#return_id").val(return_id);
		$("#from_godown_id").val(godown_id);
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/production_stock_return_list/',
			data: {
				mode : "get_store_details",
				return_id : return_id,
			},
			success: function(response)
			{

					var data = jQuery.parseJSON(response);
				// console.log(data)
				$(".unitname").html(data.unit_name);
				
				$("#godwn").html(data.gd_name);
				$("#proname").html(data.product_name);
				
				$("#unit_id").val(data.base_unit);
				$("#product_id").val(data.product_id);
				
				$('#store_accept_modal').modal('show');

				// if(godown_id > 0){
				// 	load_child_godown_list(godown_id);
				// }

				show_data();
				
			}
		});
	}
function add_field()
{

	 if ($("#godown_id").val() == ""){
		toastr.warning("Select Godown", "ERROR");
		return false;
	}

	 if ($("#aqty").val() == ""){
		toastr.warning("Enter Quantity", "ERROR");
		return false;
	}
	var return_id=$("#return_id").val();
	var godown_id=$("#godown_id").val();
	var qty=$("#aqty").val();
	var unit_id=$("#unit_id").val();
	var edit_id=$("#edit_id").val();
	var product_id=$("#product_id").val();
	var from_godown_id = $("#from_godown_id").val();
	
	var used_qty=parseFloat($("#used_qty").val()).toFixed(5);
	var total_qty=parseFloat($("#total_qty").val()).toFixed(5);
	if(isNaN(used_qty)){ used_qty=0; }
	if(isNaN(total_qty)){ total_qty=0; }
	var tusedqty=parseFloat(used_qty)+parseFloat(qty);
	if(total_qty<tusedqty){
		toastr.warning("Qty Issue", "WARNING");
		return false;
	}

	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_stock_return_list/',
		data: { 
			mode : "fieldadd",
			return_id:return_id,
			godown_id:godown_id,
			qty:qty,
			unit_id:unit_id,
			edit_id:edit_id,
			product_id:product_id,
			from_godown_id: from_godown_id
			
		},
		success: function(response)
		{

			$("#edit_id").val("");
			$("#godown_id").val("");
			$("#aqty").val("");
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
}

function show_data()
{
	//Loading();
	var eid=$('#return_id').val();
	
Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_stock_return_list/',
		data: { mode : "load_tempoutward",eid:eid},
		success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);		
				Unloading();				
			}		

		});
	}
function delete_temp_data(id)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/production_stock_return_list/',
			data: { mode : "delete_temp_data",  id : id },
			success: function(response)
			{
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("STOCK RETURN REJECTED", "SUCCESS");
						show_data();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();	
			}
		});
	}
	}

	function delete_data(id)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+inventory_domain+'app/production_stock_return_list/',
				data: { mode : "delete_data",  eid : id },
				success: function(response)
				{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("STOCK RETURN REJECTED", "SUCCESS");
						load_datatable();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}

	}

function save_store_accept()
{
	var return_id=$("#return_id").val();
	var remark=$("#remark").val();
	
	var used_qty=parseFloat($("#used_qty").val()).toFixed(5);
	var total_qty=parseFloat($("#total_qty").val()).toFixed(5);
	if(isNaN(used_qty)){ used_qty=0; }
	if(isNaN(total_qty)){ total_qty=0; }

	if(total_qty!=used_qty){
		
		toastr.warning("Qty Issue", "WARNING");
		return false;
	}

	   if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
			return false;
		}
	}
	
	

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_stock_return_list/',
		data: { 
			mode : "save_store_accept",
			return_id:return_id,
			remark:remark,
			
		},
		success: function(response)
		{
			load_datatable();
			$('#store_accept_modal').modal('hide');

		}
	});
}


function load_child_godown_list(to_godown_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_stock_return_list/',
		data: { 
			mode : "load_child_godown_list",
			godown_id : to_godown_id
		},
		success: function(response)
		{
			$('#godown_id').empty().html(response);
			$('#godown_id').select2({
				width : "100%"
			});
			Unloading();
		}
	});
}