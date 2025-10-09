//var datatable;
$(document).ready(function() {
	// validate vendor add form on keyup and submit
	load_grn_datatable();
	load_pro_table();
	load_pending_paking();
	$("#stock_general_add").validate({
		rules: {
			paking_no:{
				required: true			
			},
			paking_date: {
				required: true			
			},
			cust_id: {
				required: true			
			},
		},
		messages: {
			paking_no:{
				required: "Enter Packing No"			
			},
			paking_date: {
				required: "Select Packing Date"
			},
			cust_id: {
				required: "Select Cust"
			},
		}
	}); 
});
$("#stock_general_trn").on('submit',function(e) {
	alert("2");
	});
$("#stock_general_add").on('submit',function(e) {
	alert("1");
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_general_add").valid()) {
		return false;
	}
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/paking/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);

			if(arr.msg == '1') {
				Unloading();
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+"paking_list"; 
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
				toastr.success("GENERAL STOCK UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+inventory_domain+"paking_list"; 
			}
			$('#stock_general_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function load_deduct_productdetail(val,i="") {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response)
			load_deduct_product_unit(val,obj.product_base_unit);
			load_stock_qty();
			
			$("#deduct").hide();
			$("#deduct_batch_wise").show();
		}
	});
}
function get_sales_order(cust_id,so_id) {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "load_sales_order",cust_id :cust_id },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			//alert(obj.so_no);
			$('#salesorderid').html(obj.so_no);
			$('#salesorderid').select2("val",so_id);
		}
	});
}
function get_sales_product(so_id) {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "get_sales_product",so_id :so_id },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			//alert(obj.so_no);
			$('#sales_order_trn_id').html(obj.so_product);
		}
	});
}
function get_product_pen_qty(so_tr_id) {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "get_product_pen_qty",so_tr_id :so_tr_id },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			//alert(obj.so_no);
			//$('#qty').val(obj.product_qty);
			$('#so_pro_pending_qty').html(obj.show_qty);
			$('#unit_show').html(obj.unit_name);
		}
	});
}
function batch_wise_in_stock_open() {
	
	$("#bs-batch_wise_stock_in-modal").modal("show");
}
function add_entry() {
	var cust=$("#cust_id").val();
	var salesorderid=$("#salesorderid").val();
	var sales_order_trn_id=$("#sales_order_trn_id").val();
	var batch_no=$("#batch_no").val();
	var qty=$("#qty").val();
	var eid=$("#eid").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "add_entry",cust :cust,salesorderid:salesorderid,sales_order_trn_id:sales_order_trn_id,batch_no:batch_no,qty:qty,eid:eid },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			if(obj.status==1){
				toastr.success("Data added", "SUCCESS");
				load_pro_table();
				$("#batch_no").val("");
				$("#qty").val("");
				$('#sales_order_trn_id').select2("val","");
				$("#so_pro_pending_qty").html("");
				$("#unit_show").html("");
				
				
			}else if(obj.status==0){
				toastr.warning("Entry Not Add","ERROR");
			}
			
		}
	});
}
function load_pro_table() {
	var cust=$("#cust_id").val();
	var eid=$("#eid").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "load_pro_table",cust :cust,eid:eid },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			
			$("#stock_in_detail").html(obj.pdata);
			
		}
	});
}
function check_qty() {
	var batch_no=$("#batch_no").val();
	var salesorderid=$("#salesorderid").val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/paking/',
		data: { mode : "check_qty",batch_no :batch_no,salesorderid:salesorderid },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			
			if(obj.sales_ordertrn_id>0){
				$("#qty").val(obj.stock);
				//alert(obj.sales_ordertrn_id);
				$('#sales_order_trn_id').select2("val",obj.sales_ordertrn_id);
				//$("#sales_order_trn_id").val(obj.sales_order_trn_id);
				add_entry();
			}else{
				toastr.warning("Product Not Metch","ERROR");
			}
			
			
		}
	});
}
function delete_deduct_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		//Loading();
		$.ajax({
			type: "POST",
			url: root_domain + inventory_domain +'app/paking/',
			data: { mode : "delete_data",  trn_id : id },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					//Unloading();
					load_pro_table()
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function delete_paking_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		//Loading();
		$.ajax({
			type: "POST",
			url: root_domain + inventory_domain +'app/paking/',
			data: { mode : "delete_paking_data",  paking_id : id },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					//Unloading();
					load_grn_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function load_grn_datatable(){
	var date=$('#rep_date').val();
	var pstatus=$('#pstatus').val();

	
	
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
		"sAjaxSource": root_domain+inventory_domain+'app/paking/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{ "name": "date", "value": date },
				{ "name": "pstatus", "value": pstatus }
				);
		},
		/*"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][5]*1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
		}*/
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function load_pending_paking(){
	
	datatable = $("#dynamic-table_new").dataTable({
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
		"sAjaxSource": root_domain+inventory_domain+'app/paking/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch_pending" }
				);
		},
		/*"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][5]*1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
		}*/
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

