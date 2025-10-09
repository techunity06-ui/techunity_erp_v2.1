//var datatable;
$(document).ready(function() {

	load_datatable();
	show_data();
	
	var mode = $('#mode').val();
	
	if(mode=='Edit')
	{
		var cust_id = $('#cust_id').val();
		var invoice_id = $('#eid').val();
	
		//customer effects
		currency_change();
		get_invoice_by_cust(cust_id,'invoice_number')
		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_all_bill_sundry(invoice_id);
		get_tax_details_table();
		get_invoice_total_tax();
		get_gtotal();
	}
	get_tax_details_table();
	get_invoice_total_tax();	
	grn_to_debit_note();
// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#invoice_add").validate({
	rules: {
		
		voucher_no: {
			required: true			
		},
		cust_id: {
			required: true
		},
		sales_ledger_id: {
			required: true
		},
		branch_id: {
			required: true
		},
		sale_return_date:{
			required:true
		}
	},
	messages: {
		voucher_no:{
			required: "ENter Voucher No"			
		},
		sale_return_date: {
			required: "Enter date"
		},
		cust_id: {
			required: "Select Customer"
		},
		sales_ledger_id: {
			required: "Select Sales Account"
		},
		branch_id: {
			required: "Select Branch"
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
	else if(parseInt($('#total').val())<=0)
	{
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}
	if ($('#currency_enable').is(":checked"))
	{
		if($("#currency_id").val()==""){
			toastr.warning("Select Currency", "ERROR")
			$("#currency_id").focus();
			return false;
		}
		if($("#currency_rate").val()==""){
			toastr.warning("Enter Currency Rate", "ERROR")
			$("#currency_rate").focus();
			return false;
		}
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	update_total();
	$.ajax({
		cache:false,
		url: root_domain+finance_root_domain+'app/debitnote/',
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
				toastr.success("CREDIT NOTE ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1'){
					window.location=root_domain+finance_root_domain+'invoicereceipt/'+arr.eid;
				}
				else{
					window.location=root_domain+finance_root_domain+'debitnote';
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
					window.location=root_domain+finance_root_domain+'invoicereceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+finance_root_domain+'debitnote';
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
				url: root_domain+finance_root_domain+'app/debitnote/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					//console.log(response);
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
	//document.getElementById('bill_value').value="";
	var ratcalfiled=$("#pro_cal_type").val();
	var qty=parseFloat($('#'+ratcalfiled).val());
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
		var product_qty_hid = Number($('#product_qty_hide').val());
		
		if($("#product_qty").val()>product_qty_hid)
		{
			toastr.warning("QTY MUST BE LESS THAN PURCHASE QTY", "WARNING");
			$('#addrow').prop('disabled',true);			
		}
		else
		{
			
			var id=parseInt($('#fieldcnt').val())+1;
			var ratcalfiled=$("#pro_cal_type").val();
			//alert(id);
			if($("#"+ratcalfiled).val()!="" && $("#product_rate").val()!="")
			{
				var q=$("#"+ratcalfiled).val();
				var rate=$("#product_rate").val();
				var a=q*rate;
				
				//alert(a);
				
				$("#product_amount").val(parseFloat(a));
				var bill_value=$('#product_amount').val();
			}
			else
			{
				$("#product_amount").val(0);
			}
			
			$('#addrow').prop('disabled',false);
		}
		get_gtotal();
}

function get_gtotal(id)
{	
	var input_amount=(document.getElementsByName('amount[]'));
	var default_amount=document.getElementsByName('default_amount[]');
	
	var cnt=input_amount.length;
	var cnt1 = default_amount.length;
	//alert(cnt1);
	var total=0;
	var c_total=0;
	var gst =0;
	
	
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
	
	var gst_arr = document.getElementsByClassName('gst');
	
	for (var k = 0; k < gst_arr.length; k++) {
		
		var k1=gst_arr[k].value;
		total=parseFloat(total)+parseFloat(k1);
		//alert(total);
	}
	//alert(total);
	for(var j=0;j<cnt1;j++)
	{	
		var t1=default_amount[j].value;
		total=parseFloat(total)+parseFloat(t1);
		
	}

	/*var cgst = $('#CGST').val();
	var sgst = $('#SGST').val();
	var igst = $('#IGST').val();
	var tcs = $('#TCS').val();

	if((cgst!= 0) && (sgst!= 0) && (typeof cgst  != "undefined") && (typeof sgst  != "undefined")){
		gst = Number(cgst)+Number(sgst);
	}else if(igst!='' && (typeof igst  != "undefined")){
		gst = Number(igst);
	}else{
		gst = 0;
	}

	if((tcs != '') && (typeof tcs  != "undefined")){
		tcs = Number(tcs);
	}else{
		tcs = 0;
	} */
	
	

	//g_total= Number(gst) + Number(total.toFixed(2)) + Number(tcs);
	g_total = Number(total.toFixed(2))
	$("#g_total").val(g_total.toFixed(2));
    $("#paid_amount").val(g_total.toFixed(2));
	update_total();
	
}


function update_total()
{
	var eid = $('#eid').val();
	var g_total = $('#g_total').val();
	var basic_total = $('#total').val();
	var branch_id = $('#branch_id').val();
	var invoice_date = $('#debitnote_date').val();
	
	//var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	var gst1=[];
	var gst2=[];
	var addonsundry = {};
	
	var values = $("input.gst");
	$.each($(".gst"), function(key, value) {
	
		var new_key = this.name.match(/\d+/);
		gst1.push(new_key[0]);
		gst2.push($(this).val());	
		
	});
	
	//console.log(gst1);
	$.ajax({
		
		type:'POST',
		data:{ mode:'update_total' , invoice_id:eid, g_total:g_total , basic_total:basic_total ,
		 branch_id:branch_id , bill_sundry_tax:gst1,bill_sundry_tax1:gst2,invoice_date:invoice_date },
		url:root_domain+finance_root_domain+'app/debitnote/',
		success:function(result)
		{
			//console.log(result);
			//alert(result);
		}
		
	})
}

function load_productdetail(val) 
{
	
	var invoice_number = $('#invoice_number').val();
	
	//alert(transaction_id);
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/debitnote/',
		data: { mode : "load_sale_productdata",product_id :val , invoice_number:invoice_number  },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			Unloading();
			var obj =jQuery.parseJSON(response)
			$('#product_des').val(obj.product_desc);	
			$('#product_rate').val(obj.sale_rate);				
			$('#product_qty_hid').val(obj.sale_qty);				
			$('#product_tax').val(obj.sale_gst);				
			$('#unit_id').select2("val",obj.product_base_unit);				
			$('#hsncode').text(obj.product_hsn);
			$('.hsncode').show();
			$('#purcha_qty').text(obj.sale_qty);
			$('.purcha_qty').show();
			load_product_unit(val,obj.product_base_unit);
			//get_amount();
		}
	});
		
}



function load_stock_qty(product_id,old_qty){
	Loading(true);
	var unit_id=$("#unit_id").val();
	//alert(old_qty);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/debitnote/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id },
		success: function(data){
			//console.log(data);
			$("#product_qty").attr("placeholder",data);
			 $("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(data));
			Unloading();
		}		
	});
}

function get_statecode(cust_id){
	//alert(cust_id);
	if(cust_id){
        $.ajax({
            type: "POST",
            async: false,
            url: root_domain + finance_root_domain +'app/invoice/',
            data: { mode : "get_gst_statecode", cust_id : cust_id},
            success: function(response)
            {
            	var res = response.split(",");
                if(res){
                	$("#statecode").show();
                    $(".statecode").text(res[0]);
                    $("#cust_stateid").val(res[1]);
                }else{
                	$("#statecode").hide();
                }
            }
        });
    }
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
	else if(!$("#product_rate").val() || parseFloat($("#product_rate").val())=='0'){		
		toastr.warning("Enter Rate", "ERROR");
		return false;
	}
	
	if($('#currency_enable').is(':checked'))
	{
		var currency_enable = 1;
	}
	else
	{
		var currency_enable = 0;
	}
	
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { 
				mode : "fieldadd",
				edit_id:$("#edit_id").val(),
				grn_id : $("#grn_id").val(),
				product_id:$("#product_id").val(),
				description : $("#product_des").val(),
				product_rate:$("#product_rate").val(),
				product_qty:$("#product_qty_hide").val(),
				product_conv_qty:$("#product_conv_qty_hide").val(),
				unit_id:$("#unit_id").val(),
				conv_unitid:$("#conv_unitid").val(),
				rate_unitid:$("#rate_unit_id").val(),
				product_amount:$("#product_amount").val(),
				eid:$("#eid").val(),
				sale_gst:$('#product_tax').val(),
				invoice_number:$('#invoice_number').val(),
				cust_stateid:$('#cust_stateid').val(),
				product_hsn_code:$("#hsncode").text(),
				currency_enable:currency_enable,
				currency_rate:$('#currency_rate').val(),
				currency_id:$('#currency_id').val() },

			success: function(response)
			{
				console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				
                $("#product_id").select2("val",
                	"");
				$("#product_id").select2('focus');
				$('#invoice_number').select2("val","");
				$("#product_des").val("");
				$("#taxable_value").val("");

				$("#product_qty").val("");
				$("#product_conv_qty").val("");
				
				$("#product_qty_hide").val("");
				$("#product_conv_qty_hide").val("");
				$("#conv_unitid").val("");
				$("#unitid").val("");

				$("#unit_show").html("");
				$("#convert_unit_show").html("");
				
				//$("#sqr_ft").val("")
				$("#product_rate").val('');
				$("#product_amount").val('');
				$("#edit_id").val('');
				$("#product_qty_hid").val('');
                $('#addproduct').show();
				$('#addrow').val('Add');
				$('#hsncode').html('');
				$('#hsncode').html('');
				$('#purcha_qty').html('');
				Unloading();
				show_data();
				update_total();
			//	get_tax_details_table();
			//	get_invoice_total_tax();
			}
		});
}
function load_paymentmode(val) {
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/debitnote/',
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
    var branch_id = $('#branch_id').val();
        
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
			"sAjaxSource": root_domain+finance_root_domain+'app/debitnote/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "type_id", "value": type },{ "name": "date", "value": date },{ "name": "branch_id", "value": branch_id } );
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
	//alert(id);
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/debitnote/',
	data: { mode : "load_creditnoteno", typeid : id},
	success: function(data){
		//console.log(data);
		var no = jQuery.parseJSON(data);
		$('#voucher_no').val(no.invoiceno);				
	}
	});
}

function show_data()
{
	var eid = $('#eid').val();
	//alert(eid);
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/debitnote/',
	data: { mode : "load_tempoutward", eid:eid },
	success: function(data){
				
			//alert(data);
			//console.log(data);
			$('#sale_productdata').html(data);				
			
			get_tax_details_table();
			get_invoice_total_tax();
			get_amount();
			Unloading();
		}		
		
	});
	
}

function get_grossbalance(cust_id){
	if(cust_id){
        $.ajax({
            type: "POST",
            async: false,
            url: root_domain + finance_root_domain +'app/debitnote/',
            data: { mode : "get_grossbalance", cust_id : cust_id},
            success: function(response)
            {
                if(response){
                	$("#gross").show();
                    $(".gross").text(response);
                }else{
                	$("#gross").hide();
                }
            }
        });
    }
}



function get_tax_details_table()
{
	var eid = $('#eid').val();
	var cust_id = $('#cust_id').val();

	var addontax1=[];
	$(".addontax").each(function() {
		//alert(this.value);
		addontax1.push(this.value);
	});

	$.ajax({
        type: "POST",
        async: false,
        url: root_domain + finance_root_domain +'app/debitnote/',
        data: { mode : "get_tax_details_table",eid:eid,cust_id:cust_id,addontax1:addontax1 },
        success: function(response)
        {
        	//console.log(response);
        	var arr = JSON.parse(response);
            if(arr){
            	$(".tax_details").html(arr.resp);
                //$(".gross").text(response);
            }
        }
    });
}

function get_invoice_total_tax(){
	
	var eid = $('#eid').val();
	var addontax1=0;
	$(".addontax").each(function() {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});
	$.ajax({
        type: "POST",
        url: root_domain + finance_root_domain +'app/debitnote/',
        data: { mode : "get_invoice_total_tax",cust_id:$('#cust_id').val(),gross:$('.gross').text(),inv_total:$('#total').val(),eid:eid,addontax1:addontax1 },
        success: function(response)
        {
        	//alert(response);
        	var arr = JSON.parse(response);
            if(arr){
            	$(".invoiceTotalTax").html(arr.resp);
            }
			get_amount();
        }
    });
}

function edit_data(id,table,whereid)
{
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/debitnote/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{

					//console.log(response);
					
					var data = jQuery.parseJSON(response);
					
					//alert(data.sale_return_product);
					$("#invoice_number").select2("val",data.debitnote_invoice_no);
					get_product_from_invoice(data.debitnote_invoice_no,data.product_id);
					//$("#product_id").select2("val",data.sale_return_product);
					$("#product_des").val(data.description);
					$("#product_qty").val(data.product_qty_show);
					$("#product_qty_hide").val(data.product_qty);
					$("#product_conv_qty_hide").val(data.product_conv_qty);
					$("#product_conv_qty").val(data.product_conv_qty_show);
					$("#unit_id").val(data.unit_id)
					$("#conv_unitid").val(data.conv_unit_id)


					$("#product_qty_hid").val(data.remained_qty);
					//$("#product_tax").val(data.product_sale_gst);
					$("#product_rate").val(data.product_rate);
					$("#product_amount").val(data.product_amount);
					
					$('#hsncode').text(data.product_hsn_code);
					$('.hsncode').show();
					$('#purcha_qty').text(data.remained_qty);
					$('.purcha_qty').show();
					
					$("#edit_id").val(id);
					$('#addrow').val('Update');

					load_product_unit(data.product_id,data.rate_unit);
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
				url: root_domain+finance_root_domain+'app/debitnote/',
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
					update_total();
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
	url: root_domain+finance_root_domain+'app/debitnote/',
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
function load_consignee(cust_id,per)
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
		url: root_domain+finance_root_domain+'app/debitnote/',
		data: { mode : "load_consignee", cust_id : cust_id },
		success: function(data){
				//console.log(data);
				 $('#consignee_id').html(data);
				 $('#consignee_id').select2('val','');
				 Unloading();
				 if(per!="1"){
					load_sales_order(cust_id); 
				 }
				 
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

function load_sales_order(cust_id,so_id){
	var branch_id=$("#branch_id").val();
	if(branch_id){
		if(cust_id){
		$('#sales_order_div').attr("style","display:block");
		//var so_id=$("#sales_order_id").val();
		//alert(so_id);
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { mode : "load_sales_order", cust_id : cust_id,branch_id:branch_id },
			success: function(data){
					//console.log(data);
					 $('#sales_order_id').html(data);
					 $('#sales_order_id').select2('val',so_id);
					 Unloading();
				}
				
		});
		}else {
			$('#sales_order_div').attr("style","display:none");
		}
	}else{
		toastr.warning("Please Select Branch", "WARNING");
	}
}
function load_sales_order_data(sales_order_id)
{
	if(sales_order_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { mode : "load_sales_order_data", sales_order_id : sales_order_id },
			success: function(response){
				/* console.log(response);
				if(response!=""){
					var resp = 	JSON.parse(response);
					$('#order_no').val(resp.sales_order_no);
					$('#order_date').val(resp.sales_order_date);
					$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
					$('#transport_id').select2('val',resp.transport_id);
				} */
				Unloading();
				show_data();
			}
		});
	}
	/*else{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
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
			url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
		url: root_domain+finance_root_domain+'app/debitnote/',
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
			url: root_domain+finance_root_domain+'app/debitnote/',
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
// Dimple Panchal : start
function get_tax_on_total(formula_id){
    if(formula_id)//tax calculation on total 
        {
                var total= $("#total").val();
                var formulaid=$("#formula_id").val();
                $.ajax({
                        type: "POST",
                        async: false,
                        url: root_domain+finance_root_domain+'app/debitnote/',
                        data: { mode : "get_tax_on_total", total : total ,formulaid:formulaid},
                        success: function(response)
                        {
                                var obj=jQuery.parseJSON(response);
                                $('#tcs_total').val(obj.tax_value);
                        }
                });
        }
}
function paymentmode(id)
{
	//alert(id);
	$.ajax({
		type: "POST",
		url: root_domain+'app/payment_new/',
		data : {mode : "bank_type1",id:id},
		success: function(data){
			//alert(data);
			var data = JSON.parse(data);
			//alert(data.type);
			if(data.type == "cash"){
				$('#cheque_data').hide();
			}else{
				$('#save_cheque').show();
				$('#cheque_dtl').val('');
				$('#cheque_data').show();
				get_chequeno(id,'cheque_dtl');
			}
		}
	});
}
function show_tcs_row(cust_id){
    if(cust_id){
        $.ajax({
            type: "POST",
            async: false,
            url: root_domain+finance_root_domain+'app/debitnote/',
            data: { mode : "show_tcs_row", cust_id : cust_id},
            success: function(response)
            {
                    if(response > 0){
                        $(".tcs_tax").show();
                    } else {
                        $(".tcs_tax").hide();
                    }
            }
        });
    }
}
// Dimple Panchal : end

//dhaval upadhyay //

function get_invoice_by_cust(cust,return_div,eid='',grn_id='')
{
	//alert(return_div);
	//var grn_id = $("#grn_id").val();
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/debitnote/',
		data:{ mode:'get_invoice_by_cust', cust:cust ,eid:eid,grn_id:grn_id },
		success:function(response)
		{
			//alert(response);
			//console.log(response);
			$('#'+return_div).html(response);
			$("#"+return_div).select2({
				width: '100%',
				//minimumInputLength: 3
			});
		}
	});
	
}

function get_product_from_invoice(invoice_no,product_id='')
{
	//alert(invoice_no);
	
	//Invoice Currency status check  start - Dhaval
	
	if($('#sale_enable_multi_currency').val()==1)
	{
		var currency_enable = $('#invoice_number').find(':selected').data('currency_enable');
		
		//alert(currency_enable);
		
		var currency_conv = $('#currency_conv').val();
		
		if(currency_enable==0 || currency_conv=='')
		{
			$('#inv_number_err').html("Currency Conversion is not available for this invoice");
		}
		else
		{
			$('#inv_number_err').html("");
		}
		
	}
	//Invoice Currency status check  end - Dhaval
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/debitnote/',
		data : {mode:'get_product_from_invoice' , invoice_no:invoice_no , product_id:product_id },
		success:function(response)
		{
			//alert(response);
			$('#product_id').html(response);
			$("#product_id").select2({
				width: '100%',
				//minimumInputLength: 3
			});
		}
		
	})
	
}

var rowIdx = 0;
function addBillSundry(){
	
	
	var taxableamount=0;
	var totalsundryexist = 0;
	var basic_amount = $("#total").val();
	var netamount = $("#g_total").val();
	var cust_id = $("#cust_id").val();
	$(".gst").each(function() {
		var gstVal = $('.gst').val();
		taxableamount = Number(taxableamount) + Number(gstVal); 
	});

	$(".billsundryclass").each(function() {
		var billsundryclass = $(this).val();
		totalsundryexist = Number(totalsundryexist) + Number(billsundryclass); 
	});

	var eid = $('#eid').val();

	var bill_sundry_value = $("#bill_sundry").val();

	var bill_sundry =  $("#bill_sundry option:selected").text();
	var bill_sundry_amount = $('#bill_sundry_amount').val();
	
	var currency_enable = $('#sale_enable_multi_currency').val();
	var currency_id = $('#currency_conv').val();
	var currency_rate = $('#currency_conv_rate').val();

	if(bill_sundry_value == 0)
	{
		toastr.warning("Please Select Bill Sundry", "ERROR")
		return false;
	}else if(bill_sundry_amount == ''){
		toastr.warning("Please insert Bill Sundry Amount", "ERROR")
		return false;
	}else{
		$.ajax({
	        type: "POST",
	        async: false,
	        url: root_domain + finance_root_domain +'app/debitnote/',
	         data: { mode : "get_bill_sundry_details",sundry_ledger_id:bill_sundry_value,totalsundryexist:totalsundryexist,taxableamount:taxableamount,
	        basic_amount:basic_amount,netamount:netamount,default_amount:bill_sundry_amount,invoice_id:eid,currency_enable:currency_enable,currency_id:currency_id,currency_rate:currency_rate,invoice_date:$('#debitnote_date').val(),cust_id:cust_id},
	        success: function(response)
	        {
				
	        	var arr1 = JSON.parse(response);
	            var arr = arr1.split(",");
				if(arr[3])
				{
					get_all_bill_sundry(eid);
					//get_gtotal();
				}
				else
				{
					if(arr[0]){

						//$("#g_total").val(arr[0]);
						if(arr[4] != 0){
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]}</label>
								<div class="col-md-4">
									<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
									<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
									<input class="addontax" name="bill_sundry_addon_tax[${bill_sundry_value}]" type="hidden" value="${arr[4]}-${arr[5]}-${arr[1]}" >
								</div>
								<div class="col-md-3">
									<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
										type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
								</div>
								
							</div>`);

							get_invoice_total_tax();
							get_tax_details_table();
						}else{
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]}</label>
								<div class="col-md-4">
									<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
									<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
								</div>
								<div class="col-md-3">
									<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
										type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
								</div>
							</div>`);	
						}
						$('#bill_sundry').val('0');
						$('#bill_sundry_amount').val('');
						get_gtotal();
					}
				}

	        }
	    });
		

	}
	
}



function removeSundry(bill_sundry_value,bill_sundry_amount,id,ledger_id=''){

	Loading(true);
	
	var edit_id = $('#eid').val();
	var cust_ledger_id = $("#cust_id").val();
		
	if(edit_id=='' || edit_id=='0')
	{
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);
					
		$('.'+id).remove();	

		get_gtotal();
	}
	else
	{
		
		$.ajax({
			
			type:'post',
			url:root_domain+finance_root_domain+'app/debitnote/',
			data:{ mode : 'remove_sundry',edit_id:edit_id,ledger_id:ledger_id,cust_ledger_id:cust_ledger_id },
			success:function(result)
			{
				get_all_bill_sundry(edit_id);
				get_gtotal();
			}
		})
	}

	get_invoice_total_tax();
	get_tax_details_table();
	
	Unloading();
}

function get_sundry_label(sundry_id)
{
	//alert(sundry_id);
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/debitnote/',
		data: { mode : "get_bill_sundry_label",sundry_id:sundry_id},
		success:function(data)
		{
			//alert(data);
			if(data==1)
			{
				$('#bill_sundry_amount').attr("placeholder", "Amount");
			}
			else
			{
				$('#bill_sundry_amount').attr("placeholder", "%");
			}
		}
	})
	
}

function get_symbol(){

	//$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	var textt = " (<i class='"+symbl+"'></i>)"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});
}


function get_ledger_details(ledger_id)
{
	//alert(ledger_id);
	
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();
	var enable_multi_currency = $('#enable_multi_currency').val();
	Loading();
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/debitnote/',
		data : { mode:"get_ledger_details",ledger_id:ledger_id },
		success:function(result)
		{
			var obj = JSON.parse(result);
			
			//console.log(obj);
			//alert(enable_multi_currency);
			//alert(obj.enable_multi_currency_opening);
			
			//Cost Center popup
			if(obj.enable_cost_center==1 && company_cost_center==1)
			{
				$('#div_cost_center').show();
			}
			else
			{
				$('#div_cost_center').hide();
			}
			
			//TCS Popup
			if(obj.enable_tcs==1 && company_tcs==1)
			{
				$('#tcs_div').show();
			}
			else
			{
				$('#tcs_div').hide();
			}
			
			//Eway Bill Popup
			if(company_eway==1)
			{
				$('#eway_div').show();
			}
			else
			{
				$('#eway_div').hide();
			}
			
			//Currency Conversion
			
			if(enable_multi_currency==1 && obj.enable_multi_currency_opening==1)
			{
				$('#currency_enable_div').show();
			}
			else
			{
				$('#currency_enable_div').hide();
			}
			
			//Salesman Popup
			
			if(company_salesman==1)
			{
				$('#salesman_div').show();
			}
			else
			{
				$('#salesman_div').hide();
			}
			
			Unloading();
		}
	})
	
}

function get_tcs_reversal(check)
{
	Loading(true);
	
	//alert(check);
	if(check=='yes')
	{
		$("#ModalTcsReversal").modal("show");
		get_invoice_by_cust($('#cust_id').val(),'ref_id');
		load_tcs_reversal_data();
		get_tcs_reverse_code();
		$('#tcsr_link').show();
	}
	else
	{
		$('#tcsr_link').hide();
	}
	Unloading();
}


function get_tcs_reverse_code()
{
	
	var sale_ledger_id = $('#cust_id').val();
	
	$.ajax({
		
		type:'post',
		data:'sale_ledger_id='+sale_ledger_id+'&mode=get_tcs_reverse_code',
		url:root_domain+finance_root_domain+'app/debitnote/',
		success:function(result)
		{
			//alert(result);
			var obj = JSON.parse(result);
			
			$('#tcs_section').val(obj.sale_return_section_code);
			$('#tcs_sub_cat_code').val(obj.sale_return_cat_code);
		}
	})
	
}

function add_tcs_reversal_trn()
{
	var sale_ledger_id = $('#cust_id').val();
	var edit_id_tcs_reversal = $('#edit_id_tcs_reversal').val();
	var ref_id = $('#ref_id').val();
	var amt_reversed = $('#amt_reversed').val();
	var tcs_collected_on = $('#tcs_collected_on').val();
	var tcs_amt = $('#tcs_amt').val();
	var sur_amt = $('#sur_amt').val();
	var total_tax = $('#total_tax').val();
	var eid = $('#eid').val();
	
	
	var dataString = 'ref_id='+ref_id+'&amt_reversed='+amt_reversed+'&tcs_collected_on='+tcs_collected_on+'&tcs_amt='+tcs_amt+'&sur_amt='+sur_amt+'&total_tax='+total_tax+'&mode=tcs_reversal_trn'+'&sale_ledger_id='+sale_ledger_id+'&edit_id_tcs_reversal='+edit_id_tcs_reversal+'&invoice_id='+eid;
	//alert(dataString);
	
	$.ajax({
		
		type:'POST',
		data:dataString,
		url:root_domain+finance_root_domain+'app/debitnote/',
		success:function(result)
		{
			if(result==1)
			{
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				load_tcs_reversal_data();
			}
			else
			{
				toastr.warning("SOMETHING WENT WRONG", "ERROR");
			}
		}
	})
	
}

function load_tcs_reversal_data()
{
	    
	datatable = $("#tcs-trn-table").dataTable({
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
			"sAjaxSource": root_domain+finance_root_domain+'app/debitnote/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch_tcs_reversal" },{ "name": "sale_ledger_id", "value": $('#cust_id').val() },{ "name": "sale_return_id", "value": $('#eid').val() } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function edit_tcs_reversal(id)
{
	//alert(id);
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/debitnote/',
				data: { mode : "preedit_tcs_return",  id : id},
				success: function(response)
				{

					//console.log(response);
					
					var data = jQuery.parseJSON(response);
					
					//alert(data.sale_return_product);
					get_invoice_by_cust($('#cust_id').val(),'ref_id',data.sale_ref_no);
					$("#amt_reversed").val(data.sale_amt_reversed);
					$("#tcs_collected_on").val(data.sale_amt_tcs_collected);
					$("#tcs_amt").val(data.sale_tcs_amt);
					$("#sur_amt").val(data.sale_sur_amt)
					$("#total_tax").val(data.sale_total_tax)
					$("#edit_id_tcs_reversal").val(id);
					$('#add_reversal_btn').val('Update');
					Unloading();
				}
			});
}


function delete_tcs_reversal(id) 
{
	//alert(id);
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { mode : "delete_sale_return_trn", id : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS"); 	
					load_tcs_reversal_data();
					
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();				
			}
		});	
	}
	
}

function add_tcs_reversal()
{
	var tcs_section = $('#tcs_section').val();
	var tcs_sub_cat_code = $('#tcs_sub_cat_code').val();
	var sale_ledger_id = $('#cust_id').val();
	
	$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { mode : "tcs_reversal_add", tcs_section : tcs_section , tcs_sub_cat_code:tcs_sub_cat_code, sale_ledger_id:sale_ledger_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS"); 						
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();				
			}
		});
	
}

function get_conv_div(currency)
{
	if(currency=='1')
	{
		$('#currency_conv_div').show();
		$('#currency_conv_rate_div').show();

	}
	else
	{
		$('#currency_conv_div').hide();
		$('#currency_conv_rate_div').hide();
		
		$('#currency_conv').val('');
		$('#currency_conv_rate').val('');
	}
}

function get_conv_rate(currency)
{
			
	var currency_rate = $('#currency_conv').find(':selected').data('currency-rate');
		
	$('#currency_conv_rate').val(currency_rate);
}


function get_all_bill_sundry(invoice_id)
{
	//alert(invoice_id);
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/debitnote/',
		data:{ mode:'get_all_bill_sundry',invoice_id:invoice_id },
		success:function(response)
		{
			//console.log(response);
			$('.sundryadded').html(response);
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
			/*var arr1 = JSON.parse(response);
			var arr = arr1.split(",");

			if(arr[0]){

				$("#g_total").val(arr[0]);
				
				$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
					<label class="col-md-5 control-label">${bill_sundry}${arr[2]}</label>
					<div class="col-md-4">
						<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
						<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
							type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
					</div>
				</div>`);	
				$('#bill_sundry').val('0');
				$('#bill_sundry_amount').val('');
			} */
		}
	})
}

function delete_debit_note(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/debitnote/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					//console.log(response);
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

function load_ven_grn(vender_id,id){
	//alert(vender_id);
	
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "load_ven_grn", vender_id : vender_id,id:id },
			success: function(response){
				//console.log(response);
				var resp = JSON.parse(response);
				$('#grn_id').html(resp.pro_html);
				$('#grn_id').select2('val',id);
				Unloading();
			}
			
		});
	}
}
function load_grn_data(grn_id){
	if(grn_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "load_grn_data", grn_id:grn_id },
			success: function(response){
				//console.log(response);
				var resp = 	JSON.parse(response);
				$('#product_id').html(resp.pro_html);
				$('#product_id').select2('val','');
				Unloading();
			}
		});
	}
}


function grn_to_debit_note(){
	var grn_id = $("#grn_id").val();
	if(grn_id){
		$('.grn').show();
	}else{
		$('.grn').hide();
	}
}


// Maulik Start
function load_product_unit(product_id,edit_unit){
	if(product_id){

	}else{
		var product_id=$("#product_id").val();
	}
	if(edit_unit){

	}else{
		var edit_unit=$("#rate_unit_id").val();
	}
	//alert(product_id);
	if(product_id)//tax calculation on total 
	{

		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ finance_root_domain +'app/debitnote/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				console.log(response);
				var obj=jQuery.parseJSON(response);
				//alert(obj.qye);
				$("#rate_unit_id").html(obj.unit_option);
				//alert(edit_unit);
				if(edit_unit!="0"){
					//alert(edit_unit);
					$("#rate_unit_id").val(edit_unit);
					if(obj.product_base_unit===edit_unit){
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_unit_block").show();
							$("#convert_unit_block").show();
							$("#product_conv_qty").attr("readonly","readonly");
							$("#product_qty").removeAttr("readonly","readonly");
						}else{
							$("#convert_unit_block").hide();
						}
						$("#pro_cal_type").val("product_qty_hide");
					}else{
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_unit_block").show();
							$("#product_qty").attr("readonly","readonly");
							$("#product_conv_qty").removeAttr("readonly","readonly");
							$("#convert_unit_block").show();
						}else{
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}
				}else{
					$("#base_unit_block").show();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#product_conv_qty").removeAttr("readonly","readonly");
					$("#convert_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
				}

				$('#unit_id').val(obj.product_base_unit);
				$('#conv_unitid').val(obj.product_conv_unit);
				
				$('#unit_show').html(obj.base_unit_name);
				$('#convert_unit_show').html(obj.convert_unit_name);
				get_amount();get_discount('per');
				/*$("#convert_unit_block").show();
				if(obj.unit_status==="1"){
					$("#convert_unit_block").show();
				}else{
					$("#convert_unit_block").hide();
				}*/
			}
		});
	}
}
//Maulik End


//Maulik End
function product_convert_qty(type){
	// console.log(type)
	if(type==2){
		var conv_qty_hide=$("#product_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var	num=$("#product_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);

		if(resultb===results){
			get_amount();
			return false;
		}
		var product_conv_qty_hide=$("#product_conv_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#product_conv_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		if(resultb===results){
			get_amount();
			return false;
		}
		var conv_qty_hide=$("#product_qty").val();
	}
	// console.log(base_qty_hide);
	// console.log(conv_qty_hide);
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/debitnote/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#product_conv_qty_hide").val(conv_qty);
				}else if(type===2){
					$("#product_qty_hide").val(base_qty);
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
