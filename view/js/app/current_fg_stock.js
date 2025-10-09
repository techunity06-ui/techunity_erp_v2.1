//var datatable;
$(document).ready(function() {
	load_current_stock_datatable();
	generate_chart();
});	
function generate_chart_report(){
	load_current_stock_datatable();
	generate_chart();
}
function clear_lead_by_source_report(){
	$("#product_id").val('null');
	$('.select2').select2("val", "null");
	load_current_stock_datatable();
	generate_chart();
}

function generate_chart()
{
	var product_id=$('#product_id').val();
	//alert(product_id);
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_fg_stock&product_id='+product_id
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
		 console.log(arr1);
		var chart = new CanvasJS.Chart("report_current_fg_stock", {
				animationEnabled: true,
				theme: "light2", // "light1", "light2", "dark1", "dark2"
				axisX:{
			       // title: "time",
			        interval:1
			      },
			      axisY:{
			      	interval:1
			       // title: "distance"
			      },
				data: [{        
					type: "column", 
					click: onClick, 
					dataPoints: arr1
				}]
		});
		//alert(chart);
		chart.render();
		function onClick(e){
			//var product_name = e.dataPoint.product_name;
			var dproduct_id = e.dataPoint.product_id;
			//alert(dproduct_id);
			datatable = $("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+'app/current_fg_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "req_product_id", "value": dproduct_id });
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

function load_current_stock_datatable()
{
	var product_id=$('#product_id').val();
	//alert(product_id);
	datatable = $("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+'app/current_fg_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },{ "name": "product_id", "value": product_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
