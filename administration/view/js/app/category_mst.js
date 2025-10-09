$(document).ready(function() {
load_category_datatable();		
// validate vendor add form on keyup and submit
$("#category_add").validate({
	rules: {
		cat_name: {
			required: true
		},
		cat_parent: {
			required: true
		},
		
	},
	messages: {
		cat_name: {
			required: "Enter Sub Category Name"			
		},
		cat_parent: {
			required: "Please Select Category "
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
		e_cat_name: {
			required: true
		},
		e_cat_parent: {
			required: true
		},
		
	},
	messages: {
		e_cat_name: {
			required: "Enter Sub Category Name"			
		},
		e_cat_parent: {
			required: "Please Select Category "
		},
	}
});		

});
$("#category_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#category_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var cat_name=$("#cat_name").val();
	var cat_parent=$("#cat_parent").val();
	var branch_id = $("#abranch_id").val();
/*
	var form_data = {
		cat_name: cat_name,
		cat_parent: cat_parent,
		branch_id: branch_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};	*/

	 var form_data=new FormData(this);

    var token	=  $("#token").val();	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/categorymst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var data = JSON.parse(response);
			var responsevalue=data.msg;
			if(responsevalue.trim() == '1') {
			if(data.direct_product_add == 1){
					if(data.product_add_type == 'product_add'){
						$("#modal-add-category").modal("hide");
						$('#product_category').val(data.inserid);	
						$("#s2id_product_category .select2-chosen").text(data.category_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_category").trigger('change');
						Unloading();
					}
				}else{
						toastr.success("CATEGORY ADDED SUCCESSFULLY", "SUCCESS");
						get_category_dropdown('cat_parent');
						Unloading();
						load_category_datatable();
					}				
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#category_add').trigger('reset');
			$('#abranch_id').select2("val","1000");
			$('#cat_parent').select2("val","");
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
	var form_data = {
		eid :$("#edit_id").val(),
		e_cat_name: $("#e_cat_name").val(),
		e_cat_parent: $("#e_cat_parent").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/categorymst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("CATEGORY UPDATED SUCCESSFULLY", "SUCCESS");
				load_category_datatable();
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
	load_category_datatable();
}
function delete_category(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/categorymst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("CATEGORY DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_category(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/categorymst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#edit_id").val(obj.cat_id);
			$("#edit_pid").val(obj.cat_pid);
			get_category_dropdown('e_cat_parent');
			$("#e_cat_name").val(obj.cat_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			setTimeout(function(){
				$("#FormEditunit").valid()
				$("#ModalEditAccount").modal("show");
			},200)
			
			Unloading();
		}
	});	
}
function get_category_dropdown(sel_id)
{
	//alert(sel_id);
	var id = $('#edit_pid').val();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/categorymst/',
		data: { mode : "get_category_dropdown_data",id:id },
		success: function(response)
		{
			$('#'+sel_id).html(response);
			Unloading();
		}
	});	
}

function load_category_datatable(){
	var branch_id = $('#branch_id').val();
	//alert(branch_id);
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
			"sAjaxSource": root_domain+administration_domain+'app/categorymst/',
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

function exportCsv() {
	var branch_id = $('#branch_id').val();

	var url = root_domain +'generate_export?mode=administrator_master_category&branch_id=' + encodeURIComponent(branch_id);
	window.location.href = url;
}


function modal_remove(){
	$('#modal-add-category').modal('hide');
}
function open_model(){
	$('#modal-excel-category').modal('show');
}