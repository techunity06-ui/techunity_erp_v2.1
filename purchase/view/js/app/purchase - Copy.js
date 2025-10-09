//var datatable;
$(document).ready(function() {
	load_datatable();
	product_load();
	check_grn();
	show_data();
	get_symbol();
	currency_change();
	var mode = $('#mode').val();
	if(mode=='Edit')
	{
		var cust_id = $('#vender_id').val();
		var invoice_id = $('#eid').val();
		var bill_adjustment = $('#bill_adjustment').val();
		
		//customer effects
		//alert(cust_id);
		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_all_bill_sundry(invoice_id);
		get_gtotal();
		//get_grn(cust_id);
		get_tax_details_table();
		get_invoice_total_tax();

		if(bill_adjustment==1)
		{
			$('.adjust_advance_link').show();
		}
	}
	get_tax_details_table();
	get_invoice_total_tax();


	if($("#mode").val()=='Add' && $("#grn_id").val()==''){
		currency_rate_c();
	}
	
	//get_vendor_details('po_vendor_details');
	
	setTimeout(function(){ check_purchase_bill_type();	 }, 1000);
	// validate vendor add form on keyup and submit
	$("#po_add").validate({
		rules: {
			cust_id: {
				required: true			
			},
			po_no: {
				required: true			
			},
			po_date:{
				required : true	
			},
			purchase_ledger_id:{
				required : true	
			}
		},
		messages: {
			cust_id: {
				required: "Select Customer"
			},
			po_no: {
				required: "Enter P.O no"
			},
			po_date: {
				required : "Enter P.O date"
			},
			purchase_ledger_id: {
				required : "Select Purchase ledger"
			}
		}
	}); 
	
	
	
	
});

function update_netbalance(){
	get_gtotal();
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
	get_opening_balance('0');
	$("#ledger_add_type").val('purchase');
	$("#ledger_name").focus();
}

function showproduct(){
	
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-product').modal('show');

	$('#product_type').select2("val",0);
	$("#product_type").trigger('change');
	$("#product_add_type").val('purchase');
}

function addledger(value){
	window.location = root_domain+administration_domain+'ledger_create?id=sc';
}


$("#po_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#po_add").valid()) {
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
	
	if(parseFloat($('#total').val())<1){
		toastr.warning("Choose at least one Product!!!", "ERROR");
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);
	update_total();
			//console.log(form_data);
			$.ajax({
				cache:false,
				url: root_domain+purchase_domain+'app/purchase/',
				type: "POST",
				data: form_data,
				contentType: false,
				processData:false,
				success: function(response)
				{
					//alert(response);
					//console.log(response);	
					var arr = jQuery.parseJSON(response);
					if(arr.msg == '1') {
						Unloading();
						toastr.success("PURCHASE ADDED SUCCESSFULLY", "SUCCESS");
						window.location=root_domain+purchase_domain+'purchase_list';
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
					else if(arr.msg== 'update')
					{	
						toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");		
						Unloading();
						window.location=root_domain+purchase_domain+'purchase_list';
					}
					$('#save').prop("disabled",false);
					$('#po_add').trigger('reset');	
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus, errorThrown);
				}
			});
			
});

function add_hsn_invoice(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_purchase');
	$("#hsn_name").focus();
}

function delete_purchase(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				//console.log(response)
				if(response.trim() == "1") {
					toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
					load_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function get_discount(type)
{
	var qty=parseFloat($('#product_conv_qty').val());
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
	if($("#"+ratcalfiled).val()!="" && $("#product_rate").val()!="")
	{
		
		var q=$("#"+ratcalfiled).val();
		var rate=$("#product_rate").val();
		var a=q*rate;
		$("#total").val(parseFloat(a).toFixed(2));
                        
		if($("#product_discount").val()!="" )//discount calculation
		{	
			var discount=parseFloat($("#product_discount").val());
			a=a-discount; 
		}
	           
		$("#product_amount").val(parseFloat(a).toFixed(2));
		var bill_value=$('#product_amount').val();
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
	g_total = Number(total.toFixed(2));
	if(currency_id == $("#currency_id").val()){
		round_of = Math.round(g_total).toFixed(2);
		round = round_of-g_total;
		$("#round_of").val(round.toFixed(2));
	}else{
		round_of = g_total;
		round = 0;
		$("#round_of").val(round.toFixed(2));
	}
	$("#g_total").val(round_of);
    $("#paid_amount").val(g_total.toFixed(2));
	update_total();
	
}

function update_total()
{
	var eid = $('#eid').val();
	var g_total = $('#g_total').val();
	var basic_total = $('#total').val();
	var branch_id = $('#branch_id').val();
	var invoice_date = $('#po_date').val();
	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	//var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	var gst1=[];
	var gst2=[];
	var addonsundry = {};
	
	var values = $("input.gst");
	$.each(values, function(key, value) {
			var new_key = this.name.match(/\d+/);
			gst1.push(new_key[0]);
			gst2.push($(this).val());
	});
	
	
	$.ajax({
		
		type:'POST',
		data:{ mode:'update_total' , invoice_id:eid, g_total:g_total , basic_total:basic_total , branch_id:branch_id ,invoice_date:invoice_date, currency_id:currency_id,currency_rate:currency_rate, bill_sundry_tax:gst1, bill_sundry_tax1:gst2 },
		url:root_domain+purchase_domain+'app/purchase/',
		success:function(result)
		{
			//console.log(result);
			//alert(result);
		}
		
	})
	
}

function load_productdetail(pro_id) {
	var vender_id = $('#vender_id').val();
	if(vender_id==''){
		toastr.warning("Please Select Vender First","ERROR");
		$('#vender_id').select2('focus');
		$('#product_id').select2("val","");
		return false;
	}
	get_hsn(pro_id);
	var conversion_rate = $('#conversion_rate').val();
	var grn_id = $("#grn_id").val();
	if(grn_id){
		//alert(grn_id);
		//alert(pro_id);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "loadpurchase_productdata",product_id :pro_id, grn_id:grn_id, vender_id:vender_id },
			success: function(response)
			{

				console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_des').val(obj.description);	
				CKEDITOR.instances['pro_des'].setData(obj.product_desc);
				CKEDITOR.instances['pro_spe'].setData(obj.product_spec);
				$('#product_qty').val(obj.uqty);	
				$('#product_qty').attr("placeholder",obj.uqty);
				$('#product_qty').attr("max",obj.uqty);
				$('#product_rate').val(obj.product_rate);	
				$('#unitid').val(obj.unit_id);
				$('#unit_show').html(obj.unit_name);
				$('#product_discount').val(obj.product_discount);
				$('#discount_per').val(obj.discount_per);
				//$('#formulaid').val(obj.formulaid);
				$('#product_rate').val(obj.item_rate);
				$('#product_usd_rate').val(obj.item_rate/conversion_rate);
				load_product_unit(obj.product_id,obj.unit_id);
				get_amount();	
			}
		});
	}
	else{
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_productdata",eid :pro_id, vender_id : vender_id,po_id:$('#eid').val() },
			success: function(response)
			{
				//alert(response);
				console.log(response);
				//$("#rate_history").show();
				var obj =jQuery.parseJSON(response);
				//alert(obj.product_purchase_rate);
				if(obj.status==0)
				{
					toastr.warning("ITEM IS NOT ALLOWED BECAUSE TWO DIFFERENT SECTION OF TDS","WARNING");
					$('#product_id').select2("val","");
					$('.hsncode').html('');
				}
				else
				{
					$('#product_des').val(obj.product_desc);
					CKEDITOR.instances['pro_des'].setData(obj.product_desc);
					CKEDITOR.instances['pro_spe'].setData(obj.product_spec);
					$('#product_rate').val(obj.item_rate);				
					$('#unitid').val(obj.product_base_unit);
					$('#unit_show').html(obj.unit_name);
					load_product_unit(obj.product_id,obj.product_base_unit);
					get_product_price(pro_id);
					//load_last_rate(pro_id,obj.product_purchase_mst_rate);	
				}
			}
		});
	}
	
}

function get_product_price(product_id="") {
	if(product_id==""){
		product_id=$("#product_id").val();
	}
	var vender_id = $("#vender_id").val();
	var unit_id = $("#rate_unit_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "load_rate", product_id:product_id,vender_id:vender_id,unit_id:unit_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response);

			$('#product_rate').val(resp.rate);
			$('#product_rate').attr('data-pcard',resp.rate); 
			$('#product_rate').attr('data-pcardid',resp.purchasecardtrn_id);
			$('#discount_per').val(resp.discount_percentage);
			get_discount('per');
			Unloading();
		}
	});
}

function get_hsn(product_id){
	//alert(product_id);
	$.ajax({
        type: "POST",
        async: false,
        url: root_domain + finance_root_domain +'app/invoice/',
        data: { mode : "get_hsn_code",product_id:product_id},
        success: function(response)
        {
            if(response != ''){
            	//alert(product_id);
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

function add_field()
{
	if(!$("#product_id").val()) {		
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val() || parseFloat($("#product_qty").val())=='0') {		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#product_rate").val() || parseFloat($("#product_rate").val())=='0') {		
		toastr.warning("Enter Rate", "ERROR");
		$("#product_rate").focus();
		return false;
	}
	
	if(parseFloat($("#product_qty").attr('max'))>0){
		if(parseFloat($("#product_qty").val()) > parseFloat($("#product_qty").attr('max'))) {		
			toastr.warning("GRN QTY Doesn't Match", "ERROR");
			$("#product_qty").focus();
			return false;
		}
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	$('#addrow').attr("disabled",true);
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { 
			mode : "fieldadd",
			edit_id:$("#edit_id").val(),
			grn_id:$("#grn_id").val(),
			product_id:$("#product_id").val(),
			product_des:$("#product_des").val(),
			pro_des:$("#pro_des").val(), 
			pro_spe:$("#pro_spe").val(), 
			product_rate:$("#product_rate").val(),
			purchasecardtrn_id:$("#product_rate").attr('data-pcardid'),
			product_disc:$("#product_discount").val(),
			product_qty:$("#product_qty_hide").val(),
			product_conv_qty:$("#product_conv_qty_hide").val(),
			unit_id:$("#unitid").val(),
			conv_unitid:$("#conv_unitid").val(),
			rate_unitid:$("#rate_unit_id").val(),
			//formulaid:$("#formulaid").val(),
			product_discount:$("#product_discount").val(),
			discount_per:$("#discount_per").val(),
			product_amount:$("#product_amount").val(),
			taxable_value:$('#taxable_value').val(),
			sel_tax:$('#sel_tax').val(),
			sales_type:$("#sales_type").val(),
			po_id:$("#eid").val(),
			purchase_bill_type:$("input[name='purchase_bill_type']:checked").val(), //$("#purchase_bill_type").val(),
			currency_id:$("#currency_id").val(),
			currency_rate:$("#currency_rate").val(),
			conversion_rate:$("#conversion_rate").val(),
			product_usd_rate:$("#product_usd_rate").val(),
			product_usd_amount:$("#product_usd_amount").val(),
			branch_id:$("#branch_id").val(),
			product_hsn_code:$("#hsncode").text(),
			cust_stateid:$("#cust_stateid").val(),
			vender_id:$("#vender_id").val(),
			currency_enable:$("#currency_enable").val(),
		},
		success: function(response)
		{
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			//$("#grn_id").select2("val","");
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#rate_unit_id").val("");
			//$("#product_id").html("");
			$("#product_des").val("");
			CKEDITOR.instances['pro_des'].setData("");
			CKEDITOR.instances['pro_spe'].setData("");
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
			//$("#convert_unit_block").hide();

			$(".hsncode").hide();
			$("#product_rate").val('');
			$("#product_usd_rate").val('');
			$("#product_discount").val('');
			$("#sel_tax").val('');
			$("#taxable_value").val('');
			$("#product_amount").val('');
			$("#product_usd_amount").val('');
			$("#edit_id").val('');
			$('#addrow').val('Add');
			$('#addrow').attr("disabled",false);
			check_grn();
			Unloading();
			show_data();
			get_tax_details_table();
			get_invoice_total_tax();
			update_total();
			get_gtotal();
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
	var branch_id = $('#branch_id').val();
	var date_id = $('#date_id').val();

	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		/*"bStateSave": true,
		"fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },*/
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},

		"aoColumnDefs": [{ "bSearchable": false, "bVisible": false, "aTargets": [ 7 ] },
            { "bVisible": false, "aTargets": [ 7 ] },
            { "bVisible": false, "aTargets": [ 7 ] },
            { "bSearchable": false, "bVisible": false, "aTargets": [ 8 ] },
            { "bVisible": false, "aTargets": [ 8 ] },
             { "bVisible": false, "aTargets": [ 8 ] }
        ],

		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+purchase_domain+'app/purchase/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{ "name": "report", "value": data },
				{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "date_id", "value": date_id },
			);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;var iPageMarkets = 0;var iPageMarketses = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][6]*1;
				iPageMarkets = aaData[i][7]*1;
				iPageMarketses = aaData[i][8]*1;
			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
			$("#total_purchase").html('Rs. '+parseFloat(iPageMarkets).toFixed(2));
			$("#taxable_amt").html('Rs. '+parseFloat(iPageMarketses).toFixed(2));
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function show_data()
{
	var eid = $('#eid').val();
	var currency_rate = $("#currency_rate").val();
	var currency_id = $("#currency_id").val();
	var purchase_bill_type = $("input[name='purchase_bill_type']:checked").val();
	/*if(purchase_bill_type==='1'){
		//$('.generalfield').show();
		$('.importfield').hide();
	}else if(purchase_bill_type==='2'){
		//$('.generalfield').hide();
	}*/
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_tempoutward", po_id:eid, currency_id: currency_id, currency_rate: currency_rate },
			success: function(resp){
				//console.log(resp);
				$('#sale_productdata').html(resp);				
				Unloading();
				check_purchase_bill_type();
				get_amount();
				get_tax_details_table();
				get_invoice_total_tax();
				get_gtotal();
				//check_grn();
				
			}	
		});
	
}

function get_tax_details_table(){
	
	var eid = $('#eid').val();
	var cust_id = $('#cust_id').val();
	var company_tax_editable = $("#company_tax_editable").val();
	var currency_id = $('#currency_id').val();
	var addontax1=[];
	$(".addontax").each(function() {
		//alert(this.value);
		addontax1.push(this.value);
	});

	var salestype = $("#sales_type").val();

	$.ajax({
        type: "POST",
        async: false,
        url: root_domain + purchase_domain +'app/purchase/',
        data: { mode : "get_tax_details_table", invoice_id:eid,cust_id:cust_id,
        addontax1:addontax1,salestype:salestype,currency_id:currency_id },
        success: function(response)
        {
			//console.log(response);
        	var arr = JSON.parse(response);
            if(arr){
            	if(company_tax_editable == 0){
	            	$(".tax_details").html(arr.resp);
	            }
            }
        }
    });
    get_symbol();
}

function edit_data(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "preedit",  id : id },
		success: function(response)
		{
			//console.log(response)
			var curr = '<?php echo $_SESSION["currency_id"]?>';
			var currency_id = $('#currency_id').val();
			var data = jQuery.parseJSON(response);
			//$("#grn_id").select2("val",data.grn_id);
			$('#product_id').html(data.producthtml);
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			$("#product_id").select2('data', { id:data.product_id, text: data.product_name});
			$("#product_des").val(data.description);
			CKEDITOR.instances['pro_des'].setData(data.product_des);
			CKEDITOR.instances['pro_spe'].setData(data.pro_spe);
			$('#hsncode').text(data.product_hsn_code);
			$(".hsncode").show();
			
			$("#product_qty").val(data.product_qty_show)
			$("#product_qty_hide").val(data.product_qty)
			$("#product_conv_qty_hide").val(data.product_conv_qty)
			$("#product_conv_qty").val(data.product_conv_qty_show)
			
			$("#unitid").val(data.unit_id)
			$("#conv_unitid").val(data.conv_unit_id)

			//$("#product_rate").val(data.product_rate);
			//$("#product_rate").attr("data-pcardid",data.purchasecardtrn_id)
			$("#product_usd_rate").val(data.product_usd_rate);
			$("#product_disc").val(data.product_disc);
			$("#sel_tax").val(data.sel_tax);
			//$("#formulaid").val(0);
			//$("#product_amount").val(data.product_amount);
			$("#product_usd_amount").val(data.product_usd_amount);
			//$("#product_discount").val(data.product_discount);
			$("#discount_per").val(data.discount_per);
			if(currency_id==curr){
				$("#product_rate").val(data.product_rate);
				$("#product_rate").attr("data-pcardid",data.purchasecardtrn_id)
				$("#product_amount").val(data.product_amount);
				$("#product_discount").val(data.product_discount);
			}else{
				$("#product_rate").val(data.product_rate_conv);
				$("#product_rate").attr("data-pcardid",data.purchasecardtrn_id)
				$("#product_amount").val(data.product_amount_conv);
				$("#product_discount").val(data.product_discount_conv);
			}
			//$("#taxable_value").val(data.product_amount);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			load_product_unit(data.product_id,data.rate_unit);
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
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "delete_data",  eid : id },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					if($("#eid").val()=="0")
					{	
						//$('#product_id').html(data.producthtml);
						show_data();
					}
					else
					{
						location.reload();
					}
					Unloading();
					update_total();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function load_last_rate(pro_id,mst_rate){
	
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "last_rate", product_id:pro_id},
		success: function(resp){
			//console.log(resp);
			if(resp){
				$('#product_rate').val(resp);
			}
			else{
				$('#product_rate').val(mst_rate);
			}
			
			Unloading();
		}		
	});
}
function load_ven_grn(vender_id,id){

	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_ven_grn", vender_id : vender_id,id:id },
			success: function(response){
				//console.log(response);
				var resp = JSON.parse(response);
				$('#grn_div').show();
				$('#grn_id').html(resp.pro_html);
				$('#grn_id').select2('val',id);
				insert_product();
				Unloading();
			}
			
		});
	}else{
		$("#grn_id").html('<option value="">Choose GRN</option>');
		$('#grn_id').select2('val',"");
	}
}
function load_service_bill(vender_id,id){
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_service_bill", vender_id : vender_id,id:id },
			success: function(response){
				//console.log(response);
				var resp = JSON.parse(response);
				$('#service_div').show();
				$('#service_id').html(resp.pro_html);
				$('#service_id').select2('val',id);
				//insert_service_data();
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
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_grn_data", grn_id:grn_id },
			success: function(response){
				//console.log(response);
				var resp = 	JSON.parse(response);
				$('#product_id').html(resp.pro_html);
				$('#product_id').select2('val','');
				//alert("hi");
				Unloading();
			}
		});
	}else{
		$('#product_id').html("");
		$('#product_id').select2('val','');
	}
}
function load_purchase_order(vender_id){
	var product_id = $('#product_id').val();
	if(product_id){
		load_productdetail(product_id);
		load_product_tax(product_id,'purchase');
	}
	
	if(vender_id){
		$('#purchase_order_div').attr("style","display:block");
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_purchase_order", vender_id : vender_id },
			success: function(response){
				//console.log(response);
				$('#trn_purchaseorder_id').html(response);
				$('#trn_purchaseorder_id').select2('val','');
				
				$('#trn_purchaseorder_id_up').html(response);
				$('#trn_purchaseorder_id_up').select2('val','');
				Unloading();
			}
			
		});
		}else{
		$('#purchase_order_div').attr("style","display:none");
	}
}
function load_purhcase_order_data(purchaseorder_id){
	if(purchaseorder_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_purhcase_order_data", purchaseorder_id : purchaseorder_id },
			success: function(response){
				//console.log(response);
				var resp = 	JSON.parse(response);
				$('#order_no').val(resp.purchaseorder_no);
				$('#order_date').val(resp.purchaseorder_date);
				//$('#product_id').html(resp.pro_html);
				$('#product_id').select2('val','');
				Unloading();
			}
			
		});
	}
	/*else{
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "load_purhcase_pro"},
		success: function(response){
		console.log(response);
		var resp = 	JSON.parse(response);
		$('#order_no').val('');
		$('#order_date').val('');
		$('#product_id').html(resp.pro_html);
		$('#product_id').select2('val','');
		Unloading();
		}
		
		});
		
	}*/
	
}
function load_rate_hist(){
	
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val();
	if(vender_id==''){
		toastr.warning("Please Select Vendor", "WARNING");
		return false;
	}
	else if(product_id==''){
		toastr.warning("Please Select Product", "WARNING");
		return false;
	}
	else{
		
		// Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_rate_hist", vender_id : vender_id, product_id : product_id },
			success: function(response){
				//console.log(response);
				var arr = JSON.parse(response);
				$('#vendor_product_price_list').modal('show');
				$('#vendor_detail1').html(arr.cust_name);
				$('#pr_name').html(arr.product_name);
				$('#product_detail1').html(arr.resp);
				// Unloading();
			}
		});
		
	}	
	
}
function load_product(type_id){
	var trn_purchaseorder_id = $("#trn_purchaseorder_id").val();
	if(trn_purchaseorder_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "loadpurchase_producttypedata", type_id:type_id, purchaseorder_id:trn_purchaseorder_id },
			success: function(response){
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_id').html(obj.pro_html);
				Unloading();
			}
		});
	}
	else{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_product", type_id : type_id},
			success: function(data){
				//console.log(data);
				$('#product_id').html(data);				
				Unloading();
			}
		});
	}
	
}
function load_product_tax(pid,tran_type)
{
	//alert(pid);
	Loading();
	
	var vendor=$('#vender_id').val();
	
	if(vendor!=''){
		
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_order/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
				//alert(response);
				
				//console.log(response);
				var resp = JSON.parse(response);
				
				$('#sel_tax').val(resp.name);
				$('#formulaid').val(resp.id);
				$('#formula_tax_id').val(resp.tax_id);
				get_amount();
				Unloading();
			}
		});
		
	}
	Unloading();
}


function get_mrn(qid)
{
	//alert(qid);
	$('#table_show_mrn').modal('show');
	show_mrn_details(qid);
}

function show_mrn_details(qid)
{
	//alert(qid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/qc_detail/',
		data: { mode : "show_mrn_details", qid : qid },
		success: function(data){
			//alert(data);
			$('#mrn_div').html(data);
		}
	});
}

function load_purchase_srs_no(invoicetype_id){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "load_purchase_srs_no",invoicetype_id:invoicetype_id },
		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#po_no').val(obj.po_no);
		}
	});
}

function open_approv_pbill(po_id,po_no){
	$('#preview_approval_hist_modal').modal('show');
	$('#apprv_ref_no').html(po_no);
	$('#ref_quotation_id').val(po_id);
	load_purchase_hist_datatable();
}
function add_apprv_hist(){
	
	var form_data = {
		mode:"add_apprv_hist",
		assign_user_ids:$('#assign_user_ids').val(),
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		po_id:$('#ref_quotation_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: form_data,
		success: function(response)
		{
			$('#assign_user_ids').select2("val","");
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_purchase_hist_datatable();
			load_datatable();
			Unloading();
		}
	});	
}
function load_purchase_hist_datatable(){
	var po_id = $('#ref_quotation_id').val();
	
	$("#sales-order-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+purchase_domain+'app/purchase/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "po_id", "value": po_id }  );
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
function check_grn(){
	
	var type=$("input[name='purchase_type']:checked").val();
	if(type==="1"){
		//grn
		var grn_id=$("#grn_id").val();
		//load_grn_data(grn_id);
		$(".grn").show();
	}else{
		//direct
		//load_with_out_grn();
		$(".grn").hide();
	}
}
function load_with_out_grn(){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
			data: { mode : "load_with_out_grn" },
			success: function(response){
				//console.log(response);
				var resp = 	JSON.parse(response);
				$('#product_id').html(resp.pro_html);
				$('#product_id').select2('val','');
				//alert("hi");
				Unloading();
			}
		});
}

/*function get_vendor_name(vid) {
 	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : 'set_vendor_sesion', vendor_id : vid},
		success: function(data){
				window.location.href=root_domain+'purchase_add';
				Unloading();
			}		
		});
}
*/

function get_vendor_details(tab){
	var vendor_id = $('#vender_id').val();
	var mode = "get_"+tab;
	var eid = $('#eid').val();
	if(vendor_id){
		//Loading();
		$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : mode, vendor_id : vendor_id, eid : eid},
		success: function(data){
				//console.log(data);
				$('#'+tab).html(data);				
				//Unloading();
			}		
			
		});
	}
 }

 function get_manufacturer_details(tab){
	var vendor_id = $('#vender_id').val();
	var consignee_id = $('#consignee_id').val();
	var mode;
	if(consignee_id!=''){
		mode = "get_"+tab;
	}else{
		mode = "get_po_vendor_details";
	}
	var eid = $('#eid').val();
	
	$.ajax({
	type: "POST",
	url: root_domain+purchase_domain+'app/purchase/',
	data: { mode : mode, vendor_id : vendor_id, eid : eid, consignee_id : consignee_id},
	success: function(data){
			$('#'+tab).html(data);				
		}		
	});
 }

function open_consignee_click(){
	var cust_id=$('#vender_id').val();
	consignee_modal_open(cust_id);
	/*
	if(cust_id=="")
	{
		toastr.warning("Please Select Vendor", "WARNING");
	}
	else
	{
		consignee_modal_open(cust_id);
	}*/

	
}

function check_purchase_bill_type(){
	var type=$("input[name='purchase_bill_type']:checked").val();
	$('#product_rate').val(0);
	$('#product_usd_rate').val(0);
	if(type==="1"){
		//$('.generalfield').show();
		$('.importfield').hide();
	}else{
		//$('.generalfield').hide();
		$('.importfield').show();
	}
}

function get_currency_amount(currency, val) {
	var conversion_rate = $('#conversion_rate').val();
	var total;
	if(conversion_rate!=''){
		if(currency=='1'){
			total = parseFloat(val)/parseFloat(conversion_rate);
			$('#product_usd_rate').val(total.toFixed(2));
		}else if(currency=='2'){
			total = parseFloat(val)*parseFloat(conversion_rate);
			$('#product_rate').val(total.toFixed(2));
		}
	}else{
		toastr.warning("Please Enter Conversion Rate", "WARNING");
		$('#conversion_rate').focus();
		return false;
	}
}

function set_currency_conversion(value){
	$.ajax({
	type: "POST",
	url: root_domain+purchase_domain+'app/purchase/',
	data: { mode : 'set_currency_rate', currency_rate : value},
	success: function(data){
			//console.log(data);		
		}		
	});
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
            url: root_domain+purchase_domain+'app/purchase/',
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
		url: root_domain+purchase_domain+'app/payment_new/',
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
// Dimple Panchal : end

function insert_product()
{
	if($("#vender_id").val()=="")
	{		
		toastr.warning("Select Vendor Name", "ERROR")
		return false;
	}
	
	var grn_id=$('#grn_id').val();
	var eid=$('#eid').val();
	var cust_stateid=$('#cust_stateid').val();
	var vender_id=$('#vender_id').val();
	var branch_id=$('#branch_id').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "insert_product", grn_id : grn_id,eid:eid , cust_stateid:cust_stateid , cust_id:vender_id,branch_id:branch_id },
		success: function(data){
			//console.log(data);
			//var no = jQuery.parseJSON(data);
			get_symbol();
			show_data();
				
		}
	});
}
function insert_service_data(id){
	if($("#vender_id").val()=="")
	{		
		toastr.warning("Select Vendor Name", "ERROR")
		return false;
	}
	$("#service_div").css("display","block");
	var currency_enable = $("#currency_enable").val();
	var currency_id 	= $("#currency_id").val();
	var currency_rate 	= $("#currency_rate").val();
	var branch_id=$('#branch_id').val();

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "insert_service", service_id : id,currency_enable:currency_enable,currency_id:currency_id,currency_rate:currency_rate,branch_id:branch_id },
		success: function(data){
			show_data();
		}
	});
}
function delete_temp_data(){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase/',
		data: { mode : "delete_temp_data" },
		success: function(response)
		{
										
		}
	});	
}

//customize js by dhaval 

function get_statecode(cust_id){
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

function get_invoice_total_tax(){
	
	var eid = $('#eid').val();
	var addontax1=0;
	$(".addontax").each(function() {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});
	
	var salestype = $("#sales_type").val();
	var currency_id = $("#currency_id").val();
	$.ajax({
        type: "POST",
        async: false,
        url: root_domain + purchase_domain +'app/purchase/',
        data: { mode : "get_invoice_total_tax",cust_id:$('#vender_id').val(),
        gross:$('.gross').text(),inv_total:$('#total').val(),invoice_id:eid,
        addontax1:addontax1,salestype:salestype,currency_id:currency_id},
        success: function(response)
        {
			//alert(response);
			console.log(response);
        	var arr = JSON.parse(response);
            if(arr){
            	$(".invoiceTotalTax").html(arr.resp);
                //$(".gross").text(response);
                get_symbol();
            }
        }
    });
}


 var rowIdx = 0;
// jQuery button click event to add a row
function addBillSundry(){
	
	var taxableamount=0;
	var totalsundryexist = 0;
	var basic_amount = $("#total").val();
	var netamount = $("#g_total").val();
	var vender_id = $("#vender_id").val();
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
	
	var currency_enable = $('#currency_enable').val();
	var currency_id = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	var company_tax_editable = $("#company_tax_editable").val();

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
	        url: root_domain + purchase_domain +'app/purchase/',
	        data: { mode : "get_bill_sundry_details",sundry_ledger_id:bill_sundry_value,totalsundryexist:totalsundryexist,taxableamount:taxableamount,
	        basic_amount:basic_amount,netamount:netamount,default_amount:bill_sundry_amount,invoice_id:eid,currency_enable:currency_enable,currency_id:currency_id,currency_rate:currency_rate,invoice_date:$('#po_date').val(),vender_id:vender_id},
	        success: function(response)
	        {
	        	//alert(response);
	        	var arr1 = JSON.parse(response);
	            var arr = arr1.split(",");
				if(arr[3]=='')
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
							if(company_tax_editable != 1){
								get_invoice_total_tax();
							}
							get_tax_details_table();
							get_gtotal();
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
				get_symbol();
	        }
	    });
		

	}
	
}

function removeSundry(bill_sundry_value,bill_sundry_amount,id,ledger_id=''){
	
	Loading(true);
	
	var edit_id = $('#eid').val();
	var vender_ledger_id = $("#vender_id").val();
	var company_tax_editable = $("#company_tax_editable").val();

	//alert(ledger_id);
	
	if(edit_id=='' || edit_id=='0')
	{
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);
					
		$('.'+id).remove();	

		if(company_tax_editable != 1){
			get_invoice_total_tax();
			get_all_bill_sundry(edit_id);
		}
		
		get_gtotal();
	}
	else
	{
		
		$.ajax({
			
			type:'post',
			url:root_domain+purchase_domain+'app/purchase/',
			data:{ mode : 'remove_sundry',edit_id:edit_id,ledger_id:ledger_id,vender_ledger_id:vender_ledger_id },
			success:function(result)
			{
				//alert(result);
				//console.log(result);
				get_invoice_total_tax();
				get_all_bill_sundry(edit_id);
				get_gtotal();
				update_total();
			}
		})
	}
	
	Unloading();
}

function get_sundry_label(sundry_id)
{
	//alert(sundry_id);
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/salereturn/',
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



function get_grossbalance(cust_id){
	if(cust_id){
        $.ajax({
            type: "POST",
            async: false,
            url: root_domain + purchase_domain +'app/purchase/',
            data: { mode : "get_grossbalance", cust_id : cust_id},
            success: function(response)
            {
				//console.log(response);
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

function get_ledger_details(ledger_id)
{	
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();
	var company_trans = $('#company_trans').val();

	$.ajax({
		
		type:'POST',
		url: root_domain + purchase_domain +'app/purchase/',
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

			//Transport Popup
			if(company_trans==1)
			{
				$('#tran_div').show();
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
		}
	})
	
}

function currency_change()
{
	/*if($('#currency_enable').is(":checked"))
	{*/
		$('.currency_div').show();
	/*}
	else
	{
		$('.currency_div').hide();
	}*/
}

function get_symbol(){

	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	/*var rate = $("#currency_id").find(':selected').attr("data-currency-rate");*/
	var textt = " ("+symbl+")"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});

	/*if($("#mode").val()=='Add'){
	   $('#currency_rate').val(rate);   
	}*/
}

function currency_rate_c(){
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}

function get_all_bill_sundry(invoice_id)
{
	//alert(invoice_id);
	$.ajax({
		
		type:'POST',
		url:root_domain+purchase_domain+'app/purchase/',
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
function get_grn(vender_id,grn_id,modee)
{
	$('#grn_id').empty();
	
	$.ajax({
		
		type:'post',
		url:root_domain+purchase_domain+'app/purchase/',
		data:{mode:'get_grn_by_vendor',vender_id:vender_id,grn_id:grn_id,modee:modee},
		success:function(result){
			
			//alert(result);
			if(result!=0)
			{
				$('#grn_div').show();
				$('#grn_id').append(result);
			}
			else
			{
				$('#grn_div').hide();
			}
		}
	})
}
// function load_product_unit(product_id,unit_id){
// 	if(product_id)//tax calculation on total 
// 	{
// 		$.ajax({
// 			type: "POST",
// 			async: false,
// 			url: root_domain+purchase_domain+'app/purchase/',
// 			data: { mode : "load_product_unit", product_id : product_id},
// 			success: function(response)
// 			{
// 				var obj=jQuery.parseJSON(response);
// 					//alert(obj.qye);
// 					$('#unitid').val(obj.product_base_unit);
// 					$('#conv_unitid').val(obj.product_conv_unit);
					
// 					$('#unit_show').html(obj.base_unit_name);
// 					$('#convert_unit_show').html(obj.convert_unit_name);
// 					$("#convert_unit_block").show();
// 					if(obj.unit_status==="1"){
// 						$("#convert_unit_block").show();
// 					}else{
// 						$("#convert_unit_block").hide();
// 					}
// 				}
// 			});
// 	}
// }
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
			url: root_domain+ purchase_domain +'app/purchase/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				//console.log(response);
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
							$("#convert_unit_block").show();
							$("#product_qty").attr("readonly","readonly");
							$("#product_conv_qty").removeAttr("readonly","readonly");
						}else{
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}
				}else{
					$("#base_unit_block").show();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#convert_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
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
//Maulik End
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
	//alert(base_qty_hide);
	//alert(conv_qty_hide);
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase/',
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

function check_product_tds(prodcut_id)
{
	var vender_id = $('#vender_id').val();
	//alert(prodcut_id);
	$.ajax({

		type:'post',
		url:root_domain+purchase_domain+'app/purchase/',
		data:{mode:'check_product_tds',prodcut_id:prodcut_id,vender_id:vender_id},
		success:function(result)
		{
			//console.log(result);
		}

	})
}
function product_load(){
	var testData = [];
	// var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type=1&type=indent_po_pro_type&search=purchase_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			// console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
	load_cat_product('product_id', testData)	
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