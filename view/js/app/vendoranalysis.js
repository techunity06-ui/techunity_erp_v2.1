//var datatable;
$(document).ready(function() {
generate_report_vendor_analysis();
generate_chart();
   var date = new Date();
   var firstDay =new Date(date.getFullYear(), date.getMonth(), 1); 
   var lastDay =new Date(date.getFullYear(), date.getMonth() + 1, 0); 

  
	$('.fromdate').datepicker('setDate',firstDay);
	$('.todate').datepicker('setDate', lastDay);

	 $("#fromdate").datepicker({
        todayBtn:  1,
        autoclose: true,
    }).on('changeDate', function (selected) {
        var minDate = new Date(selected.date.valueOf());
        $('#todate').datepicker('setStartDate', minDate);
    });
    
    $("#todate").datepicker()
        .on('changeDate', function (selected) {
            var minDate = new Date(selected.date.valueOf());
            $('#fromdate').datepicker('setEndDate', minDate);
        });
});
	
function generate_chart_report(){
	generate_report_vendor_analysis();
	generate_chart();
}
function clear_lead_by_source_report(){
	//$("#product_id").val('null');
	//$("select").each(function(){ $(this).find('option[value="'+$(this).attr("value")+'"]').prop('selected', true); });
	//$('.select2').select2("val", "null");
	generate_report_vendor_analysis();
	generate_chart();
}

function generate_chart()
{
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();//vendor wise
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();
	//alert(vendor_id);
	//var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_vendor_analysis&vendor_id='+vendor_id+'&from_po_date='+from_po_date+'&to_po_date='+to_po_date+'&report_wise='+report_wise+'&specific_vendor='+specific_vendor+'&item_id='+item_id+'&specific_item='+specific_item+'&from_delivery_date='+from_delivery_date+'&item_status='+item_status+'&to_delivery_date='+to_delivery_date
	//var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_vendor_analysis&from_po_date='+from_po_date+'&to_po_date='+to_po_date
	var mainurl = root_domain + crm_domain +'app/crm_dashboard/index.php?mode=generate_report_vendor_analysis&from_po_date='+from_po_date+'&to_po_date='+to_po_date+'&vendor_id='+vendor_id+'&rep_po_date='+rep_po_date
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
		//console.log(chart);
		chart.render();
		function onClick(e){
			var dvendor_id = e.dataPoint.vendor_id;
			var rep_po_date=$("#rep_po_date").val();
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/vendoranalysis/',
				data: { mode : "vendorwiseanalysisreport", dvendor_id:dvendor_id,rep_po_date:rep_po_date },
				success: function(response)
				{
					if(response != "") {
						$('#adv-table').html(response);
						Unloading();
					}							
				}
			});
		}
	});	
}
function getitemsbyvendorid(vendorid){
	$.ajax({
		type: "POST",
		url: root_domain+'app/pendingpurchasereport/',
		data: { mode :'getitemsbyvendorid',vendorid:vendorid},		
	   success: function(response)
		{
			if(response != "") {
				$('#item_id').html('');
				$('#item_id').html(response);
				Unloading();
			}else{
				$('#item_id').html('');
			}
		}
	});	
}

function generate_report_vendor_analysis() 
{
	var from_po_date=$("#from_po_date").val();
	var to_po_date=$("#to_po_date").val();
	var from_delivery_date=$("#from_delivery_date").val();
	var to_delivery_date=$("#to_delivery_date").val();
	var report_wise = $("input[name='report_wise']:checked").val();
	var to_po_date=$("#to_po_date").val();
	var rep_po_date=$("#rep_po_date").val();
	var rep_del_date=$("#rep_del_date").val();
	var item_status=0;
	var item_status = $("input[name='item_status']:checked").val();
	var item_status_id=$("#item_status_id").val();
	var po_date_type = $("input[name='po_date_wise']:checked").val();
	var specific_vendor = $("input[name='specific_vendor']:checked").val();
	var vendor_id=$("#vendor_id").val();
	var specific_item = $("input[name='specific_item']:checked").val();
	var item_id=$("#item_id").val();
	Loading();
	//alert(vendor_id)
	$.ajax({
		type: "POST",
		url: root_domain+'app/vendoranalysis/',
		data: { mode : report_wise,item_id:item_id,specific_item:specific_item,vendor_id:vendor_id,specific_vendor:specific_vendor,item_status_id:item_status_id,item_status:item_status,po_date_type:po_date_type,rep_del_date:rep_del_date,rep_po_date:rep_po_date,to_po_date:to_po_date,from_po_date:from_po_date},		
	     success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
	
}







