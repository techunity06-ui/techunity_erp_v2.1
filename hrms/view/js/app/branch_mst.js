var datatable;
$(document).ready(function() {
	load_branch_datatable();

// validate the form when it is submitted        
$("#branch_add").validate({
	rules: {
		branch_name: {
			required: true
		},
		stateid: {
			required: true
		},
		cityid: {
			required: true
		}
	},
	messages: {
		branch_name: {
			required: "Enter Branch Name"			
		},
		stateid: {
			required: "Select State"			
		},
		cityid: {
			required: "Select City"			
		}
	}
}); 
// validate edit form on keyup and submit
$("#FormEditBranch").validate({
	rules: {
		edit_branch_name: {
			required: true
		},
		edit_stateid: {
			required: true
		},
		edit_cityid: {
			required: true
		}
	},
	messages: {
		edit_branch_name: {
			required: "Enter Branch Name"			
		},
		edit_stateid: {
			required: "Select State"	
		},
		edit_cityid: {
			required: "Select City"			
		}
	}
});		

});
$("#branch_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#branch_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		branch_name: $("#branch_name").val(), 
		branch_address: $("#branch_address").val(),
		countryid: $("#countryid").val(),
		zoneid: $("#zoneid").val(),
		stateid: $("#stateid").val(),
		cityid: $("#cityid").val(),
		branch_pincode: $("#branch_pincode").val(),
		branch_status: $("#branch_status").val(),
		branch_model: $("#branch_model").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/branch_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var response = JSON.parse(response);
			var responsevalue=response.res;
			if(responsevalue.trim() == '1') {
				toastr.success("BRANCH ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_branch_datatable();
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("BRANCH ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-category-modal").modal("hide");
				$('#branch_id').append('<option value='+response.branch_id+'>'+response.branch_name+'</option>');
				$('#branch_id').val(response.branch_id);
				$("#branch_id").trigger('change');
				$('#branch_add').trigger('reset');
				$('#addprocat').hide();
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(responsevalue.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$('#branch_add').trigger('reset');		
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditBranch").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditBranch").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid: $("#edit_id").val(),
		branch_name: $("#edit_branch_name").val(), 
		branch_address: $("#edit_branch_address").val(),
		countryid: $("#edit_countryid").val(),
		stateid: $("#edit_stateid").val(),
		cityid: $("#edit_cityid").val(),
		zoneid: $("#edit_zoneid").val(),
		branch_pincode: $("#edit_branch_pincode").val(),
		branch_status: $("#edit_branch_status").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/branch_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("BRANCH UPDATED SUCCESSFULLY", "SUCCESS");
				load_branch_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$("#ModalEditBranch").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_branch(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/branch_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("BRANCH DELETE SUCCESSFULLY", "SUCCESS");		
					load_branch_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_branch(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/branch_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditBranch").modal("show");
			$('#edit_stateid').html(obj.state_html);
			$('#edit_cityid').html(obj.city_html);
			$('#edit_zoneid').html(obj.zone_html);
			$("#edit_id").val(id);				
			$("#edit_branch_name").val(obj.branch_name);  
			$("#edit_branch_address").val(obj.branch_address);
			$("#edit_countryid").select2("val",obj.countryid);
			$("#edit_stateid").select2("val",obj.stateid);
			$("#edit_cityid").select2("val",obj.cityid);
			$("#edit_zoneid").select2("val",obj.zoneid);
			$("#edit_branch_pincode").val(obj.branch_pincode);
			$("#edit_branch_status").select2("val",obj.branch_status);
			Unloading();
		}
	});	
}
function load_branch_datatable(){
	datatable = $("#branch-table").dataTable({
		"bStateSave": true,
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
		"sAjaxSource": root_domain + hrms_domain + 'app/branch_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function load_state(parentid,control,val1)
{	
	$.ajax({
	type: "POST",
	url: root_domain+'app/customer/',
	data: { mode : "load_state",  id : parentid},
	success: function(responce){
				//console.log(responce);
				$('#'+control).html(responce);
				$("#"+control).select2("val",val1);
			}
	});

}
function add_state()
{
	if($("#countryid").val()=='')
	{
		toastr.warning("Please Select the Country", "WARNING");
	}
	else{
		$("#bs-example-modal-state").modal("show");
		$("#countryid").val($("#countryid").val());
	}
}
function load_city(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});

}
function add_city()
{
	if($("#stateid").val()=='')
	{
		toastr.warning("Please Select the State", "WARNING");
	}
	else{
		$("#bs-example-modal-city").modal("show");
		$("#state_id").val($("#stateid").val());
	}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/branch_mst/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE BRANCH STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			load_branch_datatable();
			Unloading();
		}
	});
}