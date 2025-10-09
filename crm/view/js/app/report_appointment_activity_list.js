//var datatable;
$(document).ready(function() {
	load_appointment_activity_list_datatable();
	generate_chart();
});	 
function clear_appointment_activity_list_report(){
	load_appointment_activity_list_datatable('clear');
	generate_chart('clear');
}
function generate_chart(clear = false)
{
	if(clear != 'clear'){
		var user_id = $("#user_id").val();
	}else{
		var user_id = '';
		$("#user_id").select2("val",'');
	}
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_appointment_activity_list&user_id='+user_id

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
		var chart = new CanvasJS.Chart("report_appointment_activity_list", {
				animationEnabled: true,
				theme: "light2", 
				axisX:{
				    
				},
				axisY:{
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
			var user_id = e.dataPoint.user_id;
			datatable = $("#ledger_appointment_activity").dataTable({
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
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+crm_domain +'app/report_appointment_activity_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "generate_appointment_activity_list" },
					{ "name": "user_id", "value": user_id } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();
		
		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
		//}
		}
	});	
}
function generate_report_product_service() 
{
	var cust_ind=$("#cust_ind").val();

	if(cust_ind!="")
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/report_appointment_activity_list/',
			data: { mode : "generate_appointment_activity_list",cust_ind:cust_ind},
			success: function(response)
			{
				if(response != "") {
					$('#adv-table').html(response);
					Unloading();
				}
											
			}
		});	
	}
}
function load_appointment_activity_list_datatable(clear = false){
	generate_chart();
	if(clear != 'clear'){
		var user_id = $("#user_id").val();
	}else{
		var user_id = '';
	}

	datatable = $("#ledger_appointment_activity").dataTable({
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain +'app/report_appointment_activity_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_appointment_activity_list" }
				,{ "name": "user_id", "value": user_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	//}
}

