$(document).ready(function() {
	load_forecast_report();
});
function load_forecast_report() {
	var financial_year_id = $('#financial_year_id').val();
	var forecast_type = $('#forecast_type').val();
	var forecast_month = $('#forecast_month').val();
	var branch_id = $('#branch_id').val();
	var f_user_id = $('#f_user_id').val();
	var f_product = $('#f_product').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/forecast_user_report/',
		data: { mode : "load_forecast_report", financial_year_id:financial_year_id, forecast_type:forecast_type, forecast_month:forecast_month, branch_id:branch_id, f_user_id:f_user_id, f_product:f_product},
		success: function(resp){
			//console.log(resp);
			$('#adv-table').html(resp);
			Unloading();
		}		 
	}); 
}
function load_f_period(){
	var f_by_id = $("#financial_year_id").find(':selected').attr("data-financial-type");
	var forecast_type = $('#forecast_type').val();
	
	if(f_by_id && forecast_type){
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/forecast_user/',
			data: { mode:"load_f_period", f_by_id:f_by_id, forecast_type:forecast_type },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#forecast_month').html(resp.html_resp);
				$("#forecast_month").select2("val","");
			}
		});	
	}
}