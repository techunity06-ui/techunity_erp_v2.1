$(document).ready(function() {
	load_trade_india_api();
	// validate the comment form when it is submitted        

	// validate vendor add form on keyup and submit
$("#trade_india_add").validate({
	rules: {
		trade_india_profile_id: {
			required: true
		},
		trad_india_api_key: {
			required: true
		},
		trade_india_user_id: {
			required: true
		},
		source_id: {
			required: true
		}
	},
	messages: {
		trade_india_profile_id: {
			required: "Enter Profile Id"			
		},
		trad_india_api_key: {
			required: "Enter API Key"			
		},
		trade_india_user_id: {
			required: "Enter User Id"			
		},
		source_id: {
			required: "Enter source id"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#trade_india_edit").validate({
	rules: {
		edit_trade_india_user_id: {
			required: true
		},
		edit_trade_india_profile_id: {
			required: true
		},
		edit_trad_india_api_key: {
			required: true
		},
		edit_source_id: {
			required: true
		}
	},
	messages: {
		edit_trade_india_user_id: {
			required: "Enter User Id"			
		},
		edit_trade_india_profile_id: {
			required: "Enter Profile Id"			
		},
		edit_trad_india_api_key: {
			required: "Enter API Key"			
		},
		edit_source_id: {
			required: "Enter source id"			
		}
	}
});		

});
function add_trade_india() {		
	
	var trade_india_user_id=$("#trade_india_user_id").val();
	var trade_india_profile_id=$("#trade_india_profile_id").val();
	var trad_india_api_key=$("#trad_india_api_key").val();
	var source_id=$("#source_tradeindia").val();
	
	if(!trade_india_user_id){		
		toastr.warning("Enter User Id", "ERROR");
		$("#trade_india_user_id").focus();
		return false;
	}
	
	if(!trade_india_profile_id){		
		toastr.warning("Enter Profile Id", "ERROR");
		$("#trade_india_profile_id").focus();
		return false;
	}
	
	if(!trad_india_api_key){		
		toastr.warning("Enter Api", "ERROR");
		$("#trad_india_api_key").focus();
		return false;
	}
	
	if(!source_id){		
		toastr.warning("Choose Source", "ERROR");
		$("#source_indiamart").select2('focus');
		return false;
	}
	
	var form_data = {
		trade_india_user_id: trade_india_user_id,
		trade_india_profile_id: trade_india_profile_id,
		trad_india_api_key: trad_india_api_key,
		source_id: source_id,
		mode:"Add",
		//cust_ind_model:$("#cust_ind_model").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/tradeindia_api_key/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("API Configuration SUCCESSFULLY", "SUCCESS");
				$("#trade_india_user_id").val("");
				$("#trade_india_profile_id").val("");
				$("#trad_india_api_key").val("");
				$("#source_tradeindia").select2("val","");
				Unloading();
				load_trade_india_api();
			}
			else if(response.trim() == '2') {
				toastr.success("INDUSTRY ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-group-modal").modal("hide");
				$('#product_group').append('<option value='+resp.ci_id+'>'+resp.ci_name+'</option>');	
				$('#product_group').select2("val",resp.ci_id);
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
		},
	});
	
}
//var editReq = null;
function update_trade_india() {
			
	var form_data = {
		eid :$("#edit_tradeindia").val(),
		trade_india_user_id: $("#edit_trade_india_user_id").val(),
		trade_india_profile_id: $("#edit_trade_india_profile_id").val(),
		trad_india_api_key: $("#edit_trad_india_api_key").val(),
		source_id: $("#edit_source_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/tradeindia_api_key/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_trade_india_api();
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
			$("#ModalEditTradeindia").modal("hide");					
		},
	});
}

function delete_cust_ind(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/tradeindia_api_key/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						load_trade_india_api();
					}
					else if(response.trim() == "-1") {
						toastr.error("USED INDUSTRY CAN'T BE DELETED !!!", "WARNING"); 
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}
}
function edit_trade_india(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/tradeindia_api_key/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditTradeindia").modal("show");
			$("#edit_tradeindia").val(obj.i_id);
			$("#edit_trade_india_user_id").val(obj.trade_india_user_id);
			$("#edit_trade_india_profile_id").val(obj.trade_india_profile_id);
			$("#edit_trad_india_api_key").val(obj.trad_india_api_key);
			$('#edit_source_id').select2("val",obj.source_id);
			Unloading();
		}
	});	
}
function load_trade_india_api(){
	$("#trade_india_ta").dataTable({
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
		"sAjaxSource": root_domain+administration_domain+'app/tradeindia_api_key/',
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