var datatable;
$(document).ready(function() {
	//show header
	var date = new Date();
	var month = date.getMonth() + 1;
	month = (month < 10) ? '0'+month : month;
	var year = date.getFullYear();
	var test = getDateHeader(month, year);
	$('.tempHead').remove();
	$('.showHeader .last').after(test);
	load_monthly_attendance_sheet_tbl();
	load_summary_report();
});

function load_monthly_attendance_sheet_tbl(date = '') {
	var datatable = $("#dynamic-table").dataTable({
			"bStateSave" : true,
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_monthly_attendance_sheet/',
			"fnServerParams": function ( aoData ) {
				aoData.push({ "name": "mode", "value": "fetch" }, { "name": "date", "value": date });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function load_summary_report(date = '')
{
	// console.log('dt', date);
	$('#load_summary_report').html('');
	var mainurl = root_domain + hrms_domain + 'app/hrms_monthly_attendance_sheet/index.php?mode=load_summary_report&date='+date;
	
	$.getJSON(mainurl, function(json) {
		var present = new Array();
		var absent = new Array();
		var leave = new Array();
		for(var i=0;i<json.length;i++)
		{	
			present.push({'y':parseInt(json[i].present),'label':json[i].month_date});
			absent.push({'y':parseInt(json[i].absent),'label':json[i].month_date});
			leave.push({'y':parseInt(json[i].leaves),'label':json[i].month_date});
		}

		var chart = new CanvasJS.Chart("load_summary_report", {
			animationEnabled: true,
			title: {
				text: "Summary"
			},
			legend: {
				cursor:"pointer",
				itemclick: toggleDataSeries
			},
			data: [
				{
					type: "line",
					showInLegend: true,
					legendText: "Present",
					connectNullData: true,
					dataPoints: present
				},
				{
					type: "line",
					showInLegend: true,
					legendText: "Absent",
					connectNullData: true,
					dataPoints: absent
				},
				{
					type: "line",
					showInLegend: true,
					legendText: "Leave",
					connectNullData: true,
					dataPoints: leave
				}
			]
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

function showGraphReport(date) {
	
	load_summary_report(date);
	load_monthly_attendance_sheet_tbl(date);

	var newDt = date.split("-");
	var newdata =getDateHeader(newDt[0], newDt[1]);
	$('.tempHead').remove();
	$('.showHeader .last').after(newdata);

}

function getDateHeader(month, year)
{
	var data = '';
	var start_date = '01-'+month+'-'+year;
	var start_time = new Date(year, (month-1), 1);
	var end_time = new Date(year, month, 0);

	for(var i=start_time; i<=end_time; i.setDate(i.getDate() + 1)) {
		var dt = moment(new Date(i)).format("DD ddd");
		data += '<th class="tempHead">'+dt+'</th>'
	}

	return data;
}