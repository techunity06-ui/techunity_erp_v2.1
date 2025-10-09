//var datatable;
$(document).ready(function() {
	load_industry_wise_party_list_datatable();
	generate_chart();
});	 
function clear_industry_wise_party_list_report(){
	load_industry_wise_party_list_datatable('clear');
	generate_chart('clear');
}
function generate_chart(clear = false)
{
	if(clear != 'clear'){
		var cust_ind=$("#cust_ind").val();
	}else{
		var cust_ind = '';
		$("#cust_ind").select2("val",'');
	}
	var t_id=$("#t_id").val();
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_industry_wise_party_list&cust_ind='+cust_ind+'&t_id='+t_id;

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
		var chart = new CanvasJS.Chart("report_industry_wise_party_list", {
				animationEnabled: true,
				theme: "light2", 
				axisY:{
                    interval:20
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
			var cust_ind = e.dataPoint.user_id;
			datatable = $("#ledger-industry-wise-party").dataTable({
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
			"sAjaxSource": root_domain+crm_domain +'app/report_industry_wise_party_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "generate_industry_wise_party_list" },
					{ "name": "cust_ind", "value": cust_ind } );
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
		url: root_domain + crm_domain +'app/report_industry_wise_party_list/',
		data: { mode : "generate_industry_wise_party_list",cust_ind:cust_ind},
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
function load_industry_wise_party_list_datatable(clear = false){
	generate_chart();
	if(clear != 'clear'){
		var cust_ind=$("#cust_ind").val();
	}else{
		var cust_ind= '';
	}
		var t_id=$("#t_id").val();
		var country = $("#c_add_country").val();
		var state = $("#c_add_state").val();
		var city = $("#c_add_city").val();
	

	datatable = $("#ledger-industry-wise-party").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/report_industry_wise_party_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_industry_wise_party_list" }
				,{ "name": "cust_ind", "value": cust_ind },
				{ "name": "t_id", "value": t_id },
				{ "name": "country", "value": country },
				{ "name": "state", "value": state },
				{ "name": "city", "value": city } );
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

function exportCsv() {
	var cust_ind=$("#cust_ind").val();
	var t_id=$("#t_id").val();
	var country = $("#c_add_country").val();
	var state = $("#c_add_state").val();
	var city = $("#c_add_city").val();

	var url = root_domain +'generate_export?mode=party_list&cust_ind=' + encodeURIComponent(cust_ind) + '&t_id=' + encodeURIComponent(t_id) + "&country=" + encodeURIComponent(country) + "&state=" + encodeURIComponent(state) + "&city=" + encodeURIComponent(city);
	window.location.href = url;
}
