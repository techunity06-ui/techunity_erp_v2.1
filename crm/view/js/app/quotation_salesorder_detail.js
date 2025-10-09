$(document).ready(function() {
	 //cust_so_detail_report();
	product_load();
	cust_ledger();
});

function cust_ledger(){
	var mode = $("#report_mode").val();
	if(mode == 'quotation_mode'){
		$(".quo_cust").show();
		$(".so_ledger").hide();
	}else{
		$(".quo_cust").hide();
		$(".so_ledger").show();
	}
}

function cust_so_detail_report() {
	var rep_date 	=$("#rep_date").val();
	var mode 		= $("#report_mode").val();
	var crm_cust 	= $("#crm_cust_id").val();
	var ledger_id 	= $("#cust_id").val();
	var product_id 	= $("#product_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation_salesorder_detail/',
		data: { mode : mode, date: rep_date, crm_cust:crm_cust, ledger_id:ledger_id, product_id:product_id},		
	   success: function(response)
		{
			if(response != "") {
				$('#sales_order_detail').html(response);
				Unloading();
			}
		}
	});	
}

function product_load(){
	
	var testData = [];
	var inquiry_type=1;
	var product_category='';
	

	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search';
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