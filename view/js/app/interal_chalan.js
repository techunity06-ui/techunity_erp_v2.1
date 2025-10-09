$("#create_internal_chalan").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	if (!$("#create_internal_chalan").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/internal_chalan/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			// console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("INTERNAL CHALAN ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'spare_list_pending';
			} else if(responsevalue.trim() == '2') {
				Unloading();
				toastr.success("INTERNAL CHALAN UPDATED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'spare_list_pending';
			} else {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
				window.location=root_domain+'spare_list_pending';
			}
			$('#create_internal_chalan').trigger('reset');	
			$('#save').prop("disabled",false);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

$(document).ready(function() {
	$('.recQty, .retQty').on('change', function() {
		var currentClass = $(this).attr('class');
		currentClass = currentClass.replace('form-control ','').trim();

		var nextElemClass = (currentClass == 'recQty') ? 'retQty' : 'recQty';

		var currentVal = $(this).val();
		currentVal = currentVal ? parseInt(currentVal) : 0;

		var parentsElem = $(this).parents('tr');

		var totalQty = parentsElem.find('.totalQty').val();
		totalQty = totalQty ? parseInt(totalQty) : 0;

		if(currentVal > totalQty) {
			$(this).val(totalQty);
			currentVal = totalQty;
		} else if(currentVal == '') {
			$(this).val(0);
		}

		var newVal = totalQty - currentVal;
		newVal = newVal < 0 ? 0 : parseInt(newVal);
		
		parentsElem.find('.'+nextElemClass).val(newVal);

	});
});