//var datatable;
$(document).ready(function() {
	/*$('#product_amount').hover(function(){
       var pro_amt = $('#product_amount').val();
		$('#product_amount').attr("title",pro_amt);
    });*/
	load_po_datatable();
	show_data();
// validate vendor add form on keyup and submit
 $("#purchaseorder_add").validate({
	rules: {
		stcok_adjustment_no: {
			required: true			
		},
		stcok_adjustment_date:{
			required : true	
		}
	},
	messages: {
		stcok_adjustment_no: {
			required: "Enter Stcok Adjustment No"
		},
		stcok_adjustment_date:{
			required : "Enter Stcok Adjustment Date"
		}
	}
}); 
});
$("#purchaseorder_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchaseorder_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/stcok_adjustment/',
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
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'stcok_adjustment_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'stcok_adjustment_list';
				
			}
			$('#purchaseorder_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_po(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/stcok_adjustment/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
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
	if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}
	else if($("#product_id").val()==="")
	{		
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus')
		return false;
	}
	else if($("#stock_qty").val()==="")
	{		
		toastr.warning("Enter Adjustment Stock", "ERROR")
		$("#stock_qty").focus();
		return false;
	}
	//alert($("#formula_tax_id").val());
	$.ajax({
			type: "POST",
			url: root_domain+'app/stcok_adjustment/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_type:$("#product_type").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),current_stock:$("#current_stock").val(),stock_qty:$("#stock_qty").val(),add_adjustment_qty:$("#add_adjustment_qty").val(),remove_adjustment_qty:$("#remove_adjustment_qty").val(),eid:$("#eid").val()},
			success: function(response)
			{
				console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#product_id").select2("val","")
				$("#product_id").select2('focus')
				$("#product_des").val("")
				$("#current_stock").val("")
				$("#stock_qty").val("")
				$("#add_adjustment_qty").val("")
				$("#remove_adjustment_qty").val("")
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
	var t=get_amount();
}
function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	//var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	
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
			"sAjaxSource": root_domain+'app/stcok_adjustment/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function edit_data(id)
{
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/stcok_adjustment/',
				data: { mode : "preedit",  id : id },
				success: function(response)
				{
					//console.log(response)
					var data = jQuery.parseJSON(response);
					$('#product_id').html(data.producthtml);
					//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
					$("#product_type").select2("val",data.product_type)
					$("#product_id").select2("val",data.product_id)
					$("#product_des").val(data.description)
					$("#stock_qty").val(data.stock_qty)
					$("#current_stock").val(data.current_stock)
					//$("#sqr_ft").val(data.sqr_ft)
					$("#product_rate").val(data.product_rate)
					$("#product_disc").val(data.product_disc)
					$("#unitid").select2("val",data.unit_id)
					$("#formulaid").val(data.formulaid)
					$("#product_amount").val(data.total)
					$("#product_discount").val(data.product_discount)
					$("#discount_per").val(data.discount_per)
					$("#taxable_value").val(data.product_amount)
					$("#sel_tax").val(data.sel_tax)
					$("#formula_tax_id").val(data.formula_tax_id)
					$("#product_amount_tax").val(data.product_amount_tax)
					$("#edit_id").val(id)
					$('#addrow').val('Update');
					
					Unloading();
				}
			});
}
function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/stcok_adjustment/',
				data: { mode : "delete_data",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					
						show_data()
					
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

function get_series_no(type_id){
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/stcok_adjustment/',
	data: { mode : "get_series_no", type_id:type_id},
	success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp);	
				load_pono(resp)	
			}		
	});	
}
function load_pono(id)
{
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/stcok_adjustment/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#stcok_adjustment_no').val(no.invoiceno);
				
	}
	});
}

function load_product(type_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stcok_adjustment/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			Unloading();
		}
	});
}
function current_stock1(product_id){
	//alert("hi");
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stcok_adjustment/',
		data: { mode : "current_stock", product_id : product_id},
		success: function(data){
			//console.log(data);
			$('#current_stock').val(data);				
			Unloading();
		}
	});
}

function show_data()
{
	Loading();
	var eid=$('#eid').val();
	//alert(eid);
	$.ajax({
	type: "POST",
	url: root_domain+'app/stcok_adjustment/',
	data: { mode : "load_tempoutward",eid:eid},
	success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				Unloading();
		}		
		
	});
	
}
function find_stock_aju(stock_qty){
	var current_stock=$("#current_stock").val();
	var add_aje_qty=0;
	var remove_aje_qty=0;
	if(parseFloat(current_stock)>parseFloat(stock_qty)){
		remove_aje_qty=parseFloat(current_stock)-parseFloat(stock_qty);
	}else{
		if(parseFloat(current_stock)<0){
			var aaa=Math.abs(current_stock);
			add_aje_qty=parseFloat(aaa)+parseFloat(stock_qty);
		}else{
		//alert(parseFloat(stock_qty));
		//alert(parseFloat(current_stock));
			add_aje_qty=parseFloat(stock_qty)-parseFloat(current_stock);
			
		}
	}
	if(isNaN(add_aje_qty)){add_aje_qty=0;}
	if(isNaN(remove_aje_qty)){remove_aje_qty=0;}
	$("#add_adjustment_qty").val(add_aje_qty);
	$("#remove_adjustment_qty").val(remove_aje_qty);
}