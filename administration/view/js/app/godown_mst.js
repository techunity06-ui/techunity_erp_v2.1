$(document).ready(function() {
	load_godown_datatable();	       

// validate vendor add form on keyup and submit
$("#mspec_add").validate({
	rules: {
		gd_name: {
			required: true
		},
	
	},
	messages: {
		gd_name: {
			required: "Enter Godown Name"			
		}
	
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
		e_gd_name: {
			required: true
		}
	},
	messages: {
		e_gd_name: {
			required: "Enter Godown Name"			
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
	
	var gd_name=$("#gd_name").val();
	var branch_id=$("#abranch_id").val();
	var p_gd_id=$("#p_gd_id").val();
	var gd_address =$("#gd_address").val();
	var form_data = {
		gd_name: gd_name,
		gd_address : gd_address,
		branch_id: branch_id,
		p_gd_id:p_gd_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/godown_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;

			if(msg.trim() == '1') {				
				toastr.success("GODOWN ADDED SUCCESSFULLY", "SUCCESS");
				$('#m_type_name').val();
				load_godown_datatable();
				location.reload();
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			// datatable.fnReloadAjax();
			$('#mspec_add').trigger('reset');
			$("#p_gd_id").val('0');
			$("#ModalAddLocation").modal("hide");	
			

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
		e_gd_name: $("#e_branch").val(),
		e_gd_name: $("#e_gd_name").val(),
		branch_id: $("#e_branch_id").val(), 
		edit_p_gd_id : $("#edit_p_gd_id").val(),
		e_gd_address : $("#e_gd_address").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/godown_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("GODOWN UPDATED SUCCESSFULLY", "SUCCESS");
				load_godown_datatable();
				location.reload();
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
	load_godown_datatable();
}

function delete_parameter(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/godown_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("GODOWN DELETE SUCCESSFULLY", "SUCCESS");
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
	$("#FormEditunit").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/godown_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.gd_id);
			$("#e_gd_name").val(obj.gd_name);
			$("#edit_p_gd_id").val(obj.p_gd_id);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#e_gd_address").val(obj.gd_address);
			$(".branch_row").hide();
			Unloading();
		}
	});	
	
	
}

function reload_godown_datatable(){
	var branch = $("#branch_id").val();

	location.href = root_domain+administration_domain+'godown_list/' + branch;
}

function load_godown_datatable(){
	var branch_id = $('#branch_id').val();

	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/godown_mst/',
		data: { mode : "get_location_tree", branch_id : branch_id,  },
		success: function(data){
			$('#nestable_list_3').empty();
			$('#nestable_list_3').html(data);
			// $('#nestable_list_3').nestable();	
			$('.dd').nestable('collapseAll');
			Unloading();
		}	
	});	

	/*datatable = $("#dynamic-table").dataTable({
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
			"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+administration_domain+'app/godown_mst/',
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
		$('.dataTables_length select').addClass('form-control');*/
	// validate the comment form when it is submitted 
}


function show_add_location_form(p_gd_id,branch_id){

	$('#p_gd_id').val(p_gd_id);
	if(p_gd_id > 0){
		$("#row_branch").hide();
	}else{
		$("#row_branch").show();
	}
	
	$("#abranch_id").val(branch_id);
	$('#ModalAddLocation').modal('show');
}

function exportCsv() {
	var branch_id = $('#branch_id').val();
	var url = root_domain +'generate_export?mode=administrator_master_godown&branch_id=' + encodeURIComponent(branch_id);
	window.location.href = url;
}