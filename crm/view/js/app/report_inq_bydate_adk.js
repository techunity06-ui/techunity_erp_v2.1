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
		url: root_domain + crm_domain +'app/report_inq_bydate_adk/',
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
	var source_id=$('#source_id').val();
	var date=$('#rep_date').val();
	var t_id=0;
	var cust_id = $('#cust_id').val();
	var inquiry_status = $('#inquiry_status').val();
	var assign_user_id = $('#assign_user_id').val();
	var inquiry_id = $('#inquiry_id').val();
	var branch_id  = $('#branch_id').val();
	var industry_type = $('#industry_type').val();
	var country_id = $('#c_add_country').val();
	var state_id = $('#c_add_state').val(); 
	var city_id  = $('#c_add_city').val();
	var stage_id  = $('#stage_id').val(); 
	var sales_stage_id  = $('#sales_stage_id').val(); 
	var source_id  = $('#source_id').val(); 

	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report&rep_date='+date+'&t_id='+t_id+'&cust_id='+cust_id+'&inquiry_status='+inquiry_status+'&assign_user_id='+assign_user_id+'&inquiry_id='+inquiry_id+'&branch_id='+branch_id+'&industry_type='+industry_type+'&country_id='+country_id+'&state_id='+state_id+'&city_id='+city_id;
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
		var chart = new CanvasJS.Chart("report_inq_bydate", {
			animationEnabled: true,
			theme: "light2", // "light1", "light2", "dark1", "dark2"
			axisX:{
				// title: "time",
				 interval:2
				},
				data: [{        
				type: "column", 
				click: onClick, 
				dataPoints: arr1
			}]
		});
		//console.log(chart);
		chart.render();
		function onClick(e){
			var d_inquiry_date = e.dataPoint.inquiry_date;
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_inq_bydate_adk/',
				data: { mode : "generate_report", inquiry_date:d_inquiry_date, date:date, t_id:t_id, cust_id:cust_id, inquiry_status:inquiry_status, assign_user_id:assign_user_id, inquiry_id:inquiry_id,branch_id:branch_id,industry_type:industry_type,country_id:country_id,state_id:state_id,city_id:city_id,stage_id:stage_id, sales_stage_id:sales_stage_id,source_id:source_id },
			success: function(response)
			{
				$('#adv-table').html(response);
				Unloading();							
			}
			});
		}
	});	
}

function generate_report(){
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	var cust_id = $('#cust_id').val();
	var inquiry_status = $('#inquiry_status').val();
	var assign_user_id = $('#assign_user_id').val();
	var inquiry_id = $('#inquiry_id').val();
	var branch_id  = $('#branch_id').val();
	var industry_type = $('#industry_type').val();
	var country_id = $('#c_add_country').val();
	var state_id = $('#c_add_state').val(); 
	var city_id  = $('#c_add_city').val(); 
	var stage_id  = $('#stage_id').val(); 
	var sales_stage_id  = $('#sales_stage_id').val(); 
	var source_id  = $('#source_id').val(); 

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_inq_bydate_adk/',
		data: { mode : "generate_report", date:date, t_id:t_id, cust_id:cust_id, inquiry_status:inquiry_status, assign_user_id:assign_user_id, inquiry_id:inquiry_id,branch_id:branch_id,industry_type:industry_type,country_id:country_id,state_id:state_id,city_id:city_id,stage_id:stage_id,sales_stage_id:sales_stage_id,source_id:source_id },
		success: function(response)
		{
			//var resp=JSON.parse(response);
			//$('#adv-table').html(resp.html_resp);
			$('#adv-table').html(response);
			Unloading();							
		}
	});	
}

function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/customer/',
		data: { mode : "load_state",  id : parentid, stateid: val1},
		success: function(responce){
			$('#'+control).html(responce);
			// $("#"+control).select2("val",val1);
		}
	});	
}

function load_city(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}


