$(document).ready(function() {
	Loading(true);	
	get_value();
	Unloading();
});

function get_value()
{
	Loading(true);
	$('#title_chart').html('');
        load_profit_loss();
        load_sales(); 
	load_purchase();
        //load_counts();
	//load_graph_acnt_receivable_aging();
	//load_graph_acnt_payable_aging();
	//load_graph_chartbudgetvariance();
	//load_graph_bankbalance();
	
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

function load_purchase() {
    
        $.ajax({
        type: "POST",
        url: root_domain+'app/finance_dashboard/',
        data: { mode : "load_incoming_bills"},
        success: function(response){
                //console.log(response);
                var data = JSON.parse(response);
                var chart = new CanvasJS.Chart("incoming_bills_chart", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true	
                    dataPointWidth: 40,
                    /*title:{
                            text: "Incoming Bills (Purchase Invoice)"
                    },*/
                    data: [
                    {
                        // Change type to "bar", "area", "spline", "pie",etc.
                        type: "column",
                        dataPoints: [
                                { label: "Apr",  y: 10000  },
                                { label: "May", y: 15000  },
                                { label: "june", y: 20000  },
                                { label: "july",  y: 17000  },
                                { label: "Aug",  y: 18000  },
                                { label: "Sep",  y: 16000  },
                                { label: "Oct",  y: 20000  },
                                { label: "Nov",  y: 15000  },
                                { label: "Dec",  y: 13000  },
                                { label: "Jan",  y: 9000  },
                                { label: "Feb",  y: 13000  },
                                { label: "Mar",  y: 7000  }
                        ]
                    }
                    ]
                });
                chart.render();
            }
        });
        Unloading();
}

function load_sales(){
        $.ajax({
            type: "POST",
            url: root_domain+'app/finance_dashboard/',
            data: { mode : "load_outgoing_bills"},
            success: function(response){
                var data = JSON.parse(response);
                var chart = new CanvasJS.Chart("outgoingbills_chart", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true	
                    dataPointWidth: 40,
                    /*title:{
                            text: "Outgoing Bills (Sales Invoice)"
                    },*/
                    data: [
                        {
                                // Change type to "bar", "area", "spline", "pie",etc.
                                type: "column",
                                dataPoints: [
                                        { label: "Apr",  y: 10000  },
                                        { label: "May", y: 15000  },
                                        { label: "june", y: 20000  },
                                        { label: "july",  y: 22000  },
                                        { label: "Aug",  y: 24000  },
                                        { label: "Sep",  y: 26000  },
                                        { label: "Oct",  y: 20000  },
                                        { label: "Nov",  y: 25000  },
                                        { label: "Dec",  y: 30000  },
                                        { label: "Jan",  y: 32000  },
                                        { label: "Feb",  y: 35000  },
                                        { label: "Mar",  y: 30000  }
                                ]
                        }
                    ]
                });
                chart.render();
            }
        });
        Unloading();
}

function load_graph_acnt_receivable_aging(){
	var chart = new CanvasJS.Chart("receivable_aging_chart", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Account Receivable Ageing"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "doughnut",
		radius: "100%", 
                innerRadius: "50%",
                showInLegend: "true",
                legendText: "{label} : {y}",
		dataPoints: [
			{ label: "0-30",  y: 10  },
			{ label: "31-60", y: 15  },
			{ label: "61-90", y: 25  },
			{ label: "91-121",  y: 30  },
                        { label: "121-above",  y: 30  }
		]
	}
	]
});
chart.render();
Unloading();
}

function load_graph_chartbudgetvariance(){

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
    $.ajax({
        type: "POST",
        url: root_domain+'app/finance_dashboard/',
        data: { mode : "load_bank_balance"},
        success: function(response){
                //console.log(response);
                var data = JSON.parse(response);
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
                            dataPoints: [
                                    { label: "30-09-2019",  y: 0  },
                                    { label: "31-12-2019", y: 0  },
                                    { label: "31-03-2020", y: 0  },
                                    { label: "30-06-2020", y: 0  },
                                    { label: "30-09-2020",  y: 30  }
                            ]
                        }
                    ]
                });
            chart.render();
            }
        });
Unloading();
}

function load_graph_acnt_payable_aging(){
	 var chart = new CanvasJS.Chart("payable_aging_chart", {
	theme: "light1", // "light2", "dark1", "dark2"
	animationEnabled: false, // change to true		
	title:{
		text: "Account Payable Ageing"
	},
	data: [
	{
		// Change type to "bar", "area", "spline", "pie",etc.
		type: "doughnut",
		radius: "100%", 
                innerRadius: "50%",
		indexLabelPlacement: "outside",
		dataPoints: [
			{ label: "0-30",  y: 10  },
			{ label: "31-60", y: 15  },
			{ label: "61-90", y: 25  },
			{ label: "91-121",  y: 30  },
                        { label: "121-above",  y: 30  }
		]
	}
	]
});
chart.render();
Unloading();
}

function load_profit_loss(){
	var chart = new CanvasJS.Chart("profit_loss_chart", {
	animationEnabled: true,
	/*title:{
		text: "Profit and Loss"
	},*/	
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
			{ label: "2018-2019", y: -26,color:'red' ,name:'Loss'},
			{ label: "2019-2020", y: 30,color: 'green',name:'Profit' },
			{ label: "2020-2021", y: 17,color:'green', name:'Profit'},
		]
	},
	// 
	]
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
