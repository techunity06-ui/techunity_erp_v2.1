//var datatable;
$(document).ready(function() {
	/*$('#product_amount').hover(function(){
       var pro_amt = $('#product_amount').val();
		$('#product_amount').attr("title",pro_amt);
	});*/
	load_po_datatable();
	show_data();
	product_load();
	delivery_type_permission();
	load_products();
	job_work_process();
	if($('#vender_id').val()!='' && $('#vender_id').val()!=undefined){
		get_vendor_contact_details($('#vender_id').val());
	}
// validate vendor add form on keyup and submit
$("#purchaseorder_add").validate({
	rules: {
		vender_id: {
			required: true			
		},
		purchaseorder_no: {
			required: true			
		},
		purchaseorder_date:{
			required : true	
		}
	},
	messages: {
		vender_id: {
			required: "Select Vendor"
		},
		purchaseorder_no: {
			required: "Enter P.O no"
		},
		purchaseorder_date:{
			required : "Enter P.O date"
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
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/purchase_order/',
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
				toastr.success("PURCHASE ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+arr.back;
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
				toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'po_list';
				
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
			url: root_domain+'app/purchase_order/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		Unloading();
	}
	
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
			$('#discount_per').val(disc);
		}
		else if(type=="per")
		{
			disc=((qty*rate)*parseFloat($('#discount_per').val()))/100;	
			$('#product_discount').val(disc);
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
		// pass the selection value in sel_tax field
		var formulaidSel = $("#formulaid option:selected" ).text();
		if(formulaidSel){
			$("#sel_tax").val(formulaidSel);
		}
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
					url: root_domain+'app/purchase_order/',
					data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
					success: function(response)
					{
						//alert(response);
						var obj=jQuery.parseJSON(response);
						//alert(obj.tax_total);
						$('#product_amount').val(obj.total);
						$('#product_amount_tax').val(obj.tax_total_amount);
						$('#formula_tax_id').val(obj.tax_id);
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
	function get_gtotal(eid)
	{	
	//alert(eid);
	var id=parseInt($('#fieldcnt').val());
	var t=0;
	var p=Number($('#paking').val());
	var d=Number($('#discount').val());
	var r=Number($('#round_off').val());
	//alert(r);

	// Calculate Default  Total
	var input_amount=(document.getElementsByName('amount[]'));
	var cnt=input_amount.length;

	if(cnt!=0)
	{
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
				total=Number(total)+Number(t);
			//alert(t);
		}
		$("#total").val(Number(total).toFixed(2));
                // Dimple Panchal : start
                // if tax on total
                var formula =$("#formula_id").val();
                if(formula > 0)
                {
                	get_tax_on_total(formula);
                	tcs = $("#tcs_total").val();
                	total = parseFloat(total) + parseFloat(tcs);
                } else {
                	$('#tcs_total').val(0.00);
                }
                // Dimple Panchal : End
                if(p>0)
                {
                	total=total+p;
                }
                if(d>0)
                {
                	total=Number(total)-Number(d);
                }
                if(r!=0)
                {
                	total=Number(total)+r;
                }
            }
            else
            {
            	total=0;
            }
            $("#g_total").val(total.toFixed(2));

	// Calculate Currency Total
	var m = 0;
	var currency_total=(document.getElementsByName('currency_total[]'));
	var currency_cnt=currency_total.length;
	
	if(currency_cnt!=0)
	{
		//alert(cnt);
		var curency_total=0;
		if(curency_total=="")
		{
			curency_total=0;
		}
		for(var i=0;i<currency_cnt;i++)
		{	
			var m=currency_total[i].value;

			if(m>0){
				curency_total=Number(curency_total)+Number(m);
			}
			//alert(t);
		}

		$("#currency_total").val(Number(curency_total).toFixed(2));
	}
	else
	{
		curency_total=0;
	}
	$("#currency_total").val(curency_total.toFixed(2));

	if($('#currency_type_response').val()!=undefined){
		$currency_type_response = $('#currency_type_response').val();
		//alert($currency_type_response);
		if($currency_type_response){
			$('.currency_total_div').css({'display':'block'});
			$('.currency_type_name').html($currency_type_response);
		}
	}

	
}
function vendor_price_modal(){
	var vender_id = $("#vender_id").val();
	if(vender_id=="")
	{
		toastr.warning("Select Vendor", "ERROR")
		$("#vender_id").select2('focus');
		return false;
	}
	$('#vn_id').val(vender_id);
	$('#vendor_price_list').modal('show');
	vender_detail();
	vender_price_detail();
}
function vendor_product_price_modal(){
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val(); 
	if(vender_id=="")
	{
		toastr.warning("Select Vendor", "ERROR")
		$("#vender_id").select2('focus');
		return false;
	}
	
	if(product_id=="")
	{
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus');
		return false;
	}
	$('#vendor_product_price_list').modal('show');
	vender_detail1();
	vender_product_price_detail();
}
function vender_detail(){
	var vender_id = $("#vender_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "vender_detail", vender_id:vender_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#vendor_detail').html(resp.vender_detail);
			$('#vendor_name').html(resp.vender_name);
		}		 
	});
}
function vender_detail1(){
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "vender_detail", vender_id:vender_id,product_id:product_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#vendor_detail1').html(resp.vender_detail);
			$('#pr_name').html(resp.product_name);
		}		 
	});
}
function vender_price_detail(){
	var vender_id = $("#vender_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "price_detail", vender_id:vender_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#product_detail').html(resp.product_detail);
		}		 
	});
}
function vender_product_price_detail(){
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val(); 
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "product_price_detail", vender_id:vender_id,product_id:product_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#product_detail1').html(resp.product_detail);
		}		 
	});
}
function load_productdetail(val,i) {
	if(val!=0){
		$('#addproduct').hide();
	}
	else{
		$('#addproduct').show();
	}
	var vender_id = $('#vender_id').val();
	var currency_id = $('#currency_id').val();
	var conversion_rate = $('#conversion_rate').val();
	if(vender_id==''){
		toastr.warning("Please Select Vender First","ERROR");
		$('#vender_id').select2('focus');
		return false;
	}
	/*if(currency_id==''){
		toastr.warning("Please Select Currency","ERROR");
		$('#currency_id').select2('focus');
		return false;
	}
	if(conversion_rate==''){
		toastr.warning("Please Enter Conversion Rate","ERROR");
		$('#conversion_rate').select2('focus');
		return false;
	}*/
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "load_productdata",eid :val, vender_id : vender_id },
		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response)
			//$('#product_des').val(obj.product_desc);				
			$('#product_hsn_code').val(obj.product_hsn);				
			//$('#product_rate').val(obj.product_purchase_rate);				
			$('#product_rate').val(obj.prate);				
			$('#unitid').val(obj.product_base_unit);
			//$('#unitid').select2("val",obj.product_base_unit);
			if(obj.com_stateid==obj.ven_stateid){
				$('#formulaid').val(obj.intra_tax);
			}else{
				$('#formulaid').val(obj.inter_tax);
			}
			load_product_tax(val,'purchase');
			// alert(val);
			get_product_price(val);
			load_product_unit(val,obj.product_base_unit);

		}
	});
}
function add_field()
{
	var branch_id = $('#branch_id').val();
	
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
	else if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#product_qty").focus();
		return false;
	}
	else if($("#unitid").val()==="")
	{		
		toastr.warning("Select Unit", "ERROR")
		//$("#unitid").select2('focus');
		$("#unitid").focus();
		return false;
	}
	else if($("#product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		$("#product_rate").focus();
		return false;
	}
	else if($("#currency_id").val()==="")
	{		
		toastr.warning("Select Currency", "ERROR")
		$("#currency_id").focus();
		return false;
	}
	else if($("#conversion_rate").val()==="")
	{		
		toastr.warning("Enter Conversion Rate", "ERROR")
		$("#conversion_rate").focus();
		return false;
	}else if(branch_id ==="")
	{		
		toastr.warning("Select Branch", "ERROR")
		$("#branch_id").focus();
		return false;
	}
	var delivery_type=$("#delivery_type").val();
	if(delivery_type==="product_wise"){
		var mqty=$("#m_qty").val();
		
		var total_delivery_qty=document.getElementsByName('delivery_qty[]');
		var cnt=total_delivery_qty.length;
		var grandtotal_delivery_qty=0;
		mqty=parseFloat(mqty).toFixed(3);
		for(var i=0;i<cnt;i++)
		{	
			grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		}
		var total=parseFloat(grandtotal_delivery_qty).toFixed(3);

		if(mqty!=total){
			toastr.warning("Delivery Qty Wrong", "ERROR")
			return false;
		}
	}

	var vendorID;
	/*if($("#product_rate").attr('data-type')=='1'){
	   vendorID = '';
	   var new_price = parseFloat($("#product_rate").val());
       var discount = parseFloat($("#product_rate").data('discount'));
       var tolerance = parseFloat($("#product_rate").data('tolerance'));

       if(new_price >= tolerance || new_price <= discount){

          $msg = "Please update your purchase card.";
          toastr.warning($msg, "WARNING");
          $($("#product_rate")).focus();
          return false;
       }
	}else if($("#product_rate").attr('data-type')=='0'){
		vendorID = $('#vender_id').val();
	} */
	
	var total_delivery_qty1_arr=[];
	var delivery_date_arr=[];
	var arry_edit_arry=[];
	//var total_delivery_qty1=document.getElementsByName('delivery_qty[]');
	var total_delivery_qty1 = $('input[name="delivery_qty[]"]').val();
	var arry_edit = $('input[name="arry_edit[]"]').val();
	
	i = 0;
	$('input.delivery_qty').each(function(){ 
		total_delivery_qty1_arr[i++]=$(this).val();
	});  
	
	j = 0;
	$('input.delivery_date').each(function(){ 
		delivery_date_arr[j++]=$(this).val();
	});   
	
	k = 0;
	$('input.arry_edit').each(function(){ 
		arry_edit_arry[k++]=$(this).val();
	});   
	//console.log(total_delivery_qty1_arr);
	//alert($("#formula_tax_id").val());
	var e=$("#edit_id").val();
	//alert(e);
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { 
			mode : "fieldadd",
			total_delivery_qty:total_delivery_qty1_arr,
			delivery_date:delivery_date_arr,
			arry_edit:arry_edit_arry,
			currency_id:$("#currency_id").val(),
			delivery_type:delivery_type,
			purchaseorder_due_date:$("#purchaseorder_due_date").val(),
			conversion_rate:$("#conversion_rate").val(),
			edit_id:$("#edit_id").val(),
			product_type:$("#product_type").val(),
			process_id:$("#process_id").val(),
			product_id:$("#product_id").val(),
			product_des:$("#product_des").val(),
			product_hsn_code:$("#product_hsn_code").val(),
			product_qty:$("#product_qty_hide").val(),
			product_conv_qty:$("#product_conv_qty_hide").val(),
			unit_id:$("#unitid").val(),
			conv_unitid:$("#conv_unitid").val(),
			product_rate:$("#product_rate").val(),
			product_disc:$("#product_disc").val(),
			formulaid:$("#formulaid").val(),
			product_discount:$("#product_discount").val(),
			discount_per:$("#discount_per").val(),product_amount:$("#product_amount").val(),
			purchaseorder_id:$("#eid").val(),formula_tax_id:$("#formula_tax_id").val(),
			taxable_value:$('#taxable_value').val(),sel_tax:$('#sel_tax').val(),
			product_amount_tax:$('#product_amount_tax').val(),vendor_id:vendorID,
			branch_id : branch_id
		},
		success: function(response)
		{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$('#bs-po_dispatch_date-modal').modal('hide');
				$("#product_id").select2("val","");
				$("#process_id").select2("val","");
				$("#product_id").select2('focus');
				
				$("#product_des").val("");
				$("#product_hsn_code").val("");
				$("#formulaid").val("");
				$("#discount_per").val("");
				$("#product_discount").val("");
				$("#product_qty").val("");
				$("#product_conv_qty").val("");
				$("#product_qty_hide").val("");
				$("#product_conv_qty_hide").val("");
				$("#conv_unitid").val("");
				$("#unit_id").val("");
				$("#unit_show").html("");
				$("#convert_unit_show").html("");
				$("#convert_unit_block").hide();
				$("#product_rate").val('');
				$("#product_disc").val('');
				$("#taxable_value").val('');
				$("#product_amount").val('');
				$("#edit_id").val('');
				$("#sel_tax").val('');
				$("#formula_tax_id").val('');
				$("#product_amount_tax").val('');
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				
				show_data();
			}
		});
}
function add_branch_stock_1()
{
	Loading();	
	var bstock_arr=[];
	var bid_arr=[];
	var bpriority_arr=[];
	
	var bstock = $('input[name="bstock[]"]').val();
	//var bid = $('input[name="delivery_date[]"]').val();
	
	i = 0;
	$('input.bstock').each(function(){ 
		bstock_arr[i++]=$(this).val();
	});

    /* j = 0;
	$('input.bid').each(function(){ 
     	bid_arr[j++]=$(this).val();
    });
    */
    $.ajax({
    	type: "POST",
    	url: root_domain+'app/purchase_order/',
    	data: { mode : "add_branch_stock",bstock:bstock_arr },
    	success: function(response)
    	{
			//$("#product_opening").val(response);
			Unloading();
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
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
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
		"sAjaxSource": root_domain+'app/purchase_order/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{ "name": "po_type_status", "value": po_type_status },
				{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id }
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

	function edit_data(id,table,whereid)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
			success: function(response)
			{
					//console.log(response)
					var data = jQuery.parseJSON(response);
					job_work_process(data.product_id,data.process_id);
					$('#product_id').html(data.producthtml);
					$("#product_id").select2('data', { id:data.product_id, text: data.product_name});
					$("#process_id").select2('data', { id:data.process_id, text: data.process_name});
					//$("#process_id").select2("val",data.process_id)
					//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
					$("#product_type").select2("val",data.product_type)
					//$("#product_id").select2("val",data.product_id)
					$("#product_des").val(data.description)
					$("#product_hsn_code").val(data.product_hsn_code)
					
					$("#product_qty").val(data.product_qty_show)
					$("#product_qty_hide").val(data.product_qty)
					$("#product_conv_qty_hide").val(data.product_conv_qty)
					$("#product_conv_qty").val(data.product_conv_qty_show)
					
					$("#unitid").val(data.unit_id)
					$("#conv_unitid").val(data.conv_unit_id)
					
					//$("#sqr_ft").val(data.sqr_ft)
					$("#product_rate").val(data.product_rate)
					$("#product_disc").val(data.product_disc)
					//$("#unitid").select2("val",data.unit_id)
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
				url: root_domain+'app/purchase_order/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,purchaseorder_id:$("#eid").val() },
				success: function(response)
				{
					//console.log(response)
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
			url: root_domain+'app/purchase_order/',
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
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_invoiceno", typeid : id},
			success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#purchaseorder_no').val(no.invoiceno);
				
			}
		});
	}

	function load_product_po(type_id){
		var vender_id = $('#vender_id').val();
	//alert(vender_id);
	//Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "load_product", type_id : type_id, vender_id : vender_id},
		success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			//Unloading();
		}
	});
}
function entry_po_req_data(purchaseorder_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "entry_po_req_data", purchaseorder_id : purchaseorder_id},
		success: function(data){
			//console.log(data);
			show_data();				
			Unloading();
		}
	});
}

function cancel_po_status(id, po_status) 
{
	var r= confirm(" Are you want to Change PO Status ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "cancel_po_status", eid:id, po_status:po_status },
			success: function(response)
			{
					//console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1") {
						toastr.success("PO STATUS CHANGED SUCCESSFULLY", "SUCCESS");
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
function load_consignee(cust_id)
{
	var product_id = $('#product_id').val();
	if(product_id){
		load_productdetail(product_id);
		load_product_tax(product_id,'purchase')
	}
	
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
		}
	});	
}
/*
Code By Umair: 
Comment: Below code is change status at a time
*/


/*function change_po_approval_status(id, po_approval_status,order_no) 
{
	var r= confirm(" Are you want to Change PO Approval Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchase_order/',
				data: { mode : "change_po_approval_status", eid:id, po_approval_status:po_approval_status },
				success: function(response)
				{
					//console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1") {
						toastr.success("PO APPROVAL STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
	}*/

	function change_po_approval_status(id, po_approval_status, order_no) 
	{
		$('#preview_po_approval_hist_modal').modal('show');
		$('#apprv_po_ref_no').html(order_no);
		$('#ref_ord_id').val(id);
		load_purchase_hist_datatable();
		load_party_po_dtl();
	}
	function load_party_po_dtl(){
		var purchase_order_id = $('#ref_ord_id').val();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id },
			success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
	}
	function load_purchase_hist_datatable(){
		var purchase_order_id = $('#ref_ord_id').val();

		$("#order-po-history-datatable").dataTable({
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
			"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
			"iDisplayLength": 5,
			"sAjaxSource": root_domain+'app/purchase_order/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "purchase_order_id", "value": purchase_order_id }  );
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
function add_po_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		purchase_order_id:$('#ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: form_data,
		success: function(response)
		{
			$('#po_approve_status').select2("val","0");
			$('#po_approve_remark').val("");
			load_purchase_hist_datatable();
			//load_order_confirm_datatable();
			load_po_datatable();
			Unloading();
		}
	});	
}
function load_product_tax(pid,tran_type)
{
	//alert(pid);
	Loading();
	
	var vendor=$('#vender_id').val();
	
	if(vendor!=''){
		
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				var resp = JSON.parse(response);
				
				$('#sel_tax').val(resp.name);
				$('#formulaid').val(resp.id);
				$('#formula_tax_id').val(resp.tax_id);
				
				Unloading();
			}
		});
		
	}
	Unloading();
}

function show_data()
{
	//Loading();
	var eid=$('#eid').val();
	var po_type = $('#po_type').val();
	//alert(eid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "load_tempoutward",eid:eid,po_type:po_type},
		success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				get_amount()
				//Unloading();
			}		

		});
	
}

function get_po_tax(cust_id)
{
	//alert(cust_id);
	var eid=$('#eid').val();
	$('.nav-tabs a[href="#po_items"]').tab('show');
	//alert(eid);
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "get_po_tax",cust_id:cust_id,eid:eid },
		success: function(data){

		//alert(data);
				//console.log(data);
				//$('#sale_productdata').html(data);				
				//get_amount()
				// gen vendor details
				
				get_vendor_contact_details(cust_id);
				show_data();
				Unloading();
			}		

		});
}

function get_vendor_contact_details(cust_id) {
	// body...
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "get_vendor_contact_details",cust_id:cust_id },
		success: function(data){
			var vendor = JSON.parse(data);
			$('#vendor_email').val(vendor.cust_email);
			$('#vendor_mobile').val(vendor.cust_mobile);
			Unloading();
		}		
		
	});
}


function get_vendor_details(tab){
	var vendor_id = $('#vender_id').val();
	var mode = "get_"+tab;
	var eid = $('#eid').val();
	
	//alert("dsa");
	if(vendor_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : mode, vendor_id : vendor_id, eid : eid},
			success: function(data){
				//alert(data);
				//console.log(data);
				$('#'+tab).html(data);				
				//get_amount()
				show_data();
				Unloading();
			}		
		});
	}else{
		$msg = "Please Select Vendor First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
}

function get_vendor_name(vid) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : 'set_vendor_sesion', vendor_id : vid},
		success: function(data){
			window.location.href=root_domain+'po';
			Unloading();
		}		
	});
}

function get_product_price(product_id) {
	var vender_id = $('#vender_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : 'get_purchase_card_price', vendor_id : vender_id, product_id : product_id},
		success: function(data){
			data = jQuery.parseJSON(data);
			if(data.status=='1'){
				$('#product_rate').val(data.response.price);
				$('#product_rate').attr('data-discount',data.response.discount_percentage_value);
				$('#product_rate').attr('data-tolerance',data.response.rate_tolerance_value);
				$('#product_rate').attr('data-type',"1");
			}else{
				$('#product_rate').attr('data-type',"0");
				$('#product_rate').attr('data-tolerance',"");
				$('#product_rate').attr('data-discount',"");
			}
		}		
	});
}

// Dimple Panchal : start
function get_tax_on_total(formula_id){
    if(formula_id)//tax calculation on total 
    {
    	var total= $("#g_total").val();
    	var formulaid=$("#formula_id").val();
    	$.ajax({
    		type: "POST",
    		async: false,
    		url: root_domain+'app/purchase_order/',
    		data: { mode : "get_tax_on_total", total : total ,formulaid:formulaid},
    		success: function(response)
    		{
    			var obj=jQuery.parseJSON(response);
    			$('#tcs_total').val(obj.tax_value);
    		}
    	});
    }
}
// Dimple Panchal : end
//pathik start
function load_product_unit(product_id,unit_id){
	if(product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+'app/purchase_order/',
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
	
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
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
function open_approv_quo1(){
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
	else if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#product_qty").focus();
		return false;
	}
	else if($("#unitid").val()==="")
	{		
		toastr.warning("Select Unit", "ERROR")
		//$("#unitid").select2('focus');
		$("#unitid").focus();
		return false;
	}
	else if($("#product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		$("#product_rate").focus();
		return false;
	}
	else if($("#currency_id").val()==="")
	{		
		toastr.warning("Select Currency", "ERROR")
		$("#currency_id").focus();
		return false;
	}
	else if($("#conversion_rate").val()==="")
	{		
		toastr.warning("Enter Conversion Rate", "ERROR")
		$("#conversion_rate").focus();
		return false;
	}
	
	var qty=$("#product_qty").val();
	var trn_id=$("#edit_id").val();
	var unit_show=$("#unit_show").text();
	var product_name=$("#product_id option:selected").text();
	$("#model_product_name").html(product_name+" --- "+qty +" "+unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);
	//alert();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "delivary_date_model_open",qty:qty,trn_id:trn_id},
		success: function(response)
		{
			$('#bs-po_dispatch_date-modal').modal('show');
			$("#date_des").html(response);
			//$("#m_addrow").hide();
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
		}
	});
}

function validate_dilivary_date(){
	var main_qty=$("#product_qty").val();
	var total_delivery_qty=document.getElementsByName('delivery_qty[]');
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_delivery_qty.length;
	var grandtotal_delivery_qty=0;
	var count=$("#count").val();
	main_qty=parseFloat(main_qty).toFixed(3);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new=grandtotal_delivery_qty;
		grandtotal_delivery_qty_new=parseFloat(grandtotal_delivery_qty_new).toFixed(3);
		var count1=total_arry_sr[i].value;
		
		//alert(count1);
		//alert(qval);
		if(count1!="1"){
			if(qval==="1"){
				//alert(qval);
				//alert(count1)
				$('#field'+count1).html('');
			}
		}
		if(parseFloat(grandtotal_delivery_qty_new)>=parseFloat(main_qty)){
			qval="1";
		}else{
			qval="0";
		}
	}
	var total=parseFloat(grandtotal_delivery_qty).toFixed(3);
	
	if(parseFloat(total)>parseFloat(main_qty)){
		$("#m_addrow").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count);
			var pending_qty=parseFloat(main_qty)-parseFloat(total);
			
			$("#mix_loose_material_table").append('<tr id="field'+count+'"><td   class="text-center" style="vertical-align:center;"><input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'+count+'" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation('+count+');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control delivery_qty" id="delivery_qty'+count+'" name="delivery_qty[]" onchange="validate_dilivary_date();" placeholder="'+pending_qty+'" onkeyup="qty_wise_date_validation('+count+');" /></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('+count+');" id="fieldremove'+count+'"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="'+count+'" /></td></tr>')
			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		}else{
			$("#m_addrow").show();
		}
	}
	if(qval==="1"){
		//validate_dilivary_date();
	}
}
function remove_dilivary_date(count){
	$('#field'+count).html('');
	validate_dilivary_date();
}
function delivery_type_permission(){
	var delivery_type=$("#delivery_type").val();
	if(delivery_type==="po_wise"){
		$(".delivary_product_wise").hide();
		$(".delivary_po_wise").show();
	}else{
		$(".delivary_product_wise").show();
		$(".delivary_po_wise").hide();
	}
}
function qty_wise_date_validation(count){
	var delivery_date=$("#delivery_date"+count).val();
	var delivery_qty=$("#delivery_qty"+count).val();
	if(delivery_date===""){
		toastr.warning("Select Date", "ERROR")
		$("#delivery_date"+count).focus();
		$("#delivery_qty"+count).val("");
	}
}
function send_purchase_order(purchaseorder_id){
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "send_purchase_order", purchaseorder_id:purchaseorder_id},
		success: function(response)
		{
			console.log(response);
			// toastr.success("QUOTATION SEND SUCCESSFULLY", "SUCCESS");
			// var data=jQuery.parseJSON(response);
			// var response=data.res;
			if(response.status=="success"){
				toastr.success("PURCHASE ORDER SEND SUCCESSFULLY", "SUCCESS");
			}else{
				toastr.warning("NUMBER IS INVALID / SOMETHING WENT WRONG", "ERROR");
			}
		}
	});
}
//pathik end
function shortclosepo(id,order_no){
	var r= confirm(" Are you want to full po short close ?");
	$('#ref_pord_id').val(id);
	$('#ref_po_ref_id').val(id);
	if(r) {
		$('#full_po_shortclose_reason').modal('show');
		$('#shortclose_pofull_ref_no').html(order_no);
		load_party_po_detail();
		po_close_reason();
	}else {
		$('#manual_po_shortclose_reason').modal('show');
		$('#shortclose_poman_ref_no').html(order_no);
		load_trn_tbl();
		load_party_po_det();
		m_po_close_reason();
	}
}
function load_party_po_detail(){
	var purchase_order_id = $('#ref_pord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#po_company_detail').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function load_party_po_det(){
	var purchase_order_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "load_party_purchase_dtl", purchase_order_id:purchase_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#po_comp_detail').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function load_trn_tbl(){
	po_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "po_trn_tbl", po_id:po_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#po_trn_tbl').html(resp.po_trn_tbl);
		}		 
	});
}
function add_full_poshort_close(){
	po_id = $('#ref_po_ref_id').val();
	close_reson = $('#po_close_reson').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "full_poshort_close", po_id:po_id,close_reson:close_reson },
		success: function(resp){
			$('#po_close_reson').val('');
			window.location.href=root_domain+'po_list';
		}		 
	});	
}
function add_manualpo_short_close(){
	po_id = $('#ref_po_ref_id').val();
	close_reson = $('#m_close_remark').val();
	var	po_trn_id = $("input[name='po_trn_id[]']:Checked").map(function(){return $(this).val();}).get();
	if(po_trn_id==""){
		toastr.warning("Please Select Product", "ERROR")
		return false;
	}else{
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "manual_poshort_close", po_id:po_id,close_reson:close_reson,po_trn_id:po_trn_id},
			success: function(resp){
				//console.log(resp);
				$('#m_close_remark').val('');
				window.location.href=root_domain+'po_list';
			}		 
		});	
	}
}
function po_close_reason(){
	po_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "po_close_reason", po_id:po_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#m_close_remark').val('');
			$('#f_po_close_reason').html(resp.f_po_close_reason);
		}		 
	});
}
function m_po_close_reason(){
	po_id = $('#ref_po_ref_id').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "m_po_close_reason", po_id:po_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#m_po_close_reason').html(resp.m_po_close_reason);
		}		 
	});
}
function product_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=indent_po_pro_type&search=purchase_pro_search&po_type='+po_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		//console.log(json);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}
function load_products($po_type = '')
{
	$('#product_id').select2({
		data: product_load($po_type),
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
function copy_prev_purchase_trn(prev_purchaseorder_id){
	if(prev_purchaseorder_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode:"copy_prev_purchase_trn", prev_purchaseorder_id:prev_purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				show_data();
			}
		});
	}
}
function get_revise_po_no(purchaseorder_id,start_purchaseorder_id){
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order/',
		data: { mode : "get_revise_po_no", purchaseorder_id : purchaseorder_id, start_purchaseorder_id: start_purchaseorder_id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#purchaseorder_no').val(no.purchaseorder_no);

		}
	});
}

function po_type_product_load(po_type){
	product_load(po_type);
	load_products(po_type);
}	
function job_work_process(prod_id='',proc=''){
	po_type = $('#po_type').val();
	if(po_type == 2){
		//$('#process_id').select2('display','block');
		$('#process_id').removeClass('hidden');
		$('#job_proc').removeClass('hidden');
		$('#job_proc1').removeClass('hidden');
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_process_out_side", prod_id:prod_id,proc:proc },
			success: function(resp){
				//console.log(resp);
				var resp=JSON.parse(resp);
				$('#process_id').html(resp.process_list);
			}
		});
	}else{
		$('#process_id').addClass('hidden');
		$('#job_proc1').addClass('hidden');
		$('#job_proc').addClass('hidden');
	}
}