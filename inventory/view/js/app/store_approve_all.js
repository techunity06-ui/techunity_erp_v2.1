//var datatable;
$(document).ready(function() {

});


function check_validation_all(){
	
	var errorlog=0;
	$('input.batch_id').each(function(index){ 
		var cnt = index + 1;
		var pending_qty=parseFloat($(this).val());

		if($("#qc_godown"+cnt).val() == ""){
			errorlog +=parseFloat(1);
			$("#s2id_godown_id"+cnt+" .select2-choice").css("border", "2px solid red")
		}else{
			$("#s2id_godown_id"+cnt+" .select2-choice").css("border", "none")
		}
	});
	
	if(errorlog > 0){
		toastr.warning("PLEASE CHECK THE ERROR AND SELECT GODOWN", "ERROR")
		return false;
	}else{
		$("#store_add").submit();	
	}
	
}



$("#store_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/store_approve_all/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("STORE APPROVED SUCCESSFULLY", "SUCCESS");
				window.location=arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});