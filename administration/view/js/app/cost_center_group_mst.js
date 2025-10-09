$(document).ready(function() {
	load_CostCenterGroup_datatable();	        
// validate vendor add form on keyup and submit
$("#CostCenterGroup_add").validate({
	rules: {
		CostCenterGroup_name: {
			required: true,
		}
	},
	messages: {
		CostCenterGroup_name: {
			required: "Enter Cost Center Group Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditCostCenterGroup").validate({
	rules: {
	edit_CostCenterGroup_name: {
			required: true
		}
	},
	messages: {
		edit_CostCenterGroup_name: {
			required: "Enter Cost Center Group Name"			
		}
	}
});		

});
$("#CostCenterGroup_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#CostCenterGroup_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var CostCenterGroup_name=$("#CostCenterGroup_name").val();

	var form_data = {
		CostCenterGroup_name: CostCenterGroup_name,
		mode:'Add',
		is_ajax: 1
	};	
	//alert(CostCenterGroup_name);
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/cost_center_group_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//alert(response);
			if(response.trim() == '1') {				
				toastr.success("Cost Center Group ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_CostCenterGroup_datatable();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#CostCenterGroup_add').trigger('reset');
			$("#tax_id").select2("val",'');			
			Unloading();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditCostCenterGroup").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCostCenterGroup").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		edit_CostCenterGroup_name: $("#edit_CostCenterGroup_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/cost_center_group_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("Cost Center Group UPDATED SUCCESSFULLY", "SUCCESS");
				load_CostCenterGroup_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_reload()
{
	load_CostCenterGroup_datatable();
}
function delete_cost_center_group(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/cost_center_group_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("Cost Center Group DELETE SUCCESSFULLY", "SUCCESS");								
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
						$("#tax_id").select2("val",'');			
		
					toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_cost_center_group(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/cost_center_group_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_CostCenterGroup_name").val(obj.cost_group_name);
			Unloading();
		}
	});	
}

function load_CostCenterGroup_datatable(){
	//var branch_id = $('#branch_id').val();
	//alert('hii');
	datatable = $("#cost-center-group-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+administration_domain+'app/cost_center_group_mst/',
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
	// validate the comment form when it is submitted
}