//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data();
	
// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#invoice_add").validate({
	rules: {
		invoicetype_id:{
			required: true			
		},
		invoice_date: {
			required: true			
		},
		cust_id: {
			required: true
		}
	},
	messages: {
		invoicetype_id:{
			required: "Select Type"			
		},
		invoice_date: {
			required: "Enter date"
		},
		cust_id: {
			required: "Select Customer"
		}
		
	}
}); 
});




function invoice_submit()
{
	$("#save_print").val(1);
	$("#invoice_add").submit();	
}
$("#invoice_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#invoice_add").valid()) {
		return false;
	}

	
	else if(parseInt($('.dataexist').length)<=0)
	{
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}	
	
	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/opening_material_stock_add/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("BILL ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1'){
					window.location=root_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else{
					window.location=root_domain+'materialissue_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");		
				
				Unloading();
				if ($("#save_print").val() == '1')
				{	
					window.location=root_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+'materialissue_list';
				}		
			}
			$('#invoice_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_invoice(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/opening_material_stock_add/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("Material Issue DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function add_field()
	{
		var stock_type=$("#stock_type").val();
		var product_id=$("#product_id").val();
		var product_qty=$("#product_qty_hide").val();
		var product_conv_qty=$("#product_conv_qty_hide").val();
		var product_rate=$("#product_rate").val();
		var godown_id=$("#godown_id").val();
		var vender_id=$("#vender_id").val();
		var branch_id=$("#branch_id").val();
		var base_unit_id=$("#unitid").val();
		var conv_unit_id=$("#conv_unitid").val();
		var total_amount=$("#total_amount").val();
		
		if(!stock_type){		
			toastr.warning("Select Stock Type", "ERROR");
			$("#stock_type").val('focus');
			return false;
		}
		if(!product_id){		
			toastr.warning("Select Product Name", "ERROR");
			$("#product_id").select2('focus');
			return false;
		}	
		if(parseFloat(product_qty)<=0){		
			toastr.warning("Enter Opening Stock Qty", "ERROR");
			$("#product_qty").val('focus');
			return false;
		}
		if(parseFloat(product_conv_qty)<="0"){		
			toastr.warning("Enter Opening Stock Qty", "ERROR");
			$("#product_conv_qty").val('focus');
			return false;
		}
		if(parseFloat(product_rate)<="0"){		
			toastr.warning("Enter Opening Stock Rate", "ERROR");
			$("#product_rate").val('focus');
			return false;
		}
		
		if(stock_type==="1"){
			if(!$("#godown_id").val()){		
				toastr.warning("Select Opening Stock Godown", "ERROR");
				$("#godown_id").val('focus');
				return false;
			}
		}else{
			if(!vender_id){		
				toastr.warning("Select Vender", "ERROR");
				$("#vender_id").val('focus');
				return false;
			}
		}
		
		if(!branch_id){		
			toastr.warning("Select Branch Name", "ERROR");
			$("#branch_id").select2('focus');
			return false;
		}	
	
		Loading();	
		$.ajax({
			type: "POST",
			url: root_domain+'app/opening_material_stock_add/',
			data: { mode : "fieldadd",eid:$("#eid").val(),edit_id:$("#edit_id").val(),stock_type:stock_type,product_id:product_id,product_qty:product_qty,product_conv_qty:product_conv_qty,product_rate:product_rate,godown_id:godown_id,vender_id:vender_id,branch_id:branch_id,base_unit_id:base_unit_id,conv_unit_id:conv_unit_id,total_amount:total_amount },
			success: function(response)
			{
				console.log(response);
				$("#product_id").select2("val","");
				$("#product_id").select2('focus');
				$("#vender_id").select2("val","");
				$("#product_qty").val("");
				$("#product_conv_qty").val("");
				$("#product_rate").val("");
				$("#qc_godown").val("");
				$("#base_unit_id").val("");
				$("#conv_unit_id").val("");
				$("#total_amount").val("");
				
				$("#edit_id").val('')
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
	}



function field_remove(id)
{
	$("#fieldtr"+id).html('');
	
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{

	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
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
		"sAjaxSource": root_domain+'app/opening_material_stock_add/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "type_id", "value": type },{ "name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}
	function load_invoiceno(id)
	{
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "load_invoiceno", typeid : id},
			success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#invoice_no').val(no.invoiceno);
				$('#challan_no').val(no.invoiceno);
				
			}
		});
	}

	function show_data()
	{
		var eid = $('#eid').val();
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+'app/opening_material_stock_add/',
			data: { mode : "load_tempoutward", eid:eid },
			success: function(data){
				$('#sale_productdata').html(data);				
				Unloading();
			}		
			
		});
		
	}

	function edit_data(id)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/opening_material_stock_add/',
			data: { mode : "preedit",  id : id },
			success: function(response)
			{

				
				var data = jQuery.parseJSON(response);
				
				$("#product_id").select2("val",data.product_id);
				$("#work_order_id").select2("val",data.work_order_id);
				
				
				$("#stock_type").val(data.stock_type);
				$("#product_qty").val(data.product_qty);
				$("#product_qty_hide").val(data.product_qty);
				$("#unitid").val(data.base_unit_id);
				
				$("#product_conv_qty").val(data.product_conv_qty);
				$("#product_conv_qty_hide").val(data.product_conv_qty);
				$("#conv_unitid").val(data.conv_unit_id);
				
				$("#product_rate").val(data.product_rate);
				$("#total_amount").val(data.total_amount);
				$("#godown_id").val(data.godown_id);
				$("#vender_id").val(data.vender_id);
				
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				
				load_product_unit(data.product_id);
				
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
				url: root_domain+'app/opening_material_stock_add/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,invoice_id:$("#eid").val() },
				success: function(response)
				{
					
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						show_data();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
		
	}
	
	function load_product_unit(product_id){
		if(product_id)//tax calculation on total 
		{
			$.ajax({
					type: "POST",
					async: false,
					url: root_domain+'app/opening_material_stock_add/',
					data: { mode : "load_product_unit", product_id : product_id},
					success: function(response)
					{
						var obj=jQuery.parseJSON(response);
						//alert(obj.qye);
						$('#unitid').val(obj.product_conv_unit);
						$('#conv_unitid').val(obj.product_base_unit);
						
						$('#unit_show').html(obj.convert_unit_name);
						$('#convert_unit_show').html(obj.base_unit_name);
						$("#convert_unit_block").show();
						if(obj.unit_status==="1"){
							$("#convert_unit_block").show();
						}else{
							$("#convert_unit_block").hide();
						}
						$("#product_conv_qty").val("0");
						$("#product_conv_qty_hide").val("0");
						$("#product_qty").val("0");
						$("#product_qty_hide").val("0");
					}
			});
		}
	}
	
	function product_convert_qty(type){
		if(type==2){
			var conv_qty_hide=$("#product_qty").val();
				var s=parseFloat(conv_qty_hide);
				results = s.toFixed(3);
				
			var	num=$("#product_qty_hide").val();
				var d=parseFloat(num);
				resultb = d.toFixed(3);
			if(resultb===results){
				get_amount();
				return false;
			}
			var product_conv_qty_hide=$("#product_conv_qty_hide").val();
		}else{
			var base_qty_hide=$("#product_conv_qty").val();
				var d=parseFloat(base_qty_hide);
				resultb = d.toFixed(3);
			
			var base_qty_hidess=$("#product_conv_qty_hide").val();
				var s=parseFloat(base_qty_hidess);
				results = s.toFixed(3);
		
			if(resultb===results){
				get_amount();
				return false;
			}
			var conv_qty_hide=$("#product_qty").val();
		}
		
		var base_qty=$("#product_conv_qty").val();
		var conv_qty=$("#product_qty").val();
		var product_id=$("#product_id").val();
		
		if(product_id){
			$.ajax({
				type: "POST",
				url: root_domain+'app/opening_material_stock_add/',
				data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
				success: function(response)
				{
					
					var arr = jQuery.parseJSON(response);			
					if(type===1){
							$("#product_conv_qty_hide").val(base_qty);
					}else if(type===2){
						$("#product_qty_hide").val(conv_qty);
					}
					
					if(type===1){
						$("#product_qty").val(arr.show_qty);
						$("#product_qty_hide").val(arr.hide_qty);
					
					}else if(type===2){
						$("#product_conv_qty").val(arr.show_qty);
						$("#product_conv_qty_hide").val(arr.hide_qty);
						
					}else{
						$("#product_conv_qty").val(arr.show_qty);
						$("#product_conv_qty_hide").val(arr.hide_qty);
						$("#product_qty").val(arr.show_qty);
						$("#product_qty_hide").val(arr.hide_qty);
					}
					get_amount();
				}
				});
		}else{
			toastr.warning("Select Product First", "WARNING");
			$("#product_conv_qty").val("0");
			$("#product_conv_qty_hide").val("0");
			$("#product_qty").val("0");
			$("#product_qty_hide").val("0");
		}
		
	}
	
	function get_amount(){
		var product_qty=parseFloat($("#product_qty").val());
		var product_rate=parseFloat($("#product_rate").val());
		
		if(product_qty!="" && product_qty!="0" && product_rate!="" && product_rate!="0"){
			var total_amount=parseFloat(product_qty)*parseFloat(product_rate);
				$("#total_amount").val(total_amount);
		}
	}

