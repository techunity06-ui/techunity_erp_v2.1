$(document).ready(function() {
	
	Loading(true);	
	get_value();
	Unloading();
	$("#product_category").trigger('change');
	$("#product_id1").trigger('change');
	$("#product_id2").trigger('change');
	
});

function get_value()
{
	Loading(true);
	load_production_qty();
	load_yealy_production_report();
	// load_complet_vs_reject_report();
	$('#title_chart').html('');
	load_all_data_graph();
	load_graph(); 
	multicolchart();
	multibarchart();
	
	Unloading();
}

function toggleDataSeries(e) {
	if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else {
		e.dataSeries.visible = true;
	}
	chart.render();
}

function toolTipFormatter(e) {
	var str = "";
	var total = 0 ;
	var pending = 0 ;
	var str3;
	var str2 ;
	//alert(e.entries.length);
	for (var i = 0; i < e.entries.length; i++){
		//alert(e.entries[i].dataPoint.y);
		var str1 = "<span style= \"color:"+e.entries[i].dataSeries.color + "\">" + e.entries[i].dataSeries.name + "</span>: <strong>"+  e.entries[i].dataPoint.y + "</strong> <br/>" ;
		total = e.entries[i].dataPoint.y + total;
		// pending=e.entries[i].dataPoint.y;
		// if(i>0){
		// 	pending=pending-e.entries[i].dataPoint.y;
		// }
		str = str.concat(str1);
	}
	str2 = "<strong>" + e.entries[0].dataPoint.label + "</strong> <br/>";
	str3 = "<span style = \"color:Tomato\">Pending: </span><strong>" + e.entries[0].dataPoint.pending + "</strong><br/>";
	return (str2.concat(str)).concat(str3);
}

function productselect(){
	load_graph();
}
function getproducts(categoryid){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'getproducts',categoryid:categoryid},		
		success: function(response)
		{
			if(response != "") {
				$('#product_id').html('');
				$('#product_id').select2('val','');
				$('#product_id').html(response);
				Unloading();
			}else{
				$('#product_id').html('');
			}
			load_graph();
		}
	});	
}
function getproducts(categoryid){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'getproducts',categoryid:categoryid},		
		success: function(response)
		{
			if(response != "") {
				$('#product_id').html('');
				$('#product_id').select2('val','');
				$('#product_id').html(response);
				Unloading();
			}else{
				$('#product_id').html('');
			}
			load_graph();
		}
	});	
}

function onClick(e) {
	//window.location.replace("http://stackoverflow.com");
	alert(e.dataSeries.type + ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" );
}

function load_graph()
{
	var grpdataPoints=[];
	var woorderdataPoints=[];
	var product_id=$("#product_id").val();
	var product_category=$("#product_category").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'groupchart',product_category:product_category,product_id:product_id},		
		async:false,
		success: function(response)
		{
			// grpdataPoints=response;
			// alert('dd');
			var arr	= JSON.parse(response);
	        for (var i = 0; i < arr.length; i++) {
	      	  grpdataPoints.push({y:Number(arr[i]['y']),label:arr[i]['label']});
	        }
	   }
    });	
	// console.log(grpdataPoints);
	
	var chart = new CanvasJS.Chart("chartContainer", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Group vs Order"
	},
	data: [
	{
		type: "column",
		click: onClick,
		dataPoints:grpdataPoints
			// dataPoints: [
			// 	{ label: "MRP",  y: 104.00  },
			// 	{ label: "PO", y: 15  },
			// ]
		}
		]
	});
	chart.render();
	Unloading();
}


function multibarchart(){
	var chart = new CanvasJS.Chart("chartContainer3", {
		animationEnabled: true,
		title:{
			text: "Target vs Achived"
		},	
		axisY: {
			title: "Achived",
			titleFontColor: "#4F81BC",
			lineColor: "#4F81BC",
			labelFontColor: "#4F81BC",
			tickColor: "#4F81BC"
		},
		axisY2: {
			title: "Target",
			titleFontColor: "#C0504E",
			lineColor: "#C0504E",
			labelFontColor: "#C0504E",
			tickColor: "#C0504E"
		},	
		toolTip: {
			shared: true
		},
		legend: {
			cursor:"pointer",
			itemclick: toggleDataSeries
		},
		data: [{
			type: "column",
			name: "Target",
			legendText: "Target",
			click: onClick,
			indexLabel: "{y}",
			showInLegend: true, 
			dataPoints:[
			{ label: "Jan", y: 26 },
			{ label: "Feb", y: 302.25 },
			{ label: "Mar", y: 157.20 },
			{ label: "Apr", y: 148.77 },
			{ label: "May", y: 101.50 },
			{ label: "Jun", y: 97.8 },
			{ label: "Jul", y: 101.50 },
			{ label: "Aug", y: 101.50 },
			{ label: "Sep", y: 101.50 },
			{ label: "Oct", y: 101.50 },
			{ label: "Nov", y: 101.50 },
			{ label: "Dec", y: 101.50 },

			]
		},
		{
			type: "column",	
			name: "Achieve",
			legendText: "Achieve",
			click: onClick,
		//axisYType: "secondary",
		indexLabel: "{y}",
		showInLegend: true,
		dataPoints:[
		{ label: "Jan", y: 20 },
		{ label: "Feb", y: 30.25 },
		{ label: "Mar", y: 17.20 },
		{ label: "Apr", y: 48.77 },
		{ label: "May", y: 1.50 },
		{ label: "Jun", y: 97.8 },
		{ label: "Jul", y: 11.50 },
		{ label: "Aug", y: 10.50 },
		{ label: "Sep", y: 101.50 },
		{ label: "Oct", y: 11.50 },
		{ label: "Nov", y: 10.50 },
		{ label: "Dec", y: 101.50 },

		]
	}]
});
	chart.render();
}

function multicolchart(){
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var product_id = $('#product_id').val();
	var salesorderdataPoints=[];
	var woorderdataPoints=[];
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'graphsalesorderdata', product_id : product_id, start_date : start_date, end_date : end_date},		
		async:false,
		success: function(response)
		{
			//console.log(response);
			var arr	= JSON.parse(response);
			for (var i = 0; i < arr.length; i++) {
				salesorderdataPoints.push({y: arr[i]['y'], label:  arr[i]['label'],pending: arr[i]['pending']});
				woorderdataPoints.push({y: arr[i]['wo'], label:  arr[i]['label'],pending: arr[i]['pending'] });
			}
		}
	});	
	
	var chart = new CanvasJS.Chart("chartContainer2", {

		animationEnabled: true,
		title:{
			text: "Sale Order VS Work Order"
		},
		axisY: {
			title: "Orders",
			includeZero: true
		},
		legend: {
			cursor:"pointer",
			itemclick : toggleDataSeries
		},
		toolTip: {
			shared: true,
			content: toolTipFormatter
		},

		data: [{
			type: "bar",
			showInLegend: true,
			name: "Sales Order",
			color: "gold",
			dataPoints:salesorderdataPoints,
		 // dataPoints: [
		 // 	{ y: 24, label: "UPS 3200B" },
		 // 	{ y: 236, label: "UPS 3200A" },
		 // 	{ y: 260, label: "UBS 3200 MANUAL" },
		 // ]
	},
	{
		type: "bar",
		showInLegend: true,
		name: "Work Order",
		color: "silver",
		dataPoints:woorderdataPoints,
		 // dataPoints: [
		 // { y: 20, label: "UPS 3200B" },
		 // 	{ y: 200, label: "UPS 3200A" },
		 // 	{ y: 244, label: "UBS 3200 MANUAL" },
		 // ]
	},
	]
});
	chart.render();
	Unloading();
}


//pathik start
function getproducts_w(categoryid){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'getproducts',categoryid:categoryid},		
		success: function(response)
		{
			if(response != "") {
				$('#product_id1').html('');
				$('#product_id1').select2('val','');
				$('#product_id1').html(response);
				Unloading();
			}else{
				$('#product_id1').html('');
			}
			//load_graph();
		}
	});	
}
function getwork_order_w(){
	var product_id=$("#product_id1").val();
	var wo_sp_id=$("#wo_sp_id").val();
	//alert(product_id);
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/production_dashboard/',
			data: { mode :'get_work_order',product_id:product_id,sp_id:wo_sp_id},		
			success: function(response)
			{
				if(response != "") {
					$('#work_order_id1').html('');
					$('#work_order_id1').select2('val','');
					$('#work_order_id1').html(response);
					Unloading();
				}else{
					$('#work_order_id1').html('');
				}
				$('#work_order_id1').trigger('change');
				//load_graph();
			}
		});	
	}else{
		$('#work_order_id1').html('');
	}
}

function load_work_order_graph(){
	var work_order_id1=$('#work_order_id1').val();
	//alert(work_order_id1);
	//var c_year=$('#c_year3').val();
	var wono=$( "#work_order_id1 option:selected" ).text();
	//alert(wono);
	var mainurl = root_domain + production_domain +'app/production_dashboard/index.php?mode=load_work_order_status&work_order_id1='+work_order_id1
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
        if(arr1.length){
            var chart = new CanvasJS.Chart("lead_by_product_container", {
             animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "Work Order ("+wono+")"
			},
			/* subtitles: [{
				text: "Currency Used: Thai Baht (฿)"
			}], */
			data: [{
				click: function(e){
    location.href = root_domain+production_domain+'work_order_new_print/' + $('#work_order_id1').val();
   },
				type: "pie",
				radius: "100%",
				showInLegend: "true",
				legendText: "{label}",
				indexLabelFontSize: 16,
				//indexLabel: "{label} - #percent",
				indexLabel: "{label} - {y} %",
				//yValueFormatString: "฿#,##0",
				dataPoints: arr1
			}]
		});
            chart.render();
        } else {
            $("#lead_by_product_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
        }
    });
}

function getproducts_j(categoryid){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'getproducts',categoryid:categoryid},		
		success: function(response)
		{
			if(response != "") {
				$('#product_id2').html('');
				$('#product_id2').select2('val','');
				$('#product_id2').html(response);
				Unloading();
			}else{
				$('#product_id2').html('');
			}
			//load_graph();
		}
	});	
}

function getwork_order_j(){
	var product_id=$("#product_id2").val();
	var jc_rp_id = $("#jc_rp_id").val();
	//alert(product_id);
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/production_dashboard/',
			data: { mode :'get_job_work',product_id:product_id,rp_id:jc_rp_id},		
			success: function(response)
			{
				if(response != "") {
					$('#job_work_id').html('');
					$('#job_work_id').select2('val','');
					$('#job_work_id').html(response);
					Unloading();
				}else{
					$('#job_work_id').html('');
				}
				$('#job_work_id').trigger('change');
				//load_graph();
			}
		});	
	}else{
		$('#job_work_id').html('');
	}
}

function load_job_work_graph(){
	var job_work_id=$('#job_work_id').val();
	//alert(work_order_id1);
	//var c_year=$('#c_year3').val();
	var wono=$( "#job_work_id option:selected" ).text();
	//alert(wono);
	var mainurl = root_domain + production_domain +'app/production_dashboard/index.php?mode=load_job_work_status&job_work_id='+job_work_id
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
        if(arr1.length){
            var chart = new CanvasJS.Chart("lead_by_job_work_container", {
             animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "Job Card ("+wono+")"
			},
			/* subtitles: [{
				text: "Currency Used: Thai Baht (฿)"
			}], */
			data: [{
				type: "pie",
				radius: "100%",
				showInLegend: "true",
				legendText: "{label}",
				indexLabelFontSize: 16,
				//indexLabel: "{label} - #percent",
				indexLabel: "{label} - {y} %",
				//yValueFormatString: "฿#,##0",
				dataPoints: arr1
			}]
		});
            chart.render();
        } else {
            $("#lead_by_job_work_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
        }
    });
}

//pathik end

function load_production_qty(){
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/production_dashboard/',
			data: { mode :'get_production_qty_data'},		
			success: function(response)
			{
				var arr	= JSON.parse(response);
				$("#total_completed").empty().html(arr.completed);
				$("#total_pending").empty().html(arr.pending);
				$("#total_reject").empty().html(arr.reject);
				Unloading();
			}
		});	
}


function load_yealy_production_report(){
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/production_dashboard/',
			data: { mode :'get_production_yearly_data'},		
			success: function(response)
			{		
					
					var arr	= JSON.parse(response);
					
					var planning = arr.planning;
					var completed = arr.completed;
					var pending = arr.pending;
					var reject = arr.rejected;
					
					var chart = new CanvasJS.Chart("yealy_production_report", {
						animationEnabled: true,
						title:{
							text: "Yearly Production"
						},	
						axisY: {
							title: "QTY",
							titleFontColor: "#4F81BC",
							lineColor: "#4F81BC",
							labelFontColor: "#4F81BC",
							tickColor: "#4F81BC"
						},
						axisY2: {
							title: "Productivity",
							titleFontColor: "#C0504E",
							lineColor: "#C0504E",
							labelFontColor: "#C0504E",
							tickColor: "#C0504E"
						},	
						toolTip: {
							shared: true
						},
						legend: {
							cursor:"pointer",
							itemclick: toggleDataSeries
						},
						data: [{
							type: "column",
							name: "Planning",
							legendText: "Planning",
							showInLegend: true, 
							dataPoints:planning
						},{
							type: "column",
							name: "Completed",
							legendText: "Completed",
							showInLegend: true, 
							dataPoints:completed
						},
						{
							type: "column",
							name: "Pending",
							legendText: "Pending",
							showInLegend: true, 
							dataPoints:pending
						},
						{
							type: "column",	
							name: "Rejected",
							legendText: "Rejected",
							showInLegend: true,
							dataPoints: reject,
					}]
				});
				chart.render();
				Unloading();
			}
		});	
	// 1 - planning qty,  2-complete qty, 3-pending qty, 4 - rejected qty 

}

$("#rep_date").change(function(){

	if(isLoad == 0){
		isLoad = 1;
		load_complet_vs_reject_report();	
	}
	
});

$("#wo_rep_date").change(function(){
	if(wisLoad == 0){
		wisLoad = 1;
		load_workorder_piechart();	
	}	
});

function load_complet_vs_reject_report(){
	$('#complet_vs_reject_report').empty();
 $('.title_chart1').html('');
Loading();
var product_id = $("#product_ids").val();
var date=$('#rep_date').val();
 var mainurl = root_domain + production_domain +'app/production_dashboard/index.php?mode=get_complete_vs_reject&product_id='+product_id+'&date='+date;

 $.getJSON(mainurl, function(json) {
 	var arrlength = Object.keys(json).length;

 	if(arrlength >5){
 		arrlength = 5;
 	}else{
 		arrlength = arrlength / 2;
 	} 
		
		 if(jQuery.isEmptyObject(json)) {

			$('#complet_vs_reject_report').html('<strong>No Data !!</strong>');
		}
		else{
			var arr=new Array();

			for(var i=0;i<arrlength;i++)
			{	
				arr[i]=[json[json[i]],json[i]];	
			}
			fil_arr=arr;
			$('#complet_vs_reject_report').jqBarGraph({
				data: fil_arr,
				colors: ['#3fc343','#ef7774',''],
				legends: ['Competed','Rejected'],
				legend: true,
				width: 1100,
				color: '#ffffff',
				type: 'multi',
				postfix: '',
				showValues: true,
				title: '<h3 class="title_chart1">Completed VS Rejected</h3>'
			});
		}
	});
 Unloading();

}



function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		isLoad = 0;
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}


$('#product_ids').select2({
	data: product_load(),
	placeholder: 'search',
	multiple: true,
	// tags: true,
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



$('#wo_product_ids').select2({
	data: product_load(),
	placeholder: 'search',
	// multiple: true,
	// tags: true,
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


function load_workorder_piechart(){
	var product_id = $("#wo_product_ids").val();
	var date=$('#wo_rep_date').val();
	
	var mainurl = root_domain + production_domain +'app/production_dashboard/index.php?mode=load_workorder_piechart&product_id='+product_id+'&date='+date;
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
        if(arr1.length){
            var chart = new CanvasJS.Chart("workorder_piechart", {
             animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "Production Workorder Status Report"
			},
			
			data: [{
				type: "pie",
				radius: "100%",
				showInLegend: "true",
				legendText: "{label}",
				indexLabelFontSize: 16,
				//indexLabel: "{label} - #percent",
				indexLabel: "{label} - {y} %",
				//yValueFormatString: "฿#,##0",
				dataPoints: arr1
			}]
		});
            chart.render();
        } else {
            $("#workorder_piechart").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
        }
    });
}




function load_all_data_graph()
{
	var grpdataPoints=[];
	var woorderdataPoints=[];
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_dashboard/',
		data: { mode :'get_all_data_graph'},		
		async:false,
		success: function(response)
		{
			// grpdataPoints=response;
			// alert('dd');
			var arr	= JSON.parse(response);
	        for (var i = 0; i < arr.length; i++) {
	      	  grpdataPoints.push({y:Number(arr[i]['y']),label:arr[i]['label']});
	        }
	   }
    });	
	// console.log(grpdataPoints);
	
	var chart = new CanvasJS.Chart("all_data_report", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Production Report"
	},
	data: [
	{
		type: "column",
		click: onClick,
		dataPoints:grpdataPoints
			// dataPoints: [
			// 	{ label: "MRP",  y: 104.00  },
			// 	{ label: "PO", y: 15  },
			// ]
		}
		]
	});
	chart.render();
	Unloading();
}