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
    load_counts();
    get_five_customer();
    get_five_vendors();
    get_five_sold_products();
    get_five_purchased_products();
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
    var amount_filter = $('#pur_amount_filter').val();
    var purchase_filter = $('#purchase_filter').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "load_purchase", amount_filter : amount_filter, purchase_filter: purchase_filter},
        success: function(response){
                //console.log(response);
                var data = JSON.parse(response);
                var arr = new Array();
                for(var i=0; i< data.length; i++)
                {	
                    arr[i] = data[i],data[i];	
                }
                var chart = new CanvasJS.Chart("month_wise_purchase", {
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
                        dataPoints: arr
                    }
                    ]
                });
                chart.render();
            }
        });
    Unloading();
}

function load_sales(){
    var amount_filter = $('#amount_filter').val();
    var sales_filter = $('#sales_filter').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "load_sales", amount_filter : amount_filter, sales_filter: sales_filter},
        success: function(response){
            var data = JSON.parse(response);
            var arr1 = new Array();
            for(var i=0; i< data.length; i++)
            {	
                arr1[i] = data[i],data[i];	
            }
            var chart = new CanvasJS.Chart("month_wise_sales", {
                    theme: "light1", // "light2", "dark1", "dark2"
                    animationEnabled: false, // change to true	
                    dataPointWidth: 40,
                    data: [
                    {
                        type: "column",
                        dataPoints: arr1
                    }
                    ]
                });
            chart.render();
        }
    });
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
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "load_counts"},
        success: function(response){
                //console.log(response);
                var data = JSON.parse(response);
                $('#sales').html(data.sales);
                $('#sales_percentage').html(data.sales_percentage);
                $('#purchase').html(data.purchase);
                $('#purchase_percentage').html(data.purchase_percentage);
                $('#payable').html(data.outgoing_bills);
                $('#payable_percentage').html(data.outgoing_bills_percentage);
                $('#receivable').html(data.incoming_bills);
                $('#receivable_percentage').html(data.incoming_bills_percentage);
            }
        });
    Unloading();
}

function get_five_customer(){
    var cust_filter = $('#cust_filter').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "getcust", cust_filter : cust_filter},
        success: function(response){
                //console.log(response);
                $("#five_customer").html(response);
            }
        });
    Unloading();
}
function get_five_vendors(){
    var vendor_filter = $('#vendor_filter').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "getvendors", vendor_filter : vendor_filter},
        success: function(response){
                //console.log(response);
                $("#five_vendors").html(response);
            }
        });
    Unloading();
}
function get_five_sold_products(){
    var product_filter = $('#product_filter').val();
    var product_type = $('#product_type').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "getsold_products", product_filter : product_filter, product_type : product_type},
        success: function(response){
                //console.log(response);
                $("#sold_products").html(response);
            }
        });
    Unloading();
}
function get_five_purchased_products(){
    var product_filter = $('#purchase_product_filter').val();
    var product_type = $('#purchase_product_type').val();
    $.ajax({
        type: "POST",
        url: root_domain+'app/monitoring_dashboard/',
        data: { mode : "getpurchase_products", product_filter : product_filter, product_type : product_type},
        success: function(response){
                //console.log(response);
                $("#purchased_products").html(response);
            }
        });
    Unloading();
}
