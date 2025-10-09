$(document).ready(function() {
	load_source_mst_datatable();

// validate vendor add form on keyup and submit
$("#category_mst").validate({
	rules: {
		party_cat: {
			required: true
		},
	},
	messages: {
		party_cat: {
			required: "Enter Party Category"			
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditpartycatMst").validate({
	rules: {
		e_party_cat: {
			required: true
		}
	},
	messages: {
		e_party_cat: {
			required: "Enter Party Category"			
		}
	}
});		

});
$("#category_mst").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#category_mst").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		party_cat: $("#party_cat").val(),
		mode:"Add",
		source_mst_model:$("#source_mst_model").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/party_category/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("PARTY CATEGORY ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_source_mst_datatable();
			}
			else if(response.trim() == '2') {
				toastr.success("PARTY CATEGORY ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-group-modal").modal("hide");
				$('#product_group').append('<option value='+resp.rb_id+'>'+resp.rb_name+'</option>');	
				$('#product_group').select2("val",resp.rb_id);
				$("#product_group").trigger('change');
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
			$('#category_mst').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditpartycatMst").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditpartycatMst").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_party_cat: $("#e_party_cat").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/party_category/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				$('#ModalEditPartyCatMst').modal("hide");
				load_source_mst_datatable();
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
			$("#FormEditpartycatMst").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_source_mst(id) 
{
	var r= confirm(" Are you sure, you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain+'app/party_category/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("PARTY CATEGORY DELETE SUCCESSFULLY", "SUCCESS");
						load_source_mst_datatable();
					}
					else if(response.trim() == "-1") {
						toastr.error("USED REASON CAN'T BE DELETED !!!", "WARNING"); 
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}
	
}
function edit_source_mst(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/party_category/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditPartyCatMst").modal("show");
			$("#edit_id").val(obj.cc_id);
			$("#e_party_cat").val(obj.cc_name);
			Unloading();
		}
	});	
}
function load_source_mst_datatable(){
	$("#source-mst-datatable").dataTable({
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
		"sAjaxSource": root_domain+crm_domain+'app/party_category/',
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