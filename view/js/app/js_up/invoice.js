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
	else if($('#same_as').is(':checked')==false && $("#consignee_id").val()=="") 
	{
		toastr.warning("SELECT CONSIGNEE OR SAME AS CONSIGNEE", "ERROR")
		return false;
	}
	else if(parseInt($('#total').val())<=0)
	{
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}	
	
	//Get All Product Stock count
	var trn_pro_stk=(document.getElementsByName('trn_pro_stk[]'));
	var cnt_pro_stk=(document.getElementsByName('cnt_pro_stk[]'));
	var cnt=trn_pro_stk.length;
	for(var i=0;i<cnt;i++)
	{
		var trn_stk=parseFloat(trn_pro_stk[i].value);
		var pro_stk=parseFloat(cnt_pro_stk[i].value);
		if(trn_stk>pro_stk){
			toastr.warning("Product Out of Stock !!!", "ERROR");
			return false;
		}
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/invoice/',
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
				toastr.success("BILL ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1'){
					window.location=root_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else{
					window.location=root_domain+'invoice_list';
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
					window.location=root_domain+'invoice_list';
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
				url: root_domain+'app/invoice/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response);
					if(response.trim() == "1") {
						toastr.success("INVOICE DELETE SUCCESSFULLY", "SUCCESS");
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
function add_discount(type)
{
	var total=$("#total").val();
	var discount_amt=0; var discount_per=0;
	if(total!="")
	{
		if(type=="amt")
		{
			discount_amt=$('#discount_amt').val();
			discount_per=((discount_amt*100)/total).toFixed(2);
			$("#discount_per").val(discount_per);
		}
		else if(type=="per")	
		{
			discount_per=$('#discount_per').val();
			discount_amt=((total*discount_per)/100).toFixed(2);
			$("#discount_amt").val(discount_amt);
		}
		get_gtotal($('#formulaid').val());
	}
}

function demo()
{
	var paymentterms = $('#payment_terms').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "reminder", paymentterms : paymentterms},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$('#payment_reminder').val(obj.payment_days);
		}
	});
}
function add_freight()
{
	get_gtotal($('#formulaid').val());
}
function cal_discount()
{
	get_gtotal($('#formulaid').val());
}

function get_discount(type)
{
	document.getElementById('bill_value').value="";
	var qty=parseFloat($('#product_qty').val());
	var rate=parseFloat($('#product_rate').val());
	var disc=0;
	if(qty!="" && rate !="")
	{	
		if(type=="amt")
		{
			disc=100*parseFloat($('#product_discount').val())/(qty*rate);
          var  disc1=disc.toFixed(2);			
			$('#discount_per').val(disc1);
		}
		else if(type=="per")
		{
			//alert('hi');
			disc=((qty*rate)*parseFloat($('#discount_per').val()))/100;	
		var	disc1=disc.toFixed(2);
			$('#product_discount').val(disc1);
			
		}
	}
	else
	{
		$('#product_discount').val('');
		$('#discount_per').val('');
	}
	
	get_amount();
}
function get_amount()
{	
		//alert('hiii');
		var id=parseInt($('#fieldcnt').val())+1;
		//alert(id);
		if($("#product_qty").val()!="" && $("#product_rate").val()!="")
		{
			var q=$("#product_qty").val();
			var rate=$("#product_rate").val();
			var a=q*rate;
			if($("#product_discount").val()!="" )//discount calculation
			{	
				var discount=parseFloat($("#product_discount").val());
				a=a-discount; 
			}
			$("#product_amount").val(parseFloat(a));
			$("#taxable_value").val(parseFloat(a));
			var bill_value=$('#product_amount').val();
			if($("#formulaid").val()!="")//tax calculation
			{
				var total=a;
				var formulaid=$("#formulaid").val();
				//alert(formulaid);
				$.ajax({
					type: "POST",
					url: root_domain+'app/invoice/',
					data: { mode : "get_product_amount",  product_amount : bill_value ,formulaid:formulaid},
					success: function(response)
					{
						//alert(response);
						var obj=jQuery.parseJSON(response);
						$('#product_amount').val(obj.total);
					}
				});
			}
		}
		else
		{
			$("#product_amount").val(0);
		}
	get_gtotal();
}
function get_gtotal(id)
{	
	var input_amount=(document.getElementsByName('amount[]'));
	
	var cnt=input_amount.length;
	//alert(cnt);
	var total=0;var c_total=0;
	if(total=="")
	{
		total=0;
	}
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=parseFloat(total)+parseFloat(t);
		
	}
	$("#total").val(parseFloat(total));
	
	var p=$("#packing").val();
	if(p>0)
	{
		total=parseFloat(total)+parseFloat(p);
	}
	var f=$("#freight").val();
	if(f>0)
	{
		total=parseFloat(total)+parseFloat(f);
	}
	var c=$("#cutting").val();
	if(c>0)
	{
		total=parseFloat(total)+parseFloat(c);
	}
	/*
	var d=$("#discount_amt").val();
	if(d>0)
	{
		c_total=parseFloat(c_total)-parseFloat(d);
	}
	var r=$("#round_off").val();
	if(r!=0)
	{
		c_total=parseFloat(c_total)+parseFloat(r);
	}*/
	
	g_total=total.toFixed(2);
	$("#g_total").val(g_total);
	/*$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "formulavalue",eid :id,total : g_total, c_total:c_total},
			success: function(response)
			{
				//console.log(response);
				$('#showformulatextbox').html(response);
				g_total=Math.round($('#rate').val());
				//g_total=(g_total);
				$("#g_total").val(g_total);
			}
	});*/
	
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
		var cust_id = $('#cust_id').val();
			if(cust_id==''){
				toastr.warning("Please Select Customer First","ERROR");
				$('#cust_id').select2('focus');
				return false;
			}
			$.ajax({
				type: "POST",
				url: root_domain+'app/invoice/',
				data: { mode : "load_productdata",eid :val, cust_id:cust_id },
				success: function(response)
				{
					//console.log(response);
					
					var obj =jQuery.parseJSON(response)
					//$('#product_rate').val(obj.product_mst_rate);	
					$('#product_hsn_code').val(obj.product_hsn_code);
					$('#product_rate').val(obj.product_rate);
					$('#unit_id').select2("val",obj.unitid);;
					// Load last customer rate function	
				}
			});
	//Load Stock Function 
	load_stock_qty(val,0);
}



function load_stock_qty(product_id,old_qty){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_stock_qty", product_id:product_id },
		success: function(data){
			//console.log(data);
			$("#product_qty").attr("placeholder",data);
			$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(data));
			Unloading();
		}		
	});
}
function add_field()
{
	
	if(!$("#product_id").val()){		
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val() || parseFloat($("#product_qty").val())=='0'){		
		toastr.warning("Enter Qty", "ERROR");
		return false;
	}
	else if(parseFloat($("#product_qty").val()) > parseFloat($("#product_qty").attr('max'))) {		
		toastr.warning("PRODUCT OUT OF STOCK", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#product_rate").val() || parseFloat($("#product_rate").val())=='0'){		
		toastr.warning("Enter Rate", "ERROR");
		return false;
	}
	
	
	
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_id:$("#product_id").val(),model_id:$("#model_id").val(),ser_status:$("#ser_status").val(),product_hsn_code:$("#product_hsn_code").val(),product_qty:$("#product_qty").val(),product_rate:$("#product_rate").val(),unit_id:$("#unit_id").val(),formulaid:$("#formulaid").val(),product_discount:$("#product_discount").val(),discount_per:$("#discount_per").val(),taxable_value:$('#taxable_value').val(),bill_value:$("#bill_value").val(),bill_black_value:$("#bill_black_value").val(),product_amount:$("#product_amount").val(),invoice_id:$("#eid").val() },
			success: function(response)
			{
				console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				
				$("#product_id").select2("val","")
				$("#model_id").select2("val","")
				$("#product_id").select2('focus')
				$("#product_des").val("")
				$("#product_hsn_code").val("")
				$("#formulaid").val("")
				$("#product_discount").val("")
				$("#discount_per").val("")
				$("#taxable_value").val("")
				$("#product_qty").val("");
				$("#product_qty").attr("max","").attr("placeholder","");
				//$("#sqr_ft").val("")
				$("#unit_id").select2('val',"")
				$("#product_rate").val('');
				$("#product_disc").val('');
				$("#product_amount").val('')
				$("#edit_id").val('')
				$("#start_serial1").val('')
				$("#end_serial1").val('')
				$("#start_serial2").val('')
				$("#end_serial2").val('')
				$("#start_serial3").val('')
				$("#end_serial3").val('')
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}
function load_paymentmode(val) {
	$.ajax({
	type: "POST",
	url: root_domain+'app/invoice/',
	data: { mode : "paymentmode", paymentmodeid : val},
	success: function(response){
				//console.log(response);
				$('#product_list').append(response);
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
			"sAjaxSource": root_domain+'app/invoice/',
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
	url: root_domain+'app/invoice/',
	data: { mode : "load_tempoutward", eid:eid },
	success: function(data){
				
				//alert(data);
				//console.log(data);
				 $('#sale_productdata').html(data);				
				  get_amount()
				 Unloading();
		}		
		
	});
	
}

function edit_data(id,table,whereid)
{
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/invoice/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{

					console.log(response);
					//alert(response);
					var data = jQuery.parseJSON(response);
					//alert(data.model_id);
					//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
					$("#product_id").select2("val",data.product_id);
					var load_product=load_prowise_model(data.product_id,data.model_id);
					//$("#model_id").html(load_produc);
					$("#model_id").select2("val",data.model_id);
					$("#product_hsn_code").val(data.product_hsn_code);
					//Load Product STOCK
					load_stock_qty(data.product_id,data.product_qty);
					
					$("#product_qty").val(data.product_qty);
					$("#product_rate").val(data.product_rate);
					$("#product_disc").val(data.product_disc)
					$("#unit_id").select2("val",data.unit_id);
					$("#formulaid").val(data.formulaid);
					$("#product_amount").val(data.total)
					$("#product_discount").val(data.product_discount)
					$("#discount_per").val(data.discount_per)
					$("#taxable_value").val(data.taxable_value)
					$("#bill_value").val(data.bill_value)
					$("#product_amount").val(data.product_amount)
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
				url: root_domain+'app/invoice/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,invoice_id:$("#eid").val() },
				success: function(response)
				{
				console.log(response)
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
function last_rate(mst_rate)
{
	Loading()
	var cust_id=$("#cust_id").val();
	var product_id=$("#product_id").val();
	$.ajax({
	type: "POST",
	url: root_domain+'app/invoice/',
	data: { mode : "last_rate",product_id:product_id,cust_id:cust_id},
	success: function(resp){
				//console.log(resp);
				if(resp){
					$('#product_rate').val(resp);	
				}else{
					$('#product_rate').val(mst_rate);
				}
				 			
				 Unloading();
		}		
		
	});
	
}
function load_consignee(cust_id)
{
	//alert(cust_id);
	var product_id = $('#product_id').val();
	if(product_id){
		load_productdetail(product_id);
	}
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_consignee", cust_id : cust_id },
		success: function(data){
				//console.log(data);
				 $('#consignee_id').html(data);
				 $('#consignee_id').select2('val','');
				 Unloading();
				 load_sales_order(cust_id);
			}
			
	});
	
}
function open_consignee_click()
{
	var cust_id=$('#cust_id').val();
	if(cust_id=="")
	{
		toastr.warning("Please Select Customer", "WARNING");
	}
	else
	{
		consignee_modal_open(cust_id);
	}
}

function load_sales_order(cust_id){
	if(cust_id){
	$('#sales_order_div').attr("style","display:block");
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_sales_order", cust_id : cust_id },
		success: function(data){
				//console.log(data);
				 $('#sales_order_id').html(data);
				 $('#sales_order_id').select2('val','');
				 Unloading();
			}
			
	});
	}else {
		$('#sales_order_div').attr("style","display:none");
	}
}
function load_sales_order_data(sales_order_id)
{
	if(sales_order_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "load_sales_order_data", sales_order_id : sales_order_id },
			success: function(response){
				console.log(response);
				if(response!=""){
					var resp = 	JSON.parse(response);
					$('#order_no').val(resp.sales_order_no);
					$('#order_date').val(resp.sales_order_date);
					//$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
				}
				Unloading();
			}
		});
	}
	/*else{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "load_sales_pro"},
			success: function(response){
				console.log(response);
				if(response!="")
				{
					var resp = 	JSON.parse(response);
					$('#order_no').val("");
					$('#order_date').val("");
					$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
				}
				Unloading();
			}
		});
	}*/
}
function load_rate_hist(){
	
	var cust_id = $("#cust_id").val();
	var product_id = $("#product_id").val();
	if(cust_id==''){
		toastr.warning("Please Select Customer", "WARNING");
		return false;
	}
	else if(product_id==''){
		toastr.warning("Please Select Product", "WARNING");
		return false;
	}
	else{
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "load_rate_hist", cust_id : cust_id, product_id : product_id },
			success: function(response){
				console.log(response);
				var arr = JSON.parse(response);
				$('#hist_tbl tbody').html(arr.resp);
				$('#cust_hist').html(arr.cust_name);
				$('#pro_hist').html(arr.product_name);
				$('#bs-example-modal-rate_history').modal();
				Unloading();
			}
		});
		
	}	

}
function open_serial_number()
{
	var product_id=$('#product_id').val();
	if(product_id=="")
	{
		toastr.warning("Please Select Product", "WARNING");
		$("#product_id").select2('focus');
		$('#product_id').select2('open');
	}
	else
	{
		$('#bs-serial-modal-lg').modal();
	}
}
function load_qty(product_id,old_qty)
{

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_qty",product_id:product_id},
		success: function(resp){
			//console.log(resp);
			if(resp!=""){
				$('#product_qty').attr("placeholder",resp);
				//$('#product_qty').attr("max",resp);
				$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(resp));
			}
			Unloading();
		}		
	});
}

function load_product_typeiwse(type_id){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_product_typeiwse", type_id : type_id},
		success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			Unloading();
		}
	});
}

function getBillValue(bill_value)
{
	//alert(bill_value);
	var taxable_value=$('#taxable_value').val();
	//alert(taxable_value);
	var bill_total=taxable_value-bill_value;
	//alert(bill_total);
	
	if(bill_total<0 || bill_value==0)
	{
		//alert("value not ok");
		$('#err_id').html('Enter Value Less Than Taxable Value');
		$("#addrow").attr("disabled", true);
	}
	else
	{
		$("#addrow").attr("disabled", false);
		$('#err_id').html('');
		
		$('#bill_black_value').val(bill_total);
		$('#product_amount').val(bill_value);
	}
	
}


function load_cust_prowise_model(product_id){
	var cust_id= $('#cust_id').val();

	if(!cust_id){
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
		return false; 
	}
	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode:"load_cust_prowise_model_invoice", product_id:product_id, cust_id:cust_id },
			success: function(response)
			{
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				//$('#model_id').show(); 
				$('#model_id').html(resp.model_resp_html);
				$('#model_id').select2("val",""); 
				Unloading();
			}
		}); 
	}
}

function load_prowise_model(product_id,model_id){
	
	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode:"load_prowise_model", product_id:product_id },
			success: function(response)
			{
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				//$('#model_id').show(); 
				$('#model_id').html(resp.model_resp_html);
				$('#model_id').select2("val",model_id); 
				Unloading();
			}
		}); 
	}
}

function load_model_service_status(){
	var cust_id= $('#cust_id').val();
	var product_id= $('#product_id').val();
	var model_id= $('#model_id').val();
	var complaint_date= $('#invoice_date').val();
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/complaint/',
		data: { mode:"load_model_service_status", model_id:model_id, product_id:product_id, cust_id:cust_id , complaint_date:complaint_date },
		success: function(response){
			console.log(response); 
			var resp = JSON.parse(response);
			$('#ser_status').val(resp.ser_sts);
			
			Unloading();
		}
	});
} 
function copy_quot_trn_data(quotation_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode:"copy_quot_trn_data", quotation_id:quotation_id },
		success: function(response){
			//console.log(response); 
			Unloading();
			show_data();
		}
	});
}
function copy_comp_spare_trn_data(complaint_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode:"copy_comp_spare_trn_data", complaint_id:complaint_id },
		success: function(response){
			//console.log(response); 
			Unloading();
			show_data();
		}
	});
}
function open_inv_srl_no(trancation_id,product_name){
	$('#inv_srl_modal').modal('show');
	$('#ref_trancation_id').val(trancation_id);
	$('#head_inv_srl_modal_pro_name').html(product_name);
	show_pro_srl_no();
}
function add_pro_srl_no()
{
	if(!$("#pro_srl_no").val()){
		toastr.warning("Enter Serail No.", "ERROR");
		$("#pro_srl_no").focus();
		return false;
	}
	
	var form_data = { 
		mode : "add_pro_srl_no",
		pro_srl_no:$("#pro_srl_no").val(), 
		trancation_id:$("#ref_trancation_id").val(),
		invoice_id:$("#eid").val()
	};
	
	$('#add_pro_srl_no_btn').prop("disabled",true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			$("#pro_srl_no").val("");
			$('#add_pro_srl_no_btn').html('Add');
			$('#add_pro_srl_no_btn').prop("disabled",false);
			Unloading();
			show_pro_srl_no();
		}
	});
}
function show_pro_srl_no() {
	var trancation_id = $('#ref_trancation_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "show_pro_srl_no", trancation_id:trancation_id },
		success: function(resp){
			//console.log(resp);
			$('#inv-srlno-modal-datatable').html(resp);
			Unloading();
			count_pro_srl_no();
		}		 
	}); 
}
function count_pro_srl_no(){
	var trancation_id = $('#ref_trancation_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "count_pro_srl_no", trancation_id:trancation_id },
		success: function(resp){
			//console.log(resp);
			if(resp.trim()=='1'){
				$('#add_pro_srl_div').show();
			}
			else{
				$('#add_pro_srl_div').hide();
			}
		}		 
	}); 
}
function delete_inv_srl_data(inv_srl_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode:"delete_inv_srl_data", inv_srl_trn_id:inv_srl_trn_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_pro_srl_no();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

function check_due_payment(cid)
{
	if(cid){
		$.ajax({
			type: "POST",
			url: root_domain+'app/complaint/',
			data: { mode : "check_complian_due",  cust_id : cid },
			success: function(resnse)
			{
				//alert(resnse);
				if(resnse>0)
				{
					$('#cust_status_due_show').show();
					$('#save').prop('disabled', true);
					$('#saveprint').prop('disabled', true);
					$('#addrow').prop('disabled', true);
					$('#check_due_div').show();
				}
				else
				{
					$('#cust_status_due_show').hide();
					$('#save').prop('disabled', false);
					$('#saveprint').prop('disabled', false);
					$('#addrow').prop('disabled', false);
					$('#check_due_div').hide();
				}
			}
		});
	}
}

function enable_invoice()
{
	if($('#check_due').is(":checked"))
	{
		$('#cust_status_due_show').hide();
		$('#save').prop('disabled', false);
		$('#saveprint').prop('disabled', false);
		$('#addrow').prop('disabled', false);
	}
	else
	{
		$('#cust_status_due_show').show();
		$('#save').prop('disabled', true);
		$('#saveprint').prop('disabled', true);
		$('#addrow').prop('disabled', true);
	}
}