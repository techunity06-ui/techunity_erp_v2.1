//var datatable;
$(document).ready(function() {
	$("#import_customer").validate({
		rules: {
			excel_file: {
				required: true			
			}
		},
		messages: {
			excel_file: {
				required: "Select CSV File"			
			}
		}
	});
	load_bom_datatable();
});

$("#import_customer").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	if (!$("#import_customer").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var token	=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/import_product_opening_stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var response=data.res;
			Unloading();
			if(response == '1') {
				$('#msg').html('<span style="color:green">Data Cheked Successfully</span>');
				$('#check_button').hide();
				$('#mode').val('import_data');
				$('#import_button').show();
			}
			else if(response == '-1')
			{
				toastr.info("SELECT WRONG FILE", "INFO")
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '0')
			{
				$('#msg').html('<span style="color:red"> Coloums Does Not Match Please Check With demo File</span>');
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '3')
			{
				$('#msg').html('<span style="color:red"> Coloum Name Does Not Match Please Check With demo File</span>');
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '4')
			{
				toastr.success("CUSTOMER IMPORT SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'import_bom_print/'+data["temp_id"];		
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '5')
			{
				$('#import_customer').trigger('reset');
				 $('#check_button').show();
				$('#mode').val('check_data');
				$('#import_button').hide();
				show_importedcust_data();
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function show_importedcust_data(total)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/import_product_opening_stock/',
	data: { mode : "show_importedcustdata"},
	success: function(responce){
				console.log(responce);
				Unloading();
				 $('#imported_data_section').show();
				$('#temp_custdata').html(responce);
				 
			}
	});
				
}
function add_bom1(main_id){
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/import_product_opening_stock/',
	data: { mode : "add_bom_new",main_id:main_id},
	success: function(responce){
				console.log(responce);
				if(responce==="1"){
					window.location=root_domain+'bom_upload_list';
					
				}else if(responce==="-1"){
					toastr.info("ERROR In Upload", "INFO");
				}
				Unloading();
				 
				 
			}
	});
}
function open_update(i_id){
	$.ajax({
		type: "POST",
		url: root_domain+ 'app/import_product_opening_stock/',
		data: { mode : "preedit",i_id:i_id },
		success: function(response)
		{
			$("#bs-add_bom_data").modal("show");
			
			var arr = jQuery.parseJSON(response);
			$("#bom_temp_id").val(i_id);
			$("#product_name").val(arr.product_name);
			$("#unit_name").val(arr.unit_name);
			$("#qty").val(arr.qty);
			$("#product_id").select2("val",arr.product_id);
			$("#unit_id").select2("val",arr.unit_id);
			check_unit();
		}
	});	
}

function check_unit(){
	var product_id=$("#product_id").val();
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+ 'app/import_product_opening_stock/',
			data: { mode : "check_bom",product_id:product_id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				$("#sy_unit_name").val(arr.unit_name);
				$("#unit_id").val(arr.product_base_unit);
			}
		});	
	}
}
$("#ind_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#ind_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+ 'app/import_product_opening_stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+crm_domain + 'inquiry_list';
				load_inquiry_datatable();
				location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				//window.location=root_domain+crm_domain + 'inquiry_list';
				location.reload();
			}
			Unloading();
			//$('#inquiry_add').trigger('reset');
			$("#bs-add_bom_data").modal("hide");	
			$("#unit_id").select2("val","");
			$("#product_id").select2("val","");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_bom_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
	
	$("#dispatch-list-datatable").dataTable({
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
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/import_product_opening_stock/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch" }, {"name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}


function delete_bom(bom_temp_id){
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/import_product_opening_stock/',
			data: { mode : "delete",  bom_temp_id : bom_temp_id },
			success: function(response)
			{

					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("BOM DELETE SUCCESSFULLY", "SUCCESS");
						load_bom_datatable();
						Unloading();
					}else if(response.trim() == "2") {
						toastr.info("Please Remove Used In Bom", "INFO");
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}							
				}
			});	
	}
}