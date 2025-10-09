$(document).ready(function() {
	load_mspec_datatable();	   

// validate vendor add form on keyup and submit
$("#mspec_add").validate({
	rules: {
		m_type_name: {
			required: true
		},
	
	},
	messages: {
		m_type_name: {
			required: "Enter Material Type"			
		}
	
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	e_m_type_name: {
			required: true
		}
	},
	messages: {
		e_m_type_name: {
			required: "Enter Material Type"			
		}
	}
});		

});
$("#mspec_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#mspec_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var m_type_name=$("#m_type_name").val();
	var m_type_width=$("#m_type_width").val();
	var m_type_height=$("#m_type_height").val();
	var m_type_thick=$("#m_type_thick").val();
	var m_type_density=$("#m_type_density").val();
	var branch_id=$("#abranch_id").val();

	var form_data=new FormData(this);
	
/*	var form_data = {
		m_type_name: m_type_name,
		m_type_width: m_type_width,
		m_type_height: m_type_height,
		m_type_thick: m_type_thick,
		m_type_density: m_type_density,
		branch_id: branch_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};*/	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/material_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("MATERIAL SPECIFICATION ADDED SUCCESSFULLY", "SUCCESS");
				$('#m_type_name').val();
				Unloading();
				load_mspec_datatable();
			}
			
			$('#mspec_add').trigger('reset');
			$('#abranch_id').select2("val",1000);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditunit").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditunit").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	
	var form_data=new FormData(this);	
	form_data.append('eid', $("#edit_id").val());
	form_data.append('mode', 'edit');
	/*form_data = {
		eid :$("#edit_id").val(),
		e_m_type_name: $("#e_m_type_name").val(),
		e_m_width: $("#e_m_width").val(),
		e_m_height: $("#e_m_height").val(),
		e_m_thick: $("#e_m_thick").val(),
		e_m_density: $("#e_m_density").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};*/	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/material_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("MATERIAL SPECIFICATION UPDATED SUCCESSFULLY", "SUCCESS");
				load_mspec_datatable();
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
	load_mspec_datatable();
}

function delete_parameter(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/material_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("MATERIAL SPECIFICATION DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
						
					toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_parameter(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/material_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.ms_id);
			$("#e_m_type_name").val(obj.ms_name);
			$.each( obj.param, function( key, value ) {
				$("#"+key).val(value);
			});
			$("#edit_formula_id").val(obj.formula);
			$/*("#e_m_width").val(obj.m_type_width);
			$("#e_m_height").val(obj.m_type_height);
			$("#e_m_thick").val(obj.m_type_thick);
			$("#e_m_density").val(obj.m_type_density);*/
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditunit").valid()
			Unloading();
		}
	});	
}

function get_product_dropdown(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/material_mst/',
		data: { mode : "get_all_product",id:id },
		success: function(response)
		{
			$('#e_p_product').html(response);
			$('#e_p_product').select2("val",id);
			Unloading();
		}
	});	
}

function load_mspec_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/material_mst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
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

function copy_fun(value){
	var edit_id = $('#edit_id').val();
	if(edit_id==''){
		var tax=$('#formula_id').val();
		var new_tax=tax+value;
		$('#formula_id').val(new_tax);
	}else{
		var tax=$('#edit_formula_id').val();
		var new_tax=tax+value;
		$('#edit_formula_id').val(new_tax);
	}
	
	$('#bs-example-component_code').modal("hide");
}

function get_formula()
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/material_mst/',
		data: { mode : "get_formula" },
		success: function(response)
		{
			$('#parameter_data').html(response);
			$("#bs-example-component_code").modal("show");
			Unloading();
		}
	});	
}