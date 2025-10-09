$(document).ready(function() {
	load_forecast_datatable(); 
	
	// validate vendor add form on keyup and submit
	$("#forecast_add").validate({
		rules: {
			t_id: {
				required: true
			},
			f_by_id: {
				required: true
			},
			f_year: {
				required: true
			},
			f_target_period: {
				required: true
			},
			f_period_id: {
				required: true
			},
			f_target_amt: {
				required: true
			},
			f_target_qty: {
				required: true
			}
			
		},
		messages: {
			t_id: {
				required: "Select Territory"
			},
			f_by_id: {
				required: "Select Forecast By"
			},
			f_year: {
				required: "Select Forecast Year"
			},
			f_target_period: {
				required: "Select Target Period"
			},
			f_period_id: {
				required: "Select Forecast Period"
			},
			f_target_amt: {
				required: "Enter Target Amount"
			},
			f_target_qty: {
				required: "Enter Target Qty"
			}
		}
	}); 
	
});
$("#forecast_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#forecast_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	$("#submit_btn").prop("disabled",true);		
	
	var form_data = new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/forecast/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			var resp=JSON.parse(response);
			var msg=resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("FORECAST ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'forecast_list';
			}
			else if(msg.trim() == '2') {				
				toastr.success("FORECAST UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'forecast_list';
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");		
			}
			$("#submit_btn").prop("disabled",false);	
			Unloading();
		}
	});
	
});
function delete_forecast(forecast_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/forecast/',
			data: { mode : "delete", forecast_id:forecast_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("FORECAST DELETED SUCCESSFULLY", "SUCCESS");
					load_forecast_datatable();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
			}
		});	
	}
	
}

function load_forecast_datatable(){
	var date= $('#rep_date').val();
	
	$("#forecast-datatable").dataTable({
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
		"sAjaxSource": root_domain+'app/forecast/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
}
function load_f_by_year(f_by_id){
	$.ajax({
		type: "POST",
		url: root_domain+'app/forecast/',
		data: { mode:"load_f_by_year", f_by_id:f_by_id },
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#f_year').html(resp.html_resp);
			$("#f_year").select2({
				width: '100%'
			});
		}
	});	
}
function load_f_period(){
	var f_by_id = $('#f_by_id').val();
	var f_target_period = $('#f_target_period').val();
	
	if(f_by_id && f_target_period){
		$.ajax({
			type: "POST",
			url: root_domain+'app/forecast/',
			data: { mode:"load_f_period", f_by_id:f_by_id, f_target_period:f_target_period },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#f_period_id').html(resp.html_resp);
				$("#f_period_id").select2("val","");
			}
		});	
	}
}