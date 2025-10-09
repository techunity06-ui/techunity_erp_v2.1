$(document).ready(function() {
	Loading(true);	
	get_value();
	Unloading();
});

function get_value()
{
	Loading(true);
	$('#title_chart').html('');
	load_graph(); 
	load_graph_outgoing_bills();
	load_graph_acnt_receivable_aging();
	load_graph_acnt_payable_aging();
	load_graph_chartbudgetvariance();
	load_graph_bankbalance();
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
	var str3;
	var str2 ;
	for (var i = 0; i < e.entries.length; i++){
		var str1 = "<span style= \"color:"+e.entries[i].dataSeries.color + "\">" + e.entries[i].dataSeries.name + "</span>: <strong>"+  e.entries[i].dataPoint.y + "</strong> <br/>" ;
		total = e.entries[i].dataPoint.y + total;
		str = str.concat(str1);
	}
	str2 = "<strong>" + e.entries[0].dataPoint.label + "</strong> <br/>";
	str3 = "<span style = \"color:Tomato\">Total: </span><strong>" + total + "</strong><br/>";
	return (str2.concat(str)).concat(str3);
}


function load_graph()
{
	 var chart = new CanvasJS.Chart("chartContainer", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Incoming Bills (Purchase Invoice)"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-01-2020",  y: 56437  },
			{ label: "30-02-2020", y: 64772  },
			{ label: "23-03-2020", y: 76324  },
			{ label: "10-04-2020",  y: 76377  },
			{ label: "23-05-2020",  y: 88648  },
			{ label: "16-06-2020",  y: 19378  },
			{ label: "29-07-2020",  y: 64368  },
		]
	}
	]
});
	 
chart.render();
Unloading();
}


function load_graph_outgoing_bills()
{
	 /* var chart = new CanvasJS.Chart("chartContaineroutgoingbills", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Outgoing Bills (Sales Invoice)"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-09-2019",  y: 543672  },
			{ label: "30-11-2019", y: 743672  },
			{ label: "23-01-2020", y: 653287  },
			{ label: "31-05-2020",  y: 304372  },
			{ label: "31-05-2020",  y: 443522  },
			{ label: "31-05-2020",  y: 884676  },
			{ label: "31-05-2020",  y: 646568  },
			{ label: "31-05-2020",  y: 886543  },
		]
	}
	]
});
	 
chart.render();
Unloading(); */

var chart = new CanvasJS.Chart("chartContaineroutgoingbills", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Incoming Bills (Purchase Invoice)"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-01-2020",  y: 64378  },
			{ label: "30-02-2020", y: 37632  },
			{ label: "23-03-2020", y: 76324  },
			{ label: "10-04-2020",  y: 43676  },
			{ label: "23-05-2020",  y: 43774  },
			{ label: "16-06-2020",  y: 32783  },
			{ label: "29-07-2020",  y: 63676  },
		]
		
	}
	]
});
	 
chart.render();
Unloading();

}

function load_graph_acnt_receivable_aging()
{
	 var chart = new CanvasJS.Chart("chartAcntReciver", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Account Receivable Ageing"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-09-2019",  y: 43766  },
			{ label: "30-11-2019", y: 43674  },
			{ label: "23-01-2020", y: 25546  },
			{ label: "23-01-2020", y: 88467  },
			{ label: "23-01-2020", y: 94834  },
			{ label: "23-01-2020", y: 325437  },
			{ label: "31-05-2020",  y: 436237  },
		]
	}
	]
});
chart.render();
Unloading();
}


function load_graph_chartbudgetvariance()
{

	var chart = new CanvasJS.Chart("chartbudgetvariance", {
	animationEnabled: true,
	title:{
		text: "Budget Variance"
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
Unloading();
}


function load_graph_bankbalance()
{
	 var chart = new CanvasJS.Chart("chartbankbalance", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Bank Balance"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-09-2019",  y: 43766  },
			{ label: "30-11-2019", y: 43674  },
			{ label: "22-01-2020", y: 25546  },
			{ label: "23-02-2020", y: 88467  },
			{ label: "22-03-2020", y: 94834  },
			{ label: "24-04-2020", y: 325437  },
			{ label: "31-05-2020",  y: 436237  },
		]
	}
	]
});
chart.render();
Unloading();
}

function load_graph_acnt_payable_aging()
{
	 var chart = new CanvasJS.Chart("chartAcntPayable", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Account Payable Ageing"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "column",
		dataPoints: [
			{ label: "22-09-2019",  y:326323  },
			{ label: "30-11-2019", y: 243772  },
			{ label: "23-01-2020", y: 253232  },
			{ label: "23-01-2020", y: 937273  },
			{ label: "23-01-2020", y: 323567  },
			{ label: "23-01-2020", y: 332732  },
			{ label: "23-01-2020", y: 326748  },
			{ label: "31-05-2020",  y:832672  },
		]
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
		text: "Profit and Loss"
	},	
	axisY: {
		title: "Profit-Loss",
		titleFontColor: "#4F81BC",
		lineColor: "#4F81BC",
		labelFontColor: "#4F81BC",
		tickColor: "#4F81BC"
	},
	axisY2: {
		title: "Year",
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
		indexLabel: "{y}",
		showInLegend: true, 
		dataPoints:[
			{ label: "2018-2019", y: -26999,color:'red' ,name:'Loss'},
			{ label: "2019-2020", y: 173276,color: 'green',name:'Profit' },
			{ label: "2020-2021", y: 303277,color:'green', name:'Profit'},
		]
	},
	// 
	]
});
chart.render();

}
/*FOR MULTIPLE BAR GRAPH*/
// function multibarchart(){
// 	var chart = new CanvasJS.Chart("chartContainer3", {
// 	animationEnabled: true,
// 	title:{
// 		text: "Profit and Loss"
// 	},	
// 	axisY: {
// 		title: "Achived",
// 		titleFontColor: "#4F81BC",
// 		lineColor: "#4F81BC",
// 		labelFontColor: "#4F81BC",
// 		tickColor: "#4F81BC"
// 	},
// 	axisY2: {
// 		title: "Target",
// 		titleFontColor: "#C0504E",
// 		lineColor: "#C0504E",
// 		labelFontColor: "#C0504E",
// 		tickColor: "#C0504E"
// 	},	

// 	toolTip: {
// 		shared: true
// 	},
// 	legend: {
// 		cursor:"pointer",
// 		itemclick: toggleDataSeries
// 	},
// 	data: [{
// 		type: "column",
// 		name: "Target",
// 		legendText: "Target",
// 		indexLabel: "{y}",
// 		showInLegend: true, 
// 		dataPoints:[
// 			{ label: "2018-2019", y: -26 },
// 			{ label: "2019-2020", y: 30 },
// 			{ label: "2020-2021", y: 17 },
			

// 		]
// 	},
// 	{
// 		type: "column",
// 		name: "Target1",
// 		legendText: "Target1",
// 		indexLabel: "{y}",
// 		showInLegend: true, 
// 		dataPoints:[
// 			{ label: "2018-2019", y: 16 },
// 			{ label: "2019-2020", y: -300 },
// 			{ label: "2020-2021", y: 177 },
// 		]
// 	},
// 	{
// 		type: "column",	
// 		name: "Achieve",
// 		legendText: "Achieve",
// 		//axisYType: "secondary",
// 		indexLabel: "{y}",
// 		showInLegend: true,
// 		dataPoints:[
// 			{ label: "2018-2019", y: 26 },
// 			{ label: "2019-2020", y: -30 },
// 			{ label: "2020-2021", y: 17 },
			
// 		]
// 	}]
// });
// chart.render();

// }
