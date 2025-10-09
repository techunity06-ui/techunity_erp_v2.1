$(document).ready(function() {
	Loading(true);	
	get_value();
	Unloading();
	$("#product_category").trigger('change');
});

function get_value()
{
	Loading(true);
	$('#title_chart').html('');
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
		url: root_domain+'app/production_dashboard/',
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
		url: root_domain+'app/production_dashboard/',
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
	console.log(grpdataPoints);
	
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
	var salesorderdataPoints=[];
	var woorderdataPoints=[];
	$.ajax({
		type: "POST",
		url: root_domain+'app/production_dashboard/',
		data: { mode :'graphsalesorderdata'},		
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
			//dataPoints:salesorderdataPoints,
		 dataPoints: [
		 	{ y: 24, label: "UPS 3200B" },
		 	{ y: 236, label: "UPS 3200A" },
		 	{ y: 260, label: "UBS 3200 MANUAL" },
		 ]
	},
	{
		type: "bar",
		showInLegend: true,
		name: "Work Order",
		color: "silver",
		//dataPoints:woorderdataPoints,
		 dataPoints: [
		 { y: 20, label: "UPS 3200B" },
		 	{ y: 200, label: "UPS 3200A" },
		 	{ y: 244, label: "UBS 3200 MANUAL" },
		 ]
	},
	]
});
	chart.render();
	Unloading();
}