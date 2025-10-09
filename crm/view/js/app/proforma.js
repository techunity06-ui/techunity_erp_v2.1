//var datatable;
$(document).ready(function() {
	load_datatable();
	load_company_data();
	get_quotation_and_salesorder();
	load_consignee();
	var cust_id = $('#cust_id').val();
	get_statecode("<?=$rel['cust_id']?>");
	show_data();
	get_amount();
	tc_format_view();
	get_tax_details_table();
	get_invoice_total_tax();
	load_trans_add();
	var pro_type = $('#pro_type').val();
	var pro_search = $('#pro_search').val();
	//load_inquiry_type_product(pro_type,pro_search);
	product_load_pro_l();

	product_load();
	get_symbol();
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

	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/proforma/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			var print_path = $('#print_path').val();			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("BILL ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1')
				{
					window.location=root_domain + print_root_domain +print_path+'/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain + crm_domain +'proforma_list';
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
					window.location=root_domain + print_root_domain +print_path+'/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain + crm_domain +'proforma_list';
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
			url: root_domain + crm_domain +'app/proforma/',
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
		url: root_domain + crm_domain +'app/proforma/',
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
	var rate_unit = $("#rate_unit_id").val();
	var base_unit = $("#unitid").val();
	if(rate_unit != base_unit){
		var qty=parseFloat($('#product_conv_qty').val());
	}else{
		var qty=parseFloat($('#product_qty').val());
	}
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
	var ratcalfiled=$("#pro_cal_type").val();
	var id=parseInt($('#fieldcnt').val())+1;
	if($("#"+ratcalfiled).val()!="" && $("#product_rate").val()!="")
	{
		var q=$("#"+ratcalfiled).val();
		var rate=$("#product_rate").val();
		var a=q*rate;
			if($("#product_discount").val()!="" )//discount calculation
			{	
				var discount=parseFloat($("#product_discount").val());
				a=a-discount; 
			}
			$("#product_amount").val(parseFloat(a));
			$("#taxable_value").val(parseFloat(a));
			/*if($("#formulaid").val()!="")//tax calculation
			{
				var total=a;
				var formulaid=$("#formulaid").val();
				$.ajax({
					type: "POST",
					url: root_domain + purchase_domain +'app/purchase/',
					data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
					success: function(response)
					{
						var obj=jQuery.parseJSON(response);
						$('#product_amount').val(obj.total);
					}
				});
			}*/
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

	$("#total").val(parseFloat(total)).trigger("change");
	
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

	//g_total= Number(gst) + Number(total.toFixed(2)) + Number(tcs);
	g_total = Number(total.toFixed(2));
	$("#g_total").val(g_total.toFixed(2)).trigger("change");
	$("#paid_amount").val(g_total.toFixed(2));
	console.log($("#g_total").val());
	console.log($("#total").val());
	if($("#advance_payment").val() !=""){
		total = parseFloat(total) - parseFloat($("#advance_payment").val());
	}
	if($("#adv_amt").val() != ""){
		total = parseFloat(total) - parseFloat($("#adv_amt").val());
	}
	$("#pending_amount").html(Number(total.toFixed(2)) + "/-");
	$("#pen_amt").val(Number(total.toFixed(2)));
	update_total(g_total);
}
function load_productdetail(val,i) {
	var sales_order_id = $("#sales_order_id").val();
	$("#rate_history").show();
	if(sales_order_id){
		$('#addproduct').hide();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/proforma/',
			data: { mode : "loadsales_productdata",product_id :val, sales_order_id:sales_order_id },
			success: function(response)
			{
				//console.log(response);
				
				var obj =jQuery.parseJSON(response);
				$('#product_des').val(obj.description);				
				$('#product_hsn').val(obj.product_hsn_code);
				$('#product_hsn_code').val(obj.product_hsn);
				var qty=(obj.product_qty)-(obj.qty);
				$('#product_qty').val(qty);
				
				$('#product_rate').val(obj.product_rate);	
				$('#unit_id').select2("val",obj.unit_id);
				$('#product_discount').val(obj.product_discount);
				$('#discount_per').val(obj.discount_per);
				//$('#product_amount').val(obj.product_amount);	
				$('#formulaid').val(obj.formulaid);
				get_amount();	
				load_product_unit(val,obj.unit_id);
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
			url: root_domain + crm_domain +'app/proforma/',
			data: { mode : "load_productdata",eid :val, cust_id:cust_id },
			success: function(response)
			{
						//console.log(response);
						var obj =jQuery.parseJSON(response);
						
						CKEDITOR.instances['product_des'].setData(obj.product_desc);
						CKEDITOR.instances['product_spec'].setData(obj.product_spec);			
						$('#product_hsn').val(obj.product_hsn_code);
						$('#product_hsn_code').val(obj.product_hsn);
						$('#product_rate').val(obj.product_sale_rate);	
						$('#unit_id').select2("val",obj.product_base_unit);
						// $('#formulaid').val(obj.fom_id);
						// last_rate(obj.product_mst_rate); // Load last customer rate function	
						getrate();
						load_product_unit(val,obj.product_base_unit);
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
	/*alert($("#product_amount").val());*/
	var start=[];var end=[];
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),
		product_id:$("#product_id").val(),
		item_size:$("#item_size").val(),
		cat_id:$("#product_category_id").val(),
		product_disc:$("#product_des").val(),
		product_spec:$("#product_spec").val(),
		product_hsn:$("#product_hsn").val(),
		product_hsn_code:$("#product_hsn_code").val(),
		
		product_qty:$("#product_qty").val(),
		product_conv_qty:$("#product_conv_qty").val(),
		
		product_rate:$("#product_rate").val(),
		cust_stateid:$("#cust_stateid").val(),
		
		unit_id:$("#unitid").val(),
		conv_unitid:$("#conv_unitid").val(),
		rate_unitid:$("#rate_unit_id").val(),
		
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
		end_serial3:$("#end_serial3").val(),
		currency_rate:$('#currency_rate').val(),
		currency_id:$('#currency_id').val(),
		gst_type:$('#gst_type').val(),
		start:start,
		end:end 
	},
		success: function(response)
		{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#product_id").select2("val","")
				$("#item_size").val("")
				$("#product_id").select2('focus')
				$("#product_des").val("")
				$("#product_hsn_code").val("")
				$("#product_hsn").val("")
				$("#formulaid").val("")
				$("#product_discount").val("")
				$("#discount_per").val("")
				$("#taxable_value").val("")
				
				$("#product_qty").val("")
				$("#product_conv_qty").val("")
				$("#product_qty_hide").val("");
				$("#product_conv_qty_hide").val("");
				//$("#sqr_ft").val("")
				$("#conv_unitid").val("");
				$("#unit_id").val("");
				$("#unit_show").html("");
				$("#convert_unit_show").html("");

				$("#p_qty").val('');
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
				$(".hsncode").hide();
				$(".product_stock_label").hide();

				if(durva_permission==1)
				{
					$("#addrow1").show();
					$("#addrow").hide();
				}
				else
				{
					$('#addrow').html('Add');
				}

				Unloading();
				show_data();
				get_tax_details_table();
				get_invoice_total_tax();
				get_symbol();
				$('#bs-batch_wise_stock-modal1').modal('hide');
			}
		});
}
function load_paymentmode(val) {
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
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
		"bStateSave": true,
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
		"sAjaxSource": root_domain + crm_domain +'app/proforma/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "type_id", "value": type },{ "name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
		// ,
        // "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
        //     var iPageMarket = 0;
        //     for ( var i=iStart ; i<aaData.length  ; i++ )
        //     {
        //         iPageMarket += aaData[ aiDisplay[i] ][5]*1;

        //     }

        //     var nCells = nRow.getElementsByTagName('th');
        //     nCells[1].innerHTML = (iPageMarket );
        // }
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}
	function load_invoiceno(id)
	{
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/proforma/',
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
		var eid = $('#eid').val();
		if(durva_permission==1){
			mode = "load_tempoutward_durva";
		}else{
			mode = "load_tempoutward";	
		}
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/proforma/',
			data: { mode : mode, performa_invoice_type : performa_invoice_type, sales_order_id : sales_order_id, quotation_id: quotation_id, eid : eid },
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				get_tax_details_table();
				get_invoice_total_tax();
				get_amount();
				get_symbol();
				Unloading();
			}		

		});

	}

	function edit_data(id,table,whereid)
	{
		$("#addrow1").hide();
		$("#addrow").show();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/proforma/',
			data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
			success: function(response)
			{
				console.log(response)
				var data = jQuery.parseJSON(response);
					//$('#product_id').html(data.producthtml);
					//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
					$('#product_id').html(data.producthtml);
					$("#product_id").select2('data', { id:data.product_id, text: data.product_name});
					$("#product_des").val(data.description);
					$("#product_hsn_code").val(data.product_hsn);
					$("#product_hsn").val(data.product_hsn_code);
					$("#item_size").val(data.item_size);
					/*
					$("#start_serial1").val(data.start_serial1);
					$("#end_serial1").val(data.end_serial1);
					$("#start_serial2").val(data.start_serial2);
					$("#end_serial2").val(data.end_serial2);
					$("#start_serial3").val(data.start_serial3);
					$("#end_serial3").val(data.end_serial3);
					*/
					$("#product_qty").val(data.product_qty_show)
					$("#product_qty_hide").val(data.product_qty)
					$("#product_conv_qty_hide").val(data.product_conv_qty)
					$("#product_conv_qty").val(data.product_conv_qty_show)
					
					$("#unitid").val(data.unit_id)
					$("#conv_unitid").val(data.conv_unit_id)
					//$("#sqr_ft").val(data.sqr_ft);
					$("#product_rate").val(data.product_rate);
					$("#product_spec").val(data.product_disc)
					$("#formulaid").val(data.formulaid);
					$("#product_amount").val(data.product_amount)
					$("#product_discount").val(data.product_discount)
					$("#discount_per").val(data.discount_per)
					$("#taxable_value").val(data.product_amount)
					$("#edit_id").val(id);
					$('#addrow').val('Update');
					CKEDITOR.instances['product_des'].setData(data.product_desc);
					CKEDITOR.instances['product_spec'].setData(data.product_spec);
					
					get_hsn(data.product_id);
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
				url: root_domain + crm_domain +'app/proforma/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,invoice_id:$("#eid").val() },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response);
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
			url: root_domain + crm_domain +'app/proforma/',
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
				url: root_domain + crm_domain +'app/proforma/',
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
				url: root_domain + crm_domain +'app/proforma/',
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
			url: root_domain + crm_domain +'app/proforma/',
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
			url: root_domain + crm_domain +'app/proforma/',
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
			url: root_domain + crm_domain +'app/proforma/',
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
		url: root_domain + crm_domain +'app/proforma/',
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
		url: root_domain + crm_domain +'app/proforma/',
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
				window.location=root_domain + crm_domain +'proforma_list';
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
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_series_no"},
		success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp.trim());	
				load_invoiceno(resp.trim())	
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
    		url: root_domain + purchase_domain +'app/purchase/',
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
			$('#addpartybtn').css('display','none');
			$('#addlegderbtn').css('display','block');
		}else if(value=='1'){
			$('.quotation_div').removeClass('hide');
			$('.sales_order_div').addClass('hide');
			$('#addpartybtn').css('display','block');
			$('#addlegderbtn').css('display','none');
		}
		else if(value=='2'){
			$('.quotation_div').addClass('hide');
			$('.sales_order_div').removeClass('hide');
			$('#addpartybtn').css('display','none');
			$('#addlegderbtn').css('display','block');
		}

		if(value!='3'){
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/proforma/',
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
		url: root_domain + crm_domain +'app/proforma/',
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
	var cust_id = $('#cust_id').val();
	var cust_stateid = $('#cust_stateid').val();


	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "insert_quotation_salesorder_item", cust_stateid:cust_stateid, cust_id : cust_id, id : id, performa_invoice_type : performa_invoice_type, invoice_id:$("#eid").val()},
		success: function(resp){
			if($("#eid").val()=="")
			{
				show_data();
				get_amount();
				get_tax_details_table();
				get_invoice_total_tax();
				get_sales_bill_sundry(id, performa_invoice_type);
			}
			else
			{
				location.reload();
			}
		}		
	});
}
function get_sales_bill_sundry(id, performa_invoice_type){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_sales_bill_sundry", id : id, performa_invoice_type: performa_invoice_type},
		success: function(response){
			// var arr = JSON.parse(response);
			$('.sundryadded').append(response);
			get_amount();
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
		}		
	});
}
function get_so_detail(id){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_so_detail", id : id},
		success: function(response){
			var arr = JSON.parse(response);
			$('#order_no').val(arr.po_no);
			$('#order_date').val(arr.po_date);
			$('#currency_id').select2('val',arr.currency_id);
			$('#currency_rate').val(arr.currency_rate);
		}		
	});
}

function get_quotation_detail(id){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_quotation_detail", id : id},
		success: function(response){
			var arr = JSON.parse(response);
			
			$('#currency_id').select2('val',arr.currency_id);
			$('#currency_rate').val(arr.currency_rate);
		}		
	});
}

function get_hsn(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode').text(response);
				$(".hsncode").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode").hide();
				$(".product_stock_label").hide();
				$('#product_id').select2("val","");
				return false;
			}
		}
	});
	
}
function get_statecode(cust_id){
	var cust_id = $('#cust_id').val();
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();
	if(cust_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain +'app/proforma/',
			data: { mode : "get_gst_statecode", cust_id : cust_id, performa_invoice_type : performa_invoice_type},
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


function get_grossbalance(cust_id){
	if(cust_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain +'app/proforma/',
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

function update_total()
{
	var eid 			= $('#eid').val();
	var g_total 		= $('#g_total').val();
	var basic_total 	= $('#total').val();
	var branch_id 		= $('#branch_id').val();
	var currency_id 	= $("#currency_id").val();
	var currency_rate 	= $("#currency_rate").val();
	// var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	var gst1=[];
	var gst2=[];
	var addonsundry = {};
	
	var values = $("input.gst");
	$.each(values, function(key, value) {
		var new_key = this.name.match(/\d+/);
		gst1.push(new_key[0]);
		gst2.push($(this).val());
		/*var new_key = this.name.match(/\d+/);
		gst[new_key] = $(this).val();*/
		//gst.push($(this).val());
		//var new_key = this.name.match(/\d+/);
		//console.log("-->"+key+" :: "+new_key + "  :: " +  $(this).val());
	});
	
	
	$.ajax({
		
		type:'POST',
		data:{ mode:'update_total' , invoice_id:eid, g_total:g_total , basic_total:basic_total , branch_id:branch_id ,currency_id:currency_id,currency_rate:currency_rate, bill_sundry_tax:gst1,bill_sundry_tax1:gst2 },
		url:root_domain + crm_domain +'app/proforma/',
		success:function(result)
		{
			// console.log(result);
			//alert(result);
		}
		
	})
	
}
function get_invoice_total_tax(){

	var eid = $('#eid').val();
	var addontax1=0;
	$(".addontax").each(function() {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});

	var currency_id = $("#currency_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_invoice_total_tax",cust_id:$('#cust_id').val(),gross:$('.gross').text(),inv_total:$('#total').val(),invoice_id:eid,addontax1:addontax1,currency_id:currency_id},
		success: function(response)
		{
			// console.log(response);
			var arr = JSON.parse(response);
			if(arr){
				$(".invoiceTotalTax").html(arr.resp);
				if(arr.isTcs==1){
					$('.tcs_details').show();
				}else{
					$('.tcs_details').hide();
				}
                //$(".gross").text(response);
            }
        }
    });
}
function get_tax_details_table(){
	
	var eid = $('#eid').val();
	var cust_id = $('#cust_id').val();
	var currency_id = $('#currency_id').val();
	var discount = $('#discount').val();
	var addontax1=[];
	$(".addontax").each(function() {
		//alert(this.value);
		addontax1.push(this.value);
	});
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "get_tax_details_table" , invoice_id:eid,cust_id:cust_id,addontax1:addontax1,currency_id:currency_id,discount:discount },
		// success: function(response)
		// {

		// 	var arr = JSON.parse(response);
		// 	if(arr){
		// 		$(".tax_details").html(response.arr);
        //         //$(".gross").text(response);
        //     }
        // }
		success: function(response) {
			console.log(response); // See what the server is returning
		}
    });
}

function get_ledger_details(ledger_id)
{	
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();


	$.ajax({
		
		type:'POST',
		url: root_domain + crm_domain +'app/proforma/',
		data : { mode:"get_ledger_details",ledger_id:ledger_id },
		success:function(result)
		{
			var obj = JSON.parse(result);
			//Cost Center popup
			if(obj.enable_cost_center==1 && company_cost_center==1)
			{
				$('#div_cost_center').show();
			}
			
			//TCS Popup
			if(obj.enable_tcs==1 && company_tcs==1)
			{
				$('#tcs_div').show();
			}
			
			//Eway Bill Popup
			if(company_eway==1)
			{
				$('#eway_div').show();
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
			
			//Check SEZ Enable
			
			if(obj.enable_sez==1)
			{
				$('#sez_enable_text').show();
			}
			else
			{
				$('#sez_enable_text').hide();
			}
			
		}
	})
	
}
function open_pi_approv_payment(invoice_id,invoice_no){
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(invoice_no);
	$('#ref_ord_id').val(invoice_id);
	load_pi_hist_datatable();
	load_party_pi_dtl();
}
function load_pi_hist_datatable(){
	var invoice_id = $('#ref_ord_id').val();
	
	datatable = $("#order-po-history-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain + crm_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain +'app/proforma/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_pi_hist_datatable" }, { "name": "invoice_id", "value": invoice_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
	
}
function load_party_pi_dtl(){
	var invoice_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: { mode : "load_party_pi_dtl", invoice_id:invoice_id },
		success: function(resp){
			var resp=JSON.parse(resp);
			
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function add_po_apprv_hist(){
	
	var form_data = {
		mode:"add_pi_apprv_hist",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		invoice_id:$('#ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma/',
		data: form_data,
		success: function(response)
		{
			$('#po_approve_status').select2("val","0");
			$('#po_approve_remark').val("");
			load_pi_hist_datatable();
			//load_order_confirm_datatable();
			load_datatable();
			Unloading();
		}
	});	
}
function get_sundry_label(sundry_id)
{
	//alert(sundry_id);
	
	$.ajax({
		
		type:'POST',
		url:root_domain + crm_domain +'app/proforma/',
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
var rowIdx = 0;
// jQuery button click event to add a row
function addBillSundry(){
	var taxableamount=0;
	var totalsundryexist = 0;
	var basic_amount = $("#total").val();
	var netamount = $("#g_total").val();
	var discount = $("#discount").val();
	//alert(netamount);
	$(".gst").each(function() {
		var gstVal = $('.gst').val();
		taxableamount = Number(taxableamount) + Number(gstVal); 
	});

	$(".billsundryclass").each(function() {
		var billsundryclass = $(this).val();
		totalsundryexist = Number(totalsundryexist) + Number(billsundryclass); 
	});

	var currency_enable = $('#currency_enable').val();
	var currency_id = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	var gst_type = $('#gst_type').val();
	var eid = $('#eid').val();

	var bill_sundry_value = $("#bill_sundry").val();
	var bill_sundry =  $("#bill_sundry option:selected").text();
	var bill_sundry_amount = $('#bill_sundry_amount').val();

	if(bill_sundry_value == 0)
	{
		toastr.warning("Please Select Bill Sundry", "ERROR")
		return false;
	}else if(bill_sundry_amount == ''){
		toastr.warning("Please insert Bill Sundry Amount", "ERROR")
		return false;
	}else{
		Loading(true);
		$.ajax({
			type: "POST",
			async: false,
			url:root_domain + crm_domain +'app/proforma/',
			data: { mode : "get_bill_sundry_details",sundry_ledger_id:bill_sundry_value,totalsundryexist:totalsundryexist,taxableamount:taxableamount,basic_amount:basic_amount,netamount:netamount,gst_type:gst_type,default_amount:bill_sundry_amount,invoice_id:eid,currency_enable:currency_enable,currency_id:currency_id,currency_rate:currency_rate,invoice_date:$('#invoice_date').val(),discount:discount},
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
						if(arr[4] != 0){
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
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
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
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
		get_symbol();
	}
	Unloading();
}
function get_all_bill_sundry(invoice_id)
{
	
	$.ajax({
		
		type:'POST',
		url:root_domain + crm_domain +'app/proforma/',
		data:{ mode:'get_all_bill_sundry',invoice_id:invoice_id },
		success:function(response)
		{
			// console.log(response);
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

function removeSundry(bill_sundry_value,bill_sundry_amount,id,ledger_id=''){

	Loading(true);
	
	var edit_id = $('#eid').val();
	
	//alert(ledger_id);
	
	if(edit_id=='' || edit_id=='0')
	{
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);

		$('.'+id).remove();	
		get_invoice_total_tax();
		get_all_bill_sundry(edit_id);
		get_gtotal();
	}
	else
	{
		
		$.ajax({
			
			type:'post',
			url:root_domain + crm_domain +'app/proforma/',
			data:{ mode : 'remove_sundry',edit_id:edit_id,ledger_id:ledger_id },
			success:function(result)
			{
				get_invoice_total_tax();
				get_all_bill_sundry(edit_id);
				get_gtotal();
			}
		})
	}
	
	Unloading();
}
function showproduct(){
	branch_id = $('#branch_id').val();
	if(!branch_id){
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$('#modal-add-product').modal('show');
	$("#product_add_type").val('proforma');
	//$("#ledger_name").focus();
}

function add_hsn_invoice(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_proforma');
	$("#hsn_name").focus();
}
function showledger(){
	branch_id = $('#branch_id').val();
	if(!branch_id){
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-ledger').modal('show');
	// get_opening_balance('0');
	$("#ledger_add_type").val('proforma');
	$("#ledger_name").focus();
	var country = $("#countryid").val();
		// var state = <?=$stateid?>;
		load_state(country,'stateid','');
		load_city(state,'cityid','');
	}
	function showparty(){
		branch_id = $('#branch_id').val();
		if(!branch_id){
			toastr.warning("Choose Branch!!!", "ERROR");
			$('#branch_id').select2('focus');
			return false;
		}
		$.fn.modal.Constructor.prototype.enforceFocus = function() {};
		$('#bs-example-modal-lg').modal('show');
	// get_opening_balance('0');
	// $("#ledger_add_type").val('proforma');
	$("#cust_name").focus();
}
function getrate(){
	var product_id = $('#product_id').val();
	var unit_id = $('#unit_id').val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "getrate",product_id:product_id, unit_id:unit_id},
		success: function(response)
		{
			var data=jQuery.parseJSON(response);
			$('#product_rate').val(data.price);
			get_amount();
		}
	});
}

function get_advance(type)
{
	var net_amt = parseFloat($("#g_total").val());
	var disc=0;
	if(net_amt!="")
	{	
		if(type=="amt")
		{
			disc=100*parseFloat($('#adv_amt').val())/net_amt;	
			if(isNaN(disc)) {
				var disc = 0;
			}	
			$('#adv_per').val(disc);
		}
		else if(type=="per")
		{
			disc=(net_amt*parseFloat($('#adv_per').val()))/100;	
			if(isNaN(disc)) {
				var disc = 0;
			}
			$('#adv_amt').val(disc);			
		}
	}
	else
	{
		$('#adv_amt').val('');
		$('#adv_per').val('');
	}
	get_gtotal();
}

function get_symbol(){

	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	//var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	var textt = " ("+symbl+")"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});
	//$('#currency_rate').val(rate);
}

function currency_rate_c(){
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}

function tc_format_view(){
	var tc_for = $("input[name='tc_format']:checked").val();
	var proforma_type = $("input[name='performa_invoice_type']:checked").val();
	if(tc_for==1){
		$("#format_1").show();
		$("#format_2").hide();
	}else{
		$("#format_1").hide();
		$("#format_2").show();
		if(proforma_type==2){
			$('.common_terms').show();
			$('.party_terms').hide();
			$('.ledger_terms').show();
			$('.quotation_terms').hide();
			$('.sales_order_terms').show();
			$('.multi_condition').show();
		}else if(proforma_type==3){
			$('.common_terms').show();
			$('.party_terms').hide();
			$('.ledger_terms').show();
			$('.quotation_terms').hide();
			$('.sales_order_terms').hide();
			$('.multi_condition').show();
		}else{
			$('.common_terms').show();
			$('.party_terms').show();
			$('.ledger_terms').hide();
			$('.quotation_terms').show();
			$('.sales_order_terms').hide();
			$('.multi_condition').show();
		}
	}
}

function load_typeswise_terms(proforma_id) 
{
	//alert(purchaseorder_id);
	var quot_type = $('input[name="quot_type"]:checked').val();
	var performa_invoice_type = $('input[name="performa_invoice_type"]:checked').val();
	var terms_type = $('input[name="terms_type"]:checked').val();
	var cust_id = $("#cust_id").val();
	var quotation_id=''; var sales_order_id='';
	if(terms_type==1 || terms_type==2){
		if(cust_id==""){
			toastr.warning("Select Customer", "ERROR");
			$("#cust_id").focus();
			$("input[name='terms_type'][value='0']").prop('checked', true);
			return false;
		}
	}
	
	if(performa_invoice_type==1){
		quotation_id = $("#quotation_id").val();
	}else if(performa_invoice_type==2){
		sales_order_id = $("#sales_order_id").val();
	}
	

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/proforma/',
			data: { mode : "load_typeswise_terms", quot_type:quot_type, proforma_id:proforma_id,performa_invoice_type:performa_invoice_type,quotation_id:quotation_id,sales_order_id:sales_order_id,cust_id:cust_id,terms_type:terms_type },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#proforma_terms_cond_div').html(resp.resp_html);
				Unloading();
			}
		});
	}
}

function terms_check_all(obj){
	$('.terms_checkbox').prop('checked', obj.checked);
}


function open_batch_wise_qty(){
	load_batch_datatable();
	if($("#product_id").val()==="")
	{		
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus')
		return false;
	}
	else if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#product_qty").focus();
		return false;
	}
	
	var qty=$("#product_qty").val();
	var product_id=$("#product_id").val();
	
	
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain +'app/proforma/',
		data: { mode : "accessories_model_open",qty:qty,product_id:product_id},
		success: function(response)
		{
			
			var data = jQuery.parseJSON(response);
			
			$('#bs-batch_wise_stock-modal1').modal('show');
			
			$("#batch_data").html(data.html_data);	
			product_load_pro();
				
			CKEDITOR.replace('acc_product_desc', {
	            enterMode: CKEDITOR.ENTER_BR
	        });
		
			//validate_qty(0);	
		}
	});
}

function load_batch_datatable()
{
	
	var product_id=$('#product_id').val();
	
	var edit_id = $("#edit_id").val();
	
	datatable = $("#batch_stock_table").dataTable({
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
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain +'app/proforma/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_accessories_qty" },
				{ "name": "product_id", "value": product_id },
				{"name":"edit_id","value":edit_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function product_load_pro_l(){
	
	var testData = [];
	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product('acc_product_id_l', testData);	
	// return testData;
}
function load_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
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
}
/*function load_inquiry_type_product(type,pro_search){
	var inquiry_type = $('#inquiry_type').val();
	$('#projectItem').css('display','none');
	if(inquiry_type){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/proforma/',
			data: { mode:"load_inquiry_type_product", inquiry_type:inquiry_type, pro_type: type, pro_search:pro_search},
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_id').empty().append(obj.product_list);
				// $("#product_id").select2({
				// 	width: '100%'
				// });
				Unloading();
			}
		});
	}	
}*/

function add_accessories_product_pop()
{
	
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	
	if($("#acc_product_id").val()==="")
	{		
		toastr.warning("Select Product Id", "ERROR");
		$("#acc_product_id").select2("focus");
		return false;
	}
	if($("#acc_product_qty").val()==="")
	{		
		toastr.warning("Enter Product Qty", "ERROR");
		$("#acc_product_qty").val("focus");
		return false;
	}

/* var specification = new Array();
	var selected = $('.categojj').select2("data");
for (var i = 0; i <= selected.length-1; i++) {
    specification.push(selected[i].text);
	} */

var form_data = { 
		mode : "add_accessories_product_pop",
		edit_id:$("#edit_id_accessories").val(),
		acc_product_id:$("#acc_product_id").val(), 
		pid:$("#pid").val(), 
		acc_product_qty:$("#acc_product_qty").val(), 
		acce_rate:$("#acce_rate").val(), 
		acc_amount:$("#acc_amount").val(), 
		acc_product_desc:$("#acc_product_desc").val() 
		//specification:specification
	};

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/proforma/',
		data: form_data,
		success: function(response)
		{	
			////console.log(response)
			$("#acc_product_id").select2("val","");	
			$("#acc_product_qty").val('');	
			$("#acce_rate").val('');	
			$("#acc_amount").val('');	
			CKEDITOR.instances['acc_product_desc'].setData("");
			$("#edit_id_accessories").val('')
			$("#add_party_purchase").val("Add");
			Unloading();
			load_batch_datatable();
		}
	});
}
function edit_data_accessories_product_pop(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/proforma/',
		data: { mode : "preedit_accessories_product",  id : id },
		success: function(response)
		{	
			//////console.log(response);
			var data = jQuery.parseJSON(response);
			$("#acc_product_id").select2('data', { id:data.product_id, text: data.product_name});
			$("#acc_product_qty").val(data.qty);
			$("#acce_rate").val(data.acce_rate);
			$("#acc_amount").val(data.acc_amount);
			$("#edit_id_accessories").val(id);
			CKEDITOR.instances['acc_product_desc'].setData(data.product_desc);
			//$("#add_alternative_btn").val("Update");
			Unloading();
		}
	});
}

function delete_data_accessories_product_pop(id)
{
	
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/proforma/',
			data: { mode : "delete_data_alternative_product_pop",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function open_accesorice_wise_product_list(id){
	var cust_id = $('#cust_id').val();
	get_statecode(cust_id);	
	//alert(cust_id);
	
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain +'app/proforma/',
		data: { mode : "open_accesorice_wise_product_list",product_id:id},
		success: function(response)
		{
			//alert(response);
			var data = jQuery.parseJSON(response);
			
			$('#bs-batch_wise_stock-modal2').modal('show');
			
			$("#batch_data1").html(data.html_data);	
			product_load_pro_l();
				
		CKEDITOR.replace('acc_product_desc_l', {
            enterMode: CKEDITOR.ENTER_BR
        });
		
			//validate_qty(0);	
		}
	});
}

function load_product_dtls_pop_list(product_id){
	
	var product_attr =  $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	
	/* if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	} */

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode:"load_product_dtls", product_id:product_id,inquiry_type:inquiry_type},
			success: function(response)
			{
				////console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				CKEDITOR.instances['acc_product_desc_l'].setData(resp.product_desc);
				//CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if(currency_id != curr){
					rate = parseFloat(resp.product_sale_rate)/parseFloat(currency_rate);
				}else{
					rate = resp.product_sale_rate;
				}
				
				$('#acce_rate_l').val(rate.toFixed(2));
				//$('#unitid').select2("val",resp.product_base_unit);
				$('#current_stock_pop_l').css('display', 'block');
				$('#current_stock_pop_l').html('Current Stock: '+resp.current_stock);
				$('.unit_pop_l').css('display', 'block');
				$('#unit_pop_l').html('Unit: '+resp.unit_name);
				Unloading();						
			}
		});	
	}
}

function get_hsn_pop_list(product_id){
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain +'app/invoice/',
		data: { mode : "get_hsn_code",product_id:product_id},
		success: function(response)
		{
			if(response != ''){
				$('#hsncode_pop_l').text(response);
				$(".hsncode_pop_l").show();
			}else{
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode_pop_l").hide();
				
				$('#acc_product_id_l').select2("val","");
				return false;
			}
		}
	});	
}

function get_amount_pop_list(){	
	var product_qty = parseFloat($("#acc_product_qty_l").val());
	var product_rate = parseFloat($("#acce_rate_l").val());
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		$("#acc_amount_l").val(product_amount);
	}
	else {
		$("#acc_amount_l").val(0);
	}	
}

function add_field_list(){
	
	
	if(!$("#acc_product_id_l").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#acc_product_id_l").select2('focus');
		return false;
	}
	else if(!$("#acc_product_qty_l").val()){
		toastr.warning("Enter Quantityyyyyy", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if($("#acc_product_qty_l").val() <= 0){
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if(!$("#acce_rate_l").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#acce_rate").focus();
		return false;
	}
	else if($("#acce_rate_l").val() <= 0){
		toastr.warning("Rate must be greater than 0", "ERROR");
		$("#acce_rate_l").focus();
		return false;
	}
	else if(!$("#acc_amount_l").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#acc_amount_l").focus();
		return false;
	}
	
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	
	
	var form_data = { 
		mode : "add_field_list",
		product_id:$("#acc_product_id_l").val(), 
		pid:$("#pid_l").val(), 
		product_qty:$("#acc_product_qty_l").val(), 
		product_rate:$("#acce_rate_l").val(), 
		product_amount:$("#acc_amount_l").val(),
		cust_stateid:$("#cust_stateid").val(),  
		gst_type:$('#gst_type').val(),
		product_desc:$("#acc_product_desc_l").val(), 
		user_id : $("#user_id").val(),
		invoice_id:$("#eid").val()
		
	};
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/proforma/',
		data: form_data,
		success: function(response)
		{
			console.log(response);
			$("#acc_product_id_l").select2("val","");
			$("#pid_l").val("");
			$("#acc_product_qty_l").val("");
			$("#acce_rate_l").val("");
			$("#acc_amount_l").val("");
			CKEDITOR.instances['acc_product_desc_l'].setData("");
			$('#bs-batch_wise_stock-modal2').modal('hide');
			Unloading();
			show_data();
			dataget();
			get_tax_details_table();
			get_invoice_total_tax();
		}
	});
}

function dataget(product_spec_id,product_spec_id_id){
	
	
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"dataget", product_spec_id:product_spec_id },
			success: function(response)
			{
				console.log(response);
				
				var data=jQuery.parseJSON(response);
				
				$('#specification_id').html(data.res);
				
				$('#specification_id').select2("val",product_spec_id_id.split(','));
				
				Unloading();						
			}
		});		
}

function get_terms_detail(id){
	var tc_id = $("#ref_tc_id"+id).val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/proforma/',
		data: { mode : "get_terms_detail", tc_id : tc_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$("#tc_details"+id).val(obj.tc_details);
		}
	});
}

function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var product_category = '';
	var cat = '';
	
	if(comp_config.cat_wise_product_load==1){
		product_category = $("#product_category_id").val();
		cat = '&product_category='+product_category;	
	} 
	
	if(inquiry_type == 2)
	{
		$('#product_rate').attr('readonly', true);
	}
	else
	{
		
		$('#product_rate').attr('readonly',false);
	}
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search'+cat;
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

	load_cat_product12('product_id', testData)
}

function load_cat_product12(id, testData){
	$('#'+id).select2({
		data: testData,
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
			url: root_domain+ crm_domain +'app/proforma/',
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
						// $("#pro_cal_type").val("product_qty_hide");
					}
					// else{
						// if(obj.product_base_unit != obj.product_conv_unit){
						// 	$("#base_unit_block").show();
						// 	$("#product_qty").attr("readonly","readonly");
						// 	$("#product_conv_qty").removeAttr("readonly","readonly");
						// 	$("#convert_unit_block").show();
						// }else{
						// 	$("#base_unit_block").hide();
						// }
						// $("#pro_cal_type").val("product_conv_qty_hide");
					// }
				}else{
					$("#base_unit_block").show();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#product_conv_qty").removeAttr("readonly","readonly");
					$("#convert_unit_block").hide();
					// $("#pro_cal_type").val("product_qty_hide");
				}

				$('#unitid').val(obj.product_base_unit);
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
			url: root_domain+crm_domain+'app/proforma/',
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
//Maulik End
function load_trans_add(){
	var tc_id = $("#transid").val();
	var edit_id = $("#trans_add_ed").val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/sales_order/',
		data: { mode : "load_trans_add", tc_id : tc_id,edit_id:edit_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$("#trans_add").html(obj.html);
		}
	});
}
function open_print(url){

	var r= confirm("Are you print with header ?");
	if(r) {
		url=url+"/1";
		window.open(url, '_blank');
	}else{
		window.open(url, '_blank');
	}


}




function calculate_gst_to_all_product(gst_type){
	var cust_stateid = $("#cust_stateid").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/proforma/',
		data: { 
			mode : "add_gst_for_all_product", 
			gst_type : gst_type,
			edit_id:$("#eid").val(),
			cust_stateid : cust_stateid
		},
		success: function(response)
		{
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
		}
	});
}

function exportCsv() {
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	
	var url = root_domain +'generate_export?mode=proforma_list&data=' + encodeURIComponent(data) + "&date=" + encodeURIComponent(date) + "&type=" + encodeURIComponent(type);
	window.location.href = url;
}