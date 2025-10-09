
function store_return_material(){
	
	var obj = {};
	obj.pidChecked = [];
	obj.start_qtyChecked = [];
	obj.working_qtyChecked=[];
	obj.product_id=[];
	obj.release_trn_id = []
		
	var so_stock=(document.getElementsByName('start_qty1[]'));
	var cnt=so_stock.length;
	var so_stock1=0;
	for(var i=0;i<cnt;i++)
	{
		if(so_stock[i].value > 0){
			so_stock1 += parseFloat(so_stock[i].value);
		}
	} 
	if(so_stock1<="0"){
		toastr.warning("Enter Any One", "WARNING"); 
		  return false;
	}
	var errorlog=0;
	

	var id = 1;
	$('input.start_qty').each(function(){ 
     	
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var product_name=$(this).attr("data-product_name");
		var product_id=$(this).attr("data-product_id");
		var release_trn_id = $(this).attr("data-release_trn_id");
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(start_qty>working_qty){
				errorlog +=parseFloat(1);
			}else{
				obj.start_qtyChecked.push(start_qty);
				obj.pidChecked.push(pid);
				obj.working_qtyChecked.push(working_qty);
				obj.product_id.push(product_id);
				obj.release_trn_id.push(release_trn_id);
			}		
			
		}
		});	

	if(errorlog>"0"){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		return false;
	}
		var mode=$("#mode").val();
		var max_available_qty=$("#max_available_qty").val();
		   var remark=$("#remark").val();
		   var issue_no=$("#issue_no").val();
		   var issue_date=$("#issue_date").val();
		   var release_id = $("#release_id").val();
		    var release_type = $("#release_type").val();
		     var return_user_id = $("#user_id").val();
		     
    $.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_return_material/',
		data: { 
				mode : "add_return_material",
				pid:obj.pidChecked,
				pid_wise_start_qty:obj.start_qtyChecked,
				remark:remark,
				issue_no:issue_no,
				issue_date:issue_date,
				product_ids: obj.product_id,
				release_trn_ids : obj.release_trn_id,
				release_type:release_type,
				return_user_id:return_user_id,
				release_id:release_id

			},
		success: function(response)
		{
			if(response == '1') {
					toastr.success("MATERIAL RETURN SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			location.reload();
		}
	});  
}




function check_start_validation(){
	var obj = {};
	obj.pidChecked = [];
	obj.start_qtyChecked = [];
	obj.working_qtyChecked=[];
		 $('#sp_btn').show();
		//alert("dsa");
	var total_qty=0;	
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();

		var start_qty=parseFloat($(this).val());
		// console.log('-->' + start_qty);
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var product_name=$(this).attr("data-product_name");
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Product : "+product_name+"", "WARNING"); 
				   $('#save').hide();
				   $('#btnCancel').css('margin-left','45%');	
			}else{
				
				obj.start_qtyChecked.push(start_qty);
				obj.pidChecked.push(pid);
				obj.working_qtyChecked.push(working_qty);
				 $('#save').show();
				  $('#btnCancel').css('margin-left','20px');	
			}
			
		}
		
	});
} 