$(document).ready(function() {
	load_dashbord_count();
	//load_po_and_purchase_diff();
	load_top_20_product();
	load_top_20_vender();
	load_top_20_cat();
	load_top_20_delay_product();
	load_target_chart();
	po_and_purchase_diff_new();
});
function load_dashbord_count()
{

	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_dashboard/',
		data: { mode : "dashbord_count"},
		success: function(response)
		{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#over_due_7days").html(obj.over_due_7days);
				$("#today_over_due_inword").html(obj.today_over_due_inword);
				$("#over_due_inworde").html(obj.over_due_inworde);
				Unloading();
			}
		});	
}


/*function load_po_and_purchase_diff()
{
	
	var mainurl = root_domain  +'app/purchase_dashboard/index.php?mode=load_purchase_po_com';
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		var arr2=new Array();
		//console.log(json);	
		for(var i=0;i<11;i++)
		{	
			var poa=json[i]+"po";
			var pura=json[i]+"pur";

			//arr1[i]=json[i],json[poa];
					arr1[i].push(json[poa]);
			arr2[i]=json[pura];
		}
		console.log(arr1);	
        if(arr1.length){
            var chart = new CanvasJS.Chart("chartContainer", {
             theme: "light2",
				title: {
				    text: "TOTAL PO AND PURCHASE"
				},
				subtitles: [{
				    text: ""
				}],
				toolTip: {
				    shared: true
				},
				
			data: [{
		        type: "stackedArea",
		        name: "PO",
		        showInLegend: true,
		        visible: true,
		        yValueFormatString: "#,##0 GWh",
		        dataPoints: arr1
		    },
		    {
		        type: "stackedArea",
		        name: "PURCHASE",
		        showInLegend: true,
		        yValueFormatString: "#,##0 GWh",
		        dataPoints: arr2
		    }]
		});
            chart.render();
        } else {
            $("#lead_by_city_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
        }
    });	
}*/

/*function load_po_and_purchase_diff()
{
	
	var d_start_date=$('#lead_by_product_start_date').val();
	var d_end_date=$('#lead_by_product_end_date').val();
	var d_user_id=$('#lead_by_product_user_id').val();
	//var c_year=$('#c_year3').val();
	
	var mainurl = root_domain + 'app/purchase_dashboard/index.php?mode=load_purchase_po_com&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id
	$.getJSON(mainurl, function(json1) {
		//console.log(json1);
		var arr1=new Array();
		var arr2=new Array();
		var array_used=new Array();
		var array_used2=new Array();
		
		for(var i=0;i<12;i++)
		{	
			var arr1_t=new Array();
			var arr1_t6=new Array();
			var poa =json1[i]+'po';
			var pura =json1[i]+'pur';
			arr1[i]=json1[poa];

			array_used[i]=arr1[i][0];
			arr2[i]=json1[pura];
			array_used2[i]=arr2[i][0];
		//console.log(arr1[i][0]);	
	}
	

		//console.log(array_used2);	
		var chart = new CanvasJS.Chart("chartContainer", {
			animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "TOP 20 VENDOR"
			},
			
			data: [{
				type: "stackedArea",
				name: "PO",
				showInLegend: true,
				visible: true,
				yValueFormatString: "#,##0 GWh",
				dataPoints: arr
			},
			{
				type: "stackedArea",
				name: "PURCHASE",
				showInLegend: true,
				yValueFormatString: "#,##0 GWh",
				dataPoints: arr
			}]
		});
		chart.render();

	});	
}*/
function toggleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chart1.render();
}
function load_top_20_vender()
{
	var d_start_date=$('#lead_by_product_start_date').val();
	var d_end_date=$('#lead_by_product_end_date').val();
	var d_user_id=$('#lead_by_product_user_id').val();
	var vendor_filter=$('#vendor_filter').val();
	
	var mainurl = root_domain +purchase_domain+ 'app/purchase_dashboard/index.php?mode=top_20_vender&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id+'&vendor_filter='+vendor_filter;
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
		if(arr1.length){
			var chart = new CanvasJS.Chart("chartContainer20ven", {
				animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "TOP 20 VENDOR"
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
				indexLabel: "{label} - {y}%",
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

function load_top_20_product()
{
	var d_start_date=$('#lead_by_product_start_date').val();
	var d_end_date=$('#lead_by_product_end_date').val();
	var d_user_id=$('#lead_by_product_user_id').val();
	var product_filter=$('#product_filter').val();
	
	var mainurl = root_domain +purchase_domain+ 'app/purchase_dashboard/index.php?mode=top_20_product&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id+'&product_filter='+product_filter;
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
		if(arr1.length){
			var chart = new CanvasJS.Chart("chartContainer20product", {
				animationEnabled: true,
			//exportEnabled: true,
			title:{
				text: "TOP 20 PRODUCT"
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
				indexLabel: "{label} - {y}",
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

function load_top_20_cat()
{
	var d_start_date=$('#lead_by_product_start_date').val();
	var d_end_date=$('#lead_by_product_end_date').val();
	var d_user_id=$('#lead_by_product_user_id').val();
	var pur_cat_filter=$('#pur_cat_filter').val();
	
	var mainurl = root_domain +purchase_domain+ 'app/purchase_dashboard/index.php?mode=top_20_cat&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id+'&pur_cat_filter='+pur_cat_filter;
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		console.log(arr1);
		if(arr1.length){
			if(pur_cat_filter==0){
				var note = "Quantity";
			}else{
				var note = "Amount";
			}

			var chart = new CanvasJS.Chart("chartContainer20cat", {

			animationEnabled: true,
	
	title:{
		text:"TOP 5 Purchase Category"
	},
	axisX:{
		interval: 1
	},
	axisY2:{
		interlacedColor: "rgba(1,77,101,.2)",
		gridColor: "rgba(1,77,101,.1)",
		title: note
	},
	data: [{
		type: "bar",
		name: "companies",
		axisYType: "secondary",
		color: "#014D65",
		dataPoints:arr1
	}]
});
chart.render();
		} else {
			$("#lead_by_product_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
		}
	});	
}
function load_top_20_delay_product()
{
	var d_start_date=$('#lead_by_product_start_date').val();
	var d_end_date=$('#lead_by_product_end_date').val();
	var d_user_id=$('#lead_by_product_user_id').val();
	//var c_year=$('#c_year3').val();
	
	var mainurl = root_domain +purchase_domain+ 'app/purchase_dashboard/index.php?mode=top_20_dealy_product&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
		if(arr1.length){
			var chart = new CanvasJS.Chart("chartContainerdealyitem", {
				animationEnabled: true,
		    //theme: "light2", // "light1", "light2", "dark1", "dark2"
		    title: {
		    	text: "Top 20 DELAY ITEM"
		    },
		    axisY: {
		    	title: "Number of Days"
		    },
		    data: [{
		    	type: "pie",
				radius: "100%",
				showInLegend: "true",
				legendText: "{label}",
				indexLabelFontSize: 16,
				//indexLabel: "{label} - #percent",
				indexLabel: "{label} - {y}",
		    	dataPoints: arr1
		    }]
		});
		chart.render();
		} else {
			$("#lead_by_product_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
		}
	});	
}


function detalils_view(type){
	if(type == '1'){
		window.location=root_domain+purchase_domain+'over_due_inward';
	}else if(type == '2'){
		window.location=root_domain+purchase_domain+'today_inward';
	}else{
		window.location=root_domain+inventory_domain+'overdue_po_pro_list';
	}
}

function load_target_chart()
{

	var mainurl = root_domain +purchase_domain+'app/purchase_dashboard/index.php?mode=load_target_chart';
	
	$.getJSON(mainurl, function(json) {
		console.log(json);
		
		var arr=new Array();
		var arr1=new Array();
		var arr2=new Array();
		var a="[";
		for(var i=0;i<json.length;i++)
		{	
			arr[i]=json[i];	
			
		}
		
	//	console.log(arr2);		
		var chart = new CanvasJS.Chart("chart-5", {
			animationEnabled: true,
			title:{
				text: "Top 20 Rate Difference"
			},	
			axisY: {
				title: "Price",
				titleFontColor: "#4F81BC",
				lineColor: "#4F81BC",
				labelFontColor: "#4F81BC",
				tickColor: "#4F81BC"
			},
			axisY2: {
				title: "Millions of Barrels/day",
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
				name: "Min Rate",
				legendText: "Min Rate",
				showInLegend: true, 
				dataPoints:arr[0]
			},
			{
				type: "column",	
				name: "Current Rate",
				legendText: "Current Rate",
				
				showInLegend: true,
				dataPoints:arr[2]
			},
			{
				type: "column",	
				name: "Higest Rate",
				legendText: "Higest Rate",
				
				showInLegend: true,
				dataPoints:arr[1]
			}
			]

		});
		chart.render();

})
}

function po_and_purchase_diff_new(){
var pur_amount_filter = $('#pur_amount_filter').val();
var mainurl = root_domain +purchase_domain+'app/purchase_dashboard/index.php?mode=load_purchase_po_com&pur_amount_filter='+pur_amount_filter;
	
	$.getJSON(mainurl, function(json) {
		
		
		var arr=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr[i]=json[i];
		}
//console.log(arr[0]);
	var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	exportEnabled: true,
	title:{
		text: "Total Po And Purchase"             
	}, 
	axisY:{
		title: "Total Amount"
	},
	toolTip: {
		shared: true
	},
	legend:{
		cursor:"pointer",
		itemclick: toggleDataSeries
	},
	data: [{        
		type: "spline",  
		name: "PO",        
		showInLegend: true,
		dataPoints: arr[0]
	}, 
	{        
		type: "spline",
		name: "PURCHASE",        
		showInLegend: true,
		dataPoints: arr[1]
	}]
});

chart.render();
	})
}