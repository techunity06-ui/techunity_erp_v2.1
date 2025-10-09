$(document).ready(function() {
	Loading(true);
	load_counts();
	load_today_complain();
	load_total_complains();
	load_category_complaints();
	load_weekend_complaints();
	load_profit_loss_chart();
	Unloading();
});

function load_counts() 
{
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_report/',
		data: { mode : "get_counts" },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$('.live_complaint_cnt').text(obj.live_complain.total);
			$('.inst_done_cnt').text(obj.inst_done.total);
			$('.inst_pending_cnt').text(obj.inst_pending.total);
		}
	});
}

function load_today_complain()
{
	$('#today_complaints_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=today_complain_chart';

	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr.push({'y':parseInt(json[i].total_complaints),'label':json[i].state});
		}

		var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();

		var curDate = dd + '-' + mm + '-' + yyyy;

		loadPieChart('today_complaints_chart', "New Complaints till "+curDate, arr);
	});
}

function load_total_complains()
{
	$('#total_complaints_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=total_complains_chart';
	
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr.push({'y':parseInt(json[i].total_complaints),'label':json[i].state});
		}

		loadPieChart('total_complaints_chart', 'Total Complaints', arr);
		
	});
}

function load_category_complaints(cat_id = '', start_date = '', end_date = '')
{
	$('#category_complaints_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=category_complaints_chart&cat_id='+cat_id+'&start_date='+start_date+'&end_date='+end_date;
	
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr.push({'y':parseInt(json[i].total_complaints),'label':json[i].cat_prd});
		}

		loadPieChart('category_complaints_chart', 'Category wise Complaints', arr);
	});
}

function loadPieChart(id, labelText, data) {
	var chart = new CanvasJS.Chart(id, {
		animationEnabled: true,
		title: {
			text: labelText
		},
		data: [{
			type: "pie",
			startAngle: 240,
			yValueFormatString: ": ##",
			indexLabel: "{label} {y}",
			dataPoints: data
		}]
	});

	chart.render();
}

function load_employee_chart(emp_id)
{
	if(!emp_id){
		return false;
	}

	$('#employee_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=employee_chart&emp_id='+emp_id;
	
	$.getJSON(mainurl, function(json) {
		var startedarr = new Array();
		var notdonearr = new Array();
		var closedarr = new Array();
		for(var i=0;i<json.length;i++)
		{	
			startedarr.push({'y':parseInt(json[i].started),'label':json[i].month});
			notdonearr.push({'y':parseInt(json[i].not_done),'label':json[i].month});
			closedarr.push({'y':parseInt(json[i].completed),'label':json[i].month});
		}

		var chart = new CanvasJS.Chart("employee_chart", {
			animationEnabled: true,
			title:{
				text: "Employee Chart"
			},	
			axisY: {
				title: "Complaints",
				titleFontColor: "#4F81BC",
				lineColor: "#4F81BC",
				labelFontColor: "#4F81BC",
				tickColor: "#4F81BC"
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
				name: "Started",
				legendText: "Started",
				showInLegend: true, 
				dataPoints: startedarr
			},
			{
				type: "column",	
				name: "Not Done",
				legendText: "Not Done",
				axisYType: "secondary",
				showInLegend: true,
				dataPoints: notdonearr
			},
			{
				type: "column",	
				name: "Completed",
				legendText: "Completed",
				axisYType: "secondary",
				showInLegend: true,
				dataPoints: closedarr
			}]
		});
		chart.render();

		function toggleDataSeries(e) {
			if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
				e.dataSeries.visible = false;
			}
			else {
				e.dataSeries.visible = true;
			}
			chart.render();
		}
		
	});
}

function load_weekend_complaints(date_val = '', product_val = '')
{

	$('#weekend_complaints_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=weekend_complaints_chart&date_val='+date_val+'&product_val='+product_val;
	
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr.push({'y':parseInt(json[i].total_complaints),'label':json[i].complaint_date});
		}

		var chart = new CanvasJS.Chart("weekend_complaints_chart", {
			animationEnabled: true,
			title: {
				text: "Today's Service Report"
			},
			axisY: {
				title: "Complaints Closed",
			},
			data: [{
				type: "line",
				name: "CPU Utilization",
				connectNullData: true,
				dataPoints: arr
			}]
		});
		chart.render();
	});
}

function weekendchange()
{
	var productVal = $('#product').val();
	var weekdateVal = $('#weekdate').val();

	load_weekend_complaints(weekdateVal, productVal);
}

$('.show-date-picker').datepicker({
    format: "dd-mm-yyyy",
    todayHighlight: true,
    autoclose: true
});

$('#start_date').datepicker()
.on('changeDate', function(e) {
	var start_date = e.format(0,"dd-mm-yyyy");
	var end_date = $('#end_date').val();

	if(start_date == '') {
		var date = new Date();
		var month = date.getMonth() + 1;
		start_date = '01-'+month+'-'+date.getFullYear();
		$('#start_date').datepicker('setDate', start_date);
	}
	
    job_start_date = start_date.split('-');
    job_end_date = end_date.split('-');

    // var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
	// var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);
	
	var new_start_date = new Date(job_start_date[2], job_start_date[1], job_start_date[0]);
    var new_end_date = new Date(job_end_date[2], job_end_date[1], job_end_date[0]);

	$('#end_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
	
	if(end_date == '' || new_start_date > new_end_date) {
	    $('#end_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
	}

	changeCategoryVal();
});

$('#end_date').datepicker()
.on('changeDate', function(e) {
	var start_date = $('#start_date').val();
	var end_date = e.format(0,"dd-mm-yyyy");

	if(end_date == '') {
		$('#end_date').datepicker('setDate', start_date);
	}
});

function changeCategoryVal()
{
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var category = $('#category').val();

	load_category_complaints(category, start_date, end_date);
}

function load_profit_loss_chart()
{
	$('#profit_loss_chart').html('');
	var mainurl = root_domain+service_domain+'app/complaint_report/index.php?mode=profit_loss_chart';
	
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(key in json)
		{	
			var value = json[key];
			var setColor = (value < 0) ? '#DC3545' : '#28A745';
			arr.push({label: key, y: value, color: setColor});
		}
		var chart = new CanvasJS.Chart("profit_loss_chart", {	
		  title: {
		    text: "Profit Loss Chart"
		  },
		  data: [
		    {
		      type: "column",
		      dataPoints: arr
		    }					
		  ]
		});

		chart.render();
	});
}