//var datatable;
$(document).ready(function() {
	load_ledger_datatable();
	load_funnel();
});	 
function quote_stage_report_filters() 
{
	var d_start_date=$('#quote_stage_start_date').val();
	var d_end_date=$('#quote_stage_end_date').val();
	var d_user_id=$('#quote_stage_user_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_quotes_stage_funnel/',
		data: { 
				mode : "generate_report_product_service",
				start_date : d_start_date,
				end_date : d_end_date,
				user_id : d_user_id
			},
		success: function(response)
		{
			if(response != "") {
				Unloading();
				load_ledger_datatable();
			}
										
		}
	});	
}

function clear_lead_by_source_report(){
	load_ledger_datatable();
}

function load_funnel()
{
	var d_start_date=$('#quote_stage_start_date').val();
	var d_end_date=$('#quote_stage_end_date').val();
	var d_user_id=$('#quote_stage_user_id').val();
	//alert("dsa");
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=load_funal&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		var chart = new CanvasJS.Chart("quote_stage_container", {
			animationEnabled: true,
			title: {
				text: ""
			},
			data: [{
				type: "funnel",
				click: onClick,
				indexLabel: "{label} - {y}",
				yValueFormatString: "#,##0",
				neckHeight: 0,
				dataPoints: arr1
			}]
		});
		chart.render();
		function onClick(e){
			var d_start_date=$('#quote_stage_start_date').val();
			var d_end_date=$('#quote_stage_end_date').val();
			var d_user_id=$('#quote_stage_user_id').val();
			var d_opp_id = e.dataPoint.id;
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_quotes_stage_funnel/',
				data: { 
						mode : "generate_report_product_service",
						start_date : d_start_date,
						end_date : d_end_date,
						user_id : d_user_id,
						opp_id : d_opp_id
					},
				success: function(response)
				{
					if(response != "") {
						Unloading();
						load_ledger_datatable(d_opp_id);
					}
												
				}
			});
		}
	});	
}

function load_ledger_datatable(d_opp_id = ""){
	var d_start_date=$('#quote_stage_start_date').val();
	var d_end_date=$('#quote_stage_end_date').val();
	var d_user_id=$('#quote_stage_user_id').val();
	
	var datatable = $("#ledger-table").dataTable({
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
		"aLengthMenu": [[-1,10, 20, 30, 50], ["All",10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain +'app/report_quotes_stage_funnel/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_product_service" },
				{ "name": "start_date", "value": d_start_date },
				{ "name": "end_date", "value": d_end_date },
				{ "name": "user_id", "value": d_user_id },
				{ "name": "opp_id", "value": d_opp_id }
			);
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

function exportCsv() {
	
	var d_start_date=$('#quote_stage_start_date').val();
	var d_end_date=$('#quote_stage_end_date').val();
	var d_user_id=$('#quote_stage_user_id').val();

	var url = root_domain +'generate_export?mode=stage_funnel&start_date=' + encodeURIComponent(d_start_date) + '&end_date=' + encodeURIComponent(d_end_date) + "&user_id=" + encodeURIComponent(d_user_id);
	window.location.href = url;
}

