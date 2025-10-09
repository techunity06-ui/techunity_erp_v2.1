//var datatable;
$(document).ready(function() {
	load_ledger_datatable();
	generate_chart();
});	 
// function generate_chart_report(){
// 	load_ledger_datatable();
// 	generate_chart();
// }
function clear_lead_by_source_report(){
	load_ledger_datatable();
}
function generate_chart()
{
	var source_id=$("#source_id").val();
	//alert(source_id);
	var date=$("#rep_date").val();
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_leads_executive&source_id='+source_id+'&date='+date
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
		var chart = new CanvasJS.Chart("report_leads_by_executive", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				axisX:{
				       // title: "time",
//				        interval:2
				      },
				axisY:{
                                    interval:5
                                   // title: "distance"
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
			datatable = $("#ledger-table").dataTable({
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
			"sAjaxSource": root_domain+crm_domain +'app/report_leads_by_executive/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "generate_report_product_service" },{ "name": "date", "value": date },{ "name": "source_id", "value": user_id } );
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
	load_ledger_datatable();
	/*var date=$("#rep_date").val();
	var source_id=$("#source_id").val();

	if(source_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/report_leads_by_executive/',
		data: { mode : "generate_report_product_service",date:date,source_id:source_id},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}*/
}
function load_ledger_datatable(){
	generate_chart();
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();

	//alert(source_id);
	//if(source_id){
	
	datatable = $("#ledger-table").dataTable({
		/* "bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        }, */
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
		"sAjaxSource": root_domain + crm_domain +'app/report_leads_by_executive/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_product_service" },{ "name": "date", "value": date },{ "name": "source_id", "value": source_id } );
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
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();
	
	var url = root_domain +'generate_export?mode=excecutive&date=' + encodeURIComponent(date) + "&source_id=" + encodeURIComponent(source_id);
	window.location.href = url;
}

