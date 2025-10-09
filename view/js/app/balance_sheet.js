//var datatable;
$(document).ready(function() {
	load_balance_sheet();
});

function reload_data(){
	load_balance_sheet();
}

function load_balance_sheet(){
	var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        Loading();
	$.ajax({
                cache: false,
		type: "POST",
                async : true,
		url: root_domain+'app/balance_sheet/',
		data: { mode : "load_balance_sheet",show_details :false, start_date : startDate, end_date: endDate },
		success: function(response){
				 //console.log(response);
				$('#balance_sheet_id').html(response);
                                setTimeout(function(){ 
                                    get_pl_value(); 
                                }, 1000);
                                hide_details();
				Unloading();
			}
			
	});
}
Mousetrap.bind({
    'shift+v': show_details
});
Mousetrap.bind({
    'shift+c': hide_details
}); 
function show_details() {
    $(".descripc").show();
//    var date=$('#rep_date').val();
//	Loading();
//	$.ajax({
//		type: "POST",
//		url: root_domain+'app/balance_sheet/',
//		data: { mode : "load_balance_sheet",show_details :true, date : date },
//		success: function(response){
//				 $('#balance_sheet_id').html(response);
//                                 $(".descripc").show();
//                                 get_pl_value();
//				 Unloading();
//			}
//			
//	});
}
function hide_details() {
    $(".descripc").hide();
}

function get_pl_value(){
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    $.ajax({
        type: "POST",
        dataType: "json",
        url: root_domain + 'app/profit_loss_report/pl_value',
        data: { mode : "load_profit_loss",show_details :false, start_date : startDate, end_date: endDate },
        success: function(response){
                    var html = '';
                    var grand_total = 0;
                    if(response.net_profit > 0){
                        total_liability = parseFloat($('#total_liability').val());
                        html += '<td><strong>Profit & Loss A/C<strong></td>';
                        html += '<td style="text-align: right;">'+ response.net_profit +'</td>';
                        $(".net_profit").html(html);
                        $(".net_profit").show();
                        grand_total = parseFloat(response.net_profit) + total_liability;
                    }
                    if(response.net_loss > 0){
                        total_assets = parseFloat($('#total_assets').val());
                        html += '<td><strong>Profit & Loss A/C<strong></td>';
                        html += '<td style="text-align: right;">'+ response.net_loss +'</td>';
                        $(".net_loss").html(html);
                        $(".net_loss").show();
                        grand_total = parseFloat(response.net_loss) + total_assets;
                    }
                    $(".grand_total").html(parseFloat(Math.abs(grand_total)).toFixed(2));
                }
    });
}