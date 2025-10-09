//var datatable;
$(document).ready(function() {
	
	product_load();

	$("#store_return").validate({
		rules: {
			// issue_no:{
			// 	required: true			
			// },
			user_id: {
				required: true			
			},
			branch_id: {
				required: true			
			},
			product_id: {
				required: true			
			},
			godown_id: {
				required: true			
			},
			product_base_qty:{
				required: true				
			},
			product_conv_qty:{
					required: true	
			}
		},
		messages: {
			// issue_no:{
			// 	required: "Select Issue No."			
			// },
			user_id: {
				required: "Select User Name"
			},
			branch_id: {
				required: "Choose Branch"
			},
			product_id: {
				required: "Choose Product"
			},
			godown_id: {
				required: "Choose Godown"
			},
			product_base_qty: {
				required: "Enter Base Quantity"
			},
			product_conv_qty: {
				required: "Enter Convert Quantity"
			}
		}
	}); 

});

$("#store_return").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#store_return").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/direct_return_material/',
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
				toastr.success("MATERIAL RETURN SUCCESSFULLY", "SUCCESS");
				// location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		
			$('#store_return').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

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
	  // $("#product_id").select2('data', { id:1, title: "UPS 3200B"});
	},
});



function load_product_detail(pro_id) {

	//alert(pro_id);

	// $pro_id = $("#product_id").val();
	
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
				
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		
	}
}