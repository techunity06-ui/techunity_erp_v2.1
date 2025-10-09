//var datatable;
$(document).ready(function() {
	load_ledger_datatable();
	generate_chart();
});	 
function load_data_chart(){
	load_ledger_datatable();
	generate_chart();
}
function clear_lead_by_source_report(){
	load_ledger_datatable();
}
function generate_chart()
{
	var start_date=$("#source_id").val();
	//alert(date);
	var mainurl = root_domain + crm_domain +'app/report_inquiry_leads_by_months/index.php?mode=generate_report_leads_months&start_date='+start_date
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
		var chart = new CanvasJS.Chart("report_leads_by_months", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				axisY:{
                                    interval:5
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
			var leaddate = e.dataPoint.month;
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
			"sAjaxSource": root_domain+crm_domain +'app/report_leads_by_months/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "generate_report_product_service" },{ "name": "source_id", "value": leaddate });
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
	
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();
	//alert(source_id);
	if(source_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain +'app/report_leads_by_months/',
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
	}
}
function load_ledger_datatable(){
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();
	
	//if(source_id){
		//alert(source_id);
	
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
		"sAjaxSource": root_domain+crm_domain +'app/report_inquiry_leads_by_months/',
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

