//var datatable;
$(document).ready(function() {
	product_load();
	generate_sales_order_due_date_report_data();
});

function generate_sales_order_due_date_report_data(){
	var rep_date = $('#rep_date').val();
	var product_id = $('#product_id').val();
	var cust_id = $('#cust_id').val();
	var user_id = $('#user_id').val();
	var sales_order_id = $('#sales_order_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/salesorderduedatewisereport/',
		data: { mode : 'sales_order_due_date_report', date : rep_date, cust_id: cust_id, product_id: product_id, user_id:user_id, sales_order_id:sales_order_id},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}
function product_load(){
	var testData = [];
	var inquiry_type='1';
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search';
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