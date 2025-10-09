//var datatable;
$(document).ready(function() {
	generate_report();
	generate_chart();
});	
function generate_chart_report(){
	generate_report();
	generate_chart();
}
function quote_stage_report_filters() 
{
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_task_act_byowner/',
		data: { mode : "generate_report", date:date, t_id:t_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}

function clear_lead_by_source_report(){
	generate_report();
}

function generate_chart()
{
	var date=$('#rep_date').val();
	var user_id=$('#user_id').val();
	var task_type_id=$('#task_type_id').val();
	var task_status=$('#task_status').val();
	var task_rel_id = $('input:checkbox:checked.fil_chk').map(function(){ return this.value; }).get().join(",");
	
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_owner&rep_date='+date+'&user_id='+user_id+'&task_type_id='+task_type_id+'&task_status='+task_status+'&task_rel_id='+task_rel_id
		//alert(mainurl);
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		if(json== '' || json== null){
			arr1[i]='';
		}else{
			for(var i=0;i<json.length;i++)
			{	
				arr1[i]=json[i],json[i];	
			}
		}
		// console.log(arr1);
		var chart = new CanvasJS.Chart("report_task_act_byowner", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				data: [{        
					type: "column", 
					click: onClick, 
					dataPoints: arr1
				}]
		});
		//console.log(chart);
		chart.render();
		function onClick(e){
			var date=$('#rep_date').val();
			var task_sub_name = e.dataPoint.task_type_id;
			//alert(d_user_id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_task_act_byowner/',
				data: { mode : "generate_report",date:date,task_type_id:task_sub_name },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#adv-table').html(resp.html_resp);
				Unloading();							
			}
			});
		}
	});	
}

function generate_report(){
	var date=$('#rep_date').val();
	var user_id=$('#user_id').val();
	var task_type_id=$('#task_type_id').val();
	var task_status=$('#task_status').val();
	var task_rel_id = $('input:checkbox:checked.fil_chk').map(function(){ return this.value; }).get().join(",");

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_task_act_byowner/',
		data: { mode : "generate_report", date:date, user_id:user_id, task_type_id:task_type_id , task_status:task_status, task_rel_id:task_rel_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}


