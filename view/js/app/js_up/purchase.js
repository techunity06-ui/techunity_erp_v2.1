//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data();	
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
			}
		}
	}); 
});
$("#po_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#po_add").valid()) {
		return false;
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
	
	var ex_name = [];
	
	$('.ex_name').each(function ()
		{
			ex_name.push($(this).val());
		});
		
		var ex_amount = [];
		
		$('.ex_amount').each(function ()
			{
				ex_amount.push($(this).val());
			});
			
			form_data.append('ex_name',ex_name);
			form_data.append('ex_amount',ex_amount);
			
			//console.log(form_data);
			$.ajax({
				cache:false,
				url: root_domain+'app/purchase/',
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
						toastr.success("PURCHASE ADDED SUCCESSFULLY", "SUCCESS");
						window.location=root_domain+'purchase_list';
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
						window.location=root_domain+'purchase_list';
					}
					$('#save').prop("disabled",false);
					$('#po_add').trigger('reset');	
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
			url: root_domain+'app/purchase/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response)
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
		/*if($("#product_discount").val()!="" )//discount calculation
			{	
			var discount=parseFloat($("#product_discount").val());
			a=a-discount; 
		}*/
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
	
	
}
function load_productdetail(pro_id) {
	var vender_id = $('#vender_id').val();
	if(vender_id==''){
		toastr.warning("Please Select Vender First","ERROR");
		$('#vender_id').select2('focus');
		return false;
	}
		
	var grn_id = $("#grn_id").val();
	if(grn_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
			data: { mode : "loadpurchase_productdata",product_id :pro_id, grn_id:grn_id },
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_des').val(obj.description);	
				$('#product_qty').attr("placeholder",obj.product_qty);
				$('#product_qty').attr("max",obj.product_qty);
				$('#product_rate').val(obj.product_rate);	
				$('#unitid').select2("val",obj.unit_id);
				$('#product_discount').val(obj.product_discount);
				$('#discount_per').val(obj.discount_per);
				$('#formulaid').val(obj.formulaid);
				get_amount();	
			}
		});
	}
	else{
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
			data: { mode : "load_productdata",eid :pro_id, vender_id : vender_id },
			success: function(response)
			{
				console.log(response);
				
				$("#rate_history").show();
				var obj =jQuery.parseJSON(response)
				//alert(obj.product_purchase_rate);
				$('#product_type').select2("val",obj.product_type);
				//$('#product_des').val(obj.product_des);				
				$('#product_hsn_code').val(obj.product_hsn);				
				$('#product_rate').val(obj.product_purchase_rate);				
				$('#unitid').select2("val",obj.product_base_unit);
				
				//load_last_rate(pro_id,obj.product_purchase_mst_rate);	
			}
		});
	}
	
}
function add_field()
{
	if(!$("#product_id").val()) {		
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val() || parseInt($("#product_qty").val())=='0') {		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#unitid").val()) {		
		toastr.warning("Select Unit", "ERROR");
		$("#unitid").select2('focus');
		return false;
	}
	else if(!$("#product_rate").val() || parseInt($("#product_rate").val())=='0') {		
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
	
	
	$('#addrow').attr("disabled",true);
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),grn_id:$("#grn_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),product_qty:$("#product_qty").val(),product_rate:$("#product_rate").val(),product_disc:$("#product_discount").val(),unit_id:$("#unitid").val(),formulaid:$("#formulaid").val(),product_discount:$("#product_discount").val(),discount_per:$("#discount_per").val(),product_amount:$("#product_amount").val(),taxable_value:$('#taxable_value').val(),sel_tax:$('#sel_tax').val(),po_id:$("#eid").val() },
		success: function(response)
		{
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#grn_id").select2("val","");
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#formulaid").val("");
			$("#discount_per").val("");
			$("#product_discount").val("");
			$("#product_qty").val("");
			$("#unit_id").select2('val',"");
			$("#product_rate").val('');
			$("#product_discount").val('');
			$("#sel_tax").val('');
			$("#taxable_value").val('');
			$("#product_amount").val('');
			$("#edit_id").val('');
			$('#addrow').val('Add');
			$('#addrow').attr("disabled",false);
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
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
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
		"sAjaxSource": root_domain+'app/purchase/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function show_data()
{
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
		data: { mode : "load_tempoutward", po_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#sale_productdata').html(resp);				
			Unloading();
			get_amount();
		}	
	});
}

function edit_data(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
		data: { mode : "preedit",  id : id },
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#grn_id").select2("val",data.grn_id);
			//$('#product_id').html(data.producthtml);
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			$("#product_id").select2("val",data.product_id);
			$("#product_des").val(data.description);
			$("#product_hsn_code").val(data.product_hsn_code);
			$("#product_qty").val(data.product_qty);
			$("#product_rate").val(data.product_rate);
			$("#product_disc").val(data.product_disc);
			$("#unitid").select2("val",data.unit_id);
			$("#sel_tax").val(data.sel_tax);
			$("#formulaid").val(data.formulaid);
			$("#product_amount").val(data.total);
			$("#product_discount").val(data.product_discount);
			$("#discount_per").val(data.discount_per);
			$("#taxable_value").val(data.product_amount);
			$("#edit_id").val(id);
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
			url: root_domain+'app/purchase/',
			data: { mode : "delete_data",  eid : id },
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
function load_last_rate(pro_id,mst_rate){
	
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
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
function load_ven_grn(vender_id){
	
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
			data: { mode : "load_ven_grn", vender_id : vender_id },
			success: function(response){
				//console.log(response);
				var resp = JSON.parse(response);
				$('#grn_id').html(resp.pro_html);
				//$('#trn_purchaseorder_id').select2('val','');
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
			url: root_domain+'app/purchase/',
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
			url: root_domain+'app/purchase/',
			data: { mode : "load_purchase_order", vender_id : vender_id },
			success: function(response){
				console.log(response);
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
			url: root_domain+'app/purchase/',
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
		url: root_domain+'app/purchase/',
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
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
			data: { mode : "load_rate_hist", vender_id : vender_id, product_id : product_id },
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
function load_product(type_id){
	var trn_purchaseorder_id = $("#trn_purchaseorder_id").val();
	if(trn_purchaseorder_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
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
			url: root_domain+'app/purchase/',
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
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
				//alert(response);
				
				console.log(response);
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
		url: root_domain+'app/qc_detail/',
		data: { mode : "show_mrn_details", qid : qid },
		success: function(data){
			alert(data);
			$('#mrn_div').html(data);
		}
	});
}

function load_purchase_srs_no(){
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase/',
		data: { mode : "load_purchase_srs_no" },
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
		url: root_domain+'app/purchase/',
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
		"sAjaxSource": root_domain+'app/purchase/',
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