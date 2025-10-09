$(document).ready(function() {
	stock_report_filter();
	product_load();
	load_products();
   /*var date = new Date();
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
   $('#toDate').datepicker('setDate', lastDay);*/
  
});

function populateEndDate() {
  var date2 = $('#dateStart').datepicker('getDate');
  date2.setDate(date2.getDate() + 1);
  $('#dateEnd').datepicker('setDate', date2);
  $("#dateEnd").datepicker("option", "minDate", date2);
}
//report_type get stock
function generate_stock_report(){

	/*var from_date=$("#fromDate").val();
	var to_date=$("#toDate").val();*/
	var date = $("#rep_date").val();
	var pr_type=$('#product_type').select2("val");
	var item_id=$('#product_id').val();
	var stock_value=$("#stock_value").val();
	
	var pr_cat=$('#product_category').select2("val");
	//report wise filter pass filter name product_type
	var report_wise = $("input[name='report_type']:checked").val();

	if(stock_value ==''){
		toastr.warning("Choose Stock Value", "ERROR")
	  	return false;
	}
	/*if(report_wise =="typewise")
	{		
		if(pr_type==''){
			toastr.warning("Enter Product Category", "ERROR")
		  	return false;
		}
	}*/
	/*var withconv=0;*/
	/*var chkds = $("input[name='withconv']:checkbox");
	if (chkds.is(":checked"))  {
		withconv=1;
	} */
	// console.log(report_wise)
	if(report_wise =="catgroupwise")
	{
		if($("#product_category").val() == ""){
			toastr.warning("Choose Product Category", "ERROR")
	  		return false;
		}
	}else if(report_wise =="typewise"){
		if($("#product_type").val() == ""){
			toastr.warning("Choose Product Type", "ERROR")
	  		return false;
		}
	}else{
		if(item_id == ""){
			toastr.warning("Choose Product", "ERROR")
	  		return false;
		}
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stockreport/',
		data: { mode : report_wise,stock_value:stock_value,item_id:item_id,pr_cat:pr_cat,pr_type:pr_type,date:date},		
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
function stock_report_filter(){
	var type = $("input[name=report_type]:checked").val();
	if(type == 'itemwisedetail'){
		$('.itemwisefil').show();
		$('.catgroupwisefil').hide();
		$('.typewisefil').hide();
		$('#product_id').select2("val","");
		$('#product_category').select2("val","");
		$('#product_type').select2("val","");
	}else if(type == 'catgroupwise'){
		$('.catgroupwisefil').show();
		$('.itemwisefil').hide();	
		$('.typewisefil').hide();
		$('#product_id').select2("val","");
		$('#product_category').select2("val","");
		$('#product_type').select2("val","");
	}else if(type == 'typewise'){
		$('.typewisefil').show();
		$('.catgroupwisefil').hide();
		$('.itemwisefil').hide();
		$('#product_id').select2("val","");	
		$('#product_category').select2("val","");
		$('#product_type').select2("val","");
	}
}