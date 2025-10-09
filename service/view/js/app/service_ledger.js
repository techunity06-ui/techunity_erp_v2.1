$(document).ready(function() {
});

function report_ledger(cust_id = '') 
{
	var date=$("#rep_date").val();
	var g_id=$("#g_id").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/ledger/',
		data: { mode : "generate_report_ledger",date:date,g_id:g_id, cust_id: cust_id },
		success: function(response)
		{
			$('#data_table').html(response);
			Unloading();						
		}
	});
}

function report_ledger_detail() 
{
	Loading();
	var l_id = $("#l_id").val();
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
	$.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/ledger/',
		data: { mode : "report_ledger_detail", l_id : l_id, start_date: start_date, end_date: end_date},
		success: function(response)
		{
			$('#data_table').html(response);  
			Unloading();						
		}
	});
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

	report_ledger_detail();
});

$('#end_date').datepicker()
.on('changeDate', function(e) {
	var start_date = $('#start_date').val();
	var end_date = e.format(0,"dd-mm-yyyy");

	if(end_date == '') {
		$('#end_date').datepicker('setDate', start_date);
	}

	report_ledger_detail();
});