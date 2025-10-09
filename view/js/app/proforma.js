//var datatable;
$(document).ready(function() {
	load_datatable();
	get_amount();
	load_company_data();
	get_quotation_and_salesorder();
	load_consignee();	
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
function invoice_submit(){
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
	/*else if($('#same_as').is(':checked')==false && $("#consignee_id").val()=="") 
	{
		toastr.warning("SELECT CONSIGNEE OR SAME AS CONSIGNEE", "ERROR")
		return false;
	}*/
	else if(parseInt($('#total').val())<=0)
	{
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/proforma/',
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
				toastr.success("BILL ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1')
				{
					window.location=root_domain+'proformareceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+'proforma_list';
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
					window.location=root_domain+'proformareceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+'proforma_list';
				}
			//	toastr.success("SLIDER UPDATED SUCCESSFULLY", "SUCCESS");		
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
				url: root_domain+'app/proforma/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
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
				url: root_domain+'app/proforma/',
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
		var id=parseInt($('#fieldcnt').val())+1;
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
			if($("#formulaid").val()!="")//tax calculation
			{
				var total=a;
				var formulaid=$("#formulaid").val();
				$.ajax({
					type: "POST",
					url: root_domain+'app/purchase/',
					data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
					success: function(response)
					{
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
        
        //Start : Dimple Panchal
	// if tax on total
	var formula =$("#formula_id").val();
	if(formula > 0)
	{
                get_tax_on_total(formula);
                tcs = $("#tcs_total").val();
                total = parseFloat(total) + parseFloat(tcs);
	}
	else {
                $("#tcs_total").val(0.00);
        }
        //End : Dimple Panchal
        
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
	g_total=total.toFixed(2);
	$("#g_total").val(g_total);
	
	
}
function load_productdetail(val,i) {
	var sales_order_id = $("#sales_order_id").val();
	$("#rate_history").show();
	if(sales_order_id){
		$('#addproduct').hide();
		$.ajax({
					type: "POST",
					url: root_domain+'app/proforma/',
					data: { mode : "loadsales_productdata",product_id :val, sales_order_id:sales_order_id },
					success: function(response)
					{
						//console.log(response);
						
						var obj =jQuery.parseJSON(response)
						$('#product_des').val(obj.description);				
						$('#product_hsn_code').val(obj.product_hsn_code);
						var qty=(obj.product_qty)-(obj.qty);
						$('#product_qty').val(qty);
						
						$('#product_rate').val(obj.product_rate);	
						$('#unit_id').select2("val",obj.unit_id);
						$('#product_discount').val(obj.product_discount);
						$('#discount_per').val(obj.discount_per);
						//$('#product_amount').val(obj.product_amount);	
						$('#formulaid').val(obj.formulaid);
						get_amount();	
					}
				});
		
	}
	else{
		
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
					url: root_domain+'app/proforma/',
					data: { mode : "load_productdata",eid :val, cust_id:cust_id },
					success: function(response)
					{
						//console.log(response);
						var obj =jQuery.parseJSON(response)
						
						CKEDITOR.instances['product_des'].setData(obj.product_desc);
                        CKEDITOR.instances['product_spec'].setData(obj.product_spec);			
						$('#product_hsn_code').val(obj.product_hsn);				
						$('#product_rate').val(obj.product_sale_rate);	
						$('#unit_id').select2("val",obj.product_base_unit);
						$('#formulaid').val(obj.fom_id);
						last_rate(obj.product_mst_rate); // Load last customer rate function	
					}
				});
		
	}

}

function add_field()
{
	
	if($("#product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	
	if($("#product_rate").val()==="0.00")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if($("#product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	/*if($("#product_qty").val() > $("#product_qty").attr('max'))
	{		
		toastr.warning("PRODUCT OUT OF STOCK", "ERROR")
		return false;
	}*/
	for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
	}
	
	var start=[];var end=[];
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+'app/proforma/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),
					product_id:$("#product_id").val(),
					product_disc:$("#product_des").val(),
					product_spec:$("#product_spec").val(),
					product_hsn_code:$("#product_hsn_code").val(),
					product_qty:$("#product_qty").val(),
					product_rate:$("#product_rate").val(),
					
					unit_id:$("#unit_id").val(),
					formulaid:$("#formulaid").val(),
					product_discount:$("#product_discount").val(),
					discount_per:$("#discount_per").val(),
					product_amount:$("#product_amount").val(),
					invoice_id:$("#eid").val(),
					start_serial1:$("#start_serial1").val(),
					end_serial1:$("#end_serial1").val(),
					start_serial2:$("#start_serial2").val(),
					end_serial2:$("#end_serial2").val(),
					start_serial3:$("#start_serial3").val(),
					end_serial3:$("#end_serial3").val(),start:start,end:end },
			success: function(response)
			{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#product_id").select2("val","")
				$("#product_id").select2('focus')
				$("#product_des").val("")
				$("#product_hsn_code").val("")
				$("#formulaid").val("")
				$("#product_discount").val("")
				$("#discount_per").val("")
				$("#taxable_value").val("")
				$("#product_qty").val("")
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
				if($("#eid").val()=="")
				{
					show_data()
				}
				else
				{
					location.reload();
				}
			}
		});
}
function load_paymentmode(val) {
	$.ajax({
	type: "POST",
	url: root_domain+'app/proforma/',
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
			"sAjaxSource": root_domain+'app/proforma/',
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
	url: root_domain+'app/proforma/',
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
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();
	var sales_order_id = $('#sales_order_id_data').val();
	var quotation_id = $('#quotation_id').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/proforma/',
	data: { mode : "load_tempoutward", performa_invoice_type : performa_invoice_type, sales_order_id : sales_order_id, quotation_id: quotation_id },
	success: function(data){
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
				url: root_domain+'app/proforma/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{
					console.log(response)
					var data = jQuery.parseJSON(response);
					//$('#product_id').html(data.producthtml);
					//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
					$("#product_id").select2("val",data.product_id);
					$("#product_des").val(data.product_disc);
					$("#product_hsn_code").val(data.product_hsn_code);
					/*
					$("#start_serial1").val(data.start_serial1);
					$("#end_serial1").val(data.end_serial1);
					$("#start_serial2").val(data.start_serial2);
					$("#end_serial2").val(data.end_serial2);
					$("#start_serial3").val(data.start_serial3);
					$("#end_serial3").val(data.end_serial3);
					*/
					$("#product_qty").val(data.product_qty);
					//$("#sqr_ft").val(data.sqr_ft);
					$("#product_rate").val(data.product_rate);
					$("#product_spec").val(data.product_disc)
					$("#unit_id").select2("val",data.unit_id);
					$("#formulaid").val(data.formulaid);
					$("#product_amount").val(data.total)
					$("#product_discount").val(data.product_discount)
					$("#discount_per").val(data.discount_per)
					$("#taxable_value").val(data.product_amount)
					$("#edit_id").val(id);
					$('#addrow').val('Update');
					CKEDITOR.instances['product_des'].setData(data.product_desc);
                    CKEDITOR.instances['product_spec'].setData(data.product_spec);
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
				url: root_domain+'app/proforma/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,invoice_id:$("#eid").val() },
				success: function(response)
				{
				console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					if($("#eid").val()=="")
					{	
						//$('#product_id').html(data.producthtml);
						show_data()
					}
					else
					{
						location.reload();
					}
						Unloading();
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
	url: root_domain+'app/proforma/',
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
function load_consignee()
{
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();
	var cust_id = $('#cust_id').val();
	var consignee_id = $('#edit_consignee_id').val();

	if(performa_invoice_type!='' && cust_id!=''){
		$.ajax({
		type: "POST",
		url: root_domain+'app/proforma/',
		data: { mode : "load_consignee", cust_id : cust_id, performa_invoice_type : performa_invoice_type, consignee_id : consignee_id },
		success: function(data){
				//console.log(data);
				 $('#consignee_id').html(data);
				 $('#consignee_id').select2('val',consignee_id);
				 //load_sales_order(cust_id);
			}
			
	});
	}
	
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
		url: root_domain+'app/proforma/',
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
{ if(sales_order_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/proforma/',
		data: { mode : "load_sales_order_data", sales_order_id : sales_order_id },
		success: function(response){
				//console.log(response);
				if(response!="")
				{
					var resp = 	JSON.parse(response);
					$('#order_no').val(resp.sales_order_no);
					$('#order_date').val(resp.sales_order_date);
					$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
				}
				Unloading();
			}
			
	});
}else{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/proforma/',
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
}
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
			url: root_domain+'app/proforma/',
			data: { mode : "load_rate_hist", cust_id : cust_id, product_id : product_id },
			success: function(response){
					//console.log(response);
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

	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+'app/proforma/',
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
$("#use_cr_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#use_cr_add").valid()){
		return false;
	}
	else if(parseInt($('#total_cr').val())<=0){
		toastr.warning("Enter Credit Amount", "ERROR");
		return false;
	}
	else if(parseInt($('#total_cr').val()) > parseInt($('#invoice_balance').val())){
		toastr.warning("Credit Amount Should be less than Invoice Balance", "ERROR");
		$('#total_cr').focus();
		return false;
	}
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/proforma/',
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
				toastr.success("CREDITS APPLIED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'proforma_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			$('#use_cr_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_total_cr(){
	var input_amount=(document.getElementsByName('used_credit_amt[]'));
	var cnt=input_amount.length;
	var total=0;
	if(total==""){
		total=0;
	}
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=parseFloat(total)+parseFloat(t);
	}
	$("#total_cr").val(parseFloat(total));
}
function get_series_no(){
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/proforma/',
	data: { mode : "get_series_no"},
	success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp);	
				load_invoiceno(resp)	
			}		
	});	
}
// Start : Dimple Panchal
function get_tax_on_total(formula_id){
    if(formula_id > 0)//tax calculation on total 
        {
                var total = $("#total").val();
                var formulaid = $("#formula_id").val();
                $.ajax({
                        type: "POST",
                        async : false,
                        url: root_domain+'app/purchase/',
                        data: { mode : "get_tax_on_total", total : total ,formulaid:formulaid},
                        success: function(response)
                        {
                                var obj=jQuery.parseJSON(response);
                                $('#tcs_total').val(obj.tax_value);
                        }
                });
        }
}
// End : Dimple Panchal


function get_quotation_and_salesorder(){
	var value = $('input[name="performa_invoice_type"]:checked').val();
	var cust_id = $('#cust_id').val();
	var edit_sales_order_id = $('#edit_sales_order_id').val();
	var edit_quotation_id = $('#edit_quotation_id').val();

	//if(cust_id!=''){

		if(value=='3'){
			$('.quotation_div').addClass('hide');
			$('.sales_order_div').addClass('hide');
		}else if(value=='1'){
			$('.quotation_div').removeClass('hide');
			$('.sales_order_div').addClass('hide');
		}
		else if(value=='2'){
			$('.quotation_div').addClass('hide');
			$('.sales_order_div').removeClass('hide');
		}

		if(value!='3'){
			$.ajax({
				type: "POST",
				url: root_domain+'app/proforma/',
				data: { mode : "performa_invoice_type", cust_id : cust_id, performa_type : value, edit_sales_order_id : edit_sales_order_id, edit_quotation_id: edit_quotation_id},
				success: function(resp){
					var obj=jQuery.parseJSON(resp);
					if(obj.performa_type=='1'){
						$('#quotation_id').empty().append(obj.data);
					}
					if(obj.performa_type=='2'){
						$('#sales_order_id_data').empty().append(obj.data);

					}
				}		
			});
		}

	/*}else{
		$("#performa_invoice_direct").prop("checked",true);
		toastr.warning("Please select company.", "ERROR");
		return false;
	}*/

}

function load_company_data(){
	var edit_customer_id = $('#edit_customer_id').val();
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();
	var eid = $('#eid').val();

	$.ajax({
			type: "POST",
			url: root_domain+'app/proforma/',
			async:false,
			data: { mode : "load_company_data", edit_customer_id : edit_customer_id, performa_invoice_type : performa_invoice_type},
			success: function(resp){
				var obj=jQuery.parseJSON(resp);
				$('#cust_id').empty().append(obj.data);
				$("#cust_id").select2({
		         	width: '100%'
		        });
				
		        if(eid==''){
		        	show_data();
		        }
		        
			}		
		});
}

function insert_quotation_salesorder_item(id){
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();

	$.ajax({
		type: "POST",
		url: root_domain+'app/proforma/',
		data: { mode : "insert_quotation_salesorder_item", id : id, performa_invoice_type : performa_invoice_type, invoice_id:$("#eid").val()},
		success: function(resp){
			if($("#eid").val()=="")
			{
				show_data();
			}
			else
			{
				location.reload();
			}
		}		
	});
}