//var datatable;
$(document).ready(function() {
	var pro_type = $('#pro_type').val();
	var pro_search = $('#pro_search').val();
	var cust_id = $('#cust_id').val();
	get_statecode(cust_id);
	load_quotation_datatable();
	show_data();
	show_dfd_attach_data();
	show_inq_attach_data();
	product_load();
	get_tax_details_table();
	get_invoice_total_tax();
	currency_change();
	get_symbol();
	product_load_pro_l();
	delivery_type_permission();
	load_inquiry_type_product(pro_type,pro_search);
	load_trans_add();
    // validate vendor add form on keyup and submit
    $("#quotation_add").validate({
    	rules: {
    		quotation_date: {
    			required: true			
    		},
    		cust_id: {
    			required: true			
    		},
    		c_con_id: {
    			required: true			
    		},
    		inquiry_id: {
    			required: true			
    		},
    		
    	},
    	messages: {
    		quotation_date: {
    			required: "Enter Quotation Date"
    		},
    		cust_id: {
    			required: "Choose Customer"
    		},
    		c_con_id: {
    			required: "Choose Person"
    		},
    		inquiry_id: {
    			required: "Choose Inquiry"
    		},
    		
    	}
    }); 
}); 

function product_validation(quot_id){
	var product = check_product(quot_id);
	if(product === false){		
		toastr.warning("Add Product Please!!", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	return true;	
}
function check_product_validation(quot_id, back_link){
	if(!quot_id){
		if(!product_validation(quot_id)){
			return false;
		}
	}
	location.href = back_link;
}
$("#quotation_add").on('submit',function(e) {
	var quot_id = $('#eid').val();
	if(!product_validation(quot_id)){
		return false;
	}
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	
	if (!$("#quotation_add").valid()) {
		return false;
	} 
	if(!$("#quot_address").val()){
		toastr.warning("Please Enter Address", "ERROR");
		return false;
	} else if(parseInt($('#total').val())<=0)
	{
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	

	var an_id = new Array();
	var selected = $('.anexureeee').select2("data");
	for (var i = 0; i <= selected.length-1; i++) {
	    an_id.push(selected[i].text);
	}
	form_data.append('anx_id', an_id);
	//Hide Form Submit Alert
	setFormSubmitting();
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/quotation/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("QUOTATION ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain+'quotation_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("QUOTATION UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain + crm_domain + 'quotation_list';	
			}
			Unloading();
			$('#quotation_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function check_product(quotation_id){
	var has_product = false;
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/quotation/',
		data: { mode:"has_product", quotation_id : quotation_id },
		success: function(response)
		{
            //console.log(response);
            if(response == '0'){
            	has_product = false;
            } else {
            	has_product = true;
            }

            return has_product;
        }
    });
	
}

function get_discount(type)
{
	var ratcalfiled=$("#pro_cal_type").val();
	var product_qty = parseFloat($("#"+ratcalfiled).val());
	var product_rate = parseFloat($("#product_rate").val());
	var enable_quotation_limit = $('#enable_quotation_limit').val();
	var quotation_disc_limit = $('#quotation_disc_limit').val();
	var disc=0;var disc1=0;
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{	
		if($('#product_discount').val() || $('#discount_per').val()){
			if(type=="amt") {
				disc=100*parseFloat($('#product_discount').val())/(product_qty*product_rate);
				var disc1=disc.toFixed(2);			
				$('#discount_per').val(disc1);
			}
			else if(type=="per") {
				disc=((product_qty*product_rate)*parseFloat($('#discount_per').val()))/100;	
				var disc1=disc.toFixed(2);
				$('#product_discount').val(disc1);
			}
			var discount_per = $('#discount_per').val();
			if(parseInt($('#enable_quotation_limit').val())==1){
				if(parseFloat(discount_per) > parseInt(quotation_disc_limit)){
					console.log(discount_per);
					toastr.warning("Please Enter Discount Below "+parseInt($('#quotation_disc_limit').val())+" %", "ERROR");
					$('#discount_per').val(quotation_disc_limit);
					get_discount('per');
				}
			}
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

		$("#total").val(parseFloat(a));

		if($("#product_discount").val()!="" )//discount calculation
		{	
			var discount=parseFloat($("#product_discount").val());
			a=a-discount; 
		}

		$("#product_amount").val(parseFloat(a));
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
	
	var cnt  = input_amount.length;
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
	// alert(g_total)
	$("#g_total").val(g_total.toFixed(2));
	$("#paid_amount").val(g_total.toFixed(2));
	update_total();
	
}
function load_quotation_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	var stage_id=$('#stage_id').val();
	var approve_status=$('#approve_status_val').val();

	Loading(true);
	
	$("#quotation-datatable").dataTable({
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
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/quotation/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"},
				{"name": "date", "value": date},
				{"name": "stage_id", "value": stage_id},
				{"name": "approve_status", "value": approve_status},
				{"name": "branch_id", "value": branch_id});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][6]*1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
			$('#quotcount').html('Count: '+aaData.length);
			$('#quotamt').html('Rs. '+parseFloat(iPageMarket ).toFixed(2));
			Unloading();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_quotation(quotation_id,quotation_no,prev_quotation_id) 
{
	var r= confirm(" Are you sure want to delete '"+quotation_no+"' ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/quotation/',
			data: { mode : "delete",  quotation_id : quotation_id, prev_quotation_id:prev_quotation_id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("QUOTATION DELETE SUCCESSFULLY", "SUCCESS");
					load_quotation_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	} 
}
function load_cust_inq(cust_id) 
{
	if(cust_id) {
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain + 'app/quotation/',
			data: { mode : "load_cust_inq",  cust_id : cust_id },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#inquiry_id').html(resp.resp_html);
				$('#inquiry_id').select2("val","");
			}
		});
	}
}

function load_typeswise_terms(quotation_id) 
{
	var quot_type  = $("input[name='quot_type']:checked").val();
	var terms_type = $("input[name='terms_type']:checked").val();
	var cust_id    = $("#cust_id").val(); 
	/*var quotation_id = $("#eid").val();*/
	if($("#cust_id").val()==""){
		toastr.warning("Choose Customer", "ERROR")
		$("#cust_id").focus();
		return false;
	}

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain + 'app/quotation/',
			data: { mode : "load_typeswise_terms", quot_type:quot_type, quotation_id:quotation_id,terms_type:terms_type,cust_id:cust_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#quot_terms_cond_div').html(resp.resp_html);
				Unloading();
			}
		});
	}
}

function add_field()
{	
	if(!$("#product_id").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val()){
		toastr.warning("Enter Quantity", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#product_rate").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#product_rate").focus();
		return false;
	}
	else if($("#product_qty").val() <= 0){
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if($("#product_rate").val() <= 0){
		toastr.warning("Rate must be greater than 0", "ERROR");
		$("#product_rate").focus();
		return false;
	}

	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}

	if($('#currency_enable').is(':checked'))
	{
		var currency_enable = 1;
	}
	else
	{
		var currency_enable = 0;
	}
	
	var specification = new Array();
	var selected = $('.categojj').select2("data");
	
	for (var i = 0; i <= selected.length-1; i++) {
    	specification.push(selected[i].text);
	}


	var delivery_type=$("#delivery_type").val();
	if(delivery_type==="product_wise"){
		var mqty=$("#m_qty").val();
		
		var total_delivery_qty=document.getElementsByName('delivery_qty[]');
		var cnt=total_delivery_qty.length;
		var grandtotal_delivery_qty=0;
		mqty=parseFloat(mqty).toFixed(4);
		for(var i=0;i<cnt;i++)
		{	
			grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		}
		var total=parseFloat(grandtotal_delivery_qty).toFixed(4);

		if(mqty!=total){
			toastr.warning("Delivery Qty Wrong", "ERROR")
			return false;
		}
	}

	var item_no = '';  // speciel field for global_engg
	var item_size = ''; // speciel field for global_engg
	var item_class = ''; // speciel field for global_engg

	item_no = $("#item_no").val()
	item_size = $("#item_size").val()
	item_class = $("#item_class").val()

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

	var unit_wise=$("#unit_wise").val();
	if($("#delivery_type").val()=='product_wise'){
		unit_wise = $("#unit_wise").val();
	}

	var dynamic_data = "";
	var dynamic_data_arr = {};
	$('input[class^="dy_fields\\["]').each(function () {
		var vl = $(this).val();
		var db_name = $(this).attr("data-id");
		var fval = $("#field_id_"+vl).val();
		if (dynamic_data) {
			dynamic_data = dynamic_data + ",";
		}
		dynamic_data_arr[db_name] = fval;
	})

	var form_data = { 
		mode : "add_field",

		total_delivery_qty:total_delivery_qty1_arr,
		delivery_date:delivery_date_arr,
		arry_edit:arry_edit_arry,

		edit_id:$("#edit_id").val(),
		product_id:$("#product_id").val(), 
		cat_id:$("#cat_id").val(),
		rcat_id : $("#parent_cat_id").val(),
		branch_id:$("#branch_id").val(), 
		product_desc:$("#product_desc").val(), 
		product_spec:$("#product_spec").val(),
		delivery_type : $("#delivery_type").val(),
		quo_delivery_date:$("#quo_delivery_date").val(),
		specification:specification,
		product_other_desc:$("#product_other_desc").val(),
		level_id:$("#level_id").val(),

		unit_wise : unit_wise,
		product_qty:$("#product_qty").val(), 
		unitid:$("#unitid").val(), 

		product_conv_qty : $("#product_conv_qty").val(),
		conv_unit_id : $("#conv_unitid").val(),
		rate_unit : $("#rate_unit_id").val(),

		product_rate:$("#product_rate").val(), 
		product_discount:$("#product_discount").val(), 
		discount_per:$("#discount_per").val(), 
		product_amount:$("#product_amount").val(), 
		formulaid:$("#formulaid").val(), 
		product_total:$("#product_total").val(),
		cust_stateid:$("#cust_stateid").val(),
		product_hsn_code:$("#hsncode").text(), 
		act_amt_flag:$('input[name="act_amt_flag"]:checked').val(), 
		quotation_id:$("#eid").val(),
		inquiry_type:$('#inquiry_type').val(),

		currency_enable:currency_enable,
		currency_rate:$('#currency_rate').val(),
		currency_id:$('#currency_id').val(),
		gst_type:$('#gst_type').val(),
		orange:$('#orange').val(),
		mfg:$('#mfg').val(),
		trading:$('#trading').val(),
		repairing:$('#repairing').val(),
		other:$('#other').val(),
		orange_total:$('#orange_total').val(),
		mfg_total:$('#mfg_total').val(),
		trading_total:$('#trading_total').val(),
		repairing_total:$('#repairing_total').val(),
		other_total:$('#other_total').val(),

		inquiry_id:$('#inquiry_id').val(),
		item_no:item_no,
		item_size : item_size,
		item_class : item_class,
		dynamic_data: dynamic_data_arr
	};
	
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain +'app/quotation/',
		data: form_data,
		success: function(response)
		{
			$('input[class^="dy_fields\\["]').each(function () {
				var vl = $(this).val();
				$("#field_id_"+vl).select2("val", "")
			})

			//console.log(response);
			$('#bs-po_dispatch_date-modal').modal('hide');
			$("#parent_cat_id").select2("val","");
			if(aeon_permission!=1){
				$("#cat_id").select2("val","");
			}
			$("#product_id").select2("val","");
			$("#specification_id").select2("val","");
			/*$("#cat_id").select2("val","");*/
			$("#level_id").select2("val","1");
			//$("#product_desc").val("");
			CKEDITOR.instances['product_spec'].setData("");
			//$("#product_spec").val("");
			CKEDITOR.instances['product_desc'].setData("");
			$("#product_other_desc").val("");
			
			$("#rate_unit_id").val("");
			$("#product_qty").val("");
			
			$("#product_conv_qty").val("");
			$("#product_qty_hide").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$("#unit_id").val("");
			$("#unit_show").html("");
			$("#convert_unit_show").html("");


			$("#product_rate").val("");
			$("#product_discount").val("");
			$("#discount_per").val("");
			$("#item_size").val("");
			$("#item_class").val("");
			$("#product_amount").val("");
			$("#formulaid").val("");
			$("#product_total").val("");
			$("#orange").val("");
			$("#mfg").val("");
			$("#trading").val("");
			$("#repairing").val("");
			$("#other").val("");
			$("#orange_total").val('');
		$("#mfg_total").val('');
		$("#trading_total").val('');
		$("#repairing_total").val('');
		$("#other_total").val('');
			$("#edit_id").val("");
			$('#quot_trn_btn').html('Add');
			$('#bud_ttl_span').html("");
			$('#act_amt_flag').prop("checked",false);
			$('#product_rate').attr('readonly', false);
			if($('#inquiry_type').val()!='2'){
			    $('#projectItem').css('display','none');
			}else{
			    $('#projectItem').css('display','block');
			}
			$(".hsncode").hide();
			
			$(".product_stock_label").hide();
			
			
			if(durva_permission==1)
			{
				
				$("#addrow1").show();
				$("#quot_trn_btn").hide();
				
			}
			else
			{
				$('#quot_trn_btn').html('Add');
			}
			
			
			$('#bs-batch_wise_stock-modal1').modal('hide');
			Unloading();
			show_data();
			dataget();
			get_tax_details_table();
			get_invoice_total_tax();
		}
	});
}

function show_data() {
	var eid = $('#eid').val();

	var mode ='';
	if(durva_permission==1){
		mode = "show_data_durva";
	}else{
		mode = "show_data";	
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/quotation/',
		data: { mode : mode, quotation_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#quot_trn_div').html(resp);
			Unloading();
			get_amount();
			get_symbol();
		}		 
	}); 
}
function edit_trn_data(quot_trn_id,project_wise)
{
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain + 'app/quotation/',
		data: { mode:"edit_trn_data", quot_trn_id:quot_trn_id,project_wise:project_wise },
		success: function(response)
		{
			//console.log(response);
			var resp = jQuery.parseJSON(response);	

			// Get Master fields data
			var master_fields_data = resp.master_fields_data;
			if (master_fields_data) {
				$.each(master_fields_data, function (k, vl) {
					var labl = vl.master_field_db_name;
					$.map(resp, function(dvalue, dkey) {
						if (dkey == labl) {
							if (dvalue == '0') {
								dvalue = "";
							}
							$("#field_id_"+vl.master_field_id).select2("val", dvalue);		
						}
					});
				});
			}

			if(quotation_rate_fixed=='1'){
				$('#product_rate').attr('readonly', true);
			}
			var curr = '<?php echo $_SESSION["currency_id"]?>';
			var currency_id = $('#currency_id').val();
			//load_product_category_wise(resp.cat_id);
			//$("#cat_id").select2("val",resp.cat_id);
			//alert(resp.rcat_id);
			if(reciclar==1){
				$("#parent_cat_id").select2("val",resp.rcat_id);
			}
			if(aeon_permission==1 || reciclar==1){
				$("#cat_id").select2("val",resp.cat_id);
            }

			$("#product_id").select2("val",resp.product_id);
			$("#old_product_id").val(resp.product_id);
			$("#quotation_trn_id").val(resp.quot_trn_id);
			$("#product_id").select2('data', { id:resp.product_id, text: resp.product_name});
			$("#level_id").select2("val",resp.level_id);
			
			$("#unitid").select2("val",resp.unitid);
			$("#conv_unitid").val(resp.conv_unit_id);
			$("#unit_wise").val(resp.unit_wise);

			$("#product_qty").val(resp.product_qty);
			$("#product_qty_hide").val(resp.product_qty)
			$("#product_conv_qty_hide").val(resp.product_conv_qty)
			$("#product_conv_qty").val(resp.product_conv_qty)

			$("#discount_per").val(resp.discount_per);
			if(curr == currency_id){
				$("#product_rate").val(resp.product_rate);
				$("#product_discount").val(resp.product_discount);
				$("#product_amount").val(resp.product_amount);
				$("#product_total").val(resp.product_total);
			}else{
				$("#product_rate").val(resp.product_rate_conv);
				$("#product_discount").val(resp.product_discount_conv);
				$("#product_amount").val(resp.product_amount_conv);
				$("#product_total").val(resp.product_total_conv);
			}

			$("#formulaid").val(resp.formulaid);
			
			$("#orange").val(resp.orange);
			$("#mfg").val(resp.mfg);
			$("#trading").val(resp.trading);
			$("#repairing").val(resp.repairing);
			$("#other").val(resp.other);
			$("#orange_total").val(resp.orange_total);
				$("#mfg_total").val(resp.mfg_total);
				$("#trading_total").val(resp.trading_total);
				$("#repairing_total").val(resp.repairing_total);
				$("#other_total").val(resp.other_total);
			$("#item_no").val(resp.item_no);
			$("#item_size").val(resp.item_size);
			$("#item_class").val(resp.item_class);

			$("#edit_id").val(quot_trn_id);
			$('#quot_trn_btn').html('Update');
			$('#bud_ttl_span').html(resp.budget_trn_g_total);
			CKEDITOR.instances['product_desc'].setData(resp.product_desc);
			CKEDITOR.instances['product_spec'].setData(resp.product_spec);
			CKEDITOR.instances['product_other_desc'].setData(resp.product_other_desc);
			if(resp.act_amt_flag=='1'){
				$('#act_amt_flag').prop("checked",true);
			}
			else{
				$('#act_amt_flag').prop("checked",false);
			}
			if(project_wise=='1'){
				$('#projectItem').css('display','block');
			}

			load_product_unit(resp.product_id, resp.unitid);
			get_hsn(resp.product_id);
			dataget(resp.product_spec_id,resp.product_spec_id_id);
			Unloading();
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




function delete_trn_data(quot_trn_id, project_wise)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain +'app/quotation/',
			data: { mode:"delete_trn_data", quot_trn_id:quot_trn_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				else if(response.trim() == "2") {
					toastr.warning("PLEASE DELETE SUB ITEM", "WARNING");
				}	
				show_data();
				get_tax_details_table();
				get_invoice_total_tax();
				Unloading();						
			}
		});	
	}
}

function load_product_dtls(product_id)
{
	var product_attr =  $('#inquiry_type').val();
	var inquiry_type = $('#inquiry_type').val();
	var branch_id = $('#branch_id').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id	 = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	}
	if(product_id){
		var cust_id = $('#cust_id').val();
		if(!cust_id){
			toastr.warning("Please Select Customer First","ERROR");
			$('#cust_id').select2('focus');
			$('#product_id').select2("val","");
			return false;
		}
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain +'app/quotation/',
			data: { mode:"load_product_dtls", product_id:product_id, cust_id:cust_id,inquiry_type:inquiry_type },
			success: function(response)
			{
				console.log(response);
				if(quotation_rate_fixed=='1'){
					$('#product_rate').attr('readonly', true);
				}
				var resp=jQuery.parseJSON(response);
				var rate=0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				$('#unitid').select2("val",resp.product_base_unit);
				
				$('#product_qty').val('0');
				$('#product_amount').val('0');
				$('#formulaid').val(resp.formula_id);
				CKEDITOR.instances['product_desc'].setData(resp.product_desc);
				CKEDITOR.instances['product_spec'].setData(resp.product_spec);

				if(currency_id != curr){
					rate = parseFloat(resp.psalerate)/parseFloat(currency_rate);
				}else{
					rate = resp.psalerate;
				}
				$('#product_rate').val(rate.toFixed(2));
				$('#current_stock').css('display', 'block');
				$('#current_stock').html('Current Stock: '+resp.current_stock);
				load_product_unit(product_id,resp.product_base_unit);
				Unloading();	
				if(durva_permission==1)
				{
					add_accessories_data();
				}
			}
		});	
	}
	if(product_attr!='2'){
		$('#projectItem').css('display','none');
	}else{
		$('#projectItem').css('display','block');
	}
}

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
			url: root_domain+ crm_domain +'app/quotation/',
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
			url: root_domain+crm_domain+'app/quotation/',
			data: { mode : "convert_qty",  type : type, base_qty:base_qty_hide, conv_qty:conv_qty_hide, product_id:product_id},
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

function load_inq_pro(inquiry_id,inquiry_name)
{
	
	if(inquiry_id) {
		var c_con_id = $('#c_con_id').val();
		var cust_id = $('#cust_id').val();
		var cust_stateid = $('#cust_stateid').val();
		var gst_type = $('#gst_type').val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/quotation/',
			data: { mode:"load_inq_pro", inquiry_id:inquiry_id, c_con_id:c_con_id, def_quot_subject:$("#inquiry_id option:selected").text(), cust_id: cust_id, cust_stateid: cust_stateid,gst_type:gst_type },
			success: function(response)
			{
				console.log(response);
				//var data=jQuery.parseJSON(response);
				$('#quot_subject').val(inquiry_name);
				show_data();	
				Unloading();						
			}
		});	
	}
}
function load_annex_content(an_id)
{

	var an_id = new Array();
	var selected = $('.anexureeee').select2("data");
	for (var i = 0; i <= selected.length-1; i++) {
	    an_id.push(selected[i].text);
	}
	//var an_id = $("#an_id").val();

		// Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/quotation/',
			data: { mode:"load_annex_content", an_id:an_id },
			success: function(response)
			{
				// console.log(response);
				// var resp=jQuery.parseJSON(response);
				// //Put Ckeditor DATA
				CKEDITOR.instances['quot_annex_content'].setData(response);
				// //Scroll To Bottom of the page
				// // animate to just above the select2, now with plenty of room below
				// // $('html, body').animate({
				// // 	scrollTop: $("#an_id").offset().top - 10
				// // }, 1000);
				// //$("html, body").animate({ scrollTop: $(document).height() }, 1000);
				// Unloading();						
			}
		});	
}
function view_cust_address(){
	var cust_id = $('#cust_id').val();
	if(!cust_id){
		toastr.warning("Customer Not Found!!!", "WARNING");
		$('html, body').animate({
			scrollTop: ($("#quotation_no").offset().top) - (160)
		}, 1000);
		$('#cust_id').select2('focus');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customer/',
		data: { mode:"view_cust_address", cust_id:cust_id },
		success: function(response)
		{
			//console.log(response);
			var resp=jQuery.parseJSON(response);	
			$('#preview_cust_address_modal').modal('show');
			$('#preview_cust_address_div').html(resp.resp_html);
		}
	});
}
function copy_address(add_cont){
	$('#preview_cust_address_modal').modal('hide');
	$('#quot_address').val(add_cont);
}

function copy_prev_quot_trn(prev_quotation_id){
	
	if(prev_quotation_id){
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/quotation/',
			data: { mode:"copy_prev_quot_trn", prev_quotation_id:prev_quotation_id, },
			success: function(response)
			{
				console.log(response);
				show_data();
			}
		});
	}
}
function load_item_det(req_product_id){
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode:"load_product_dtls", product_id:req_product_id },
		success: function(response)
		{
			//console.log(response);
			var resp=jQuery.parseJSON(response);
			$('#req_product_desc').val(resp.product_desc);
			$('#req_product_rate').val(resp.product_purchase_mst_rate);
			$('#req_unitid').select2("val",resp.product_base_unit);
			Unloading();						
		}
	});
}
function get_budget_amount(){
	var req_product_qty = parseFloat($("#req_product_qty").val());
	var req_product_rate = parseFloat($("#req_product_rate").val());
	if(req_product_qty && req_product_rate && req_product_qty!='0' && req_product_rate!='0')
	{
		var req_product_amount=parseFloat((req_product_qty)*(req_product_rate));
		$("#req_product_amount").val(parseFloat(req_product_amount).toFixed(2));
	}
	else {
		$("#req_product_amount").val(0);
	}
	get_budget_gtotal();
}

function get_budget_margin(type){
	var budget_margin_per = parseFloat($('#budget_margin_per').val());
	var budget_margin_amt = parseFloat($('#budget_margin_amt').val());
	var budget_trn_ttl = parseFloat($('#budget_trn_ttl').val());
	
	var disc=0;
	if(type=="amt") {
		disc=100*(budget_margin_amt)/(budget_trn_ttl);
		$('#budget_margin_per').val((disc).toFixed(2));
	}
	else if(type=="per") {
		disc=((budget_trn_ttl)*(budget_margin_per))/100;	
		$('#budget_margin_amt').val((disc).toFixed(2));
	}
	
	get_budget_gtotal();
}
function get_budget_gtotal()
{	
	var t=0;
	var input_amount=(document.getElementsByName('req_product_amount_ttl[]'));
	var cnt=input_amount.length;
	var total=0;
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=parseFloat(total)+parseFloat(t);
	}
	$("#budget_trn_ttl").val(parseFloat(total).toFixed(2));
	
	var budget_margin_amt = parseFloat($('#budget_margin_amt').val());
	if(budget_margin_amt>0){
		total=parseFloat(total)+parseFloat(budget_margin_amt);
	}
	$("#budget_trn_g_total").val(parseFloat(total).toFixed(2));
}
function add_budget_trn_data(){
	if(!$("#req_product_id").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#req_product_id").select2('focus');
		return false;
	}
	else if(!$("#req_product_qty").val()){
		toastr.warning("Enter Quantity", "ERROR");
		$("#req_product_qty").focus();
		return false;
	}
	else if(!$("#req_product_rate").val()){
		toastr.warning("Enter Rate", "ERROR");
		$("#req_product_rate").focus();
		return false;
	}
	
	var form_data = { 
		mode : "add_budget_trn_data",
		budget_trn_edit_id: $("#budget_trn_edit_id").val(),
		req_product_id: $("#req_product_id").val(), 
		req_product_desc: $("#req_product_desc").val(), 
		req_product_qty: $("#req_product_qty").val(), 
		req_product_rate: $("#req_product_rate").val(), 
		req_unitid: $("#req_unitid").val(), 
		req_product_amount: $("#req_product_amount").val(), 
		quot_trn_id: $("#quot_trn_id").val()
	};
	
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: form_data,
		success: function(response)
		{
			if(response.trim()=='-1'){
				toastr.warning("Item Already Exist!!!", "WARNING");
			}
			//console.log(response);
			$("#req_product_id").select2("val","");
			$("#req_product_desc").val("");
			$("#req_product_qty").val("");
			$("#req_product_rate").val("");
			$("#req_unitid").select2("val","");
			$("#req_product_amount").val("");
			$("#budget_trn_edit_id").val("");
			$('#addbudgetrow').val('Add');
			Unloading();
			show_budget_trn_data();
		}
	});
}

function show_budget_trn_data() {
	var quot_trn_id = $('#quot_trn_id').val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "show_budget_trn_data", quot_trn_id:quot_trn_id },
		success: function(resp){
			//console.log(resp);
			$('#quot_budget_trn_div').html(resp);
			Unloading();
			get_budget_amount();
		}		 
	}); 
}
function edit_budget_trn_data(quot_budget_trn_id)
{ 
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode:"edit_budget_trn_data", quot_budget_trn_id:quot_budget_trn_id },
		success: function(response)
		{
			//console.log(response);
			var resp = jQuery.parseJSON(response);
			$("#req_product_qty").focus();
			$("#req_product_id").select2("val",resp.req_product_id);
			$("#req_product_desc").val(resp.req_product_desc);
			$("#req_product_qty").val(resp.req_product_qty);
			$("#req_product_rate").val(resp.req_product_rate);
			$("#req_product_amount").val(resp.req_product_amount);
			$("#req_unitid").select2("val",resp.req_unitid);
			$("#budget_trn_edit_id").val(quot_budget_trn_id);
			$('#addbudgetrow').val('Update');
			Unloading();
		}
	});
}
function delete_budget_trn_data(quot_budget_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/quotation/',
			data: { mode:"delete_budget_trn_data", quot_budget_trn_id:quot_budget_trn_id },
			success: function(response)
			{
				//console.log(response);
				var resp=jQuery.parseJSON(response);
				var response=resp.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_budget_trn_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
$("#quotation_budget_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#quotation_budget_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	//Hide Form Submit Alert
	setFormSubmitting();
	
	$.ajax({
		cache:false,
		url: root_domain+'crm/app/quotation/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("BUDGET UPDATED SUCCESSFULLY", "SUCCESS");
				var referrer =  document.referrer;
				window.location=referrer;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#save').prop('disabled', false);
			$('#quotation_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function copy_master_bom_data(){
	var product_id = $('#product_id').val();
	var product_qty = $('#product_qty').val();
	var quot_trn_id = $('#quot_trn_id').val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode:"copy_master_bom_data", product_id:product_id, product_qty:product_qty, quot_trn_id:quot_trn_id },
		success: function(response)
		{
			//console.log(response);
			Unloading();
			show_budget_trn_data();
		}
	});	
}

function approv_quot(quotation_id,approve_status)
{
	var conf = confirm("Are Sure Want to change Quotation Authorize Status ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/quotation/',
			data: { mode:"approv_quot", quotation_id:quotation_id, approve_status:approve_status },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("QUOTATION STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				load_quotation_datatable();
				Unloading();
			}
		});	
	}
	
}

function open_approv_quot(quotation_id,quotation_no,inquiry_id){
	
	$('#preview_approval_hist_modal').modal('show');
	$('#apprv_ref_no').html(quotation_no);
	$('#ref_quotation_id').val(quotation_id);
	$('#inquiry_id').val(inquiry_id);
	load_quot_hist_datatable();
	show_attach_doc_data();
	load_party_po_dtl();
}

function open_qutation_print_option(quotation_id,quotation_no){
	//alert(quotation_id);
	//alert(quotation_no);
	$('#open_qutation_print_option').modal('show');
	$('#apprv_ref_no').html(quotation_no);
	$('#ref_quotation_id').val(quotation_id);
	//load_quot_hist_datatable();
	//load_party_po_dtl();
}

function add_apprv_hist(){
	
	var form_data = {
		mode:"add_apprv_hist",
		assign_user_ids:$('#assign_user_ids').val(),
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		quotation_id:$('#ref_quotation_id').val()
	};
	var status = 'Approved';
	if($('#approve_status').val() === '2'){
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: form_data,
		success: function(response)
		{
			if(response){
				$('#assign_user_ids').select2("val","");
				$('#approve_status').select2("val","0");
				$('#approve_remark').val("");
				load_quot_hist_datatable();
				load_quotation_datatable();
			} else {
				toastr.warning("You have already "+ status, "ERROR");
				$('#assign_user_ids').select2("val","");
				$('#approve_status').select2("val","0");
				$('#approve_remark').val("");
			}
		}
	});
	Unloading();
}

function load_quot_hist_datatable(){
	var quotation_id = $('#ref_quotation_id').val();
	
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
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+'crm/app/quotation/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_quot_hist_datatable" }, { "name": "quotation_id", "value": quotation_id }  );
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

function open_quot_email(quotation_id, cust_id){
	$('#send_email_modal').modal("show");
	$('#email_ref_id').val(quotation_id);
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "open_quot_email", quotation_id:quotation_id, cust_id:cust_id },
		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#to_email_id').val(obj.to_email_id);
			$('#ccemail_id').val("");
			$('#bccemail_id').val("");
			CKEDITOR.instances['email_content'].setData(obj.email_content);
			$('#email_subject').val(obj.email_subject);
			Unloading();
		}
	});
}

function open_mail_modal(quotation_id,cust_mail){
	$('#send_email_modal').modal("show");
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "get_mail_data", quotation_id:quotation_id },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			$('#to_email_id').val(cust_mail);
			$('#email_subject').val(obj.quot_subject);
			$('#quotation_id').val(quotation_id);
			CKEDITOR.instances['email_content'].setData(obj.quot_header);
			Unloading();
		}
	});
}

function open_mail_dir_modal(quotation_id,cust_mail,email_page_path=null){
	$('#send_email_via_quotation_dir_modal').modal("show");	

	Loading(true);
	
	$('#to_email_id_d').val(cust_mail);
	$('#email_page_path').val(email_page_path);
	$('#quotation_id_d').val(quotation_id);
	
	Unloading();
}

function send_quotation_whatsapp(quotation_id,cust_mobile,inquiry_id,email_page_path=null) {

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "send_quotation_whatsapp", quotation_id:quotation_id, cust_mobile:cust_mobile,inquiry_id:inquiry_id, email_page_path:email_page_path },
		success: function(response)
		{
			console.log(response);
			Unloading();
		}
	});
}

$("#send_email_add_d").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#send_email_add_d").valid()) {
		return false;
	} 
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#send_mail_btn_d').prop('disabled', true);
	var form_data=new FormData(form);
	// console.log(form_data);return false;
	$.ajax({
		cache:false,
		url: root_domain+'crm/app/quotation/',
		type: "POST",
		// data: { mode : "send_mail_quotation" , form_data:form_data},
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	return false;
			var arr = jQuery.parseJSON(response);			
			//console.log(arr);
			if(arr.msg == '1') {
				toastr.success("MAIL SENT SUCCESSFULLY", "SUCCESS");
				$('#send_email_via_quotation_dir_modal').modal('hide');
				load_quotation_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#send_mail_btn_d').prop('disabled', false);
			$('#send_email_add_d').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
	
});

function open_outlook(){
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}

	if(!$("#to_email_id").val()){		
		toastr.warning("Enter Mail Id", "ERROR");
		$("#to_email_id").select2('focus');
		return false;
	}

	var content = $("#email_content").val();
	window.location="mailto:"+$("#to_email_id").val()+"?subject="+$('#email_subject').val()+"&body="+$(content).text();

}

$("#send_email_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#send_email_add").valid()) {
		return false;
	} 
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#send_mail_btn').prop('disabled', true);
	var form_data=new FormData(form);
	//console.log(form_data);return false;
	$.ajax({
		cache:false,
		url: root_domain+'crm/app/quotation/',
		type: "POST",
		//data: { mode : "send_mail_quotation" , form_data:form_data},
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	return false;
			var arr = jQuery.parseJSON(response);			
			//console.log(arr);
			if(arr.msg == '1') {
				toastr.success("MAIL SENT SUCCESSFULLY", "SUCCESS");
				$('#send_email_modal').modal('hide');
				load_quotation_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#send_mail_btn').prop('disabled', false);
			$('#send_email_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_dfd_attch_field()
{
	/*var ext = $('#dfd_attch_file').val().split('.').pop().toLowerCase();
	if($.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
		toastr.warning("Only image type jpg/png/jpeg/gif is allowed", "ERROR");
		$("#dfd_attch_file").focus();
		return false;
	}*/

	if(!$("#dfd_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#dfd_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_dfd_attch_field");
	form_data.append('quotation_id', $("#eid").val());
	form_data.append("dfd_attch_file", document.getElementById('dfd_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#dfd_attch_file").val("").focus();
			$('#dfd_attch_btn').val('Add');
			Unloading();
			show_dfd_attach_data();
		}
	});
}

function show_dfd_attach_data() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "show_dfd_attach_data", quotation_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#dfd_attch_trn_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_dfd_attach_data(dfd_attach_id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/quotation/',
			data: { mode:"delete_dfd_attach_data", dfd_attach_id:dfd_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_dfd_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function copyToClipboard(element) {
	console.log(element);
	var $temp = $("<input>");
	$("body").append($temp);
  //$temp.val($(element).text()).select();
  $temp.val(element).select();
  document.execCommand("copy");
  $temp.remove();
}

function load_def_quotation_no(start_quotation_id){
	var prev_quotation_id = $("#prev_quotation_id").val();
	var quot_revise_type  = $('input[name="quot_revise_type"]:checked').val();

	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+'crm/app/quotation/',
		data: { mode : "load_def_quotation_no", start_quotation_id: start_quotation_id,prev_quotation_id:prev_quotation_id,quot_revise_type:quot_revise_type,eid : eid },

		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#quotation_no').val(obj.quot_no);
		}
	});
}

function terms_check_all(obj){
	$('.terms_checkbox').prop('checked', obj.checked);
}
/*
Code By Umair : 13-07-2021
Comment: Load Product Based On the Inquiry Type
START
*/
function load_inquiry_type_product(type, pro_search){
	var inquiry_type = $('#inquiry_type').val();
	$('#projectItem').css('display','none');
	if(inquiry_type){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/quotation/',
			data: { mode:"load_inquiry_type_product", inquiry_type:inquiry_type , pro_type: type , pro_search:pro_search},
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_id').empty().append(obj.product_list);
				/*$("#product_id").select2({
					width: '100%'
				});*/
				Unloading();
			}
		});
	}	
}

function load_project_item(){
	/*var branch_id = $('#branch_id').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}*/
	$('#add_project_wise_item_modal').modal('show');
	
	/*var eid = $('#eid').val();
	if(eid==''){
		add_project_data();
	}*/
	show_project_data();
}
function load_project_item_list(quotation_trn_id,project_assign_id){
	$('#add_project_wise_item_modal').modal('show');
	show_project_data_list(quotation_trn_id,project_assign_id);
}
function show_project_data_list(quotation_trn_id,project_assign_id)
{
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "load_tempoutward",project_assign_id:project_assign_id, quotation_trn_id: quotation_trn_id  },
		success: function(data){
			$('#sale_productdata').html(data);				
		}		
	});
}

function show_project_data()
{
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	var eid = $('#eid').val();
	var quotation_trn_id = $('#quotation_trn_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "load_tempoutward",project_assign_id:project_assign_id, inquiry_type : inquiry_type,eid : eid,quotation_trn_id: quotation_trn_id  },
		success: function(data){
			$('#sale_productdata').html(data);				
		}		
	});
}

function add_project_field(){
	if($("#project_product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#project_product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	if($("#project_product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if($("#branch_id").val()==="")
	{		
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	Loading();	
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "add_project_field",
		edit_id:$("#project_edit_id").val(),
		quotation_trn_id:$("#quotation_trn_id").val(),
		product_id:$("#project_product_id").val(),
		product_des:$("#project_product_des").val(),
		product_spec:$("#project_product_spec").val(),
		product_hsn_code:$("#project_product_hsn_code").val(),
		product_qty:$("#project_product_qty").val(),
		product_rate:$("#project_product_rate").val(),
		project_assign_id:$("#product_id").val(),
		inquiry_type:$("#inquiry_type").val(),
		branch_id:$("#branch_id").val(),
		inquiry_id:$('#project_inquiry_id').val(),
		formulaid:$("#project_formulaid").val(),
		eid:$('#eid').val()
	},
	success: function(response)
	{
		$("#project_product_id").select2("val","")
		$("#project_product_des").val("")
		$("#project_product_spec").val("")
		$("#project_product_hsn_code").val("")
		$("#project_product_qty").val("")
		$("#project_product_rate").val('')
		$("#project_edit_id").val('')
		$('#project_addrow').val('Add');
		$("#project_formulaid").val("");
		Unloading();
		show_project_data();
	}
});
}
function load_productdetail(val) {
	/*if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}*/
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			CKEDITOR.instances['project_product_des'].setData(obj.product_desc);
			CKEDITOR.instances['project_product_spec'].setData(obj.product_spec);	
			$('#project_product_hsn_code').val(obj.product_hsn);
			$('#project_product_rate').val(obj.product_sale_rate);

		}
	});
}
function edit_project_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "edit_project_data",  id : id},
		success: function(response)
		{
				//console.log(response)
				var data = jQuery.parseJSON(response);
				$("#project_product_id").select2("val",data.product_id)
				$("#project_product_hsn_code").val(data.product_hsn_code)
				$("#project_product_des").val(data.description)
				$("#project_product_qty").val(data.product_qty)
				$("#project_product_rate").val(data.product_rate)
				$("#project_formulaid").val(data.formulaid);
				$("#project_edit_id").val(id)
				$('#project_addrow').val('Update');
				CKEDITOR.instances['project_product_des'].setData(data.product_desc);
				CKEDITOR.instances['project_product_spec'].setData(data.product_spec);
				Unloading();
			}
		});
}
function delete_project_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/quotation/',
			data: { mode : "delete_project_data",  eid : id},
			success: function(response)
			{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_project_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
	}
}

function get_project_amount()
{	
	var product_qty = parseFloat($("#project_product_qty").val());
	var product_rate = parseFloat($("#project_product_rate").val());
	
	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		/*$("#product_amount").val(parseFloat(product_amount).toFixed(2));
		$("#product_total").val(parseFloat(product_amount).toFixed(2));*/
		if($("#project_formulaid").val()!="")//tax calculation
		{
			var formulaid=$("#project_formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/quotation/',
				data: { mode : "get_project_amount", product_amount:product_amount ,formulaid:formulaid },
				success: function(response)
				{
					var obj=jQuery.parseJSON(response);
					//$('#product_total').val(obj.product_total);
				}
			});
		}
	}
	else {
		//$("#product_amount").val(0);
	}
}
/* END */

function send_quotation_mail(quotation_id, status){
	var r= prompt(" Please enter the quotation subject.");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'crm/app/quotation/',
			data: { mode:"send_quotation_mail", quotation_id:quotation_id,  status:status, subject:r},
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("QUOTATION MAIL SENT SUCCESSFULLY", "SUCCESS");
					window.location=root_domain + crm_domain + 'quotation_list';
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

function send_quotation(quotation_id, quotation_no){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/quotation/',
		data: { mode : "send_quotation", quotation_id:quotation_id ,quotation_no:quotation_no },
		success: function(response)
		{
			console.log(response);
			// toastr.success("QUOTATION SEND SUCCESSFULLY", "SUCCESS");
			// var data=jQuery.parseJSON(response);
			// var response=data.res;
			if(response.status=="success"){
				toastr.success("QUOTATION SEND SUCCESSFULLY", "SUCCESS");
			}else{
				toastr.warning("NUMBER IS INVALID / SOMETHING WENT WRONG", "ERROR");
			}
		}
	});
}
function preview_cust_dtls(){
	var cust_id = $('#cust_id').val();
	if(cust_id){

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + administration_domain + 'app/customer/',
			data: { mode:"preview_cust_dtls", cust_id:cust_id },
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#preview_cust_dtls_modal1').modal('show');
				$('#preview_cust_dtls_div').html(obj.html_resp);
				$('#preview_cust_pro_div').html(obj.row);
				Unloading();
			}
		});
	} else {
		toastr.warning("Select Company First", "ERROR");
	}
}
function load_product_history(){
	//$('#preview_product_history_modal').modal('show');
	show_product_history_data();
}
function show_product_history_data(){
	var cust_id = $('#cust_id').val();
	var product_id = $('#product_id').val();
	var eid = $('#eid').val();
	if(cust_id == ''){
		toastr.warning("Select Company First", "ERROR");
		$("#cust_id").focus();
		return false;
	}
	if(product_id == ''){
		toastr.warning("Select Product First", "ERROR");
		$("#product_id").focus();
		return false;
	}
	if(product_id && cust_id){
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/quotation/',
			data: { mode : "load_product_history",product_id:product_id, cust_id:cust_id, eid:eid},
			success: function(data){
				$('#preview_product_history_modal').modal('show');
				$('#preview_product_history_div').html(data);				
			}		
		});
	}
}
/* END */
function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var product_category = '';
	var cat = '';
	
	if(comp_config.cat_wise_product_load==1){
		product_category = $("#cat_id").val();
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
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	load_cat_product('product_id', testData)
}

/* $('#product_id').select2({
	data: product_load(),
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
 */

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

	if(cust_id){
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain +'app/quotation/',
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
function get_tax_details_table(){
	
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
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "get_tax_details_table" , invoice_id:eid,cust_id:cust_id,addontax1:addontax1 },
		success: function(response)
		{

			var arr = JSON.parse(response);
			if(arr){
				$(".tax_details").html(arr.resp);
                //$(".gross").text(response);
            }
            get_symbol();
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
	var currency_id = $("#currency_id").val();
	var quot_type = $('input[name="quot_type"]:checked').val();;
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "get_invoice_total_tax",cust_id:$('#cust_id').val(),gross:$('.gross').text(),inv_total:$('#total').val(),invoice_id:eid,addontax1:addontax1,currency_id:currency_id,quot_type:quot_type},
		success: function(response)
		{
			console.log(response);
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
            get_symbol();
        }
    });
}
function update_total()
{
	var eid = $('#eid').val();
	var g_total = $('#g_total').val();
	var basic_total = $('#total').val();
	var branch_id = $('#branch_id').val();
	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	//var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	var gst=[];
	var addonsundry = {};
	
	var values = $("input.gst");
	$.each(values, function(key, value) {
		
		var new_key = this.name.match(/\d+/);
		gst[new_key] = $(this).val();
	});
	
	
	$.ajax({
		
		type:'POST',
		data:{ mode:'update_total' , invoice_id:eid, g_total:g_total , basic_total:basic_total , branch_id:branch_id,currency_id:currency_id,currency_rate:currency_rate , bill_sundry_tax:gst },
		url:root_domain + crm_domain +'app/quotation/',
		success:function(result)
		{
			console.log(result);
			//alert(result);
		}
		
	})
	
}
function get_sundry_label(sundry_id)
{
	//alert(sundry_id);
	
	$.ajax({
		
		type:'POST',
		url:root_domain + crm_domain +'app/quotation/',
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
	//alert(netamount);
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

	if(bill_sundry_value == 0)
	{
		Unloading();
		toastr.warning("Please Select Bill Sundry", "ERROR")
		return false;
	}else if(bill_sundry_amount == ''){
		Unloading();
		toastr.warning("Please insert Bill Sundry Amount", "ERROR")
		return false;
	}else{
		Loading(true);
		$.ajax({
			type: "POST",
			async: false,
			url:root_domain + crm_domain +'app/quotation/',
			data: { mode : "get_bill_sundry_details",sundry_ledger_id:bill_sundry_value,totalsundryexist:totalsundryexist,taxableamount:taxableamount,
			basic_amount:basic_amount,netamount:netamount,default_amount:bill_sundry_amount,invoice_id:eid,currency_enable:currency_enable,currency_id:currency_id,currency_rate:currency_rate,invoice_date:$('#invoice_date').val()},
			success: function(response)
			{

				var arr1 = JSON.parse(response);
				//console.log(arr1);
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
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"> </span></label>
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
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"> </span></label>
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
		Unloading();

	}
	
	Unloading();
	
}


function get_all_bill_sundry(invoice_id)
{
	
	$.ajax({
		
		type:'POST',
		url:root_domain + crm_domain +'app/quotation/',
		data:{ mode:'get_all_bill_sundry',invoice_id:invoice_id },
		success:function(response)
		{
			console.log(response);
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
	var cust_ledger_id = $("#cust_id").val();
	
	
	
	if(edit_id=='' || edit_id=='0')
	{
		
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);

		$('.'+id).remove();	
		get_invoice_total_tax();
		get_tax_details_table();
		get_gtotal();
	}
	else
	{
		
		$.ajax({
			
			type:'post',
			url:root_domain + crm_domain +'app/quotation/',
			data:{ mode : 'remove_sundry',edit_id:edit_id,ledger_id:ledger_id,cust_ledger_id:cust_ledger_id },
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
function add_inq_attch_field() {
	if(!$("#inq_attch_doc_name").val()){		
		toastr.warning("Enter Document Name", "ERROR");
		$("#inq_attch_doc_name").focus();
		return false;
	}
	if(!$("#inq_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#inq_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_inq_attch_field");
	form_data.append('inquiry_id', $("#inquiry_id").val());
	form_data.append('inq_attch_doc_name', $("#inq_attch_doc_name").val());
	form_data.append("inq_attch_file", document.getElementById('inq_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#inq_attch_doc_name").val("").focus();
			$("#inq_attch_file").val("");
			$('#inq_attch_btn').val('Add');
			Unloading();
			show_inq_attach_data();
		}
	});
}
function show_inq_attach_data() {
	var eid = $('#inquiry_id').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "show_inq_attach_data", inquiry_id:eid,modee:chkmode },
		success: function(resp){
			//console.log(resp);
			$('#inq_attch_trn_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_inq_attach_data(inq_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/inquiry/',
			data: { mode:"delete_inq_attach_data", inq_attach_id:inq_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_inq_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function load_party_po_dtl(){
	var quotation_id = $('#ref_quotation_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "load_party_po_dtl", quotation_id:quotation_id },
		success: function(resp){
			var resp=JSON.parse(resp);
			$('#mod_quot_comp_div_sec').html(resp.mod_quot_comp_div_sec);
			$('#mod_quot_pro_div_sec').html(resp.mod_quot_pro_div_sec);
			$('#mod_quot_doc_div_sec').html(resp.mod_quot_doc_div_sec);
		}		 
	});
}
function showproduct(){
	// product_type_sel = $('#product_type_sel').val();
	// if(!product_type_sel){
	// 	toastr.warning("Choose Product type!!!", "ERROR");
	// 	$('#product_type_sel').select2('focus');
	// 	return false;
	// }
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-product').modal('show');

	// $('#product_type').select2("val",$("#product_type_sel").val());
	// $("#product_type").trigger('change');
	// get_opening_balance('0');
	$("#product_add_type").val('quotation');
	//$("#ledger_name").focus();
}

function add_hsn_invoice(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_quotation');
	$("#hsn_name").focus();
}
function getrate(){
	var product_id = $('#product_id').val();
	var unit_id = $('#unitid').val();

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
function no_of_inquiry(inquiry_user_id){
	var user_id = inquiry_user_id.value;
	var inquiry_id = $("#eid").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode : "no_of_inquiry", user_id:user_id, inquiry_id:inquiry_id },
		success: function(response)
		{
			$('#no_of_inquiry').html("Number of Inquiry = "+response);
			Unloading();
		}
	});
}

function currency_change()
{
	if($('#currency_enable').is(":checked"))
	{
		$('.currency_div').show();
	}
	else
	{
		$('.currency_div').hide();
	}
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
	/*$('#currency_rate').val(rate);*/
}

function currency_rate_c(){
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}

function open_revise_quotation_history(quotation_id,start_quotation_id,quotation_no){
	$("#preview_revision_hist").modal("show");
	$("#quo_no").html(quotation_no);
	load_revision_data(quotation_id,start_quotation_id);
}

function load_revision_data(quotation_id,start_quotation_id){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/quotation/',
		data: { mode : "load_revision_data", quotation_id:quotation_id, start_quotation_id:start_quotation_id },
		success: function(response)
		{
			$('#quot_rev_histo').html(response);
			Unloading();
		}
	});
}

//////////////////////////////////////////////////Product load-harshil///////////////////////////////////////////////////////////////////////////////

function load_product_category_wise(product_category){
	//alert(product_category);
	var testData = [];
	var branch_id = $('#branch_id').val();
		
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#cat_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	}

	
	var inquiry_type=$("#inquiry_type").val();
	
	if(inquiry_type == 2)
	{
		$('#product_rate').attr('readonly', true);
	}
	else
	{
		
		$('#product_rate').attr('readonly',false);
	}
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&product_category='+product_category+'&type=crm_pro_type&search=crm_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			//console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
	load_cat_product('product_id', testData)	
	// return testData;
	// return testData;
	
	
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////Harshil  - 21-9-2022///////////////////////////////////////////////////////////////////
	
function open_accesorice_wise_product_list(id){
	
	
		var cust_id = $('#cust_id').val();
		get_statecode(cust_id);	
		//alert(cust_id);
		
		$.ajax({
			type: "POST",
			url: root_domain+ crm_domain +'app/quotation/',
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
	load_cat_product('acc_product_id_l', testData)	
	// return testData;
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
		quotation_id:$("#eid").val()
		
	};
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/quotation/',
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
		url: root_domain+ crm_domain +'app/quotation/',
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

function add_accessories_data()
{
	
	var inquiry_type = $('#inquiry_type').val();
	var product_id = $('#product_id').val();
	
	var eid = $('#eid').val();
	var branch_id = $('#branch_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation/',
		data: { mode : "add_accessories_data",product_id:product_id },
		success: function(data){
		//console.log(data);
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
		"sAjaxSource": root_domain + crm_domain +'app/quotation/',
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
	
function edit_data_accessories_product_pop(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/quotation/',
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
			url: root_domain+crm_domain+'app/quotation/',
			data: { mode : "delete_data_alternative_product_pop",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
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
		url: root_domain+crm_domain+'app/quotation/',
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
			url: root_domain + crm_domain + 'app/quotation/',
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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function add_attch_doc_field() {
	if(!$("#inq_attch_doc_name").val()){		
		toastr.warning("Enter Document Name", "ERROR");
		$("#inq_attch_doc_name").focus();
		return false;
	}
	if(!$("#inq_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#inq_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_attch_doc_field");
	form_data.append('inquiry_id', $("#inquiry_id").val());
	form_data.append('inq_attch_doc_name', $("#inq_attch_doc_name").val());
	form_data.append("inq_attch_file", document.getElementById('inq_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/quotation/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#inq_attch_doc_name").val("").focus();
			$("#inq_attch_file").val("");
			$('#inq_attch_btn').val('Add');
			Unloading();
			show_attach_doc_data();
		}
	});
}
function show_attach_doc_data() {
	var eid = $('#inquiry_id').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/quotation/',
		data: { mode : "show_attach_doc_data", inquiry_id:eid,modee:chkmode },
		success: function(resp){
			//console.log(resp);
			$('#attach_doc_detail').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_doc_attach_data(inq_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/quotation/',
			data: { mode:"delete_doc_attach_data", inq_attach_id:inq_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_attach_doc_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

//Maulik Start
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

function open_approv_quo1(){
	/*if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}*/
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
	var unitid = $('#unitid').val();
	var rate_unitid = $('#rate_unit_id').val();
	
	if(unitid == rate_unitid){
		var qty=$("#product_qty").val();
		var unit_show=$("#unit_show").text();		
	}else{
		var qty=$("#product_conv_qty").val();
		var unit_show=$("#convert_unit_show").text();
	}
	
	var trn_id=$("#edit_id").val();
	var product_name = $("#product_id").select2('data').text;
	$("#model_product_name").html(product_name+" --- "+qty +" "+unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);

	//alert();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/quotation/',
		data: { mode : "delivary_date_model_open",qty:qty,trn_id:trn_id},
		success: function(response)
		{
			$('#bs-po_dispatch_date-modal').modal('show');
			$("#date_des").html(response);
			if(trn_id == ''){
				$("#m_addrow").hide();
			}
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
		}
	});
}

function delivery_schedule(){
	var unitid = $('#unitid').val();
	var unit_wise = $('#unit_wise').val();
	
	if(unitid == unit_wise){
		var qty=$("#product_qty").val();
		var unit_show=$("#unit_show").text();		
	}else{
		var qty=$("#product_conv_qty").val();
		var unit_show=$("#convert_unit_show").text();
	}
	
	var trn_id=$("#edit_id").val();
	var product_name = $("#product_id").select2('data').text;
	$("#model_product_name").html(product_name+" --- "+qty +" "+unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);

	//alert();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/quotation/',
		data: { mode : "delivary_date_model_open",qty:qty,trn_id:trn_id},
		success: function(response)
		{
			$("#date_des").html(response);
			if(trn_id == ''){
				$("#m_addrow").hide();
			}
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
		}
	});
}

function load_unit_product(){
	var product_id = $("#product_id").val();
	var rate_unit = $("#rate_unit_id").val();
	var edit_id = $("#edit_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/quotation/',
		data: { mode : "load_product_unit", product_id : product_id, rate_unit : rate_unit, edit_id:edit_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$("#unit_wise").html(obj.unit_option);

		}
	});
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


function validate_dilivary_date(){
	var unitid = $('#unitid').val();
	var unit_wise = $('#unit_wise').val();

	if(unitid == unit_wise){
		var main_qty=$("#product_qty").val();
	}else{
		var main_qty=$("#product_conv_qty").val();
	}
	var total_delivery_qty=document.getElementsByName('delivery_qty[]');
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_delivery_qty.length;
	var grandtotal_delivery_qty=0;
	var count=$("#count").val();
	main_qty=parseFloat(main_qty).toFixed(4);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new=grandtotal_delivery_qty;
		grandtotal_delivery_qty_new=parseFloat(grandtotal_delivery_qty_new).toFixed(4);
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
	var total=parseFloat(grandtotal_delivery_qty).toFixed(4);
	
	if(parseFloat(total)>parseFloat(main_qty)){
		$("#m_addrow").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count); 
			var pending_qty=parseFloat(main_qty)-parseFloat(total);
			
			$("#mix_loose_material_table").append('<tr id="field'+count+'"><td class="text-center" style="vertical-align:center;"><input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'+count+'" name="delivery_date[]" placeholder="Delivery Date" onchange="qty_wise_date_validation('+count+');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control delivery_qty" id="delivery_qty'+count+'" name="delivery_qty[]" onchange="validate_dilivary_date();" placeholder="'+pending_qty+'" onkeyup="qty_wise_date_validation('+count+');" /></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('+count+');" id="fieldremove'+count+'"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="'+count+'" /></td></tr>')
			
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

/*function load_parent_cat(){
	var parent_id = $("#parent_cat_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry/',
		data: { mode : "load_parent_cat",parent_id :parent_id },
		success: function(response)
		{
			$("#cat_id").html(response);
		}
	});
}*/

function get_terms_detail(id){
	var tc_id = $("#ref_tc_id"+id).val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/quotation/',
		data: { mode : "get_terms_detail", tc_id : tc_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$("#tc_details"+id).val(obj.tc_details);
		}
	});
}

function load_trans_add(){
	var tc_id = $("#transid").val();
	var edit_id = $("#trans_add_ed").val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+ crm_domain +'app/sales_order/',
		data: { mode : "load_trans_add", tc_id : tc_id, edit_id:edit_id},
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
		url: root_domain+ crm_domain +'app/quotation/',
		data: { 
			mode : "add_gst_for_all_product", 
			gst_type : gst_type,
			edit_id:$("#edit_id").val(),
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




function calculate_orange(){

	var qty = 0;
	var orange = 0;
	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#orange").val() != ''){
		orange = $("#orange").val();
	}

	var orange_total = parseFloat(qty) *  parseFloat(orange);

	$("#orange_total").val(orange_total);

}





function calculate_mfg(){

	var qty = 0;
	var mfg = 0;
	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#mfg").val() != ''){
		mfg = $("#mfg").val();
	}

	var mfg_total = parseFloat(qty) *  parseFloat(mfg);

	$("#mfg_total").val(mfg_total);

}

function calculate_trading(){

	var qty = 0;
	var trading = 0;
	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#trading").val() != ''){
		trading = $("#trading").val();
	}

	var trading_total = parseFloat(qty) *  parseFloat(trading);

	$("#trading_total").val(trading_total);

}

function calculate_repairing(){

	var qty = 0;
	var repairing = 0;
	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#repairing").val() != ''){
		repairing = $("#repairing").val();
	}

	var repairing_total = parseFloat(qty) *  parseFloat(repairing);

	$("#repairing_total").val(repairing_total);

}

function calculate_other(){

	var qty = 0;
	var other = 0;
	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#other").val() != ''){
		other = $("#other").val();
	}

	var other_total = parseFloat(qty) *  parseFloat(other);

	$("#other_total").val(other_total);

}


function calculate_special_total(){
	var qty = 0;
	var orange = 0;
	var mfg = 0;
	var trading = 0;
	var repairing = 0;
	var other = 0;

	if($("#product_qty").val() != ''){
		qty = $("#product_qty").val();
	}

	if($("#orange").val() != ''){
		orange = $("#orange").val();
	}

	if($("#mfg").val() != ''){
		mfg = $("#mfg").val();
	}

	if($("#trading").val() != ''){
		trading = $("#trading").val();
	}

	if($("#repairing").val() != ''){
		repairing = $("#repairing").val();
	}

	if($("#other").val() != ''){
		other = $("#other").val();
	}

	var orange_total = parseFloat(qty) *  parseFloat(orange);
	var mfg_total = parseFloat(qty) *  parseFloat(mfg);
	var trading_total = parseFloat(qty) *  parseFloat(trading);
	var repairing_total = parseFloat(qty) *  parseFloat(repairing);
	var other_total = parseFloat(qty) *  parseFloat(other);

	$("#orange_total").val(orange_total);
	$("#mfg_total").val(mfg_total);
	$("#trading_total").val(trading_total);
	$("#repairing_total").val(repairing_total);
	$("#other_total").val(other_total);
}

function exportCsv() {
	var stage_id = $('#stage_id').val();
	var branch_id = "";
	var date=$('#rep_date').val();
	var approve_status=$('#approve_status_val').val();
	
	var url = root_domain +'generate_export?mode=quotation_list&date=' + encodeURIComponent(date) + "&approve_status=" + encodeURIComponent(approve_status) + "&stage_id=" + encodeURIComponent(stage_id) + "&branch_id=" + encodeURIComponent(branch_id);
	window.location.href = url;
}