//var datatable;
$(document).ready(function() {
	product_load();
	generate_report();
	/*generate_chart();*/
});	
function generate_chart_report(){
	generate_report();
	/*generate_chart();*/
}
function quote_stage_report_filters() 
{
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_inq_bydate/',
		data: { mode : "generate_report", date:date, t_id:t_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}

function clear_lead_by_source_report(){
	generate_report();
}

function generate_chart()
{
	var source_id=$('#source_id').val();
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report&rep_date='+date+'&t_id='+t_id
		//alert(mainurl);
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		if(json== '' || json== null){
			arr1[i]='';
		}else{
			for(var i=0;i<json.length;i++)
			{	
				arr1[i]=json[i],json[i];	
			}
		}
		// console.log(arr1);
		var chart = new CanvasJS.Chart("report_inq_bydate", {
			animationEnabled: true,
			theme: "light2", // "light1", "light2", "dark1", "dark2"
			axisX:{
				// title: "time",
				 interval:2
				},
				data: [{        
				type: "column", 
				click: onClick, 
				dataPoints: arr1
			}]
		});
		//console.log(chart);
		chart.render();
		function onClick(e){
			var d_inquiry_date = e.dataPoint.inquiry_date;
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_inq_bydate/',
				data: { mode : "generate_report", inquiry_date:d_inquiry_date },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#adv-table').html(resp.html_resp);
				Unloading();							
			}
			});
		}
	});	
}

function generate_report(){
	var date=$('#rep_date').val();
	var cust_id = $('#cust_id').val();
	var product_category = $('#product_category').val();
	var product_id = $('#product_id').val();
	var quot_type = $('#quot_type').val();
	var approve_status = $('#approve_status').val();
	var quotation_id = $('#quotation_id').val();

	var branch_id  = $('#branch_id').val();
	var country_id = $('#c_add_country').val();
	var state_id = $('#c_add_state').val(); 
	var city_id  = $('#c_add_city').val(); 
	var user_id  = $('#user_id').val();

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation_report/',
		data: { mode : "generate_report", date:date, cust_id:cust_id, product_category:product_category, product_id:product_id, quot_type:quot_type, approve_status:approve_status, user_id:user_id, quotation_id:quotation_id, branch_id:branch_id, country_id:country_id, state_id:state_id, city_id:city_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}

function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "load_state",  id : parentid, stateid: val1},
		success: function(responce){
			$('#'+control).html(responce);
			// $("#"+control).select2("val",val1);
		}
	});	
}

function load_city(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
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

