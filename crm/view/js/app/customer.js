$(document).ready(function() {
	load_cust_datatable();
	load_address_table();
	load_contact_table();
	load_relation_table();
	load_consignee_data();
	show_led_attach_data();
	load_cust_dispatch();
	load_cust_competitor();
	get_forecast_pr();
	get_forecast_pr_month();

	var mode = $('#mode').val();
	if(mode=='Edit'){
		$("#cust_add").validate({
			rules: {
				cust_name:{
					required:true
				},
				cust_mobile: {
					number:true,
					required:true
				},
				/*cust_cat:{
					required:true
				},
				cust_ind:{
					required:true
				},
				cust_type:{
					required:true
				},
				cust_source:{
					required:true
				},
				cust_email:{
					email:true,
					required:true
				},
				gst_no:{
					maxlength:15,
					minlength:15
				}*/
			},
			messages: {
				cust_name:{
					required: "Customer name must be Enter"
				},
				cust_mobile: {
					number:"Enter Only number ",
					required: "Mobile must be Enter"
				},
				/*cust_cat:{
					required: "Party Category must be select"
				},
				cust_ind:{
					required: "Party Industry must be select"
				},
				cust_type:{
					required: "Customer Type must be select"
				},
				cust_source:{
					required: "Source / Refer By must be select"
				},
				cust_email:{
					email:"Enter Valid Email",
					required: "Email must be Enter"
				},
				gst_no:{
					maxlength:"Maximum Length For GST No. is reached",
					minlength:"Minimum Length For GST No. is 15"
				}*/

			}
		});
	}else{
		$("#cust_add").validate({
			rules: {
				// cust_name:{
				// 	required:true
				// },
				// cust_mobile: {
				// 	number:true,
				// 	required:true
				// },
			},
			messages: {
				// cust_name:{
				// 	required: "Customer name must be Enter"
				// },
				// cust_mobile: {
				// 	number:"Enter Only number ",
				// 	required: "Mobile must be Enter"
				// },			
			}
		});
	}
	
	/*$("#add_person_form").validate({
		rules: {
			con_first:{
				required:true
			},
			com_email:{
				required:true
			},
			con_mobile:{
				required:true
			}
		},
		messages: {
			cust_name:{
				required: "Customer name is must"
			},
			con_mobile: {
				number:"Enter Only number ",
				required: "Enter Mobile Number"
			},
			com_email:{
				email:"Enter Valid Email",
				required: "Enter Email please"
			}
		}
	});*/
	
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
});

$(".btn_close").click(function() {
	$("label.error").hide();
});

$("#cust_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#cust_add").valid()) {
		return false;
	}
	
	var mode = $('#mode').val();
	if(mode=='Edit'){
		if($("#cust_model").val()!="model"){
			if($("#addre").val()<=0){
				toastr.error("Add Address", "ERROR");
				return false;
			}
		}
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("CUSTOMER ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain + crm_domain +'customer_list';
			}
			else if(responsevalue.trim() == '0') {
				Unloading();
				toastr.error("Something Went Wrong", "ERROR");	
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("CUSTOMER ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_id').append('<option value='+data.cust_id+'>'+data.cust_name+'</option>');	
				$('#cust_id').select2("val",data.cust_id);
				$("#cust_id").trigger('change');
				$('#cust_add').trigger('reset');
				$('#inquiry_type_id').select2("val","9");
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("CUSTOMER UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + crm_domain +'customer_list';		
			}
			$('#cust_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_cust(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("CUSTOMER DELETE SUCCESSFULLY", "SUCCESS");
					load_cust_datatable();
				}
				else if(response.trim() == "-1") {
					toastr.error("USED CUSTOMER TYPE CAN'T BE DELETED !!!", "WARNING"); 
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}			
				Unloading();				
			}
		});	
	}
}
function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "load_state",  id : parentid, stateid: val1},
		success: function(responce){
			$('#'+control).html(responce);
			// $("#"+control).select2("val",val1);
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
		url: root_domain+administration_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
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
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
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
				toastr.info("SELECT WRONG FILE", "INFO");
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
				window.location=root_domain + crm_domain +'customer_list';		
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
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "show_importedcustdata"},
		success: function(responce){
			console.log(responce);
			Unloading();
			$('#imported_data_section').show();
			$('#temp_custdata').html(responce);
			
		}
	});
	
}

function load_cust_datatable(){
	var party_type = $('input[name="party_type"]:checked').val();
	var business_type = $('#business_type').val();
	var branch_id = $('#branch_id').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		/*"aoColumns": [
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true }
		
			], */
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[-1,10, 20, 50, 100], ["All",10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + crm_domain +'app/customer/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },
					{ "name": "party_type", "value": party_type },
					{ "name": "business_type", "value": business_type },
					{ "name": "branch_id", "value": branch_id },
					);
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

// Add address

function add_cust_address()
{
	if($("#c_add_address").val()==="")
	{		
		toastr.warning("Enter Location", "ERROR");
		return false;
	}
	// if($("#c_add_street").val()==="")
	// {		
	// 	toastr.warning("Enter Street", "ERROR");
	// 	return false;
	// }
	if($("#c_add_country").val()==="")
	{		
		toastr.warning("Select Country List", "ERROR");
		return false;
	}
	if($("#c_add_state").val()==="")
	{		
		toastr.warning("Select State List", "ERROR");
		return false;
	}
	if($("#c_add_city").val()==="")
	{		
		toastr.warning("Select City List", "ERROR");
		return false;
	}
	// if($("#c_add_zip").val()==="")
	// {		
	// 	toastr.warning("Select Zip List", "ERROR");
	// 	return false;
	// }
	Loading(true);
	
	var c_add_address=$('#c_add_address').val();
	var c_pincode=$('#c_pincode').val();
	var c_add_country=$('#c_add_country').val();
	var c_add_state=$('#c_add_state').val();
	var c_add_city=$('#c_add_city').val();
	var c_addr_defult = $('#c_addr_default').val();
	var cust_id=$('#eid').val();
	var edit_id=$('#edit_add_id').val();
	var mode='add_cust_address';
	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , c_add_address:c_add_address, c_pincode:c_pincode, c_add_country:c_add_country,c_add_state:c_add_state,c_add_city:c_add_city,cust_id:cust_id,c_addr_defult:c_addr_defult,edit_id:edit_id },
		success: function(responsevalue)
		{	
			//console.log(response);
			//var data = JSON.parse(response);
			//var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("ADDRESS ADDEDD SUCCESSFULLY", "SUCCESS");
				$('#c_add_address').val('');
				$('#c_pincode').val('');
				$('#c_add_country').select2('val',"101");
				$('#c_add_state').select2('val',"");
				$('#c_add_city').select2('val',"");
				$('#c_addr_default').prop('checked', false);
				$("#edit_add_id").val('');
				load_address_table();
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("ADDRESS ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');	
				$('#vender_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');				
				$("#vender_id").trigger('change');
				$("#cust_id").trigger('change');
				$('#cust_id').select2("val",data.cust_id);
				$('#vender_id').select2("val",data.cust_id);
				$('#c_add_country').select2('val',"");
				$('#c_add_state').select2('val',"");
				$('#c_add_city').select2('val',"");
				$('#cust_add').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("ADDRESS UPDATED SUCCESSFULLY", "SUCCESS");
				$('#c_add_address').val('');
				$('#c_pincode').val();
				$('#c_add_country').select2('val',"");
				$('#c_add_state').select2('val',"");
				$('#c_add_city').select2('val',"");
				Unloading();
				load_address_table();		
			}
			else if(responsevalue.trim() == '0')
			{	
				toastr.success("ADDRESS UPDATED SUCCESSFULLY", "SUCCESS");
				$('#c_add_address').val('');
				$('#c_add_country').select2('val',"");
				$('#c_add_state').select2('val',"");
				$('#c_add_city').select2('val',"");
				Unloading();
				load_address_table();		
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
	
}

function load_address_table()
{
	var cust_id=$('#eid').val();
	var mode='show_cust_address';
	
	Loading(true);
	
	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , cust_id:cust_id },
		success: function(responsevalue)
		{	
			$('#cust_address_details').html(responsevalue);
			$('#add_ad_btn').val('Add');
			Unloading();
		}
	});
}

function edit_data_serial(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_serial",id:id,table:table,whereid:whereid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			$("#c_add_address").val(data.c_add_address);
			$("#c_pincode").val(data.c_add_zip);
			$("#edit_add_id").val(data.c_add_id);
			$('#c_addr_default').select2("val",data.c_addr_defult);
			$('#add_ad_btn').val('Update');
			
			load_country(data.c_add_country);

			setTimeout(function(){
				load_state(data.c_add_country,'c_add_state',data.c_add_state);
				$("#c_add_state").select2('val',data.c_add_state)
				setTimeout(function(){
					load_city(data.c_add_state,'c_add_city',data.c_add_city);
					$("#c_add_city").select2('val',data.c_add_city)
				},500); 
			},500);
			Unloading();
		}
	});
}

function delete_data_serial(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_data_serial",  eid : id ,table:table,whereid:whereid,product_id:$("#eid").val() },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_address_table();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function load_country(country)
{
	Loading(true);
	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "load_country",country:country },
		success: function(response)
		{
			$('#c_add_country').html(response);
			$('#c_add_country').select2("val",country);
		}
	});
}

//contact
function open_cust_contact(){
	var cust_id = $("#cust_id").val();
	if(!cust_id){
		toastr.warning("Customer Not Found!!!", "WARNING");
		$('#cust_id').select2('focus');
		return false;
	}
	$('#add_person_modal').modal('show');
}
function add_cust_contact()
{
	if($("#con_first").val()==="")
	{		
		toastr.warning("Enter Firstname", "ERROR")
		return false;
	}
	// if($("#con_last").val()==="")
	// {		
	// 	toastr.warning("Enter Lastname", "ERROR")
	// 	return false;
	// }
	// if($("#com_email").val()==="")
	// {		
	// 	toastr.warning("Enter Email", "ERROR")
	// 	return false;
	// }
	// if($("#con_mobile").val()==="")
	// {		
	// 	toastr.warning("Enter Mobile", "ERROR")
	// 	return false;
	// }
	// if($("#con_phone").val()==="")
	// {		
	// 	toastr.warning("Enter Phone Number", "ERROR")
	// 	return false;
	// }
	// if($("#con_job").val()==="")
	// {		
	// 	toastr.warning("Enter Job Title", "ERROR")
	// 	return false;
	// }

	Loading(true);
	
	var cust_person_model=$('#cust_person_model').val();
	var con_first=$('#con_first').val();
	var con_last=$('#con_last').val();
	var com_email=$('#com_email').val();
	var con_isd_id = $("#con_isd_id").val();
	var con_mobile=$('#con_mobile').val();
	var con_phone=$('#con_phone').val();
	var con_job=$('#con_job').val();
	var cust_id=$('#eid').val();
	var cust_ref_id=$('#cust_id').val();
	var edit_con_id=$('#edit_con_id').val();
	var mode='add_cust_contact';
	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , con_first:con_first, con_last:con_last, com_email:com_email,con_mobile:con_mobile,con_phone:con_phone,con_job:con_job,cust_id:cust_id,edit_con_id:edit_con_id,cust_person_model:cust_person_model,cust_ref_id:cust_ref_id, con_isd_id:con_isd_id },
		success: function(responsevalue)
		{	
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("Contact ADDED SUCCESSFULLY", "SUCCESS");
				$('#con_first').val('');
				$('#con_last').val('');
				$('#com_email').val('');
				$("#con_isd_id").select2('val','');
				$('#con_mobile').val('');
				$('#con_phone').val('');
				$('#con_job').val('');
				$('#edit_con_id').val('');
				load_contact_table();
			}
			else if(cust_ref_id) {
				$('#con_first').val('');
				$('#con_last').val('');
				$('#com_email').val('');
				$('#con_mobile').val('');
				$("#con_isd_id").select2('val','');
				$('#con_phone').val('');
				$('#con_job').val('');
				toastr.success("CONTACT ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_person_modal").modal("hide");
				var c_con_id = responsevalue.trim();//Get Ins ID
				load_cust_person(cust_ref_id);
				setTimeout(function(){$('#c_con_id').select2("val",c_con_id);},1000);
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("CONTACT UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				load_contact_table();		
			}
			else if(responsevalue.trim() == '0')
			{	
				toastr.success("CONTACT UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				$('#con_first').val('');
				$('#con_last').val('');
				$('#com_email').val('');
				$("#con_isd_id").select2('val','');
				$('#con_mobile').val('');
				$('#con_phone').val('');
				$('#con_job').val('');
				$('#edit_con_id').val('');
				load_contact_table();	
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
	
}

function load_contact_table()
{
	var cust_id=$('#eid').val();
	var mode='show_cust_contact';
	
	Loading(true);
	
	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , cust_id:cust_id },
		success: function(responsevalue)
		{	
			$('#cust_contact_details').html(responsevalue);
			$('#add_btn_contact').val('Add');
			Unloading();
		}
	})
}

function edit_data_contact(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_contact",id:id,table:table,whereid:whereid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			$("#con_first").val(data.c_con_fname);
			$("#con_last").val(data.c_con_lname);
			$("#com_email").val(data.c_con_email);
			$("#con_mobile").val(data.c_con_mobile);
			$("#con_phone").val(data.c_con_phone);
			$("#con_job").val(data.c_con_job);
			$("#edit_con_id").val(data.c_con_id);
			$('#add_btn_contact').val('Update');
			
			Unloading();
		}
	});
}

function delete_data_contact(id,table,whereid)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_data_contact",  eid : id ,table:table,whereid:whereid,product_id:$("#eid").val() },
			success: function(response)
			{
				console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_contact_table();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

//Existing Customer

function add_exist()
{
	Loading(true);
	
	var ext_type=$('#ext_type').val();
	var ext_product=$('#ext_product').val();
	var ext_remark=$('#ext_remark').val();
	var cust_id=$('#eid').val();
	var edit_id=$('#edit_exist_id').val();
	var mode='add_cust_exist';
	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , ext_type:ext_type, ext_product:ext_product, ext_remark:ext_remark,cust_id:cust_id,edit_id:edit_id },
		success: function(responsevalue)
		{	
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("Data ADDED SUCCESSFULLY", "SUCCESS");
				$('#ext_type').val('');
				$('#ext_product').select2('val','');
				$('#ext_remark').val('');
				load_exist_table();
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("Contact ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');	
				$('#vender_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');				
				$("#vender_id").trigger('change')
				$("#cust_id").trigger('change')
				$('#cust_id').select2("val",data.cust_id);
				$('#vender_id').select2("val",data.cust_id);
				$('#cust_add').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("Contact UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + crm_domain +'customer_list';		
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
	
}

function load_exist_table()
{
	var cust_id=$('#eid').val();
	var mode='show_cust_exist';
	
	Loading(true);
	
	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , cust_id:cust_id },
		success: function(responsevalue)
		{	
			$('#cust_exist_details').html(responsevalue);
			$('#add_exist_btn').val('Add');
			Unloading();
		}
	})
}

function edit_data_exist(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customer/',
		data: { mode : "preedit_exist",id:id,table:table,whereid:whereid},
		success: function(response)
		{

			var data = jQuery.parseJSON(response);
			$("#ext_type").val(data.c_ext_type);
			load_product(data.c_ext_product);
			$("#ext_remark").val(data.c_ext_remark);
			$("#edit_exist_id").val(data.c_ext_id);
			$('#add_exist_btn').val('Update');
			Unloading();
		}
	});
}

function delete_data_exist(id,table,whereid)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_data_exist",  eid : id ,table:table,whereid:whereid,product_id:$("#eid").val() },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_exist_table();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function load_product(pid)
{
	Loading(true);
	
	$.ajax({
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "load_product",pid:pid },
		success: function(response)
		{
			$('#ext_product').html(response);
			$('#ext_product').select2("val",pid);
		}
	})
}
function load_cust_person(cust_id){
	if(cust_id){
		Loading(true);
		$.ajax({
			type:'POST',
			url: root_domain + crm_domain +'app/customer/',
			data: { mode:"load_cust_person", cust_id:cust_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#c_con_id').html(resp.html_resp);
				$('#c_con_id').select2("val",($("#c_con_id option:eq(1)").val())).select2('focus');
				get_inquiry_type(cust_id);
				Unloading();
			}
		});
	}
}

function get_inquiry_type(cust_id){
	if(cust_id){
		Loading(true);
		$.ajax({
			type:'POST',
			url: root_domain + crm_domain +'app/inquiry/',
			data: { mode:"load_inquiry_type", cust_id:cust_id },
			success: function(response)
			{
				if(response){
					$('#inquiry_type_id').select2("val",'8');
				} else {
					$('#inquiry_type_id').select2("val",'9');
				}
				Unloading();
			}
		});
	}
}

function check_csv_data(){
	if(!$('#import_file').val()){
		toastr.warning("Select File", "ERROR");
		$("#import_file").focus();
		return false;
	}
	
	var conf_form = new FormData();
	
	conf_form.append('mode', "check_csv_data");
	conf_form.append("import_file", document.getElementById('import_file').files[0]);//append files
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: conf_form,
		contentType: false,
		processData:false,
		success: function(response){
			console.log(response);
			var resp=JSON.parse(response);
			if(resp.res=='1'){
				$('#mode').val("import_csv_data");
				$('#check_btn').hide();
				$('#submit_btn').show();
				$('#import_file').attr("readonly",true);
			}
			else if(resp.res=='2'){
				$("#import_file").val("");
				toastr.warning("Column Name Doesn't matched !!!", "ERROR", { timeOut: 9500 });
			}
			else {
				$("#import_file").val("");
				toastr.warning("SOMETHING WENT WRONG!!!", "ERROR");
			}
			Unloading();
		}
	});
}

$("#cust_imp_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#cust_imp_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");
	$("#submit_btn").prop("disabled",true);
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var response=JSON.parse(response);
			var resp=response.res;
			if(resp.trim() == '1') {
				toastr.success("DATA IMPORT SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain + crm_domain +'customer_list';
			}
			else if(resp.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			Unloading();
			$('#cust_imp_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function add_cust_relation(){
	if($("#relation").val()===""){		
		toastr.warning("Enter Relation", "ERROR");
		return false;
	}
	if($("#gender").val()===""){		
		toastr.warning("Select Gender", "ERROR");
		return false;
	}
	if($("#birth_date").val()===""){		
		toastr.warning("Select Birth date", "ERROR");
		return false;
	}
	if($("#birth_date").val() && $("#anniversary_date").val()){
		if($("#birth_date").val() === $("#anniversary_date").val()){
			toastr.warning("Birthdate and Anniversary must be Different", "ERROR");
			return false;
		}
		// if($("#birth_date").val() < $("#anniversary_date").val()){
		// 	toastr.warning("Anniversary must be greater than birthdate", "ERROR");
		// 	return false;
		// }
	}
	Loading(true);
	
	var relation = $('#relation').val();
	var gender = $('#gender').val();
	var birth_date = $('#birth_date').val();
	var anniversary_date = $('#anniversary_date').val();
	var cust_id = $('#eid').val();
	var edit_id = $('#edit_relation_id').val();
	var mode = 'add_cust_relation';
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , 
			relation : relation, 
			gender : gender, 
			birth_date : birth_date,
			anniversary_date : anniversary_date,
			cust_id : cust_id,
			edit_id : edit_id },
			success: function(responsevalue)
			{	
				if(responsevalue.trim() === '1') {
					Unloading();
					toastr.success("Relation Added", "SUCCESS");
					$('#relation').val('');
					$('#gender').select2('val','');
					$('#birth_date').val('');
					$('#anniversary_date').val('');
					$('#edit_relation_id').val('');
					load_relation_table();
				}
				else if(responsevalue.trim() === '0')
				{	
					toastr.success("Relation Updated", "SUCCESS");
					$('#relation').val('');
					$('#gender').select2('val','');
					$('#birth_date').val('');
					$('#anniversary_date').val('');
					$('#edit_relation_id').val('');
					Unloading();
					load_relation_table();		
				}
				
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	
	
}

function load_relation_table()
{
	var cust_id = $('#eid').val();
	var mode = 'show_cust_relation';
	
	Loading(true);
	
	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: { mode : mode , cust_id:cust_id },
		success: function(responsevalue)
		{	
			$('#cust_relation_details').html(responsevalue);
			$('#add_relation_btn').val('Add');
			Unloading();
		}
	});
}

function edit_realtion(relation_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_relation",relation_id : relation_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#relation').val(data.relation);
			$('#gender').select2('val',data.gender);
			$('#birth_date').val(data.birth_date);
			$('#anniversary_date').val(data.anniversary_date);
			$('#edit_relation_id').val(data.relation_id);
			$('#add_relation_btn').val('Update');
			Unloading();
		}
	});
}

function delete_realtion(relation_id){
	var confirm_status = confirm(" Are you want to delete ?");

	if(confirm_status) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_relation", relation_id : relation_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("Relation Deleted", "SUCCESS");
					load_relation_table();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("Something Went Wrong", "WARNING");
				}							
			}
		});	
	}
}

function load_consinee_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/customer/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}

function load_consinee_city(parentid,control,val1)
{	
	//alert(parentid);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			//alert(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}

function add_consignee(){
	if($("#consignee_comp_name").val()===""){		
		toastr.warning("Enter Consignee Company Name", "ERROR");
		return false;
	}
	if($("#consignee_name").val()===""){		
		toastr.warning("Enter Consignee Name", "ERROR");
		return false;
	}
	if($("#consignee_mobile").val()===""){		
		toastr.warning("Enter Consignee Mobile", "ERROR");
		return false;
	}
	// if($("#consignee_email").val()===""){		
	// 	toastr.warning("Enter Consignee Email", "ERROR");
	// 	return false;
	// }
	// if($("#gst_consinee_no").val()===""){		
	// 	toastr.warning("Enter Consignee GST No", "ERROR");
	// 	return false;
	// }
	

	var comp_name=$('#consignee_comp_name').val();
	var con_name=$('#consignee_name').val();
	var con_mobile=$('#consignee_mobile').val();
	var con_email=$('#consignee_email').val();
	var con_address=$('#consignee_address').val();
	var country_consinee_id=$('#country_consinee_id').val();
	var state_consinee_id=$('#state_consinee_id').val();
	var city_consinee_id=$('#city_consinee_id').val();
	var gst_consinee_no=$('#gst_consinee_no').val();
	var cust_id=$('#eid').val();
	var edit_id=$('#edit_id_consignee').val();
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "add_consignee",
		
		comp_name:comp_name,
		con_name:con_name,
		con_mobile:con_mobile,
		con_address : con_address,
		con_email:con_email,
		cust_id:cust_id,
		country_consinee_id: country_consinee_id,
		state_consinee_id: state_consinee_id,
		city_consinee_id: city_consinee_id,
		gst_consinee_no: gst_consinee_no,
		edit_id:edit_id,
	},
	success: function(response)
	{
			if(response.trim() == '1'){
				//console.log(response);
				$("#consignee_comp_name").val("");
				$("#consignee_name").val("");
				$("#consignee_mobile").val("");
				$("#consignee_email").val("");
				$("#consignee_address").val("");
				$("#gst_consinee_no").val("");
				$("#add_consignee_btn").val("Add");
				$('#edit_id_consignee').val('');
				load_consignee_data();
			} else if(response == '2'){
				toastr.warning("Consignee already exist", "ERROR");
			}
			
	}
	});
}

function load_consignee_data(){
	var cust_id = $('#eid').val();
	var mode = 'show_consignee_details';
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { cust_id:cust_id,mode:mode },
		success: function(data){
			$('#table_consignee_details').html(data);				
			Unloading();
		}		
	});
}

function edit_data_consignee(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_consignee",id:id,table:table,whereid:whereid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			$("#consignee_comp_name").val(data.company_name);
			$("#consignee_name").val(data.cust_name);
			$("#consignee_mobile").val(data.cust_mobile);
			$("#consignee_email").val(data.cust_email);
			$("#consignee_address").val(data.cust_address);
			$("#gst_consinee_no").val(data.gst_no);

			$("#edit_id_consignee").val(data.cust_id);

			$('#add_consignee_btn').val('Update');
			$("#country_consinee_id").select2('val',data.countryid);
			load_country(data.countryid);
			load_state(data.countryid,'state_consinee_id',data.stateid);
			load_city(data.stateid,'city_consinee_id',data.cityid);

			load_country(data.countryid);

			setTimeout(function(){
				load_state(data.countryid,'state_consinee_id',data.stateid);
				$("#state_consinee_id").select2('val',data.stateid)
				setTimeout(function(){
					load_city(data.stateid,'city_consinee_id',data.cityid);
					$("#city_consinee_id").select2('val',data.cityid)
				},500); 
			},500);
			Unloading();
			
			Unloading();
		}
	});
}

function delete_data_consignee(id)
{
	var r= confirm(" Are you sure, you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_consignee",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_consignee_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});	
	}
	
}

//dispatch details 

function add_cust_dispatch()
{
	var transporter_name = $('#transporter_name').val();
	var transporter_add = $('#transporter_add').val();
	var transporter_type = $('#transporter_type').val();
	var transporter_contact = $('#transporter_contact').val();
	var edit_dispatch_id = $('#edit_dispatch_id').val();
	var eid = $('#eid').val();

	if(transporter_name=="")
	{
		toastr.error("ENTER TRANSPORTER NAME","error");
		$('#transporter_name').focus();
	}
	else if(transporter_type=="")
	{
		toastr.error("ENTER TRANSPORTER TYPE","error");
		$('#transporter_type').focus();
	}
	else 
	{
		$.ajax({

			type:'post',
			url:root_domain+crm_domain+'app/customer/',
			data:{mode:'add_cust_dispatch',transporter_name:transporter_name,transporter_add:transporter_add,transporter_type,transporter_contact:transporter_contact,edit_dispatch_id:edit_dispatch_id,eid:eid},
			success:function(result)
			{

				console.log(result);

				if(result==1)
				{
					toastr.success("DATA INSERTED SUCCESSFULLY","success");
				}
				else if(result==3)
				{
					toastr.success("DATA UPDATED SUCCESSFULLY","success");	
				}
				else{
					toastr.warning("SOMETHING WENT WRONG","warning");
				}

				load_cust_dispatch();

				$('#transporter_name').val('');
				$('#transporter_add').val('');
				$('#transporter_type').val('');
				$('#transporter_contact').val('');
				$('#edit_dispatch_id').val('');
			}

		})
	}

}

function load_cust_dispatch()
{

	var eid = $('#eid').val();
	//alert(edit_dispatch_id);
	$.ajax({

		type:'post',
		url:root_domain+crm_domain+'app/customer/',
		data:{mode:'load_cust_dispatch',eid:eid},
		success:function(result)
		{
			// console.log(result);
			$('#dispatch_details').html(result);
			$('#add_dispatch_btn').val('Add');
		}

	})

}

function edit_cust_dispatch(eid)
{
	//alert(eid);
	$.ajax({

		type:'post',
		data:{mode:'edit_cust_dispatch',eid:eid},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			console.log(result);
			var obj = JSON.parse(result);
			$('#transporter_name').val(obj.transporter_name);
			$('#transporter_add').val(obj.transporter_add);
			$('#transporter_type').val(obj.transporter_type);
			$('#transporter_contact').val(obj.transporter_contact);
			$('#edit_dispatch_id').val(obj.transporter_dispatch_id);
			$('#add_dispatch_btn').val('Update');

		}

	})

}
function delete_cust_dispatch(did)
{
	Loading();
	var r = confirm("Are You Sure To Delete ?");

	if(r==true)
	{
		$.ajax({

			type:'post',
			url:root_domain+crm_domain+'app/customer/',
			data:{mode:'delete_cust_dispatch',did:did},
			success:function(result)
			{
				if(result==1)
				{
					toastr.success('DATA DELETED SUCCESSFULLY','SUCESS');
				}
				else
				{
					toastr.warning('SOMETHING WENT WRONG','WARNING');
				}

				load_cust_dispatch();
			}
		})
	}

	Unloading();

}
function add_competitor()
{
	var comp_name = $('#comp_name').val();
	var comp_add = $('#comp_add').val();
	var comp_email = $('#comp_email').val();
	var comp_mobile = $('#comp_mobile').val();
	var edit_comp_id = $('#edit_comp_id').val();
	var eid = $('#eid').val();

	if(comp_name=="")
	{
		toastr.error("PLEASE ENTER NAME","error");
		$('#comp_name').focus();
	}
	else if(comp_mobile=="")
	{
		toastr.error("PLEASE ENTER MOBILE","error");
		$('#comp_mobile').focus();
	}
	else{

		$.ajax({

			type:'post',
			data:{mode:'add_competitor',comp_name:comp_name,comp_add:comp_add,comp_email:comp_email,comp_mobile:comp_mobile,edit_comp_id:edit_comp_id,eid:eid},
			url:root_domain+crm_domain+'app/customer/',
			success:function(result)
			{

				$('#comp_name').val('');
				$('#comp_add').val('');
				$('#comp_email').val('');
				$('#comp_mobile').val('');
				$('#edit_comp_id').val('');

				load_cust_competitor();
			}

		})
	}

}
function load_cust_competitor()
{
	var eid = $('#eid').val();

	$.ajax({

		type:'post',
		data:{mode:'load_cust_competitor',eid:eid},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			$('#comp_data_details').html(result);

			$('#add_comp_btn').val('Add');
		}

	})

}
function edit_cust_competitor(eid)
{
	$.ajax({

		type:'post',
		data:{mode:'edit_cust_competitor',eid:eid},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			var obj = JSON.parse(result);
			//console.log(obj);
			$('#comp_name').val(obj.comp_name);
			$('#comp_add').val(obj.comp_add);
			$('#comp_email').val(obj.comp_email);
			$('#comp_mobile').val(obj.comp_mobile);
			$('#edit_comp_id').val(obj.comp_id);

			$('#add_comp_btn').val('Update');

		}
	})

}
function delete_cust_competitor(did)
{
	Loading();
	var r = confirm("Are You Sure To Delete ?");

	if(r==true)
	{
		$.ajax({

			type:'post',
			url:root_domain+crm_domain+'app/customer/',
			data:{mode:'delete_cust_competitor',did:did},
			success:function(result)
			{
				if(result==1)
				{
					toastr.success('DATA DELETED SUCCESSFULLY','SUCESS');
				}
				else
				{
					toastr.warning('SOMETHING WENT WRONG','WARNING');
				}

				load_cust_competitor();
			}
		})
	}

	Unloading();

}
function add_comp_product(comp_id)
{
	$("#modal-comp-product").modal("show");

	load_comp_product(comp_id);

}
function add_comp_modal_produdct()
{
	var comp_product_type_sel = $('#comp_product_type_sel').val();
	var comp_product_id = $('#comp_product_id').val();
	var comp_product_price = $('#comp_product_price').val();
	var comp_prudct_remark = $('#comp_prudct_remark').val();
	var comp_id = $('#comp_id').val();
	var cust_comp_product_id = $('#cust_comp_product_id').val();

	if(comp_product_type_sel=="")
	{
		toastr.error("SELECT PRODUCT TYPE","error");
		$('#comp_product_type_sel').select2('focus');
	}
	else if(comp_product_id=="")
	{
		toastr.error("SELECT PRODUCT NAME","error");
		$('#comp_product_id').select2('focus');
	}
	else if(comp_product_price=="")
	{
		toastr.error("ENTER PRODUCT PRICE","error");
		$('#comp_product_price').focus();	
	}
	else
	{
		$.ajax({

			type:'post',
			data:{mode:'add_comp_modal_produdct',comp_product_type_sel:comp_product_type_sel,comp_product_id:comp_product_id,comp_product_price:comp_product_price,comp_prudct_remark:comp_prudct_remark,comp_id:comp_id,cust_comp_product_id:cust_comp_product_id},
			url:root_domain+crm_domain+'app/customer/',
			success:function(result)
			{
				//alert(comp_id);
				if(result==1)
				{
					toastr.success("DATA INSERTED SUCCESSFULLY","success");
					load_comp_product(comp_id);
					
					$('#comp_product_id').select2('val','');
					$('#comp_product_price').val('');
					$('#comp_prudct_remark').val('');
					$('#cust_comp_product_id').val('');
				}
				else if(result==3)
				{
					toastr.success("DATA UPDATED SUCCESSFULLY","success");
					load_comp_product(comp_id);
					
					$('#comp_product_id').select2('val','');
					$('#comp_product_price').val('');
					$('#comp_prudct_remark').val('');
					$('#cust_comp_product_id').val('');
				}
				else
				{
					toastr.warning("SOMETHING WENT WRONG","warning");	
				}
			}

		})
	}
}

function edit_comp_product(id)
{
	//alert(id);
	$.ajax({

		type:'post',
		data:{mode:'edit_comp_product',eid:id},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			//alert(result);
			var obj = JSON.parse(result);

			$('#comp_product_type_sel').select2('val',obj.comp_product_type_sel);
			$('#comp_product_id').select2('val',obj.comp_product_id);
			$('#comp_product_price').val(obj.comp_product_price);
			$('#comp_prudct_remark').val(obj.comp_prudct_remark);
			$('#cust_comp_product_id').val(obj.cust_comp_product_id);

		}

	})
}

function load_comp_product(comp_id='')
{

	$('#comp_id').val(comp_id);

	$.ajax({

		type:'post',
		data:{mode:'load_comp_product',comp_id:comp_id},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			$('.load_product_details').html(result);
		}
	})
	//alert(comp_id);
}

function load_product_typeiwse(type_id){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customer/',
		data: { mode : "load_product_typeiwse", type_id : type_id},
		success: function(data){
			//console.log(data);
			$('#comp_product_id').html(data);
			$('#comp_product_id').select2({
				width: '100%',
				minimumInputLength: 3
			});	

			Unloading();
		}
	});
}

function delete_comp_product(did,comp_id)
{
	var r = confirm('Are You sure to delete ?');

	if(r)
	{

		$.ajax({

			type:'post',
			data:{mode:'delete_comp_product',did:did},
			url:root_domain+crm_domain+'app/customer/',
			success:function(result)
			{
				if(result==1)
				{
					toastr.success("DATA UPDATED SUCCESSFULLY","success");
				}
				else
				{
					toastr.warning("DATA UPDATED SUCCESSFULLY","warning");
				}
				load_comp_product(comp_id);
			}
		})

	}


}

function changeMonthlyBudget()
{
	var annual_budget = $('#annual_consume').val();
	if(annual_budget!='' && annual_budget!='0')
	{
		var monthBud = (parseInt(annual_budget)/parseInt(12)).toFixed(2);
		$(".monthlyDivide").each(function() { 
			$(this).val(monthBud);
		});
	}
	else
	{
		$(".monthlyDivide").each(function() { 
			$(this).val(0);
		});
	}
}

function changeAnnualBudget(){
	var temp=0;	
	$(".monthlyDivide").each(function() { 
		if($('.monthlyDivide').val() !==''){
			temp += Number($(this).val());
		}		
	});
	$('#annual_consume').val(temp.toFixed(2));
}
function get_price_form_price_list()
{
	//alert(product_id);
	var product_id = $('#forecast_pr_product_id').val();
	var version_id = $('#price_list_version_pr_id').val();
	
	$.ajax({

		type:'post',
		url:root_domain+crm_domain+'app/customer/',
		data:{"mode":"get_price_form_price_list","version_id":version_id,"product_id":product_id},
		success:function(result)
		{
			var obj = JSON.parse(result);
			//console.log(obj);
			$('#forecast_amount_pr').val(obj.product_sale_price);
		}
	})
}
function add_forecast_pr()
{
	var forecast_pr_product_id = $('#forecast_pr_product_id').val();
	var price_list_version_pr_id = $('#price_list_version_pr_id').val();
	var forecast_amount_pr = $('#forecast_amount_pr').val();
	var forecast_pro_qty = $('#forecast_pro_qty').val();
	var forecast_pro_total = $('#forecast_pro_total').val();
	var edit_id_fpr = $('#edit_id_fpr').val();
	var eid = $('#eid').val();
	//alert(eid);
	$.ajax({

		type:'post',
		url:root_domain+crm_domain+'app/customer/',
		data:{"mode":"add_forecast_pr","forecast_pr_product_id":forecast_pr_product_id,"price_list_version_pr_id":price_list_version_pr_id,"forecast_amount_pr":forecast_amount_pr,"edit_id_fpr":edit_id_fpr,"eid":eid,"forecast_pro_qty":forecast_pro_qty,"forecast_pro_total":forecast_pro_total },
		success:function(result)
		{
			//alert(result);
			if(result==1)
			{
				toastr.success("INSERTED SUCCESSFULLY","SUCCESS");

				get_forecast_pr();

				$('#forecast_pr_product_id').select2("val","");
				$('#price_list_version_pr_id').val('');
				$('#forecast_amount_pr').val('');

			}
			else if(result==3)
			{
				toastr.success("UPDATED SUCCESSFULLY","SUCCESS");	

				get_forecast_pr();

				$('#forecast_pr_product_id').select2("val","");
				$('#price_list_version_pr_id').val('');
				$('#forecast_amount_pr').val('');

			}
			else{
				toastr.warning("SOMETHING WENT WRONG","WARNING");		
			}
			
			$('#add_forecast_pr_btn').html('ADD');
		}
	})
}


function add_forecast_pr_month()
{
	var forecast_month = $('#forecast_month').val();
	var forecast_month_amount_pr = $('#forecast_month_amount_pr').val();
	var eid = $('#eid').val();
	var edit_id_fpr_month = $('#edit_id_fpr_month').val();

	$.ajax({

		type:'POST',
		data:{"mode":"add_forecast_pr_month","forecast_month":forecast_month,"forecast_month_amount_pr":forecast_month_amount_pr,"eid":eid,"edit_id_fpr_month":edit_id_fpr_month},
		url:root_domain+crm_domain+'app/customer/',
		success:function(result)
		{
			//alert(result);
			if(result==1)
			{
				toastr.success("INSERTED SUCCESSFULLY","SUCCESS");

				get_forecast_pr_month();

				$('#forecast_month').select2("val","");
				$('#forecast_month_amount_pr').val('');

			}
			else if(result==3)
			{
				toastr.success("UPDATED SUCCESSFULLY","SUCCESS");	

				get_forecast_pr_month();

				$('#forecast_month').select2("val","");
				$('#forecast_month_amount_pr').val('');

			}
			else{
				toastr.warning("SOMETHING WENT WRONG","WARNING");		
			}
			
			$('#add_forecast_month_btn').html('ADD');
			//alert(result);
		}

	})
}

function get_forecast_pr()
{
	var eid = $('#eid').val();
	//alert(eid);
	$.ajax({

		type:'POST',
		url:root_domain+crm_domain+'app/customer/',
		data:{"mode":"get_forecast_pr",eid:eid},
		success:function(result)
		{
			// console.log(result);
			$('#table_forecast_details').html(result);
		}
	})

}

function get_forecast_pr_month()
{
	var eid = $('#eid').val();
	//alert(eid);
	$.ajax({

		type:'POST',
		url:root_domain+crm_domain+'app/customer/',
		data:{"mode":"get_forecast_pr_month",eid:eid},
		success:function(result)
		{
			$('#table_forecast_details_month').html(result);
		}
	})

}

function edit_forecast_pr(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_forecast",id:id,table:table,whereid:whereid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);

			$('#forecast_pr_product_id').select2("val",data.forecast_pr_product_id);
			$('#price_list_version_pr_id').val(data.price_list_version_pr_id);
			$('#forecast_amount_pr').val(data.forecast_amount_pr);
			$('#forecast_pro_qty').val(data.forecast_pro_qty);
			$('#forecast_pro_total').val(data.forecast_pro_total);

			$('#edit_id_fpr').val(data.forecast_pr_id);

			$('#add_forecast_pr_btn').html('Update');

			Unloading();
		}
	});
}
function delete_forecast_pr(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_data_serial",  eid : id ,table:table,whereid:whereid,product_id:$("#eid").val() },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					get_forecast_pr();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function edit_forecast_pr_month(id,table,whereid)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "preedit_forecast",id:id,table:table,whereid:whereid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);

			$('#forecast_month').val(data.forecast_month);
			$('#forecast_month_amount_pr').val(data.forecast_amount_pr);
			$('#edit_id_fpr_month').val(data.forecast_pr_id);

			$('#add_forecast_month_btn').html('Update');

			Unloading();
		}
	});
}
function delete_forecast_pr_month(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/customer/',
			data: { mode : "delete_data_serial",  eid : id ,table:table,whereid:whereid,product_id:$("#eid").val() },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					get_forecast_pr_month();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function openpagemodal(){
	$('#pagepermissionmodal').modal('show');
}
$("#permission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#permission_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/customer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			//console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("PERMISSION ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain + crm_domain +'customer';
			}
			else if(responsevalue.trim() == '0') {
				Unloading();
				toastr.error("Something Went Wrong", "ERROR");	
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("PERMISSION ADDED SUCCESSFULLY", "SUCCESS");
				$("#pagepermissionmodal").modal("hide");
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#pagepermissionmodal").modal("hide");
				$('#permission_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("PERMISSION UPDATED SUCCESSFULLY", "SUCCESS");
				$("#pagepermissionmodal").modal("hide");		
				Unloading();
				window.location=root_domain + crm_domain +'customer';		
			}
			$('#permission_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function get_forecast_pr_amount(qty)
{
	var forecast_amount_pr = $('#forecast_amount_pr').val();

	var total = Number(qty) * Number(forecast_amount_pr);

	$('#forecast_pro_total').val(total);
}

function add_ledger_doc_field() {
	if(!$("#led_doc_name").val()){		
		toastr.warning("Enter Document Name", "ERROR");
		$("#led_doc_name").focus();
		return false;
	}
	if(!$("#led_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#led_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_ledger_doc_field");
	form_data.append('l_id', $("#eid").val());
	form_data.append('led_doc_name', $("#led_doc_name").val());
	form_data.append("led_attch_file", document.getElementById('led_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/customer/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#led_doc_name").val("").focus();
			$("#led_attch_file").val("");
			$('#led_attch_btn').val('Add');
			Unloading();
			show_led_attach_data();
		}
	});
}
function show_led_attach_data() {
	var eid = $('#eid').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/customer/',
		data: { mode : "show_led_attach_data", l_id:eid,modee:chkmode },
		success: function(resp){
			//console.log(resp);
			$('#led_attach_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_led_attach_data(led_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/customer/',
			data: { mode:"delete_led_attach_data", led_attach_id:led_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_led_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}


function check_mobile_no(mobile_no){
	var form_mode = $("#mode").val();
	var customer_id = $("#eid").val();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customer/',
		data: { mode : "check_mobile_no", mobile_no : mobile_no, form_mode:form_mode, customer_id:customer_id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			if(no.error){
				toastr.warning(no.error , "ERROR");
				$('#cust_mobile').val('');
				return false;

			}
		}
	});	
}
function copy_led(cust_id)
{	
	//alert(cust_id);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "copy_led",  cust_id : cust_id},
		success: function(responce){
			if(responce){
				toastr.success("Ledger Genrate SUCCESSFULLY", "SUCCESS");
				load_cust_datatable();
			}else{
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
		}
	});
	
}
function load_typeswise_terms_dom(quot_type,cust_id) 
{

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain + 'app/customer/',
			data: { mode : "load_typeswise_terms_dom", quot_type:quot_type, cust_id:cust_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				if(quot_type==1){
					$('#party_terms_cond_export_div').html(resp.resp_html);
				}else{
					$('#party_terms_cond_domestic_div').html(resp.resp_html);
				}
				Unloading();
			}
		});
	}
}

function load_typeswise_terms_exp(quot_type,cust_id) 
{

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain + 'app/customer/',
			data: { mode : "load_typeswise_terms_exp", quot_type:quot_type, cust_id:cust_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				if(quot_type==1){
					$('#party_terms_cond_export_div').html(resp.resp_html);
				}else{
					$('#party_terms_cond_domestic_div').html(resp.resp_html);
				}
				Unloading();
			}
		});
	}
}

function terms_check_all_exp(obj){
	$('.terms_checkbox_exp').prop('checked', obj.checked);
}

function terms_check_all_dom(obj){
	$('.terms_checkbox_dom').prop('checked', obj.checked);
}