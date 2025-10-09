//var datatable;
$(document).ready(function() {
	product_load();
	load_products();
   var date = new Date();
   var firstDay =new Date(date.getFullYear(), date.getMonth(), 1); 
   var lastDay =new Date(date.getFullYear(), date.getMonth() + 1, 0); 

	$("#fromDate").datepicker({
       format: 'dd-mm-yyyy',
       
       autoclose: true,
   	}).on('changeDate', function (selected) {
       var minDate = new Date(selected.date.valueOf());
       $('#toDate').datepicker('setStartDate', minDate);
   });

   $("#toDate").datepicker({
       format: 'dd-mm-yyyy',

       autoclose: true,
   }).on('changeDate', function (selected) {
           var minDate = new Date(selected.date.valueOf());
           $('#fromDate').datepicker('setEndDate', minDate);
   });

   $('#fromDate').datepicker('setDate',firstDay);
   $('#toDate').datepicker('setDate', lastDay);
  
});


function populateEndDate() {
  var date2 = $('#dateStart').datepicker('getDate');
  date2.setDate(date2.getDate() + 1);
  $('#dateEnd').datepicker('setDate', date2);
  $("#dateEnd").datepicker("option", "minDate", date2);
}

function getpo(vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode :'getpendingpo',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#pos_id').html('');
				$('#pos_id').html(response);
				Unloading();
			}else{
				$('#items_id').html('');
			}
		}
	});	
}

function getpos(vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode :'getpo',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#pos_id').html('');
				$('#pos_id').html(response);
				Unloading();
			}else{
				$('#items_id').html('');
			}
		}
	});	

}

function getitemsbyvendorid(vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode :'getitemsbyvendorid',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#item_id').html('');
				$('#item_id').html(response);
				Unloading();
			}else{
				$('#item_id').html('');
			}
		}
	});	
}

function generate_report_payment_summary_data(){
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();
	var cust_id=$("#cust_id").val();
	var reporttype = $("input[name='reporttype']:checked").val();

	var product_id=$("#product_id").val();
	var to_po_date=$("#to_po_date").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
//if($("#withconv").prop('checked') == true){
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var formattype = $("input[name='formattype']:checked").val();

	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();

	var purchase_type_status = $("input[name='purchase_type_status']:checked").val();
	var po_status_id=$("#po_status_id").val();
	var purchase_type_id=$("#purchase_type_id").val();

	
	//pendingpurchasereport
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchasebillsummary/',
		data: { mode : report_wise,purchase_type_id:purchase_type_id,purchase_type_status:purchase_type_status,po_status_id:po_status_id,item_status:item_status,item_status_id:item_status_id,specific_vendor:specific_vendor,
			formattype:formattype,vendor_id:vendor_id,specific_item:specific_item,item_id:item_id,reporttype:reporttype,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},		
	  	success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_report_purchase_bill_summary_data(){
	
	var from_po_date=$("#fromDate").val();
	var to_po_date=$("#toDate").val();
	// alert(from_po_date);
	// alert(to_po_date);
	
	var report_wise = $("input[name='report_wise']:checked").val();
	var cust_id=$("#cust_id").val();
	var reporttype = $("input[name='reporttype']:checked").val();

	
	var product_id=$("#product_id").val();
	
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
//if($("#withconv").prop('checked') == true){
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var formattype = $("input[name='formattype']:checked").val();

	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();

	var purchase_type_status = $("input[name='purchase_type_status']:checked").val();
	var po_status_id=$("#po_status_id").val();
	var purchase_type_id=$("#purchase_type").val();

	
	//pendingpurchasereport
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchasebillsummary/',
		data: { mode : report_wise,purchase_type_id:purchase_type_id,purchase_type_status:purchase_type_status,po_status_id:po_status_id,item_status:item_status,item_status_id:item_status_id,specific_vendor:specific_vendor,
			formattype:formattype,vendor_id:vendor_id,specific_item:specific_item,item_id:item_id,reporttype:reporttype,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},		
	  	success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function getAllitemsbyvendoridjobcard (vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode :'getitemsbyvendoridjobcard',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#item_id').select2('val','');
				$('#item_id').html('');
				$('#item_id').html(response);
				Unloading();
			}else{
				$('#item_id').html('');
			}
		}
	});	
}

function getitems(purchaseorderid){
	//alert()
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode :'getpendingitems',purchaseorderid:purchaseorderid},		
	   success: function(response)
		{
			if(response != "") {
				$('#items_id').html(response);
				Unloading();
			}else{
				$('#items_id').html('');
			}
		}
	});	
}

function generate_follow_up_report(){

}

function generate_item_wise_pur_bil_report(argument) {
	var fromdate=$("#fromDate").val();
	var todate=$("#toDate").val();
	var pr_type=$('#product_type').select2("val");
	var pr_cat=$('#product_category').select2("val");
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var reporttype = $("input[name='reporttype']:checked").val();
	var product_id = $("#item_id").val();
	var vender_id = $("#vender_id").val();

	if(pr_type=="")
	{		
		if(pr_cat==''){
			toastr.warning("Enter Product Category", "ERROR")
			 return false;
		}
	}
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchasebillsummary/',
		data: { mode : reporttype,fromdate:fromdate,
			todate:todate,pr_type:pr_type,pr_cat:pr_cat,po_date_type:po_date_type,product_id:product_id,vender_id:vender_id},		
	   success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_report_price_list(){
	var report_wise = $("input[name='report_wise']:checked").val();
    var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,item_id:item_id,specific_item:specific_item,vendor_id:vendor_id,specific_vendor:specific_vendor,item_status_id:item_status_id,item_status:item_status},		
	    success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_report_product_order_withratesummary(){
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();
	var to_po_date=$("#to_po_date").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
//if($("#withconv").prop('checked') == true){
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,item_id:item_id,specific_item:specific_item,vendor_id:vendor_id,specific_vendor:specific_vendor,item_status_id:item_status_id,item_status:item_status,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date},		
	   
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_report_product_order_summary() 
{
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();
	
	
	var to_po_date=$("#to_po_date").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
//if($("#withconv").prop('checked') == true){
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,item_id:item_id,specific_item:specific_item,vendor_id:vendor_id,specific_vendor:specific_vendor,item_status_id:item_status_id,item_status:item_status,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date},		
	    success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_report_product_service_data() 
{
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();
	var cust_id=$("#cust_id").val();
	var product_id=$("#product_id").val();
	var to_po_date=$("#to_po_date").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
//if($("#withconv").prop('checked') == true){
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	//alert(po_date_type);
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},		
	    success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_follow_up_report() 
{
	var fromdate=$("#fromdate").val();
	var todate=$("#todate").val();
	var vendor_id=$("#vendor_id").val();
	if($("#vendor_id").val()==="")
	{		
		toastr.warning("Enter Vendor First", "ERROR")
		$("#vendor_id").focus();
		return false;
	}
	var pos_id=$("#pos_id").val();
	var po_date_wise = $("input[name='po_date_wise']:checked").val();
	var items_id=$("#items_id").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");

	if (chkds.is(":checked")) {
		withconv=1
	} 
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : 'followupreport',fromdate:fromdate,
			todate:todate,vendor_id:vendor_id,pos_id:pos_id,items_id:items_id,
			withconv:withconv,po_date_wise:po_date_wise},		
	   
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}

function generate_purchase_order_status_report(){
	var fromdate=$("#fromdate").val();
	var todate=$("#todate").val();
	var vendor_id=$("#vendor_id").val();
	if($("#vendor_id").val()==="")
	{		
		toastr.warning("Enter Vendor First", "ERROR")
		$("#vendor_id").focus();
		return false;
	}
	var pos_id=$("#pos_id").val();
	var po_date_wise = $("input[name='po_date_wise']:checked").val();
	var items_id=$("#items_id").val();
	
	var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();

	var po_status=0;
	var po_status = $("input[name='po_status']:checked").val();
	var po_status_id=$("#po_status_id").val();
	var withconv=0;
	var chkds = $("input[name='withconv']:checkbox");
	if (chkds.is(":checked"))  {
		withconv=1
	} 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pendingpurchasereport/',
		data: { mode : 'purchaseorderstatusreport',fromdate:fromdate,
			todate:todate,vendor_id:vendor_id,po_status:po_status,po_status_id:po_status_id,pos_id:pos_id,items_id:items_id,
			po_date_wise:po_date_wise,item_status_id:item_status_id,item_status:item_status,},		
	   success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
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
	$('#item_id').select2({
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





