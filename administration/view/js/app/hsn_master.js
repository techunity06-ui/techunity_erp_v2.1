$(document).ready(function() {
	load_impregnation_msteter_datatable();	
	// validate vendor add form on keyup and submit
	$("#hsn_add").validate({
		rules: {
			
			hsn_code: {
				required: true,
				number : true,
				maxlength : 10,
			},
			hsn_desc: {
				required: true
			},
			sale_gst: {
				required: true
			}
		},
		messages: {
			
			hsn_code: {
				required: "Please Enter HSN Code",
				number : "Please Enter Only Number",
				maxlength : "maximum 10 Character valid"
			},
			hsn_desc: {
				required: "Please Enter HSN Description "
			},
			sale_gst: {
				required: "Please Enter Sale GST "
			},
		}
	}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	
	edit_hsn_code: {
			required: true,
			number : true,
			maxlength : 10,
		},
	edit_hsn_desc: {
			required: true
		},
	edit_sale_gst:{
			required: true
		},
	},
	messages: {
		
		edit_hsn_code: {
			required: "Please Enter HSN Code",
			number : "Please Enter Only Number",
			maxlength : "maximum 10 Character valid"
		},
		edit_hsn_desc: {
			required: "Please Enter HSN Desc"
		},
		edit_sale_gst:{
			required: "Please Enter Sale GST "
		},
	}
});		

});

jQuery('.numbersOnly').keyup(function () {
this.value = this.value.replace(/[^0-9\.]/g,'');
});
$("#hsn_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hsn_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	

	if($("#direct_hsn_add").val() == 1){
		var mode_v = 'add';
	}else{
		var mode_v = $("#mode").val();
	}	

	var hsn_code=$("#hsn_code").val();
	var hsn_desc=$("#hsn_desc").val();
	var sale_gst=$("#sale_gst").val();
	var form_data = {
		hsn_code: hsn_code,
		hsn_desc: hsn_desc,
		sale_gst: sale_gst,
		direct_hsn_add:$("#direct_hsn_add").val(),
		hsn_add_type:$("#hsn_add_type").val(),
		mode:mode_v,
		is_ajax: 1,
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/hsn_master/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//alert(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				
				Unloading();
				if(resp.direct_hsn_add == 1){
					/*alert(resp.hsn_add_type.toLowerCase());*/
					if(resp.hsn_add_type.toLowerCase() == 'product'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}
					// else if(obj.hsn_add_type == 'PRODUCT_INVOICE'){
					// 	$("#modal-add-ledger").modal("hide");
					// 	$('#product_hsn').append('<option value='+obj.inserid+'>'+obj.hsn_code+'</option>');	
					// 	$('#product_hsn').select2("val",obj.inserid);
					// 	$("#product_hsn").trigger('change');
					// }
				}else{
					$("#sale_gst").select2("val","");
					$("#sale_gst").val("");
					load_impregnation_msteter_datatable();
				}
				
			}
			if(msg.trim() == '2') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.g_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
				load_impregnation_msteter_datatable();
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
			$('#hsn_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
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
		hsn_code: $("#edit_hsn_code").val(),
		hsn_desc: $("#edit_hsn_desc").val(),
		sale_gst: $("#edit_sale_gst").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/hsn_master/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success(" UPDATED SUCCESSFULLY", "SUCCESS");
				load_impregnation_msteter_datatable();
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

function submit_hsn_form(){
	var hsn_code = $("#hsn_code").val();
	var hsn_desc = $("#hsn_desc").val();
	var sale_gst = $("#sale_gst").val();
	var	direct_hsn_add = $("#direct_hsn_add").val();
	var	hsn_add_type = $("#hsn_add_type").val();

	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/hsn_master/',
		data: { mode : "add", hsn_code : hsn_code,hsn_desc:hsn_desc,
		sale_gst:sale_gst,direct_hsn_add:direct_hsn_add,hsn_add_type:hsn_add_type },
		success: function(response)
		{
			//alert(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				if(resp.direct_hsn_add == 1){
					if(resp.hsn_add_type == 'PRODUCT_INVOICE'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}else if(resp.hsn_add_type == 'PRODUCT_PURCHASE'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}else if(resp.hsn_add_type == 'PRODUCT_INQUIRY'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}else if(resp.hsn_add_type == 'PRODUCT_QUOTATION'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}else if(resp.hsn_add_type == 'PRODUCT_SALES_ORDER'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}else if(resp.hsn_add_type == 'PRODUCT_PROFORMA'){
						$("#modal-add-hsn").modal("hide");
						$('#product_hsn').append('<option data-salegst='+resp.sale_gst+' value='+resp.inserid+'>'+resp.hsn_code+'</option>');	
						$('#product_hsn').select2("val",resp.inserid);
						$("#product_hsn").trigger('change');
					}	
				}
			}
			if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#hsn_add').trigger('reset');							
		}
	});

}

function delete_reload()
{
	load_impregnation_msteter_datatable();
}

function delete_parameter(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/hsn_master/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success(" DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain+administration_domain+'app/hsn_master/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.hsn_id);
			$("#edit_hsn_code").val(obj.hsn_code);
			$("#edit_hsn_desc").val(obj.hsn_desc);
			$("#edit_sale_gst").select2("val", obj.sale_gst);
			Unloading();
		}
	});	
}

function load_impregnation_msteter_datatable(){
	//alert(administration_domain);
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
			"sAjaxSource": root_domain+administration_domain+'app/hsn_master/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
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
	var url = root_domain +'generate_export?mode=administrator_master_hsn';
	window.location.href = url;
}