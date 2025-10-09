//var datatable;
$(document).ready(function() {
	product_load();
	generate_report();
	generate_chart();
});	
function generate_chart_report(){
	generate_report();
	generate_chart();
}
function clear_lead_by_source_report(){
	//$("#product_id").val('null');
	//$("select").each(function(){ $(this).find('option[value="'+$(this).attr("value")+'"]').prop('selected', true); });
	$('#product_id').select2("val", "null");
	generate_report();
	generate_chart();
}

function generate_chart()
{
	var user_id=$('#user_id').val();
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	//alert(t_id);
	var product_id=$('#product_id').val();
	var country_id = $("#c_add_country").val();
	var state_id = $("#c_add_state").val();
	var city_id = $("#c_add_city").val();
	//alert(product_id);
	var mainurl = root_domain + crm_domain +'app/report_inq_pro/index.php?mode=generate_report_inq_pro&rep_date='+date+'&t_id='+t_id+'&product_id='+product_id+'&user_id='+user_id+'&country_id='+country_id+'&state_id='+state_id+'&city_id='+city_id
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
		var chart = new CanvasJS.Chart("report_task_act_byowner", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				axisX:{
			       // title: "time",
			        interval:1
			      },
			      axisY:{
			      	interval:1
			       // title: "distance"
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
			var date=$('#rep_date').val();
			var product_name = e.dataPoint.product_name;
			var dproduct_id = e.dataPoint.product_id;
			var country_id = $("#c_add_country").val();
			var state_id = $("#c_add_state").val();
			var city_id = $("c_add_city").val();
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_inq_pro/',
				data: { mode : "generate_report", product_name:product_name,dproduct_id:dproduct_id,date:date,country_id:country_id,state_id:state_id,city_id:city_id },
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
	var user_id=$('#user_id').val();
	var t_id=$('#t_id').val();
	var product_id=$('#product_id').val();
	var country_id = $("#c_add_country").val();
	var state_id = $("#c_add_state").val();
	var city_id = $("#c_add_city").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_inq_pro/',
		data: { mode : "generate_report", date:date, user_id:user_id, t_id:t_id,  product_id:product_id,country_id:country_id,state_id:state_id,city_id:city_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
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