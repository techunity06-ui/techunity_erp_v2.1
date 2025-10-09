//var datatable;
$(document).ready(function() {
	load_datatable();
	product_load();

	$("#store_release").validate({
	rules: {
		issue_date: {
			required: true			
		},
		branch_id: {
			required: true			
		},
		user_id: {
			required: true
		},
		
	},
	messages: {
		issue_date: {
			required: "Select Issue Date"
		},
		branch_id: {
			required: "Select Branch Id"
		},
		user_id: {
			required: "Select User Name"
		},
		
	}
}); 
});

function load_datatable()
{
	var branch_id=$('#branch_id').val();
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { mode : "fetch_working",branch_id:branch_id },
			success: function(response)
			{
				var resp = jQuery.parseJSON(response);
				$('#dynamic_table_working').html(resp.html);
				if(resp.count > 0){
					$('#stock_release_count').val(resp.count)
				}else{
					$('#stock_release_count').val(0)
				}
				Unloading();
				
			}
	}); 
}

function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}



$('#product_id').select2({
	data: product_load(),
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



function load_product_detail(pro_id) {

	//alert(pro_id);

	// $pro_id = $("#product_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_detail_list/',
		data: { mode : "load_productdata",product_id :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
			if($("#edit_id").val()==""){
				$('#product_conv_unit_name').val(resp.conv_unit_name);
				$('#product_conv_unit').val(resp.product_conv_unit);
				// $('#product_conv_qty').val(resp.product_conv_qty);
				$('#product_conv_qty_hide').val(resp.product_conv_qty);

				$('#product_base_unit_name').val(resp.base_unit_name);
				$('#product_base_unit').val(resp.product_base_unit);
				// $('#product_base_qty').val(resp.product_base_qty);
				$('#product_base_qty_hide').val(resp.product_base_qty);

				
			}
				
				$('#product_id').select2('data', {id:resp.product_id, text: resp.product_name});
				/*$.ajax({
					type: "POST",
					url: root_domain+inventory_domain+'app/store_release_detail_list/',
					data: { mode : "get_stock", product_id:pro_id, unit_id : resp.product_base_unit},
					success: function(stock)
					{
					
						$(".current_stock").show();
						$("#current_stock").text(stock);
						
						if($("#edit_id").val()==""){
							$("#product_base_qty").val(stock).trigger('change');	
						}			
											
					}
				});*/

				Unloading();
				
			}
		});

	
}

function product_convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);

		if(resultb===results){
			return false;
		}
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	
	//var base_qty_hide=$("#product_base_qty_hide").val();
	//var conv_qty_hide=$("#product_conv_qty_hide").val();
	
	//var base_qty=$("#product_base_qty").val();
	
	//var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				
				if(type===1){
					$("#product_base_qty_hide").val(base_qty);
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty);
				}
				
				if(type===1){
					$("#product_conv_qty").val((arr.show_qty));
					$("#product_conv_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_base_qty").val((arr.show_qty));
					$("#product_base_qty_hide").val(arr.hide_qty);				
					
				}else{
					$("#product_base_qty").val((arr.show_qty));
					$("#product_base_qty_hide").val(arr.hide_qty);
					$("#product_conv_qty").val((arr.show_qty));
					$("#product_conv_qty_hide").val(arr.hide_qty);
				}
				if($("#godown_id").val() != ""){
					
					check_stock();	
				}
				Unloading();

			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		
	}
}
function check_stock(pro_id =""){

	if($("#product_id").val() == ""){
		$("#godown_id").select2('val',null);
		toastr.warning("Please select product", "WARNING");
		return;
	}

	if($("#godown_id").val() == ""){
		if(pro_id == ""){
			toastr.warning("Please select godown", "WARNING");	
		}
		return;
	}

	var product_id = $("#product_id").val();
	var unit_id =  $("#product_base_unit").val();
	var godown_id =  $("#godown_id").val();


	var qty = $('#product_base_qty').val();
	

	if(qty == ""){
		toastr.warning("Please enter quantity.", "WARNING");
		return false;
	}
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { mode : "get_stock", product_id:product_id, unit_id : unit_id, godown_id:godown_id},
			success: function(stock)
			{
				$(".current_stock").show();
						$("#current_stock").text(stock);
				if(stock > 0){
					if(parseInt(qty) > parseInt(stock)){
						toastr.warning("You can't enter more than current stock", "WARNING");
						$('#product_base_qty').val(stock);	
					}
				}else{
					toastr.warning("No stock available for this product.", "WARNING");
					$('#product_base_qty').val(stock);
				}
				Unloading();
			}
		});
}


function add_field(){
	if($("#product_id").val() == ""){
		toastr.warning("Please select product", "WARNING");
		return;
	}
	if($('#product_base_qty').val() == ""){
		toastr.warning("Please Enter QTY", "WARNING");
		return;
	}
	if($('#godown_id').val() == ""){
		toastr.warning("Please Select Godown", "WARNING");
		return;
	}

	var product_id = $("#product_id").val();
	var unit_id = $('#product_base_unit').val();
	var qty = $('#product_base_qty').val();
	var godown_id = $('#godown_id').val();
	Loading();

	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { mode : "get_stock", product_id:product_id, unit_id : unit_id,godown_id:godown_id},
			success: function(stock)
			{
				
				if(stock > 0){
					if(parseInt(qty) > parseInt(stock)){
						toastr.warning("You can't enter more than current stock", "WARNING");
						$('#product_base_qty').val(stock);	
					}else{

						add_stock_release_data();
					}
				}else{
					toastr.warning("No stock available for this product.", "WARNING");
					$('#product_base_qty').val(stock);
				}
				Unloading();
			}
		});
}

function add_stock_release_data(){

	var product_id = $("#product_id").val();
	var base_qty = $("#product_base_qty").val();
	var unit_id = $('#product_base_unit').val();
	var conv_unit = $("#product_conv_unit").val();
	var conv_qty = $("#product_conv_qty").val();
	var returnable = $("#returnable").val();
	var godown_id = $("#godown_id").val();

	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { 
				mode : "add_field", 
				product_id:product_id,
				base_qty : base_qty,
				unit_id : unit_id,
				conv_unit : conv_unit,
				conv_qty: conv_qty,
				returnable : returnable,
				godown_id,godown_id,
				edit_id : $('#edit_id').val()
			},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					toastr.success("STOCK MATERIAL ADDED SUCCESSFULLY", "SUCCESS")
					
					load_datatable();
				}
				else if(arr.msg == 'update') {
					toastr.success("STOCK MATERIAL UPDATE SUCCESSFULLY", "SUCCESS")
					reset_form_data();
					load_datatable();
				
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					
				}
				else if(arr.msg == '-1'){
					toastr.info("ALREADY EXISTS", "INFO")
							
				}
				Unloading();	
			}
		});
}

function reset_form_data(){
	$('#product_id').select2('val',null);
	$('#godown_id').select2('val',null);
	$("#product_base_qty").val(0);
	$("#returnable").select2('val',0);
	$(".current_stock").hide();
	$('#edit_id').val('');
	$('#add_row').val('Add');
}

function delete_material_stock(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_detail_list/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{

					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("STOCK MATERIAL DELETE SUCCESSFULLY", "SUCCESS");
						load_datatable();
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

function edit_data(id)
{
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_detail_list/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			
			$('#product_id').val(data.product_id).trigger('change');
			$('#product_conv_unit').val(data.release_conv_unit);
			$('#product_conv_qty').val(data.release_conv_qty);
			$('#product_base_unit').val(data.release_unit);
			$("#godown_id").val(data.godown_id).trigger('change');
			$("#product_base_qty").val(data.release_qty).trigger('change');	
			$('#returnable').val(data.returnable).trigger('change');
			$('#add_row').val('Update');
			$('#edit_id').val(data.release_trn_id);
			Unloading();
		}

		});
}

$("#store_release").on('submit',function(e) {


	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#store_release").valid()) {
		return false;
	}
	if($('#branch_id').val() == '1000'){
		toastr.warning("PLEASE SELECT ANY ONE BRANCH", "ERROR")
		return false;
	}

	if($('#stock_release_count').val() == 0 || $('#stock_release_count').val() == ""){
		toastr.warning("PLEASE ANY ONE PRODUCT", "ERROR")
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	/*for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
    }*/
    var form_data=new FormData(this);	
    
    $.ajax({
    	cache:false,
    	url: root_domain+inventory_domain+'app/store_release_detail_list/',
    	type: "POST",
    	data: form_data,
    	contentType: false,
    	processData:false,
    	success: function(response)
    	{

			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("MATERIAL RELEASE SUCCESSFULLY", "SUCCESS");
				location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		

			$('#store_release').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});