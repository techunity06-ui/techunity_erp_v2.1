//var datatable;
$(document).ready(function() {
	product_load();
	load_datatable();
	$("#sales_order_stock_transfer_add").validate({
		rules: {
			so_transfer_no:{
				required: true			
			},
			so_transfer_date: {
				required: true			
			},
			product_id: {
				required: true			
			},
			main_sales_order: {
				required: true			
			},
			main_qty: {
				required: true			
			},
			transfer_sales_order: {
				required: true			
			},
			transfer_qty: {
				required: true			
			}
		},
		messages: {
			transfer_no:{
				required: "Enter Stock Transfer No."			
			},
			transfer_date: {
				required: "Enter Stock Transfer Date"
			},
			product_id: {
				required: "Enter Product"
			},
			main_sales_order: {
				required: "Enter Main sales Order"
			},
			main_qty: {
				required: "Enter Main Qty"
			},
			transfer_sales_order: {
				required: "Enter Transfer Sales order"
			}
			,
			transfer_qty: {
				required: "Enter Transfer Qty"
			}
		}
	});
});

$("#sales_order_stock_transfer_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#sales_order_stock_transfer_add").valid()) {
		return false;
	}
	
	
	form.submitted = true;
	
		
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}	
	var main_qty=$("#main_qty").val();
	var transfer_qty_val=$("#transfer_qty_val").val();
	var transfer_qty=$("#transfer_qty").val();
	
	if(main_qty>=transfer_qty_val){
		var matchqty=transfer_qty_val;
	}else{
		var matchqty=main_qty;
	}
	if(matchqty<transfer_qty){
		toastr.warning("Transfer Qty Not valid", "ERROR");
		return false;
	}
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain + inventory_domain +'app/sales_order_stock_transfer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("Transfer SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain +"sales_order_stock_transfer_list"; 
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg== 'update') {
				toastr.success("Transfer UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'stock_transfer_list';
			}
			$('#stock_transfer_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=so_pro_type&search=sales_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		console.log(len);

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

function load_sales_order(){
	var product_id=$("#product_id").val();
	if(product_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + inventory_domain +'app/sales_order_stock_transfer/',
			data: { mode : "get_sales_order", product_id : product_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$('#main_sales_order').select2("val","");
				$('#main_sales_order').html(obj.mainso);
				
				$('#transfer_sales_order').select2("val","");
				$('#transfer_sales_order').html(obj.pro_html);
				
			}
		});
	}else{
		toastr.warning("Select Product", "WARNING");
	}	
}
function load_main_qty(){
	var main_sales_order=$("#main_sales_order").val();
	if(main_sales_order){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + inventory_domain +'app/sales_order_stock_transfer/',
			data: { mode : "load_main_qty", main_sales_order : main_sales_order},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$('#main_qty').val(obj.main_qty);
			}
		});
	}else{
		toastr.warning("Select Sales Order", "WARNING");
	}
}
function load_transfer_qty(){
	var transfer_sales_order=$("#transfer_sales_order").val();
	if(transfer_sales_order){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + inventory_domain +'app/sales_order_stock_transfer/',
			data: { mode : "load_transfer_qty", transfer_sales_order : transfer_sales_order},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$('#transfer_qty_val').val(obj.trans_qty);
			}
		});
	}else{
		toastr.warning("Select Sales Order", "WARNING");
	}
}
function load_datatable()
{	
	
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
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+inventory_domain+'app/sales_order_stock_transfer/',
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

function approve_returnable_channal(id) 
{
	$('#preview_returnable_approval_hist_modal').modal('show');
	$('#ref_ord_id').val(id);
	load_datatable();
}

function approve_stock_transfer(stock_transfer_id){
	if(stock_transfer_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + inventory_domain +'app/sales_order_stock_transfer/',
			data: { mode : "approve_stock_transfer", stock_transfer_id : stock_transfer_id},
			success: function(response)
			{
				//var obj=jQuery.parseJSON(response);
				//$('#transfer_qty_val').val(obj.trans_qty);
			}
		});
	}else{
		toastr.warning("SOMETHING WRONG", "WARNING");
	}
}
