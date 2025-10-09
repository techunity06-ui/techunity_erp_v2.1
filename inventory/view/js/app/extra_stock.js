//var datatable;
$(document).ready(function() {
	load_extra_stock_datatable();

$("#extra_stock_add").validate({
	rules: {
		product_id: {
			required: true			
		},
		stock_id: {
			required: true			
		},
		product_base_qty: {
			required: true			
		},
		product_conv_qty: {
			required: true			
		},
		vendor_id: {
			required: true			
		},
		branch_id: {
			required: true			
		}
	},
	messages: {
		product_id: {
			required: "Select Product"			
		},
		stock_id: {
			required: "Select Batch"				
		},
		product_base_qty: {
			required: "Enter Base Quantity"				
		},
		product_conv_qty: {
			required: "Enter Convert Quantity"				
		},
		vendor_id: {
			required: "Select Supplier"				
		},
		branch_id: {
			required: "Select Branch"				
		}
	}
}); 
});

function load_extra_stock_datatable()
{
	// var date=$('#rep_date').val();
	
	datatable = $("#dynamic-table").dataTable({
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/extra_stock/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
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

$("#extra_stock_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#extra_stock_add").valid()) {
		return false;
	}
	var batch_no = $("#stock_id").select2('data').text;
	var base_qty = parseFloat($("#product_base_qty").val());
	var stock = parseFloat($("#item_stock").val());
	/*if( base_qty > stock){
		toastr.warning("YOU CAN'T ENTER QUANTITY GREATER THAN STOCK QUANTITY", "ERROR");
		return false;
	}*/

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	
    var form_data=new FormData(this);
    form_data.append('batch_no',batch_no);
    $.ajax({
    	cache:false,
    	url: root_domain+inventory_domain+'app/extra_stock/',
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
				toastr.success("STOCK ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+'extra_stock_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			// $('#material_issue').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
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
	  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
	  
	},
});

function product_convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		// if(resultb===results){
		// 	return false;
		// }
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		// if(resultb===results){
		// 	return false;
		// }
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();

	var product_base_qty=$("#product_base_qty").data('product_base_qty');
	var product_conv_qty=$("#product_conv_qty").data('product_conv_qty');
	var product_id=$("#product_id").val();

	console.log("base_qty : " +base_qty)
	console.log("conv_qty : " +conv_qty)
	console.log("product_base_qty : " +product_base_qty)
	console.log("product_conv_qty : " +product_conv_qty)
	
	if(product_id){

		if(type===1){
					$("#product_base_qty_hide").val(base_qty.trim());
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty.trim());
				}

				if(type=="1"){
					ret_qty=(base_qty/product_base_qty)*product_conv_qty;
				}else if(type=="2"){
					ret_qty=(conv_qty/product_conv_qty)*product_base_qty;
				}else{
					ret_qty="1";
				}
				
				if(type===1){
					$("#product_conv_qty").val(ret_qty);
					$("#product_conv_qty_hide").val(ret_qty);

				}else if(type===2){
					$("#product_base_qty").val(ret_qty);
					$("#product_base_qty_hide").val(ret_qty);				
					
				}else{
					$("#product_base_qty").val(1);
					$("#product_base_qty_hide").val(1);
					$("#product_conv_qty").val(1);
					$("#product_conv_qty_hide").val(1);
				}


	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_base_qty").val("1");
		$("#product_base_qty_hide").val("1");
		$("#product_conv_qty").val("1");
		$("#product_conv_qty_hide").val("1");
	}
}



function load_product_detail(pro_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/extra_stock/',
		data: { mode : "load_productdata",eid :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
				
				$('#product_base_qty').data('product_base_qty',resp.product_base_qty.trim());
				$('#product_conv_qty').data('product_conv_qty',resp.product_conv_qty.trim());

				$('#product_conv_unit_name').val(resp.conv_unit_name);
				$('#product_conv_unit').val(resp.product_conv_unit);
				$('#product_conv_qty').val(resp.product_conv_qty.trim());
				$('#product_conv_qty_hide').val(resp.product_conv_qty.trim());

				$('#product_base_unit_name').val(resp.base_unit_name);
				$('#product_base_unit').val(resp.product_base_unit);
				$('#product_base_qty').val(resp.product_base_qty.trim());
				$('#product_base_qty_hide').val(resp.product_base_qty.trim());

				load_batch_no(pro_id,resp.product_base_unit)

				Unloading();
				
			}
		});
}



function delete_data(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/extra_stock/',
			data: { mode : "delete_data", eid:id},
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_extra_stock_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
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
		url: root_domain+inventory_domain+'app/extra_stock/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#product_id").select2('data', { id:data.product_id, text: data.product_name}).trigger('change');

			setTimeout(function(){ 
			$("#edit_id").val(data.material_issue_trn_id);
			$('#product_base_qty').data('product_base_qty',data.product_base_qty.trim());
			$('#product_conv_qty').data('product_conv_qty',data.product_conv_qty.trim());
			$('#addrow').val('Update');
			
			$("#product_base_qty").val((data.base_qty).trim());

			$("#product_base_unit").val(data.base_unit);
			$("#product_conv_unit").val(data.conv_unit);
			$("#product_conv_qty").val((data.conv_qty).trim());
			$("#product_base_qty_hide").val((data.base_qty).trim());
			$("#product_conv_qty_hide").val((data.conv_qty).trim());

			$("#product_base_unit_name").val(data.base_unit_name);
			$("#product_conv_unit_name").val(data.conv_unit_name);
			Unloading();
			}, 500);

			}
		});
}

function load_stock_qty(stock_id){
	Loading(true);
	var unit_id=$("#product_base_unit").val();
	var product_id=$("#product_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/extra_stock/',
		data: { mode : "load_stock_qty",product_id:product_id, stock_id:stock_id,unit_id:unit_id },
		success: function(data){
			//console.log(data);
			$('#item_stock').attr("placeholder",data);
			$('#item_stock').attr("max",parseFloat(data));
			$('#item_stock').val(parseFloat(data));
			$("#item_qty").attr("placeholder",data);
			$("#item_qty").attr("max",parseFloat(data));
			Unloading();
		}		
	});
}

function load_batch_no(product_id,unit_id){
	Loading(true);
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/extra_stock/',
		data: { mode : "load_batch_no", product_id:product_id,unit_id:unit_id },
		success: function(data){
			//console.log(data);
			$('#stock_id').empty().html(data);
			Unloading();
		}		
	});
}
