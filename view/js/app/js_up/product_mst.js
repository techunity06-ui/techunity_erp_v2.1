var datatable;
$(document).ready(function() {
	load_pro_tbl(); 
	show_unit_data();
	show_images_data();
	show_party_purchase();
	show_product_param();
	show_product_process();
	show_job_party_purchase();
// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#product_add").validate({
	
	ignore:[],
	
	rules: {
		
		product_type:{
			required:true
		},
		product_name: {
			required: true
		},
		item_code: {
			required: true			
		},
		product_mst_unitid: {
			required: true			
		},
		intra_tax: {
			required: true			
		},
		inter_tax: {
			required: true			
		},
		unit_require:{
			
			required: true	
		}
	},
	messages: {
		product_type:{
			required:"Select Product Type"
		},
		product_name: {
			required: "Enter Product"
		},
		item_code: {
			required: "Enter Item Code"
		},
		product_mst_unitid: {
			required: "Select Product Unit"
		},
		intra_tax: {
			required: "Select Intra Tax(CGST+SGST) "
		},
		inter_tax: {
			required: "Select Inter Tax(IGST) "
		},
		unit_require:{
			required: "Select Unit Conversion Rate"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditproduct").validate({
	rules: {
		edit_product_name: {
			required: true
		}
	},
	messages: {
		edit_product_name: {
			required: "Enter product",
		}
	}
});		
$("#product_importfile").validate({
	rules: {
		excel_file:{
			required:true
		}
	},
	messages: {
		excel_file: {
			required: "Select Product Csv file",
		}
	}
});
});
$("#product_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_add").valid()) {
		return false;
	}
	if($("#product_type").val()==="8"){
		if($("#ledger_id").val()===""){
			toastr.error("Select Ledger", "ERROR")
			return false;
		}
	}
	form.submitted = true;	
	/*var product_check = [];  
    $('.product_check').each(function(){  
		if($(this).is(":checked"))  
		{  
			 product_check.push($(this).val());  
		}  
    });  
    product_check = product_check.toString();  */
	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	var token	=  $("#token").val();	
	
	//alert(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{
			//alert(resnse);
			console.log(resnse);			
			var data = JSON.parse(resnse);
			//alert(data);
			var responsevalue=data.msg;
			//alert(responsevalue);
			if(responsevalue.trim() == '1') {
				
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS")
				$('#product_add').trigger('reset');
				$("#product_gst").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				$("#product_base_unit").select2("val","");				
				$("#product_category").select2("val","");				
				$("#product_specification").val('');				
				Unloading();
				window.location=root_domain+'product_list';
				//show_unit_data();
				//load_pro_tbl();
			}
			if(responsevalue.trim() == '2') {
				
				toastr.success("PRODUCT Updated SUCCESSFULLY", "SUCCESS")
				$('#product_add').trigger('reset');
				$("#product_gst").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				$("#product_base_unit").select2("val","");				
				$("#product_category").select2("val","");
				$("#product_specification").val('');						
				Unloading();
				window.location=root_domain+'product_list';
				//show_unit_data();
				//load_pro_tbl();
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("something wrong", "ERROR")
				$('#product_add').trigger('reset');	
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				//$("#bs-example-modal-addproduct").modal("hide");
				$('#product_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == '3') {
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_product_modal").modal("hide");
				$('#product_id').append('<option value='+data.product_id+'>'+data.product_name+'</option>'); 
				$('#product_id').select2("val",data.product_id);
				$("#product_id").trigger('change');
				$("#product_specification").val('');		
				Unloading();
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditProduct").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditProduct").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	
/*	var form_data = {
		eid :$("#edit_id").val(),
		product_type: $("input[name='edit_product_type']:checked").val(),
		product_name: $("#edit_product_name").val(),		
		product_desc: $("#edit_product_desc").val(),
		product_hsn_code: $("#edit_product_hsn_code").val(),		
		product_rate: $("#edit_rate").val(),		
		unitid: $("#edit_unitid").val(),		
		
		mode:'edit',
		is_ajax: 1
	};	*/
	
	$.ajax({
		cache:false,
		url: root_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);
			
			if(resnse.trim() == '1') {
				toastr.success("product UPDATED SUCCESSFULLY", "SUCCESS");
				load_pro_tbl();
				Unloading();						
			}
			else if(resnse.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(resnse.trim() == '-1')
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
function delete_product(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(resnse)
				{
					
					if(resnse.trim() == "1") {
						toastr.success("PRODUCT DELETE SUCCESSFULLY", "SUCCESS");
						load_pro_tbl();
						Unloading();
					}
					else if(resnse.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}		
					else if(resnse.trim() == "-1") {
						toastr.error("USED PRODUCT GROUP CAN'T BE DELETED !!!", "WARNING"); 
						Unloading();
					}					
				}
			});	
		}
	
}
function edit_product(id)
{
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/product_mst/',
			data: { mode : "preedit", id : id },
			success: function(resnse)
			{
				//console.log(resnse);
				var obj = jQuery.parseJSON(resnse);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);		
				$("#edit_product_name").val(unescape(obj.product_name));
				$("#edit_product_desc").val(obj.product_desc);
				$("#edit_product_mst_rate").val(obj.product_mst_rate);
				$("#edit_rate").val(obj.product_rate);
				$("#edit_product_hsn_code").val(obj.product_hsn_code);
				$("#edit_item_code").val(obj.item_code);
				$("#edit_unitid").select2("val",obj.unitid);
				$("#edit_intra_tax").val(obj.intra_tax);
				$("#edit_inter_tax").val(obj.inter_tax);
				$("#edit_opening_stock").val(obj.product_stock);
				$("#edit_minimum_stock").val(obj.minimum_stock);
				if(obj.multi_company==1)
				{
					$("#edit_multi_company").prop('checked',true);
				}
				else
				{
					$("#edit_multi_company").prop('checked',false);
				}
				Unloading();
			}
		});	
}
$("#product_importfile").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_importfile").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var token	=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/product_mst/',
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
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '0')
			{
				$('#msg').html('<span style="color:red"> Coloums Does Not Match Please Check With demo File</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '3')
			{
				$('#msg').html('<span style="color:red"> Coloum Name Does Not Match Please Check With demo File</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '4')
			{
				$('#msg').html('<span style="color:green"> Data Import Successfully</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '5')
			{
				$('#product_importfile').trigger('reset');
				 $('#check_button').show();
				$('#mode').val('check_data');
				$('#import_button').hide();
				show_importedproduct_data();
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function show_importedproduct_data(total)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/product_mst/',
	data: { mode : "show_importedcustdata"},
	success: function(responce){
				console.log(responce);
				Unloading();
				 $('#sampledata_show').show();
				$('#temp_productdata').html(responce);
				
			}
	});
				
}
function showtype(producttype){
	//alert(producttype);
	if(producttype== 'service'){
		$('#typepro').attr("style","display:none");
		
		$('#edittype').attr("style","display:none");
	}else{
	$('#typepro').attr("style","display:block");
	
	$('#edittype').attr("style","display:block");
	}
}
function reload(id){
	load_pro_tbl(id);
}

function load_pro_tbl(product_type){
//	var product_type= $("input[name='product_type1']:checked").val();
	var fil_product_type = $('#fil_product_type').val();
	
	var datatable = $("#product-table").dataTable({
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
			"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/product_mst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "product_type", "value": product_type },{ "name": "fil_product_type", "value": fil_product_type } );
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

function open_close_tab(tcheck,show_id)
{
	//alert(tcheck);
	if($('#'+tcheck).is(":checked")){
		
		//alert('#'+show_id);
		$('#'+show_id).show();
		$('#l'+show_id).show();
	}
	else
	{
		//alert('#'+show_id);
		$('#'+show_id).hide();
		$('#l'+show_id).hide();
	}
}

function get_product_unit(pro_unit)
{
	//alert(pro_unit);
	$('#utab_basic_unit').val(pro_unit);
	
	if(pro_unit!='3')
	{
		$('#unit_require').val('');
	}
	else
	{
		$('#unit_require').val('1');
	}
}

//Unit Conversion

function add_unit_converter()
{
	if($("#utab_alt_qty").val()==="")
	{		
		toastr.warning("Enter Alter Qty", "ERROR");
		$("#utab_alt_qty").focus();
		return false;
	}
	if($("#utab_alt_unit").val()==="")
	{		
		toastr.warning("Select Alt Unit", "ERROR");
		$("#utab_alt_unit").focus();
		return false;
	}
	if($("#utab_basic_qty").val()==="")
	{		
		toastr.warning("Enter basic Qty", "ERROR");
		$("#utab_basic_qty").focus();
		return false;
	}
	if($("#utab_basic_unit").val()==="")
	{		
		toastr.warning("Select Alt Unit", "ERROR");
		$("#utab_basic_unit").focus();
		return false;
	}
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_unit_converter",edit_id:$("#edit_id").val(),utab_alt_qty:$("#utab_alt_qty").val(),utab_alt_unit:$("#utab_alt_unit").val(),utab_basic_qty:$("#utab_basic_qty").val(),utab_basic_unit:$("#utab_basic_unit").val(),pid:$('#pid').val() },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#utab_alt_qty").val('');
			$("#utab_alt_unit").val('');
			$("#edit_id").val('')
			$("#add_unit").val("Add");
			Unloading();
			
			show_unit_data();
			
		}
	});
}

function show_unit_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_unit_converter", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_unit_converter').html(data);				
			Unloading();
		}		
	});
}

function edit_data_unit(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "preedit_unit",  id : id },
		success: function(response)
		{
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#utab_alt_qty').val(data.unit_alt_qty);
			$('#utab_alt_unit').val(data.unit_alt_unit);
			$("#utab_basic_qty").val(data.unit_basic_qty);
			$("#utab_basic_unit").val(data.unit_basic_unit);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id").val(id);
			$("#add_unit").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			Unloading();
		}
	});
}

function delete_data_unit(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_data_unit",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_unit_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

//branch Stock


function add_branch_stock()
{
	//alert('heeloo');
	Loading();	
	//var formData = new FormData(jQuery('#branch_stock_form')[0]);
	//var formData = $('#branch_stock_form').serializeArray();
	//alert(formData);
	// console.log(formData);
	var bstock_arr=[];
	var bid_arr=[];
	var bpriority_arr=[];
	
	var bstock = $('input[name="bstock[]"]').val();
	var bid = $('input[name="bid[]"]').val();
	var bpriority = $('input[name="bpriority[]"]').val();
	var form_mode = $('#form_mode').val();
	var pid = $('#pid').val();
	//var branch_mode=$('#branch_mode').val();
	i = 0;
	$('input.bstock').each(function(){ 
     
       
       bstock_arr[i++]=$(this).val();
     
   });
   
   j = 0;
	$('input.bid').each(function(){ 
     
       
       bid_arr[j++]=$(this).val();
     
   });
   
   k = 0;
	$('input.bpriority').each(function(){ 
     
      bpriority_arr[k++]=$(this).val();
     
   });
	
	//alert(bstock_arr);
	//alert(pid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_branch_stock",bstock:bstock_arr,bid:bid_arr,bpriority:bpriority_arr,pid:pid },
		success: function(response)
		{
			console.log(response);
			//alert(response);
			Unloading();
			
		}
	}); 
	
	//alert(bstock);
	//var data_to_send = $.serialize();
	
}


//Product Image

function add_product_image()
{ 
   var data = new FormData();
   data.append('file', $('#file').prop('files')[0]);
   data.append("mode",$('#img_mode').val());
   data.append("branchid",$('#branchid').val());
   data.append("pid",$('#pid').val());
  // alert(form_data);
   $.ajax({
   url: root_domain+'app/product_mst/',
    method:"POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    beforeSend:function(){
     $('#uploaded_image').html("<label class='text-success'>Image Uploading...</label>");
    },   
    success:function(data)
    {
		//alert(data);
		$("#file").val('');
		show_images_data();
		//$('#uploaded_image').html(data);
		
    }
   });
 
}

function show_images_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(form_mode);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_product_images", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#uploaded_image').html(data);				
			Unloading();
		}		
	});
}

function delete_data_image(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_data_image",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_images_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

//Party Purchase


function add_party_purchase()
{
	if($("#party_id").val()==="")
	{		
		toastr.warning("Select Party Id", "ERROR");
		$("#party_id").select2("focus");
		return false;
	}
	if($("#party_rate").val()==="")
	{		
		toastr.warning("Enter Party Rate", "ERROR");
		$("#party_rate").focus();
		return false;
	}
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_party_purchase",edit_id:$("#edit_id_party").val(),party_id:$("#party_id").val(),party_rate:$("#party_rate").val(),pid:$('#pid').val(),branchid:$('#branchid').val },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#party_id").select2("val","");
			$("#party_rate").val('');
			$("#edit_id_party").val('')
			$("#add_party_purchase").val("Add");
			Unloading();
			
			show_party_purchase();
			
		}
	});
}

function show_party_purchase()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_party_purchase", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_party_purchase').html(data);				
			Unloading();
		}		
	});
}


function edit_data_party_purchase(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "preedit_party",  id : id },
		success: function(response)
		{
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#party_id').select2("val",data.party_id);
			$('#party_rate').val(data.party_rate);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id_party").val(id);
			$("#add_party_btn").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			Unloading();
		}
	});
}

function delete_data_party_purchase(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_data_party",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_party_purchase();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}



//JObwork Party Purchase


function add_job_party_purchase()
{
	if($("#job_party_id").val()==="")
	{		
		toastr.warning("Select Party Id", "ERROR");
		$("#job_party_id").select2("focus");
		return false;
	}
	if($("#job_party_rate").val()==="")
	{		
		toastr.warning("Enter Party Rate", "ERROR");
		$("#job_party_rate").focus();
		return false;
	}
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_job_party_purchase",edit_id:$("#edit_id_job_party").val(),job_party_process_id:$("#job_party_process_id").val(),party_id:$("#job_party_id").val(),party_rate:$("#job_party_rate").val(),pid:$('#pid').val(),branchid:$('#branchid').val },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#job_party_process_id").select2("val","");
			$("#job_party_id").select2("val","");
			$("#job_party_rate").val('');
			$("#edit_id_job_party").val('')
			$("#add_job_party_btn").val("Add");
			Unloading();
			
			show_job_party_purchase();
			
		}
	});
}

function show_job_party_purchase()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_job_party_purchase", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_job_party_purchase').html(data);				
			Unloading();
		}		
	});
}


function edit_data_job_party_purchase(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "preedit_job_party",  id : id },
		success: function(response)
		{
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#job_party_id').select2("val",data.job_party_id);
			$('#job_party_process_id').select2("val",data.job_party_process_id);
			$('#job_party_rate').val(data.job_party_rate);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id_job_party").val(id);
			$("#add_job_party_btn").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			Unloading();
		}
	});
}

function delete_data_job_party_purchase(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_job_data_party",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_job_party_purchase();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}


//Party Parameter


function add_param_value()
{
	if($("#param_id").val()==="")
	{		
		toastr.warning("Select Party Id", "ERROR");
		$("#param_id").select2("focus");
		return false;
	}
	if($("#param_value").val()==="")
	{		
		toastr.warning("Enter parameter value", "ERROR");
		$("#param_value").focus();
		return false;
	}
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_param_value",edit_id:$("#edit_id_param").val(),param_id:$("#param_id").val(),param_value:$("#param_value").val(),pid:$('#pid').val() },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#param_id").select2("val","");
			$("#param_value").val('');
			$("#edit_id_param").val('')
			$("#add_param").val("Add");
			Unloading();
			
			show_product_param();
			
		}
	});
}

function show_product_param()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_product_param", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_product_parameter').html(data);				
			Unloading();
		}		
	});
}


function edit_product_param(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "preedit_param",  id : id },
		success: function(response)
		{
			console.log(response);
			var data = jQuery.parseJSON(response);
			//alert(data);
			$('#param_id').select2("val",data.param_id);
			$('#param_value').val(data.param_value);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id_param").val(id);
			$("#add_param").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			Unloading();
		}
	});
}

function delete_data_param(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_data_param",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_product_param();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

function get_product_code(pcode)
{
	//alert(pcode);
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "get_product_code",  pcode : pcode },
		success: function(response)
		{
			//alert(response);	
			var data=jQuery.parseJSON(response)
			var series=data.series;
			var code=data.code;
			
			$('#product_icode').val(series);
			$('#product_icode_code').val(code);
			Unloading();			
		}
	});	
}

// process parameter

function add_process_value()
{
	if($("#process_id").val()==="")
	{		
		toastr.warning("Select Process Id", "ERROR");
		$("#process_id").select2("focus");
		return false;
	}
	if($("#process_priority").val()==="")
	{		
		toastr.warning("Enter Process value", "ERROR");
		$("#process_priority").focus();
		return false;
	}
	if($("#process_type").val()==="")
	{		
		toastr.warning("Select Process Type", "ERROR");
		$("#process_type").focus();
		return false;
	}
	
//	if($("#process_time").val()==="")
//	{		
//		toastr.warning("Select Process Time", "ERROR");
//		$("#process_time").focus();
//		return false;
//	}
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "add_process_value",edit_id:$("#edit_id_process").val(),process_id:$("#process_id").val(),process_priority:$("#process_priority").val(),pid:$('#pid').val(),process_type:$('#process_type').val(),process_time:$('#process_time').val(),process_opening:$('#process_opening').val() },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#process_id").select2("val","");
			$("#process_priority").val('');
			$("#edit_id_process").val('')
			$("#process_type").val('')
			$("#process_time").val('')
			$("#add_process").val("Add");
			Unloading();
			
			show_product_process();
			
		}
	});
}

function show_product_process()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "load_product_process", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//alert(data);
			//console.log(data);
			$('#table_product_process').html(data);				
			Unloading();
		}		
	});
}



function edit_product_process(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_mst/',
		data: { mode : "preedit_process",  id : id },
		success: function(response)
		{
			console.log(response);
			//alert(response);
			var data = jQuery.parseJSON(response);
			//alert(data);
			$('#process_id').select2("val",data.process_id);
			$('#process_priority').val(data.process_priority);
			$('#process_type').val(data.process_type);
			$('#process_time').val(data.process_time);
			$('#process_opening').val(data.process_opening);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id_process").val(id);
			$("#add_process").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			Unloading();
		}
	});
}

function delete_data_process(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/product_mst/',
				data: { mode : "delete_data_process",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_product_process();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

function get_ms_kg()
{
	var product_width=Number($('#product_width').val());
	var product_height=Number($('#product_height').val());
	var product_thickness=Number($('#product_thickness').val());
	var product_density=Number($('#product_density').val());
	
	var total=(product_width/1000)*(product_height/1000)*(product_thickness/1000)*product_density;
	
	$('#product_kg').val(total.toFixed(2));
	//alert(total);
	
}


function changeStatus(pid,p_status)
{
	
		//alert(sp_id);
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/product_mst/',
			data: { mode : "change_status", pid : pid,p_status:p_status },
			success: function(response)
			{
				toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_pro_tbl();
			}
		});
	
}
function pro_status(type){
	if(type=="8"){
		$('.typeled').attr("style","display:block");
	}else{
		$('.typeled').attr("style","display:none");
	}
}