var datatable;
$(document).ready(function() {
	load_pro_tbl(); 
	show_unit_data();
	show_images_data();
	show_party_purchase();
	show_accessories_product();
	show_stage_purchase();
	show_product_param();
	show_product_process();
	show_job_party_purchase();
	show_make_data();
	show_scrap_data();
	show_images_tempdata();
	show_die_allocation_data();
	product_load_pro();
	product_project_load();
	show_project_pro_data();
	get_project_product();
	//load_pro_tables();
	
	$("#product_name").focus();
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
		product_icode: {
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
		product_icode: {
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
// validate vendor add form on keyup and submit
//START JAYESH 16-07-2021
$("#product_clone").validate({
	
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

$("#drawing_add").validate({
	rules: {
		drawing_number: {
			required: true			
		},
		drawing_title: {
			required: true			
		},
		/*vender_id: {
			required: true			
		},*/
		drawing_size: {
			required: true			
		},
		drawing_scale: {
			required: true			
		}
	},
	messages: {
		drawing_number: {
			required: "Enter Drawing Number"
		},
		drawing_title: {
			required: "Enter Drawing Title"
		},
		/*vender_id: {
			required: "Select Vendor"
		},*/
		drawing_size: {
			required: "Enter Drawing Size"
		},
		drawing_scale: {
			required: "Enter Drawing Scale"
		},
		purchaseorder_date:{
			required : "Enter P.O date"
		}
	}
}); 

$('#product_rb_make').change(function() {
	var make_id = $(this).val();
	var make_name = $(this).find('option:selected').text();
	$("#product_rb_make_name").val(make_name)
	// $('#'+attrId).val(decimal_value);		
});

var base_value = 100;

/*START JAYESH FOR allow only character */
    /* $("#process_loss").keydown(function(event) {
     	
        // Allow only backspace and delete and tab
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 190  ) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9 || event.keyCode == 190  ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
		if(this.value<0 || this.value>100){
			$('#process_loss').val((base_value).toFixed(2));
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}		
    });
    
    $("#process_scrap_tolerance_plus").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9  || event.keyCode == 190) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57  || event.keyCode == 9 || event.keyCode == 190 ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
    	if(this.value<0 || this.value>100){
			$('#process_scrap_tolerance_plus').val((base_value).toFixed(2));
			toastr.warning("Value should be between 0 to 100.", "WARNING");
			return false;
		}
    });
    
     $("#process_scrap_tolerance_minus").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 190 ) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9 || event.keyCode == 190 ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
    	if(this.value<0 || this.value>100){
			$('#process_scrap_tolerance_minus').val((base_value).toFixed(2));
			toastr.warning("Value should be between 0 to 100.", "WARNING");
			return false;
		}
	});*/

   /* $("#param_value").keydown(function(event) {
        // Allow only backspace and delete
       
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 190 ) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9 || event.keyCode == 190 ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
    	
    });*/
    
   /*  $("#tolerance_plus").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 190 ) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9  || event.keyCode == 190 ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
    	if(this.value<0 || this.value>100){
			$('#tolerance_plus').val((base_value).toFixed(2));
			toastr.warning("Value should be between 0 to 100.", "WARNING");
			return false;
		}
    });
    
    $("#tolerance_minus").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 190 ) {
            }
        else {
               if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9 || event.keyCode == 190 ) {
               	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
                event.preventDefault(); 
            }   
        }
    	if(this.value<0 || this.value>100){
			$('#tolerance_minus').val((base_value).toFixed(2));
			toastr.warning("Value should be between 0 to 100.", "WARNING");
			return false;
		}
	});*/


	$('#party_rate,#job_party_rate,#product_net_weight,#product_opening_valuation,#product_sale_rate,#product_purchase_rate,#weight,#product_min_stock,#product_max_stock,#product_min_order,#product_max_order,#reorder_qty,#self_life_days,#warrenty_period,#product_base_qty,#product_conv_qty,#material_issue_weight,#scrap_qty,#make_stock,#make_rate,#process_rate,#minimum_tolerance,#maximum_tolerance,#minimum_tolerance_value,#maximum_tolerance_value').bind('cut copy paste', function(e) {
		e.preventDefault();
		toastr.warning("Cut / Copy / Paste Disabled", "WARNING");

	});




});

function load_specification_content(specification_id)
{
	
	//var specification_id = $("#specification_id").val();
	//var bstock = $('input[name="specification[]"]').val();
	//alert(bstock);
	
	 var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	}
//alert(specification)

		// Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode:"load_specification_content", specification_id:specification },
			success: function(response)
			{
				 //console.log(response);
				// var resp=jQuery.parseJSON(response);
				// //Put Ckeditor DATA
				CKEDITOR.instances['product_spec'].setData(response);
				// //Scroll To Bottom of the page
				// // animate to just above the select2, now with plenty of room below
				// // $('html, body').animate({
				// // 	scrollTop: $("#an_id").offset().top - 10
				// // }, 1000);
				// //$("html, body").animate({ scrollTop: $(document).height() }, 1000);
				// Unloading();						
			}
		});	
}



function add_hsn(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product');
	$("#hsn_name").focus();
}

function add_decimal(param)
{
	var attrId = $(param).attr("id");
	var id = $('#'+attrId).val();
	var decimal_value = parseFloat(id).toFixed(2);
	$('#'+attrId).val(decimal_value);		
	return  false;
}
function add_decimal_weight(param)
{
	var attrId = $(param).attr("id");
	var id = $('#'+attrId).val();
	var decimal_value = parseFloat(id).toFixed(3);
	$('#'+attrId).val(decimal_value);		
	return  false;
}

$("#product_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_add").valid()) {
		return false;
	}
	// if($("#product_type").val()==="8"){
	// 	if($("#ledger_id").val()===""){
	// 		toastr.error("Select Ledger", "ERROR")
	// 		return false;
	// 	}
	// }
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	
	if(($("#item_status").val()=="3") || $("#item_status").val()=="2"){
		if($("#item_status_date").val()==""){
			toastr.error("Enter Item status Date", "ERROR")
			$("#item_status_date").focus();
			return false;
		}
		if($("#item_status_reason").val()==""){
			toastr.error("Enter Item status Reasons", "ERROR");
			$("#item_status_reason").focus();
			return false;
		}
	}
	/*if($('#icodeval').html()!=''){
		toastr.warning("Enter Different Item Code", "WARNING")
			$("#product_icode").focus();
			return false;
	}*/
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
    var delay = 1000;
	
	 var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	}
	//alert(specification);
	form_data.append("specification_id1", specification);

	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{	

			console.log(resnse);
			var data = JSON.parse(resnse);
			var responsevalue=data.msg;
			if(responsevalue.trim() == '1') {
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();	
				
				$("#product_gst").select2("val","");				
				//$("#product_type").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				$("#product_base_unit").select2("val","");				
				$("#product_category").select2("val","");				
				$("#product_specification").val('');				
				$("#product_hsn").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				//$("#product_base_unit").select2("val","");				
				$("#product_conv_unit").select2("val","");				
				$("#product_category").select2("val","");
				$("#product_specification").val('');
				$("#product_rb_make").val('');
				if(data.direct_product_add == 1){
					if(data.product_add_type == 'INVOICE'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'PURCHASE'){
						$("#modal-add-product").modal("hide");
						$('#product_id').append('<option value='+data.inserid+'>'+data.product_name+'</option>'); 
						$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'PURCHASE_ORDER'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
						//return false;	
					}else if(data.product_add_type == 'MANUAL_INDENT'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');	
					}else if(data.product_add_type == 'inquiry'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'QUOTATION'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'SALES_ORDER'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'bom'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}else if(data.product_add_type == 'PROFORMA'){
						$("#modal-add-product").modal("hide");
						$('#product_id').val(data.inserid);	
						$("#s2id_product_id .select2-chosen").text(data.product_name);
						//$('#product_id').select2("val",data.inserid);
					}else if(data.product_add_type == 'PURCHASE_CARD'){
						$("#modal-add-product").modal("hide");
						$('#vend_product_id').append('<option value='+data.inserid+'>'+data.product_name+'</option>'); 
						$('#vend_product_id').select2("val",data.inserid);
						$("#vend_product_id").trigger('change');
					}else if(data.product_add_type == 'PURCHASE_CARD1'){
						$("#modal-add-product").modal("hide");
						$('#product_id').append('<option value='+data.inserid+'>'+data.product_name+'</option>'); 
						$('#product_id').select2("val",data.inserid);
						$("#product_id").trigger('change');
					}
					$('#product_add').trigger('reset');
				}else{		
					$('#product_add').trigger('reset');	
					var timeoutID = setTimeout(function() {
						window.location=root_domain+administration_domain+'product_add';
					}, delay);
				}
				
			}
			if(responsevalue.trim() == '2') {
				
				toastr.success("PRODUCT UPDATED SUCCESSFULLY", "SUCCESS")
				$('#product_add').trigger('reset');
				$("#product_gst").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				$("#product_base_unit").select2("val","");				
				$("#product_category").select2("val","");
				$("#product_specification").val('');						
				Unloading();
				window.location=root_domain+administration_domain+'product_list';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("something wrong", "ERROR")
				$('#product_add').trigger('reset');	
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#product_add').trigger('reset');
				Unloading();				
			}
			/*START JAYESH Upload mage validation*/
			else if(responsevalue.trim() == '4')
			{
				toastr.info("Somethig Wrong in Image upload", "INFO")
				//$('#product_add').trigger('reset');
				Unloading();				
			}
			/*END JAYESH*/
			else if(responsevalue.trim() == '3') {
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_product_modal").modal("hide");
				$('#product_id').append('<option value='+data.product_id+'>'+data.product_name+'</option>'); 
				$('#product_id').select2("val",data.product_id);
				$("#product_id").trigger('change');
				$("#product_specification").val('');		
				Unloading();
				var timeoutID = setTimeout(function() {
					window.location=root_domain+administration_domain+'product_add';
				}, delay);
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

/*START JAYESH PRODUCT CLONE 16-07-2021*/
$("#product_clone").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_clone").valid()) {
		return false;
	}
	if($("#product_type").val()==="8"){
		if($("#ledger_id").val()===""){
			toastr.error("Select Ledger", "ERROR")
			return false;
		}
	}
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	
	if($("#item_status").val()=="3"){
		if($("#item_status_date").val()==""){
			toastr.error("Enter Item status Date", "ERROR")
			$("#item_status_date").focus();
			return false;
		}
		if($("#item_status_reason").val()==""){
			toastr.error("Enter Item status Reasons", "ERROR");
			$("#item_status_reason").focus();
			return false;
		}
	}

	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	var token	=  $("#token").val();	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{	
			var data = JSON.parse(resnse);
			var responsevalue=data.msg;
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
				window.location=root_domain+administration_domain+'product_list';
			}
			if(responsevalue.trim() == '2') {
				
				toastr.success("PRODUCT UPDATED SUCCESSFULLY", "SUCCESS")
				$('#product_add').trigger('reset');
				$("#product_gst").select2("val","");				
				$("#product_sale_gst").select2("val","");				
				$("#product_purchase_gst").select2("val","");				
				$("#product_base_unit").select2("val","");				
				$("#product_category").select2("val","");
				$("#product_specification").val('');						
				Unloading();
				window.location=root_domain+administration_domain+'product_list';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("something wrong", "ERROR")
				$('#product_add').trigger('reset');	
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#product_add').trigger('reset');
				Unloading();				
			}
			/*START JAYESH Upload mage validation*/
			else if(responsevalue.trim() == '4')
			{
				toastr.info("Somethig Werong in Image upload", "INFO")
				Unloading();				
			}
			/*END JAYESH*/
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
		url: root_domain+administration_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			//console.log(resnse);
			if(resnse.trim() == '1') {
				toastr.success("PRODUCT UPDATED SUCCESSFULLY", "SUCCESS");
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
$("#drawing_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#drawing_add").valid()) {
		return false;
	}
	form.submitted = true;	

	$(this).attr("disabled","disabled");		
	Loading(true);
	var form_data=new FormData(this);
	
	$.ajax({
		//cache:false,
		url: root_domain+administration_domain+'app/product_mst/',
		type: "POST",
		data: form_data,
		processData: false,
		contentType: false,
		success: function(resnse)
		{
			Unloading();	
			if(resnse.trim() != '0' || resnse.trim() != '-1') {
				toastr.success("DRAWING ADDED SUCCESSFULLY", "SUCCESS");
				load_drawing_number(resnse.trim());
				$('#drawing_add').trigger('reset');
				$("#ModalDrawing").modal("hide");	
			}
			else if(resnse.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(resnse.trim() == '-1')
			{
				toastr.info("DRAWING NUMBER ALREADY EXISTS", "INFO");
			}

		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function getGst(id){
	//$(this).find(':selected').data('id')
	var gst = $('#product_hsn').find(':selected').attr('data-salegst');
	//Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "getgstbyhsn", id : gst },
		success: function(response)
		{
			$('#product_sale_gst').val(response);
			$('#product_purchase_gst').val(response);	
			//Unloading();	
		}
	});
}
function delete_product(id) 
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete", token :  $("#token").val(), eid : id },
			success: function(response)
			{
				var resp = JSON.parse(response);
				if(resp.msg == "-1") {
					swal("CURRENT RECORD ALREADY USED", "warning");
					load_pro_tbl();
					Unloading();
				}else if(resp.msg == "1") {
					toastr.success("PRODUCT DELETE SUCCESSFULLY", "SUCCESS");
					load_pro_tbl();
					Unloading();
				}else if(resp.msg == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
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
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit", id : id },
		success: function(resnse)
		{ 
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
		url: root_domain+administration_domain+'app/product_mst/',
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
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "show_importedcustdata"},
		success: function(responce){
			Unloading();
			$('#sampledata_show').show();
			$('#temp_productdata').html(responce);		
		}
	});

}
function showtype(producttype){
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
	var branch_id = $('#branch_id').val();
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
		"aLengthMenu": [[-1,10, 20, 50, 100], ['ALL',10, 20, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/product_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "product_type", "value": product_type },
				{ "name": "fil_product_type", "value": fil_product_type },
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

function open_close_tab(tcheck,show_id)
{
	if($('#'+tcheck).is(":checked")){
		$('#'+show_id).show();
		$('#l'+show_id).show();
	}
	else
	{
		$('#'+show_id).hide();
		$('#l'+show_id).hide();
	}
}

function get_product_unit(pro_unit)
{
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
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "add_unit_converter",edit_id:$("#edit_id").val(),utab_alt_qty:$("#utab_alt_qty").val(),utab_alt_unit:$("#utab_alt_unit").val(),utab_basic_qty:$("#utab_basic_qty").val(),utab_basic_unit:$("#utab_basic_unit").val(),pid:$('#pid').val() },
		success: function(response)
		{
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
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_unit_converter", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_unit_converter').html(data);				
			Unloading();
		}		
	});
}

function edit_data_unit(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_unit",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#utab_alt_qty').val(data.unit_alt_qty);
			$('#utab_alt_unit').val(data.unit_alt_unit);
			$("#utab_basic_qty").val(data.unit_basic_qty);
			$("#utab_basic_unit").val(data.unit_basic_unit);
			$("#edit_id").val(id);
			$("#add_unit").val("Update");
			Unloading();
		}
	});
}

function delete_data_unit(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_unit",  eid : id },
			success: function(response)
			{
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
	Loading();	
	var bstock_arr=[];
	var bid_arr=[];
	var bpriority_arr=[];
	
	var bstock = $('input[name="bstock[]"]').val();
	var bid = $('input[name="bid[]"]').val();
	var bpriority = $('input[name="bpriority[]"]').val();
	var form_mode = $('#form_mode').val();
	var pid = $('#pid').val();
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

	var unit_id=$("#product_base_unit").val();
	if(unit_id===""){
		toastr.warning("Select Base Unit", "WARNING"); 
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "add_branch_stock",bstock:bstock_arr,bid:bid_arr,bpriority:bpriority_arr,pid:pid,unit_id:unit_id },
		success: function(response)
		{
			$("#product_opening").val(response);
			Unloading();
		}
	}); 
}


//Product Image
/*START JAYESH MULTIPLE IMAGE UPLOADS*/
function add_product_image()
{ 
	var fileInput = $('#file')[0];
	if( fileInput.files.length > 0 ){
		var data = new FormData();
		$.each(fileInput.files, function(k,file){
			data.append('file[]', file);
		});
		data.append("mode",$('#img_mode').val());
		data.append("branchid",$('#branchid').val());
		data.append("pid",$('#pid').val());
	}
	
	if($('#file').prop('files')[0]==undefined){
		toastr.warning("Please Select Image", "WARNING"); 
		return false;
	}

	$.ajax({
		url: root_domain+administration_domain+'app/product_mst/',
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
			if(data == '-1')
			{
				toastr.warning("Something Wrong", "WARNING"); 
				return false;
			}
			else
			{
				$("#file").val('');
				show_images_data();
			}

		}
	});

}

function show_images_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_product_images", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#uploaded_image').html(data);				
			Unloading();
		}		
	});
}

function delete_data_image(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_image",  eid : id },
			success: function(response)
			{
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
	if($('#valid_date').val()===""){
		toastr.warning("Enter Valid Date", "ERROR");
		$("#valid_date").focus();
		return false;
	}
	if($('#affected_date').val()===""){
		toastr.warning("Enter Effective Date", "ERROR");
		$("#affected_date").focus();
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_party_purchase",
			edit_id:$("#edit_id_party").val(),
			party_id:$("#party_id").val(),
			rate_tolerance:$("#rate_tolerance").val(),
			discount_percentage:$("#discount_percentage").val(),
			quotation_no:$("#quotation_no").val(),
			quotation_date:$("#quotation_date").val(),
			affected_date:$("#affected_date").val(),
			valid_date : $("#valid_date").val(),
			party_rate:$("#party_rate").val(),
			pid:$('#pid').val(),

		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
			}
			
			$("#party_id").select2("val","");
			$("#party_rate").val('');
			$("#edit_id_party").val('');
			$("#rate_tolerance").val('');
			$("#discount_percentage").val('');
			$("#quotation_no").val('');
			$("#quotation_date").val('');
			$("#affected_date").val('');
			$("#valid_date").val('');
			$("#add_party_btn").val("Add");
			Unloading();
			show_party_purchase();
		}
	});
}


//Alternative Product JAYESH 20-07-2021


function add_accessories_product()
{
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	
	if($("#acc_product_id").val()==="")
	{		
		toastr.warning("Select Product Id", "ERROR");
		$("#acc_product_id").select2("focus");
		return false;
	}
if($("#acc_product_qty").val()==="")
	{		
		toastr.warning("Enter Product Qty", "ERROR");
		$("#acc_product_qty").val("focus");
		return false;
	}

/* var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	} */

var form_data = { 
		mode : "add_accessories_product",
		edit_id:$("#edit_id_accessories").val(),
		acc_product_id:$("#acc_product_id").val(), 
		pid:$("#pid").val(), 
		acc_product_qty:$("#acc_product_qty").val(), 
		acc_product_desc:$("#acc_product_desc").val() 
		//specification:specification
	};

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: form_data,
		success: function(response)
		{				
			$("#acc_product_id").select2("val","");	
			$("#acc_product_qty").val('');	
			CKEDITOR.instances['acc_product_desc'].setData("");
			$("#edit_id_accessories").val('')
			$("#add_party_purchase").val("Add");
			Unloading();
			show_accessories_product();
		}
	});
}
function show_accessories_product()
{
  // alert("hr");	
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	

	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_accessories_product", product_id:product_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_accessories_product').html(data);				
			Unloading();
		}		
	});
}

function edit_data_accessories_product(id)
{
	//alert("edit");
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_accessories_product",  id : id },
		success: function(response)
		{	
			console.log(response);
			var data = jQuery.parseJSON(response);
			$("#acc_product_id").select2('data', { id:data.acc_product_id, text: data.product_name});
			$("#acc_product_qty").val(data.acc_product_qty);
			$("#edit_id_accessories").val(id);
			CKEDITOR.instances['acc_product_desc'].setData(data.acc_product_desc);
			$("#add_alternative_btn").val("Update");
			Unloading();
			get_hsn_pop(data.acc_product_id);
			load_product_dtls_pop(data.acc_product_id);
		}
	});
}


function delete_data_alternative_product(id)
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_alternative_product",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_accessories_product();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function delete_data_accessories_product(id)
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_alternative_product",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_accessories_product();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

// END JAYESH ALTERNATIVE PRODUCT

/*
Code By Umair: 28/11/2020
Comment Add Make Data
*/
function add_make()
{
	if($("#make_id").val()==="")
	{		
		toastr.warning("Select Make Id", "ERROR");
		$("#make_id").select2("focus");
		return false;
	}
	if($("#make_number_id").val()==="")
	{		
		toastr.warning("Enter Make Number Id", "ERROR");
		$("#make_number_id").select2();
		return false;
	}
	if($("#make_value").val()==="")
	{		
		toastr.warning("Enter Make Value", "ERROR");
		$("#make_value").focus();
		return false;
	}
	if($("#make_stock").val()==="")
	{		
		toastr.warning("Enter Make Stock", "ERROR");
		$("#make_stock").focus();
		return false;
	}
	if($("#make_rate").val()==="")
	{		
		toastr.warning("Enter Make Rate", "ERROR");
		$("#make_rate").focus();
		return false;
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_make_request",
			edit_id:$("#edit_id_make").val(),
			make_id:$("#make_id").val(),
			make_number_id:$("#make_number_id").val(),
			make_value:$("#make_value").val(),
			make_stock:$("#make_stock").val(),
			make_rate:$("#make_rate").val(),
			pid:$('#pid').val(),
			branchid:$('#branchid').val()
		},
		success: function(response)
		{
			$("#make_id").select2("val","");
			$("#make_number_id").select2("val","");
			$("#make_value").val('');
			$("#make_stock").val('');
			$("#make_rate").val('');
			$("#edit_id_make").val('');
			$("#add_make").val("Add");
			Unloading();
			show_make_data();	
		}
	});
}

function add_product_stage()
{
	if($("#party_stage_id").val()==="")
	{		
		toastr.warning("Select Stage", "ERROR");
		$("#party_stage_id").select2("focus");
		return false;
	}
	if($("#stage_per").val()==="")
	{		
		toastr.warning("Enter Percentage", "ERROR");
		$("#stage_per").focus();
		return false;
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "add_product_stage",edit_id_product_stage:$("#edit_id_product_stage").val(),party_stage_id:$("#party_stage_id").val(),stage_per:$("#stage_per").val(),pid:$('#pid').val() },
		success: function(response)
		{
			if(response=="-1"){
				toastr.warning("Weightage should not be greated than 100%", "WARNING");
				Unloading();
			}else{
				$("#party_stage_id").select2("val","");
				$("#stage_per").val('');
				$("#edit_id_product_stage").val('')
				$("#add_stage_btn").val("Add");
				Unloading();
				show_stage_purchase();
			}
			
		}
	});
}

function show_party_purchase()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_party_purchase", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_party_purchase').html(data);				
			Unloading();
		}		
	});
}

/*
Code By Umair: 28/11/2020
Comment: Make Details
*/
function show_make_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_make_info", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_make_data').html(data);				
			Unloading();
		}		
	});
}

function show_stage_purchase()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_stage_purchase", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_stage_purchase').html(data);				
			Unloading();
		}		
	});
}


function edit_data_party_purchase(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_party",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#party_id').select2("val",data.vendor_id);
			$('#party_rate').val(data.price);
			$('#rate_tolerance').val(data.rate_tolerance);
			$('#discount_percentage').val(data.discount_percentage);
			$('#quotation_no').val(data.quotation_number);
			$('#quotation_date').val(data.quotation_date);
			$('#affected_date').val(data.affected_date);
			$('#valid_date').val(data.valid_date);
			$("#edit_id_party").val(id);
			$("#add_party_btn").val("Update");
			Unloading();
		}
	});
}

/*
Code By Umair: 28/11/20
Comment: Edit make Data
*/
function edit_data_make_purchase(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_make",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			$('#make_id').select2("val",data.make_id);
			$('#make_number_id').select2("val",data.make_number_id);
			$('#make_value').val(data.make_value);
			$('#make_stock').val(data.make_stock);
			$('#make_rate').val(data.make_rate);
			$("#edit_id_make").val(id);
			$("#addmake_btn").val("Update");
			Unloading();
		}
	});
}
function edit_data_stage(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_stage",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#party_stage_id').select2("val",data.stage_id);
			$('#stage_per').val(data.stage_per);
			$("#edit_id_product_stage").val(id);
			$("#add_stage_btn").val("Update");
			Unloading();
		}
	});
}

function delete_data_party_purchase(id)
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_party",  eid : id },
			success: function(response)
			{
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

function delete_data_make_purchase(id)
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_make",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_make_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function delete_data_stage(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_stage",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_stage_purchase();
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
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "add_job_party_purchase",edit_id:$("#edit_id_job_party").val(),job_party_process_id:$("#job_party_process_id").val(),party_id:$("#job_party_id").val(),party_rate:$("#job_party_rate").val(),pid:$('#pid').val(),branchid:$('#branchid').val() },
		success: function(response)
		{
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
		url: root_domain+administration_domain+'app/product_mst/',
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
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_job_party",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#job_party_id').select2("val",data.job_party_id);
			$('#job_party_process_id').select2("val",data.job_party_process_id);
			$('#job_party_rate').val(data.job_party_rate);
			$("#edit_id_job_party").val(id);
			$("#add_job_party_btn").val("Update");
			Unloading();
		}
	});
}

function delete_data_job_party_purchase(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_job_data_party",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
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
	var tolerance_plus='';
	var tolerance_minus='';
	var param_unit_id='';
	if($("#param_id").val()==="")
	{		
		toastr.warning("Select Parameter", "ERROR");
		$("#param_id").select2("focus");
		return false;
	}
	
	if($("#qc_process_id").val()==="")
	{		
		toastr.warning("Select Process", "ERROR");
		$("#qc_process_id").select2("focus");
		return false;
	}
	
	if($("#param_value").val()==="")
	{		
		toastr.warning("Enter parameter value", "ERROR");
		$("#param_value").focus();
		return false;
	}else{
		var param_value = $("#param_value").val();
		if(Math.floor(param_value) == param_value && $.isNumeric(param_value)) {
			if($("#tolerance_plus").val()==="")
			{		
				toastr.warning("Enter tolerance value", "ERROR");
				$("#tolerance_plus").focus();
				return false;
			}
			if($("#tolerance_minus").val()==="")
			{		
				toastr.warning("Enter tolerance value", "ERROR");
				$("#tolerance_minus").focus();
				return false;
			}
			if($("#param_unit_id").val()==="")
			{		
				toastr.warning("Select unit", "ERROR");
				$("#param_unit_id").focus();
				return false;
			}
		}
	}

	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_param_value",
			edit_id:$("#edit_id_param").val(),
			param_id:$("#param_id").val(),
			param_value:$("#param_value").val(),
			pid:$('#pid').val(),
			tolerance_plus:$('#tolerance_plus').val(),
			tolerance_minus:$('#tolerance_minus').val(),
			param_unit_id:$('#param_unit_id').val(),
			branch_id:$('#branchid').val(),
			qc_process_id:$('#qc_process_id').val()
		},
		success: function(response)
		{
			
			$("#param_id").select2("val","");
			$("#param_value").val('');
			$("#tolerance_plus").val('');
			$("#tolerance_minus").val('');
			$("#param_unit_id").select2("val","");
			$("#qc_process_id").select2("val","");
			$("#edit_id_param").val('')
			$("#add_param").val("Add");

			$('#tolerance_plus').attr('readonly', false);
			$('#tolerance_minus').attr('readonly', false);
			$('#param_unit_id').attr('disabled', false);
			
			
			$('.qc_on_procduct').prop('checked', true);
			
			Unloading();
			
			show_product_param();
			
		}
	});
}

function show_product_param()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	var branch_id=$('#branchid').val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_product_param", product_id:product_id,form_mode:form_mode, branch_id:branch_id },
		success: function(data){
			$('#table_product_parameter').html(data);
			
			var current_number = $('.qc_row').last().attr('data-cid');	

			current_number = current_number ? current_number : 0;
			//alert(current_number);
			if(current_number==0){
				$('.qc_on_procduct').prop('checked', false);
			}
			Unloading();
		}		
	});
}

function edit_product_param(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_param",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#param_id').select2("val",data.param_id);
			$('#param_value').val(data.param_value);
			$('#tolerance_plus').val(data.tolerance_plus);
			$('#tolerance_minus').val(data.tolerance_minus);
			$('#param_unit_id').select2("val",data.unit_id);
			$('#qc_process_id').select2("val",data.process_id);
			$("#edit_id_param").val(id);
			$("#add_param").val("Update");

			if(Math.floor(data.param_value) == data.param_value && $.isNumeric(data.param_value)) {
				$('#tolerance_plus').attr('readonly', false);
				$('#tolerance_minus').attr('readonly', false);
				$('#param_unit_id').attr('disabled', false);
			}else{
				$('#tolerance_plus').attr('readonly', true);
				$('#tolerance_minus').attr('readonly', true);
				$('#param_unit_id').attr('disabled', true);
			}
			Unloading();
		}
	});
}
function delete_data_param(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_param",  eid : id },
			success: function(response)
			{
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
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "get_product_code",  pcode : pcode },
		success: function(response)
		{
			var data=jQuery.parseJSON(response)
			var series=data.series;
			var code=data.code;
			$('#product_icode').val(series);
			$('#product_icode_code').val(code);
			icode_validation(series);
			Unloading();			
		}
	});	
}

// process parameter

function add_process_value()
{
	var resource_id = '';
	if($("#process_id").val()==="")
	{		
		toastr.warning("Select Process Name", "ERROR");
		$("#process_id").select2("focus");
		return false;
	}
	/*if($("#process_rate").val()==="")
	{		
		toastr.warning("Enter Process Rate", "ERROR");
		$("#process_rate").focus();
		return false;
	}*/
	if($("#process_priority").val()==="")
	{		
		toastr.warning("Enter Process Priority", "ERROR");
		$("#process_priority").focus();
		return false;
	}
	if($("#process_type").val()==="")
	{		
		toastr.warning("Select Process Type", "ERROR");
		$("#process_type").focus();
		return false;
	}
	if($("#process_time").val()==="")
	{		
		toastr.warning("Select Process Time", "ERROR");
		$("#process_time").focus();
		return false;
	}
	if($("#process_type").val()=="1"){
		
		if($("#resource_id").val()==="" || $("#resource_id").val()==null)
		{		
			toastr.warning("Select Resource", "ERROR");
			$("#resource_id").focus();
			return false;
		}else{
			resource_id = $('#resource_id').val();
		}
	}

	if($("#process_loss").val()!=''){
		var value = $("#process_loss").val();
		if(value<0 || value>100){
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_plus").val()!=''){
		var value = $("#process_scrap_tolerance_plus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_minus").val()!=''){
		var value = $("#process_scrap_tolerance_minus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_process_value",
			edit_id:$("#edit_id_process").val(),
			process_id:$("#process_id").val(),
			process_rate:$("#process_rate").val(),
			process_priority:$("#process_priority").val(),
			pid:$('#pid').val(),
			process_type:$('#process_type').val(),
			process_time:$('#process_time').val(),
			process_opening:$('#process_opening').val(),
			process_loss:$('#process_loss').val(),
			process_scrap_tolerance_plus:$('#process_scrap_tolerance_plus').val(),
			process_scrap_tolerance_minus:$('#process_scrap_tolerance_minus').val(),
			resource_id:resource_id 
		},
		success: function(response)
		{
			
			$("#process_id").select2("val","");
			$("#process_rate").val('');
			$("#process_priority").val('');
			$("#edit_id_process").val('')
			$("#process_type").val('');
			$("#process_time").val('');
			$("#add_process").val("Add");
			$("#resource_id").select2("val","");
			$("#process_loss").val('');
			$("#process_scrap_tolerance_plus").val('');
			$("#process_scrap_tolerance_minus").val('');
			
			

			$('.process_on_procduct').prop('checked', true);
			Unloading();
			show_product_process();
		}
	});
}

function show_product_process()
{
	
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	var branch_id=$('#branchid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_product_process", product_id:product_id,form_mode:form_mode, branch_id:branch_id },
		success: function(data){
			$('#table_product_process').html(data);	
			var total_process_time = $('#total_proces_time').val();	
			
			if(total_process_time != undefined)
			{
				$("#product_making_time").val(total_process_time);
				$('#product_making_time').attr('readonly', true);
			}
			
			
			//alert(total_process_time);
			//console.log(total_process_time);
			//$("#product_making_time").attr('readonly');
			
			var current_number = $('.process_row').last().attr('data-cid');	

			current_number = current_number ? current_number : 0;
			var new_number = parseInt(current_number) + 1;

			$('.process_priority').val(new_number);
			$('.process_priority_label').html(new_number);

			if(current_number==0){
				$('.process_on_procduct').prop('checked', false);
			}
			load_product_process_qc_show();
			Unloading();
		}		
	});
}



function edit_product_process(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_process",  id : id },
		success: function(response)
		{

			var data = jQuery.parseJSON(response);
			$('#process_id').select2("val",data.process_id);
			$('#process_rate').val(data.process_rate);
			$('#process_priority').val(data.process_priority);
			$('.process_priority_label').html(data.process_priority);
			$('#process_type').val(data.process_type);
			$('#process_time').val(data.process_time);
			$('#process_opening').val(data.process_opening);
			$('#process_loss').val(data.process_loss);
			$('#process_scrap_tolerance_plus').val(data.process_scrap_tolerance_plus);
			$('#process_scrap_tolerance_minus').val(data.process_scrap_tolerance_minus);
			$('#resource_id').select2("val",data.resource_id);
			$("#edit_id_process").val(id);
			$("#add_process").val("Update");

			manage_resource(data.process_type);

			Unloading();
		}
	});
}
function delete_data_process(pr_process_id,priority_id,product_id,is_deletable)
{
	if(is_deletable == 0){
		Swal.fire({
		  title: 'Process is used in BOM.',
		  text: "You can't be delete this process!",
		  icon: 'info',
		})
	}else{
		var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/product_mst/',
				data: { mode : "delete_data_process",  eid : pr_process_id, priority_id : priority_id, product_id : product_id },
				success: function(response)
				{
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
}

function get_ms_kg()
{
	var product_width=Number($('#product_width').val());
	var product_height=Number($('#product_height').val());
	var product_thickness=Number($('#product_thickness').val());
	var product_density=Number($('#product_density').val());
	var total=(product_width/1000)*(product_height/1000)*(product_thickness/1000)*product_density;
	$('#product_kg').val(total.toFixed(2));
}


function changeStatus(pid,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
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

	//alert(type);
	if(type=="8" || type=="6"){
		$('.typeled').attr("style","display:block");
		$(".stagelist").hide();
	}else{
		if(type==0){
			$(".stagelist").show();
			$('.typeled').attr("style","display:none");
		}else{
			$(".stagelist").hide();
			$('.typeled').attr("style","display:none");
		}
		
	}
}

function get_revision_data(drawing_id)
{
	var eid=$('#eid').val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "get_revision_data",drawing_id:drawing_id},
		success: function(data){
			var arr = jQuery.parseJSON(data);
			$('#revision_id').empty().append(arr.revision_id);
			$("#revision_id").select2({
				width: '100%'
			});	
			Unloading();
		}		
	});
}

function manage_resource(type){
	if(type=='2'){
		$('.resource_label_manage').addClass('hide');
		//$('.processRate_label_manage').removeClass('hide');
	}else{
		$('.resource_label_manage').removeClass('hide');
		//$('.processRate_label_manage').addClass('hide');
	}
}


$('#image_name').change(
	function () {
		var fileExtension = ['png','jpg','jpeg'];
		if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
			toastr.warning("Only '.png, .jpg, .jpeg' format is allowed.", "WARNING");
        this.value = ''; // Clean field
        return false;
    }
});

function view_product_image(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "view_product_image", id : id },
		success: function(response)
		{
			$('#product_image').html(response);
			$("#ModalEditAccount").modal("show");
			Unloading();
		}
	});	
}

function add_drawing(){
	Loading(true);
	$("#ModalDrawing").modal("show");
	Unloading();
}

/*$("#drawing_number").blur(function(){
	var drawing_number = $(this).val();
	var data = drawing_validate(drawing_number);
	alert(data);
	if(data=='1'){
		toastr.warning("DRAWING NUMBER ALREADY EXISTS.", "WARNING");
		$("#drawing_number").focus();
		return false;
	}
});*/


function drawing_validate(drawing_number){
	var ret ='';
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/drawing/',
		async:false,
		data: { mode : 'check_drawing_number', drawing_number : drawing_number, eid : eid},
		success: function(data){
			ret =  data;
		}		
	});
	return ret ;
}

function load_drawing_number(drawing_id){
	if(drawing_id){
		Loading(true);
		$.ajax({
			type:'POST',
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode:"load_drawing_number", drawing_id:drawing_id },
			success: function(response)
			{
				Unloading();
				
				$('#drawing_id').empty().append(response);
				$("#drawing_id").select2({
					width: '100%'
				});	
				/*$('#drawing_id').html(arr.html_resp);
				$('#drawing_id').select2("val",($("#drawing_id option:eq(1)").val())).select2('focus');*/
				
			}
		});
	}
}

/*
Code By Umair: 17/02/2021
Comment: Scrap Listing
*/
function show_scrap_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_scrap_info", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_scrap_data').html(data);				
			Unloading();
		}		
	});
}

/*
Code By Umair: 17/02/2021
Comment Add Scrap Data
*/
function add_scrap()
{
	if($("#material_issue_weight").val()==="")
	{		
		toastr.warning("Enter Weight", "ERROR");
		$("#material_issue_weight").focus();
		return false;
	}
	if($("#product_scrap_id").val()==="")
	{		
		toastr.warning("Select Scrap Code", "ERROR");
		$("#product_scrap_id").select2();
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_scrap_request",
			edit_id:$("#edit_id_scrap").val(),
			product_scrap_id:$("#product_scrap_id").val(),
			material_issue_weight:$("#material_issue_weight").val(),
			pid:$('#pid').val(),
			branchid:$('#branchid').val()
		},
		success: function(response)
		{
			$("#product_scrap_id").select2("val","");
			$("#material_issue_weight").val('');
			$("#edit_id_scrap").val('');
			$("#addscrap_btn").val("Add");
			Unloading();
			show_scrap_data();	
		}
	});
}

/*
Code By Umair: 17/02/2021
Comment: Edit Scrap Data
*/
function edit_data_scrap(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_scrap",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#product_scrap_id').select2("val",data.scrap_code_id);
			$('#material_issue_weight').val(data.material_issue_weight);
			$("#edit_id_scrap").val(id);
			$("#addscrap_btn").val("Update");
			Unloading();
		}
	});
}

/*
Code By Umair: 17/02/2021
Comment: Delete Scrap Data
*/
function delete_data_scrap(id)
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_scrap",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_scrap_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function check_base_value(str){
	
	if($.isNumeric(str)) {
		$('#tolerance_plus').attr('readonly', false);
		$('#tolerance_minus').attr('readonly', false);
		$('#param_unit_id').attr('disabled', false);
	}else{
		$('#tolerance_plus').val('');
		$('#tolerance_minus').val('');
		$("#param_unit_id option:selected").prop("selected", false);
		$("#param_unit_id").select2({
			width: '100%'
		});

		$('#tolerance_plus').attr('readonly', true);
		$('#tolerance_minus').attr('readonly', true);
		$('#param_unit_id').attr('disabled', true);
	}

}

function check_param_tolerance(value){
	if(value<0 || value>100){
		toastr.warning("Tolerance value should be between 0 to 100.", "WARNING");
		return false;
	}
}



function load_product_process_qc_show()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	var branch_id=$('#branchid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_product_process_qc_show", product_id:product_id,form_mode:form_mode, branch_id:branch_id },
		success: function(data){
			//alert(data);
			$('#qc_process_id').html(data);	
			Unloading();
		}		
	});
}
function load_revision_image(revision_id){
	//alert(revision_id);
	if(revision_id){
		$("#r_image").html('<a class="btn btn-xs btn-info" title="View Image" data-toggle="tooltip" data-id="'+revision_id+'" data-placement="top" href="javascript:void(0)" onClick="view_revision_image('+revision_id+')"><i class="fa fa-eye"></i></a>');
	}
}
function view_revision_image(id)
{
	//alert(id);
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/drawing/',
		data: { mode : "view_revision_image", id : id },
		success: function(response)
		{
			$('#revision_image_list').html(response);
			$("#Modal_view_revision_image").modal("show");
			
			Unloading();
		}
	});	
}
function total_stock_value_count(){
	
	var total_delivery_qty=document.getElementsByName('bstock[]');

	var cnt=total_delivery_qty.length;
	
	var grandtotal_delivery_qty=0;

	for(var i=0;i<cnt;i++)
	{	
		var pqty=parseFloat(total_delivery_qty[i].value);
		if(isNaN(pqty)){
			pqty=0;
		}
		grandtotal_delivery_qty+=pqty;
		//alert(grandtotal_delivery_qty);
	}

	var total=parseFloat(grandtotal_delivery_qty).toFixed(2);
	
	$("#product_opening").val(total);
	
}
//hardi product image upload in server start 11-1-2022
function add_product_tempimage()
{ 
	var fileInput = $('#image')[0];
	if( fileInput.files.length > 0 ){
		var data = new FormData();
		$.each(fileInput.files, function(k,file){
			data.append('image[]', file);
		});
		data.append("mode",$('#img_tempmode').val());
		data.append("branchid",$('#branchid').val());
		data.append("pid",$('#pid').val());
	}
	
	if($('#image').prop('files')[0]==undefined){
		toastr.warning("Please Select Image", "WARNING"); 
		return false;
	}

	$.ajax({
		url: root_domain+administration_domain+'app/product_mst/',
		method:"POST",
		data: data,
		contentType: false,
		cache: false,
		processData: false,
		beforeSend:function(){
			$('#pro_temp_images').html("<label class='text-success'>Image Uploading...</label>");
		},   
		success:function(data)
		{
			if(data == '-1')
			{
				toastr.warning("Something Wrong", "WARNING"); 
				return false;
			}
			else
			{
				$("#image").val('');
				show_images_tempdata();
			}

		}
	});

}

function show_images_tempdata()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "show_images_tempdata", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#pro_temp_images').html(data);				
			Unloading();
		}		
	});
}

function delete_data_tempimage(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_tempimage",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_images_tempdata();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function copyToClipboard(element) {
	console.log(element);
	var $temp = $("<input>");
	$("body").append($temp);
  //$temp.val($(element).text()).select();
  $temp.val(element).select();
  document.execCommand("copy");
  $temp.remove();
}
//hardi end 11-1-2022
function show_die_allocation_data()
{
	var form_mode=$('#form_mode').val();
	var product_id=$('#pid').val();
	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "load_die_allocation_info", product_id:product_id,form_mode:form_mode },
		success: function(data){
			$('#table_die_allocation_data').html(data);				
			Unloading();
		}		
	});
}
function add_die_allocation()
{
	if($("#die_product_id").val()==="")
	{		
		toastr.warning("Select Capital Good", "ERROR");
		$("#die_product_id").select2("focus");
		return false;
	}
	if($("#die_customer_id").val()==="")
	{		
		toastr.warning("Select Customer", "ERROR");
		$("#die_customer_id").select2("focus");
		return false;
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { 
			mode : "add_die_allocation_request",
			edit_id:$("#edit_id_die_allocation").val(),
			die_product_id:$("#die_product_id").val(),
			die_customer_id:$("#die_customer_id").val(),
			branch_id:$('#branch_id').val(),
			product_id:$('#pid').val()
		},
		success: function(response)
		{
			$("#die_product_id").select2("val","");
			$("#die_customer_id").select2("val","");
			$("#edit_id_die_allocation").val('');
			$("#add_die_allocation_btn").val("Add");
			Unloading();
			show_die_allocation_data();	
		}
	});
}
function edit_data_die_allocation(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "preedit_die_allocation",  id : id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#die_product_id').select2("val",data.die_product_id);
			$('#die_customer_id').select2("val",data.die_customer_id);
			$("#edit_id_die_allocation").val(id);
			$("#add_die_allocation_btn").val("Update");
			Unloading();
		}
	});
}
function delete_data_die_allocation(id)
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_mst/',
			data: { mode : "delete_data_die_allocation",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_die_allocation_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function modal_remove(){
	$('#modal-add-product').modal('hide');
}
function open_model(){
	$('#modal-excel-product').modal('show');
}
function generate_product_name(){
	// alert("hi");
	var product_first_name_id=$("#product_first_name").val();
	var pro_mst_type_id=$("#pro_mst_type").val();
	var product_surface_area_id=$("#product_surface_area").val();
	var pro_cartridge_mst_id=$("#pro_cartridge_mst").val();
	var product_model_name_id=$("#product_model_name").val();
	var product_installation_id=$("#product_installation").val();
	var product_mst_type_id=$("#product_mst_type").val();
	var pro_class_mst_id=$("#pro_class_mst").val();
	var product_impregnation_id=$("#product_impregnation").val();
	
	var short_code = '';var short_description = ''; 
	if(filter_concept_permission==1){
	    short_code = 'FC';
	    short_description= ' "FILTER CONCEPT MAKE" ';
	}

	if(product_first_name_id){
		var product_first_name = jQuery("#product_first_name :selected").text();
	}else{
		var product_first_name = "";
	}
	
	if(pro_mst_type_id){
		var pro_mst_type = jQuery("#pro_mst_type :selected").text();
	}else{
		var pro_mst_type = "";
	}
	
	if(product_surface_area_id){
		var product_surface_area = jQuery("#product_surface_area :selected").text(); 
	}else{
		var product_surface_area = "";
	}
	
	if(pro_cartridge_mst_id){
		var pro_cartridge_mst = jQuery("#pro_cartridge_mst :selected").text();
	}else{
		var pro_cartridge_mst = "";
	}
	
	if(product_model_name_id){
		var product_model_name = jQuery("#product_model_name :selected").text();
	}else{
		var product_model_name = "";
	}
	
	if(product_installation_id){
		var product_installation = jQuery("#product_installation :selected").text();
	}else{
		var product_installation = "";
	}
	
	if(product_mst_type_id){
		var product_mst_type = jQuery("#product_mst_type :selected").text();
	}else{
		var product_mst_type = "";
	}
	
	if(pro_class_mst_id){
		var pro_class_mst = jQuery("#pro_class_mst :selected").text();
	}else{
		var pro_class_mst = "";
	}
	
	if(product_impregnation_id){
		var product_impregnation = jQuery("#product_impregnation :selected").text();
	}else{
		var product_impregnation = "";
	}
	
	var product_des=short_description+product_first_name+" "+pro_mst_type+" "+product_surface_area+" "+" "+pro_cartridge_mst+" "+ product_model_name+" "+product_installation+" "+product_mst_type+" "+pro_class_mst+" "+product_impregnation;
	
	var pro_first_name_code = $('#product_first_name').find('option:selected').attr('data-pcode');
	var pro_type_mst_code = $('#pro_mst_type').find('option:selected').attr('data-pcode');
	var pro_surface_area_code = $('#product_surface_area').find('option:selected').attr('data-pcode');
	var pro_cartridge_mst_code = $('#pro_cartridge_mst').find('option:selected').attr('data-pcode');
	var pro_model_code = $('#product_model_name').find('option:selected').attr('data-pcode');
	var pro_installation_code = $('#product_installation').find('option:selected').attr('data-pcode');
	var pro_mst_type_code = $('#product_mst_type').find('option:selected').attr('data-pcode');
	var pro_class_mst_code = $('#pro_class_mst').find('option:selected').attr('data-pcode');
	var pro_impregnation_code = $('#product_impregnation').find('option:selected').attr('data-pcode');
	
	if(pro_first_name_code){
		var product_first_name_code = pro_first_name_code;
	}else{
		var product_first_name_code = "";
	}
	
	if(pro_type_mst_code){
		var product_type_mst_code = pro_type_mst_code;
	}else{
		var product_type_mst_code = "";	
	}
	
	if(pro_surface_area_code){
		var product_surface_area_code = pro_surface_area_code;
	}else{
		var product_surface_area_code = "";
	}
	
	if(pro_cartridge_mst_code){
		var product_cartridge_mst_code = pro_cartridge_mst_code;
	}else{
		var product_cartridge_mst_code = "";	
	}
	
	if(pro_model_code){
		var product_model_code = pro_model_code;
	}else{
		var product_model_code = "";
	}
	
	if(pro_installation_code){
		var product_installation_code = pro_installation_code;
	}else{
		var product_installation_code = "";
	}
	
	if(pro_mst_type_code){
		var product_mst_type_code = pro_mst_type_code;
	}else{
		var product_mst_type_code = "";
	}
	
	if(pro_class_mst_code){
		var product_class_mst_code = pro_class_mst_code;
	}else{
		var product_class_mst_code = "";
	}
	
	if(pro_impregnation_code){
		var product_impregnation_code = pro_impregnation_code;
	}else{
		var product_impregnation_code = "";
	}
	
	
	
	var product_code=short_code+product_first_name_code+product_type_mst_code+product_surface_area_code+product_cartridge_mst_code+product_model_code+product_installation_code+product_mst_type_code+product_class_mst_code+product_impregnation_code; 

	var i = 1;
	var c = ' ';
	var dynamic_field = $('#dynamic_field').val();

	for(i=1; i<=dynamic_field; i++){
		var name = $("#field_id"+i).find('option:selected').attr('data-pcode');
		if(name != ''){
		    var seprator = '';
		    if(i!=1){
		        seprator = '-';
		    }
		    c +=seprator+name;
		}
	}

	$("#product_name").val(product_code+c);
	
	CKEDITOR.instances['product_desc'].setData(product_des);
}

function icode_validation(val){
	var form_mode = $('#form_mode').val();
	var product_id = $('#pid').val();
	$('#icodeval').html('');
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_mst/',
		data: { mode : "icode_validation",  product_id : product_id, form_mode:form_mode, val:val },
		success: function(response)
		{
			console.log(response);
			// $('#icodeval').html('');
			if(response.trim()=='1'){
				$('#icodeval').html('Item Code Already Exists!!');
			}else{
				$('#icodeval').html('');
			}						
		}
	});
}
function product_load_pro(){
	
	var testData = [];
	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			//console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
	load_cat_product('acc_product_id', testData)	
	// return testData;
}

function load_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
	    // query with pagination
	    query: function(q) {
	    	var pageSize,
	    	results,
	    	that = this;
	      	pageSize = 20; // or whatever pagesize
	      	results = [];
	      	if (q.term && q.term !== '') {
	        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
	        	results = _.filter(that.data, function(e) {
	        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
	        	});
	        } else if (q.term === '') {
	        	results = that.data;
	        }
	        q.callback({
	        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
	        	more: results.length >= q.page * pageSize,
	        });
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}

function load_product_dtls_pop(product_id){
	
	var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	var edit_id_accessories = $('#edit_id_accessories').val();
	
	/* if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	} */

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type},
			success: function(response)
			{
				////console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				if(!edit_id_accessories)
				{
				CKEDITOR.instances['acc_product_desc'].setData(resp.product_desc);
				}
				//CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if(currency_id != curr){
					rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
				}else{
					rate = resp.product_sale_rate;
				}
				
				$('#acce_rate').val(rate.toFixed(2));
				//$('#unitid').select2("val",resp.product_base_unit);
				$('#current_stock_pop').css('display', 'block');
				$('#current_stock_pop').html('Current Stock: '+resp.current_stock);
				$('.unit_pop').css('display', 'block');
				$('#unit_pop').html('Unit: '+resp.unit_name);
				Unloading();						
				
				
					
			}
		});	
	}
}
function get_hsn_pop(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode_pop').text(response);
				$(".hsncode_pop").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode_pop").hide();
				
				$('#acc_product_id').select2("val","");
				return false;
			}
		}
	});
	
}

function product_project_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			// console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
	load_project_cat_product('project_product_id', testData)	
	// return testData;
}

function load_project_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
	    // query with pagination
	    query: function(q) {
	    	var pageSize,
	    	results,
	    	that = this;
	      	pageSize = 20; // or whatever pagesize
	      	results = [];
	      	if (q.term && q.term !== '') {
	        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
	        	results = _.filter(that.data, function(e) {
	        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
	        	});
	        } else if (q.term === '') {
	        	results = that.data;
	        }
	        q.callback({
	        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
	        	more: results.length >= q.page * pageSize,
	        });
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}

function show_project_pro_data()
{
	var so_id=$("#eid_main").val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain + administration_domain +'app/product_mst/',
	data: { mode : "show_project_pro_data",so_id:so_id},
	success: function(data){
			//console.log(data);
			$('#sale_project_productdata').html(data);				
			Unloading();
		}		
	});
}

function add_project_field()
{
	
	if($("#project_product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#product_project_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	/*if($("#sqr_ft").val()==="")
	{		
		toastr.warning("Enter Sqr/Ft", "ERROR")
		return false;
	}*/
	if($("#product_project_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if($("#branch_id").val()==="")
	{		
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain +'app/product_mst/',
		data: { mode : "add_project_field",
		edit_id:$("#edit_id_project").val(),
		product_id:$("#project_product_id").val(),
		product_disc:$("#product_project_des").val(),
		product_spec:$("#product_project_spec").val(),
		product_hsn_code:$("#product_project_hsn_code").val(),
		product_qty:$("#product_project_qty").val(),
		product_rate:$("#product_project_rate").val(),
		project_assign_id:$("#eid_main").val(),
		branch_id:$("#branch_id").val()
	},
	success: function(response)
	{
		/*console.log(response);*/
		$("#project_product_id").select2("val","")
		$("#product_project_des").val("")
		$("#product_project_spec").val("")
		$("#product_project_hsn_code").select2("val","")
		$("#product_project_qty").val("")
		$("#product_project_rate").val('')
		$("#edit_id_project").val('')
		$('#addrow').val('Add');
		Unloading();
		show_project_pro_data();
	}
});
}	

function edit_project_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain +'app/product_mst/',
		data: { mode : "edit_project_data",  id : id},
		success: function(response)
		{
				//console.log(response)
				var data = jQuery.parseJSON(response);
				$("#project_product_id").select2('data', { id:data.product_id, text: data.product_name})
				$("#product_project_hsn_code").select2("val",data.product_hsn_code)
				$("#product_des").val(data.description)
				$("#product_project_qty").val(data.product_qty)
				$("#product_project_rate").val(data.product_rate)
				$("#formulaid").val(data.formulaid);
				$("#edit_id_project").val(id)
				$('#addrow').val('Update');
				CKEDITOR.instances['product_project_des'].setData(data.product_disc);
				CKEDITOR.instances['product_project_spec'].setData(data.product_spec);
				Unloading();
			}
		});
}

function delete_project_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + administration_domain +'app/product_mst/',
			data: { mode : "delete_project_data",  eid : id},
			success: function(response)
			{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_project_pro_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
	}
}

function get_project_product(){
	var id = $("#product_type").val();
	if(id == '-1'){
		$("#project_product").show();
	}else{
		$("#project_product").hide();
	}
}

function load_project_productdetail(val) {
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
	
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain +'app/product_mst/',
		data: { mode : "load_project_productdetail",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			CKEDITOR.instances['product_project_des'].setData(obj.product_desc);
			CKEDITOR.instances['product_project_spec'].setData(obj.product_spec);
			$('#product_project_hsn_code').select2("val",obj.product_hsn);
			$('#product_project_rate').val(obj.product_sale_rate);

		}
	});
}

function get_child_category(){
	var parent_id = $('#parent_category').val();
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain +'app/product_mst/',
		data: { mode : "get_child_category",parent_id :parent_id },
		success: function(response)
		{
			$("#product_category").html(response);
		}
	});
}


function add_category() {
	branch_id = $('#branch_id').val();
	if (!branch_id) {
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	
	$("#abranch_id").select2({
		width : "100%"
	})
	$("#abranch_id").val(branch_id)
	$('#modal-add-category').modal('show');
	$("#product_add_type").val('product_add');
	//$("#ledger_name").focus();
}