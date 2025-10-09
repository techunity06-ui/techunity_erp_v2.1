$(document).ready(function() {
	load_CostCenter_datatable();	        
// validate vendor add form on keyup and submit
$("#CostCenter_add").validate({
	rules: {
		CostCenter_name: {
			required: true,
		}
	},
	messages: {
		CostCenter_name: {
			required: "Enter Cost Center Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditCostCenter").validate({
	rules: {
	edit_CostCenter_name: {
			required: true
		}
	},
	messages: {
		edit_CostCenter_name: {
			required: "Enter Cost Center Name"			
		}
	}
});		

});
$("#CostCenter_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#CostCenter_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var CostCenter_name=$("#CostCenter_name").val();
	var cost_group_id=$("#cost_group_id").val();

	var form_data = {
		CostCenter_name: CostCenter_name,
		cost_group_id: cost_group_id,
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/cost_center_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("Cost Center ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_CostCenter_datatable();
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
			$('#CostCenter_add').trigger('reset');
			$("#cost_group_id").select2("val",'');
			//$("#tax_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

$("#cost_center_group_add").on('submit',function(e){
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#cost_center_group_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		cost_group_name :$("#cost_group_name").val(),
		mode:'add_cost_center_group',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/cost_center_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var res = response.split('-');
			if(res[0].trim() == '1') {
				$("#bs-example-modal-cost-center-group").modal("hide");
				toastr.success("COST CENTER GROUP ADDED SUCCESSFULLY", "SUCCESS");
				$('#cost_group_id').append('<option value='+res[1]+'>'+res[2]+'</option>');
				$("#cost_group_id").trigger('change');
				$('#cost_group_id').select2("val",res[1]);
				load_CostCenter_datatable();
				Unloading();						
			}
			else if(res[0].trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(res[0].trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#bs-example-modal-common-category").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

//var editReq = null;
$("#FormEditCostCenter").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCostCenter").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		edit_CostCenter_name: $("#edit_CostCenter_name").val(),
		cost_group_id:$("#e_cost_group_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/cost_center_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("Cost Center UPDATED SUCCESSFULLY", "SUCCESS");
				load_CostCenter_datatable();
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
	load_CostCenter_datatable();
}
function delete_cost_center(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/cost_center_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("Cost Center DELETE SUCCESSFULLY", "SUCCESS");								
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
function edit_cost_center(id)
{
	$("#FormEditCostCenter").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/cost_center_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_CostCenter_name").val(obj.cost_center_name);
			$("#e_cost_group_id").select2("val", obj.cost_group_id);
			Unloading();
		}
	});	
}

function add_cost_center_group(){

	$("#bs-example-modal-cost-center-group").modal("show");
	//$("#countryid").val($("#countryid").val());
	
}

function load_CostCenter_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#cost-center-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/cost_center_mst/',
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