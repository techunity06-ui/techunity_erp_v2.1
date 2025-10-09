//var datatable;
$(document).ready(function() {
	//alert('hii');
	load_balance_sheet();
   // load_profit_loss_report();
});

function reload_data(){
	load_balance_sheet();
}

function load_balance_sheet(){

	var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();

    var startdate = new Date(startDate.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3") );
    var enddate = new Date(endDate.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3") );
    if(enddate < startdate ){
        toastr.warning("Start date can not be greater then End date", "ERROR")
        return false;
    }
    
    Loading();
	$.ajax({
                cache: false,
				type: "POST",
                async : true,
				url: root_domain+finance_root_domain+'app/balance_sheet/',
				data: { mode : "load_balance_sheet",show_details :false, start_date : startDate, end_date: endDate },
				success: function(response){
				 //console.log(response);
				$('#balance_sheet_id').html(response);
               // $(".printshow").hide();
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
//		url: root_domain+finance_root_domain+'app/balance_sheet/',
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

    //alert(endDate);
    //alert('hii');
    $.ajax({
        type: "POST",
        url: root_domain + finance_root_domain+ 'app/profit_loss_report/pl_value',
        data: { mode : "load_profit_loss",show_details :false, start_date : startDate, end_date: endDate },
        success: function(response){
                   // alert(response);
                    var html = '';
                    var grand_total = 0;
                    var obj = JSON.parse(response);
                    if(obj.net_profit - obj.net_loss !=0)
                    {
                        if(obj.net_profit > 0){
                            total_liability = parseFloat($('#total_liability').val());
                            html += '<td><strong>Profit & Loss A/C<strong></td>';
                            html += '<td style="text-align: right;">'+ obj.net_profit +'</td>';
                            $(".net_profit").html(html);
                            $(".net_profit").show();
                            grand_total = parseFloat(obj.net_profit) + total_liability;
                            $(".grand_total_l").html(parseFloat(Math.abs(grand_total)).toFixed(2));
                        }
                        if(obj.net_loss > 0){
                            total_assets = parseFloat($('#total_assets').val());
                            html += '<td><strong>Profit & Loss A/C<strong></td>';
                            html += '<td style="text-align: right;">'+ obj.net_loss +'</td>';
                            $(".net_loss").html(html);
                            $(".net_loss").show();
                            grand_total = parseFloat(obj.net_loss) + total_assets;
                           // alert(grand_total);
                            $(".grand_total_a").html(parseFloat(Math.abs(grand_total)).toFixed(2));
                        }
                    }
                     // alert(obj.net_profit);
                     // alert(obj.net_loss);
                    //$(".grand_total").html(parseFloat(Math.abs(grand_total)).toFixed(2));
                }
    });
}