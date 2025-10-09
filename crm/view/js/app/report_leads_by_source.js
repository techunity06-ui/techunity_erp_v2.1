//var datatable;
$(document).ready(function() {
	load_source_code_datatable();
	load_source_code();
});	

function quote_stage_report_filters() 
{
	var source_id=$('#source_id').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain +'app/report_leads_by_source/',
		data: { 
				mode : "generate_report_product_service",
				source_id : source_id,
				extra : "datatable_filter"
			},
		success: function(response)
		{
			if(response != "") {
				Unloading();
				load_source_code_datatable(source_id);
			}
										
		}
	});	
}

function clear_lead_by_source_report(){
	load_source_code_datatable();
}

function load_data_chart(){
	load_source_code();
	load_source_code_datatable();
}
function load_source_code()
{
	var date=$("#rep_date").val();
	var source_id=$('#source_id').val();
	//alert("dsa");
	var mainurl = root_domain + crm_domain +'app/report_leads_by_source/index.php?mode=load_source_code&source_id='+source_id+'&date='+date
	$.getJSON(mainurl, function(json) {
		var arr1=new Array();
		for(var i=0;i<json.length;i++)
		{	
			arr1[i]=json[i],json[i];	
		}
		//console.log(arr1);
		var chart = new CanvasJS.Chart("report_leads_by_source_container", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				data: [{        
					type: "column", 
					click: onClick, 
					dataPoints: arr1
				}]
});
chart.render();
		function onClick(e){
			var d_source_id = e.dataPoint.id;
		
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/report_leads_by_source/',
				data: { 
						mode : "generate_report_product_service",
						source_id : d_source_id
					},
				success: function(response)
				{
					if(response != "") {
						Unloading();
						load_source_code_datatable(d_source_id);
					}
												
				}
			});
		}
	});	
}

function load_source_code_datatable(d_source_id = "",extra = ""){
	var source_id=$('#source_id').val();
	var date=$("#rep_date").val();
	var datatable = $("#source-table").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/report_leads_by_source/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_product_service" },
				{ "name": "source_id", "value": d_source_id },
				{ "name": "date", "value": date },
				{ "name": "extra", "value": extra }
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
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();
	var extra=$("#extra").val();
	
	var url = root_domain +'generate_export?mode=source&date=' + encodeURIComponent(date) + "&source_id=" + encodeURIComponent(source_id);
	window.location.href = url;
}

