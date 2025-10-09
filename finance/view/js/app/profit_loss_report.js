//var datatable;
$(document).ready(function() {
    //alert('in js file');
	load_profit_loss_report();
});

function reload_data(){
	load_profit_loss_report();
}

function load_profit_loss_report(){
	//alert('hii');
	var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
	
//	alert(startDate);
//	alert(endDate);
	
	Loading();
	$.ajax({
                cache: false,
		type: "POST",
                async : true,
                url: root_domain+finance_root_domain+'app/profit_loss_report/',
		data: { mode : "load_profit_loss",show_details :false, start_date : startDate, end_date: endDate },
		success: function(response){
			$('#profitloss_report_id').html(response);
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
	//alert('hii');
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
	Loading();
	$.ajax({
            cache: false,
            type: "POST",
            async : true,
            url: root_domain+finance_root_domain+'app/profit_loss_report/',
            data: { mode : "load_profit_loss",show_details :true, start_date : startDate, end_date: endDate },
            success: function(response){
						console.log(response);
                        $('#profitloss_report_id').html(response);
                        $(".descripc").show();
                        Unloading();
                    }
	});	
	//alert('Open');
}
function hide_details() {
    $(".descripc").hide();
}