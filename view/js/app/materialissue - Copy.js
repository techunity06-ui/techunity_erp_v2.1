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
		url: root_domain+'app/materialissue/',
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
			url: root_domain+'app/materialissue/',
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

function load_productdetail(val,i) {
	
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
		//Load Stock Function 
		load_stock_qty(val,0);
	}



	function load_stock_qty(product_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/materialissue/',
			data: { mode : "load_stock_qty", product_id:product_id },
			success: function(data){
			//console.log(data);
			$("#product_qty").attr("placeholder",data);
			$("#product_qty").attr("max",parseFloat(data));
			Unloading();
		}		
	});
	}
	function add_field()
	{
		if(!$("#work_order_id").val()){		
			toastr.warning("Select Work Order", "ERROR");
			$("#work_order_id").select2('focus');
			return false;
		}
		if(!$("#product_id").val()){		
			toastr.warning("Select Product Name", "ERROR");
			$("#product_id").select2('focus');
			return false;
		}
	// else if(!$("#product_qty").val() || parseFloat($("#product_qty").val())=='0'){		
	// 	toastr.warning("Enter Qty", "ERROR");
	// 	return false;
	// }
	if(parseFloat($("#product_qty").val()) > parseFloat($("#product_qty").attr('max'))) {		
		toastr.warning("PRODUCT OUT OF STOCK", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	// else if(!$("#product_rate").val() || parseFloat($("#product_rate").val())=='0'){		
	// 	toastr.warning("Enter Rate", "ERROR");
	// 	return false;
	// }
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/materialissue/',
		data: { mode : "fieldadd",eid:$("#eid").val(),edit_id:$("#edit_id").val(),product_id:$("#product_id").val(),work_order_id:$("#work_order_id").val(),product_qty:$("#product_qty").val(), },
		success: function(response)
		{
			console.log(response);
			$("#product_id").select2("val","")
			$("#product_id").select2('focus')
			$("#product_qty").val("");
			$("#product_qty").attr("max","").attr("placeholder","");
			$("#work_order_id").select2("val","")
			$("#work_order_id").select2('focus')
			$("#work_order_id").val("");
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
		"sAjaxSource": root_domain+'app/materialissue/',
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
			url: root_domain+'app/materialissue/',
			data: { mode : "load_tempoutward", eid:eid },
			success: function(data){
				$('#sale_productdata').html(data);				
				Unloading();
			}		
			
		});
		
	}

	function edit_data(id,table,whereid)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/materialissue/',
			data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
			success: function(response)
			{

				
				var data = jQuery.parseJSON(response);
				
				$("#product_id").select2("val",data.product_id);
				$("#work_order_id").select2("val",data.work_order_id);
				
				
				$("#product_qty").val(data.issue_qty);
				
				$("#edit_id").val(id);
				$('#addrow').val('Update');
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
				url: root_domain+'app/materialissue/',
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

