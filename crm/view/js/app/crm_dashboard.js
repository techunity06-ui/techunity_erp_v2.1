$(document).ready(function() {
	Loading(true);	
	get_value();
	Unloading();
});

function get_value()
{
	Loading(true);
	$('#title_chart').html('');
	load_graph(); 
	load_disp_chart(); 
        load_target_chart();
	//load_pend_disp();
	//load_pend_task();
	Unloading();
}

function load_graph()
{
	$('#chart-3').html('');
	Loading(true);	
	var c_year=$('#c_year').val();
	var log_user_id=$('#log_user_id').val();
	var mainurl = root_domain+'crm/app/crm_dashboard/index.php?mode=dynamic_chart&c_year='+c_year+'&log_user_id='+log_user_id;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		for(var i=0;i<12;i++)
		{	
			arr[i]=json[i];	
		}
		Morris.Bar({
			element: 'chart-3',
			data: arr,
			barSizeRatio:0.55,
			xkey: 'device',
			ykeys: ['geekbench'],
			labels: ['Total Inquiry'],
			barRatio: 0.4,
			xLabelAngle: 35,
			hideHover: 'auto',
			barColors: ['#6883a3'],
			lineWidth:25
		});
	});
	Unloading();
}
function load_disp_chart()
{
	$('#chart-4').html('');
	Loading();	
	var c_year=$('#c_year').val();
	var disp_mon=$('#disp_mon').val();
	var disp_year=$('#disp_year').val();
	var log_user_id=$('#log_user_id').val();
	var mainurl = root_domain+'crm/app/crm_dashboard/index.php?mode=load_disp_chart&c_year='+c_year+'&disp_mon='+disp_mon+'&disp_year='+disp_year+'&log_user_id='+log_user_id;
	$.getJSON(mainurl, function(json) {
		//console.log(json);
		if(!json){
			$('#chart-4').html('<strong>No Pending Dispatch !!</strong>');
		}
		else{	
			Morris.Bar({
				element: 'chart-4',
				data: json,
				barSizeRatio:0.55,
				xkey: 'device',
				ykeys: ['geekbench'],
				labels: ['Total Pending'],
				barRatio: 0.4,
				xLabelAngle: 35,
				hideHover: 'auto',
				barColors: ['#6883a3'],
				lineWidth:25
			});
		}
	});
	Unloading();
}
function load_target_chart()
{
	$('#chart-5').html('');
	$('.title_chart1').html('');
	Loading();
	var t_pro_id=$('#t_pro_id').val();
	var t_pro_year=$('#t_pro_year').val();
	var t_pro_wise=$('#t_pro_wise').val();
	var log_user_id=$('#log_user_id').val();
	
	var mainurl = root_domain+'crm/app/crm_dashboard/index.php?mode=load_target_chart&t_pro_id='+t_pro_id+'&t_pro_year='+t_pro_year+'&t_pro_wise='+t_pro_wise+'&log_user_id='+log_user_id;
	
	$.getJSON(mainurl, function(json) {
		//console.log(json);
		if(!json){
			$('#chart-5').html('<strong>No Pending Dispatch !!</strong>');
		}
		else{
			var arr=new Array();
			for(var i=0;i<12;i++)
			{	
				arr[i]=[json[json[i]],json[i]];	
			}
			fil_arr=arr;
			$('#chart-5').jqBarGraph({
				data: fil_arr,
				colors: ['#6883a3','#3fc343',''],
				legends: ['Target','Achived',''],
				legend: true,
				width: 1100,
				color: '#ffffff',
				type: 'multi',
				postfix: '',
				showValues: true,
				title: '<h3 class="title_chart1">Target Chart</h3>'
			});
		}
	});
	Unloading();
}