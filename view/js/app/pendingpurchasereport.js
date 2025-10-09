//var datatable;
$(document).ready(function() {
	// $('.datepicker').datepicker({
 //        defaultDate: new Date(),
 //      //s  "autoclose": true
 //     });
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

function getpo(vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
		data: { mode :'getitemsbyvendorid',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#item_id').select2('val','');
				$('#item_id').html('');
				$('#item_id').html(response);
				Unloading();
			}else{
				$('#item_id').select2('val','');
				$('#item_id').html('');
			}
		}
	});	
}

function reporttype(val){
	if(val=='detail'){
		$("input:radio[name='formattype']").each(function(i) {
       this.checked = false;
});
	}
}

function generate_report_product_brief_data(){


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

	var po_status = $("input[name='po_status']:checked").val();
	var po_status_id=$("#po_status_id").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,po_status:po_status,po_status_id:po_status_id,item_status:item_status,item_status_id:item_status_id,specific_vendor:specific_vendor,
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
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
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

function generate_item_wise_report(argument) {
	var fromdate=$("#fromDate").val();
	var todate=$("#toDate").val();
	var pr_type=$('#product_type').select2("val");
	var pr_cat=$('#product_category').select2("val");
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	if(pr_type=="")
	{		
		if(pr_cat==''){
			toastr.warning("Enter Product Category", "ERROR")
		   // $("#product_type").focus();
		    return false;
		}
	}
	
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
		data: { mode : 'itemgroupwisereport',fromdate:fromdate,
			todate:todate,pr_type:pr_type,pr_cat:pr_cat,po_date_type:po_date_type},		
	   //	data: { mode : "vendorwisereport",date:date,cust_id:cust_id,product_id:product_id},
		//data: { mode : "itemwisereport",to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},
		//data: { mode : "withconv",withconv:withconv,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},
		
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
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
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
	//alert(item_id);
	Loading();
	//withconv
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
		data: { mode : report_wise,withconv:withconv,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},		
	   //	data: { mode : "vendorwisereport",date:date,cust_id:cust_id,product_id:product_id},
		//data: { mode : "itemwisereport",to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},
		//data: { mode : "withconv",withconv:withconv,to_po_date:to_po_date,from_po_date:from_po_date,product_id:product_id},
		
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
	var fromdate=$("#fromDate").val();
	var todate=$("#toDate").val();
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

	if (chkds.is(":checked"))  {
		withconv=1
	} 
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
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
		url: root_domain+'app/pendingpurchasereport/',
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





