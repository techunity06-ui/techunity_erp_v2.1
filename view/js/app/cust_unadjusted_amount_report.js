$(document).ready(function() {
    reload_data();
});
function reload_data()
{
        generate_chart();
	generate_report();
}

function generate_report() {
	var cust_id = '0';
        var cust_type = $('input[name=cust_type]:Checked').val();
        
        if(cust_type > 0){
            cust_id = $("#cust_id").val();
        }
        
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain + 'app/report_cust_unadjusted_amount/',
		data: { mode : "generate_report",cust_id:cust_id},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
}

function generate_chart(cust_id = 0)
{
        var cust_type = $('input[name=cust_type]:Checked').val();
        
        if(cust_type > 0){
            cust_id = $("#cust_id").val();
        }
	
	var mainurl = root_domain + 'app/report_cust_unadjusted_amount/index.php?mode=generate_chart&cust_id='+cust_id;
	$.getJSON(mainurl, function(json) {
		var chart_data = new Array();
		for(var i=0;i<json.length;i++)
		{	
			chart_data[i] = json[i],json[i];	
		}
		var chart = new CanvasJS.Chart("chart_container", {
			animationEnabled: true,
                        legend:{
                                horizontalAlign: "right",
                                verticalAlign: "center"
                        },
			data: [{
				type: "pie",
                                showInLegend: true,
				radius: "100%",
                                click: onClick,
                                explodeOnClick: false,
                                toolTipContent: "<b>{label}</b>: INR {y} (#percent%)",
				legendText: "{label} (#percent%)",
				indexLabelFontSize: 12,
				indexLabel: "{label}",
                                indexLabelPlacement: "inside",
				dataPoints: chart_data
			}]
		});
		chart.render();
                function onClick(e) {
                    var cust_id = e.dataPoint.id;
                    
                    Loading();
                    generate_chart(cust_id);
                    
                    $.ajax({
                        type: "POST",
                        url: root_domain + 'app/report_cust_unadjusted_amount/',
                        data: { mode : "generate_report",cust_id:cust_id},
                        success: function(response)
                        {
                            if(response != "") {
                                    $('#adv-table').html(response);
                                    Unloading();
                            }
                        }
                    });
                    $('#cust_type_all').attr('checked',false);
                    $('#cust_type_sc').attr('checked',true);
                    $('#cust_div').show();
                    $('#cust_id').select2('val',cust_id);
                }
	});	
}