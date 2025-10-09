$(document).ready(function() {
	Loading(true);	
	get_value();
	Unloading();
});

function get_value()
{
	Loading(true);
	$('#title_chart').html('');
    load_counts();
	load_graph(); 
	load_graph_outgoing_bills();
	load_graph_chartbudgetvariance();
	load_graph_bankbalance();
	load_graph_acnt_receivable_aging();
	load_graph_acnt_payable_aging();
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


function load_graph() {
    
        var mainurl = root_domain+'app/finance_dashboard/index.php?mode=load_incoming_bills';
        $.getJSON(mainurl, function(json) {
            var arr=new Array();
            for(var i=0;i<json.length;i++)
            {	
                    arr[i]=json[i],json[i];	
            }
                var chart = new CanvasJS.Chart("incoming_bills_chart", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true		
                    title:{
                            text: "Incoming Bills (Purchase Invoice)",
                            horizontalAlign: "center",
                            fontStyle : "normal",
                            fontWeight: "bold",
                            fontFamily: "Arial",
                            fontSize: 15
                    },
                    data: [
                    {
                        // Change type to "bar", "area", "spline", "pie",etc.
                        type: "column",
                       // dataPoints: arr
					   dataPoints: [
									{ label: "Apr", y: 40000 },
									{ label: "May", y: 67600 },
									{ label: "Jun", y: 40780 },
									{ label: "Jul", y: 54405 },
									{ label: "Aug", y: 64564 },
									{ label: "Sep", y: 93246 },
									{ label: "Oct", y: 85546 },
									{ label: "Nov", y: 34543 },
									{ label: "Dec", y: 63477 },
									{ label: "Jan", y: 94432 },
									{ label: "Feb", y: 23877 },
									{ label: "Mar", y: 67458 },
								 ]
                    }
                    ]
                });
                chart.render();
        });
        Unloading();
}


function load_graph_outgoing_bills()
{
        var mainurl = root_domain+'app/finance_dashboard/index.php?mode=load_outgoing_bills';
        $.getJSON(mainurl, function(json) {
            var arr=new Array();
            for(var i=0;i<json.length;i++)
            {	
                    arr[i]=json[i],json[i];	
            }
                var chart = new CanvasJS.Chart("outgoingbills_chart", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true		
                    title:{
                            text: "Outgoing Bills (Sales Invoice)",
                            horizontalAlign: "center",
                            fontStyle : "normal",
                            fontWeight: "bold",
                            fontFamily: "Arial",
                            fontSize: 15
                    },
                    data: [
                        {
                                // Change type to "bar", "area", "spline", "pie",etc.
                                type: "column",
                               // dataPoints: arr
							   dataPoints: [
									{ label: "Apr", y: 5000 },
									{ label: "May", y: 20000 },
									{ label: "Jun", y: 40000 },
									{ label: "Jul", y: 30000 },
									{ label: "Aug", y: 9000 },
									{ label: "Sep", y: 80000 },
									{ label: "Oct", y: 8000 },
									{ label: "Nov", y: 45009 },
									{ label: "Dec", y: 64377 },
									{ label: "Jan", y: 6348 },
									{ label: "Feb", y: 80543 },
									{ label: "Mar", y: 70864 },
								 ]
                        }
                    ]
                });
                chart.render();
        });
        Unloading();
}

function load_graph_acnt_receivable_aging()
{
    var mainurl = root_domain+'app/finance_dashboard/index.php?mode=load_receivable_ageing';
    $.getJSON(mainurl, function(json) {
            var arr=new Array();
            for(var i=0;i<json.length;i++)
            {	
                    arr[i]=json[i],json[i];	
            }
            var chart = new CanvasJS.Chart("receivable_aging_chart", {
                theme: "light1", // "light2", "dark1", "dark2"
                animationEnabled: false, // change to true		
                title:{
                        text: "Account Receivable Ageing",
                        horizontalAlign: "left",
                        fontStyle : "normal",
                        fontWeight: "bold",
                        fontFamily: "Arial",
                        fontSize: 15
                },
                data: [
                {
                    // Change type to "bar", "area", "spline", "pie",etc.
                    type: "doughnut",
                    radius: "100%", 
                    innerRadius: "50%",
                    showInLegend: "true",
                    legendText: "{label} : {y}",
                    dataPoints: arr
					
                }
                ]
            });
            chart.render();
        });
    Unloading();
}


function load_graph_chartbudgetvariance()
{

	var chart = new CanvasJS.Chart("budgetvariance_chart", {
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
			{ label: "Dec", y: 101.50 }

		]
	}]
});
chart.render();
Unloading();
}


function load_graph_bankbalance() {
    var mainurl = root_domain+'app/finance_dashboard/index.php?mode=load_bank_balance';
    $.getJSON(mainurl, function(json) {
            var arr=new Array();
            for(var i=0;i<json.length;i++)
            {	
                    arr[i]=json[i],json[i];	
            }
            var chart = new CanvasJS.Chart("chartbankbalance", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true		
                    title:{
                            text: "Bank Balance"
                    },
                    data: [
                    {
                            // Change type to "bar", "area", "spline", "pie",etc.
                            type: "spline",
                            dataPoints: arr
                        }
                    ]
                });
            chart.render();
        });
Unloading();
}

function load_graph_acnt_payable_aging()
{
    var mainurl = root_domain+'app/finance_dashboard/index.php?mode=load_payable_ageing';
    $.getJSON(mainurl, function(json) {
            var arr=new Array();
            for(var i=0;i<json.length;i++)
            {	
                    arr[i]=json[i],json[i];	
            }
	var chart = new CanvasJS.Chart("payable_aging_chart", {
            theme: "light1", // "light2", "dark1", "dark2"
            animationEnabled: false, // change to true		
            title:{
                    text: "Account Payable Ageing",
                    horizontalAlign: "left",
                    fontStyle : "normal",
                    fontWeight: "bold",
                    fontFamily: "Arial",
                    fontSize: 15
            },
            data: [
            {
                    // Change type to "bar", "area", "spline", "pie",etc.
                    type: "doughnut",
                    radius: "100%", 
                    innerRadius: "50%",
                    indexLabelPlacement: "outside",
                    dataPoints: arr
            }
            ]
        });
        chart.render();
    });
Unloading();
}

function multibarchart(){
	var chart = new CanvasJS.Chart("profit_loss_chart", {
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
		name: "Income",
		legendText: "Income",
		indexLabel: "{y}",
		showInLegend: true, 
		dataPoints:[
			{ label: "2018-2019", y: 50 },
			{ label: "2019-2020", y: 90 },
			{ label: "2020-2021", y: 26 }
		]
	},
	{
		type: "column",	
		name: "Expense",
		legendText: "Expense",
		//axisYType: "secondary",
		indexLabel: "{y}",
		showInLegend: true,
		dataPoints:[
			{ label: "2018-2019", y: 30 },
			{ label: "2019-2020", y: 50 },
			{ label: "2020-2021", y: 30 }
		]
	},
        {
		type: "column",	
		name: "Profit/Loss",
		legendText: "Profit/Loss",
		//axisYType: "secondary",
		indexLabel: "{y}",
		showInLegend: true,
		dataPoints:[
			{ label: "2018-2019", y: 20 },
			{ label: "2019-2020", y: 40 },
			{ label: "2020-2021", y: -4 }
		]
	}]
});
chart.render();

}

function load_counts(){
    $.ajax({
        type: "POST",
        url: root_domain+'app/finance_dashboard/',
        data: { mode : "load_counts"},
        success: function(response){
                console.log(response);
                var data = JSON.parse(response);
                $('#outgoing_bills').html(data.outgoing_bills);
                $('#outgoing_bills_percentage').html(data.outgoing_bills_percentage);
                $('#incoming_bills').html(data.incoming_bills);
                $('#incoming_bills_percentage').html(data.incoming_bills_percentage);
                $('#incoming_payment').html(data.incoming_payment);
                $('#outgoing_payment').html(data.outgoing_payment);
        }
    });
    Unloading();
}
