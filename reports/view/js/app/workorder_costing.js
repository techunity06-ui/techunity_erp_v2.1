
function change_template(bom_costing_id,costing_rate){
	
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+report_domain+'app/workorder_costing/',
			data: { mode : "load_bom_costing_template_data",  costing_rate:costing_rate,bom_costing_id:bom_costing_id},
			success: function(response)
			{
				$("#bom_costing_valuation").empty().html(response);
				Unloading();
			}
		});
}



function calculate_rate(id,costing_rate,type){ // type :: 1 - percentage, 2 - plus
	var value = $("#input_rate_"+id).val();
	var total = 0;
	if(type == 1){
		total = (costing_rate * value) / 100;
	}else {
		total = value;
	}
	$("#txt_tmp_total_"+id).html(total);
	get_grand_total(costing_rate);
}

function get_grand_total(costing_rate){
	var total = costing_rate;
	$('#bom_costing_valuation .input_temp_rate').each(function(index){ 
		var value = 0;
		value = parseFloat($(this).html().trim());
		var operation = $(this).attr('data-operation');
		if(operation == 0){
			total = total + value;
		}else {
			total = total - value;	
		}
		console.log(total);
		$("#total_product_costing strong").html(total.toFixed(2));
	});
}



function save_costing_template_value(){
	var bom_costing_id = $("#dyn_bom_costing_id").val();
	var sp_id = $("#sp_id").val();
	var data = {};
	data.temp_name = [];
	data.value = [];
	data.operation = [];
	data.formula = [];
	// data.total_value = [];

	var grand_total = $("#lbl_grand_total").html();
	$('#bom_costing_valuation .tmp_typename').each(function(index){ 
		var name = $(this).html();
		data.temp_name.push(name);
	});
	$('#bom_costing_valuation .input_rate').each(function(index){ 
		var value = $(this).val();
		var formula = $(this).attr('data-cal-type');
		data.value.push(value);
		data.formula.push(formula);
	});
	$('#bom_costing_valuation .input_temp_rate').each(function(index){ 
		var operation = $(this).attr('data-operation');
		var value = parseFloat($(this).html().trim());
		data.operation.push(operation);
		// data.total_value.push(value);
	});

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+report_domain+'app/workorder_costing/',
			data: { 
					mode : "save_costing_data",  
					bom_costing_id : bom_costing_id,
					grand_total:grand_total,
					temp_name:data.temp_name,
					value : data.value,
					type:data.operation,
					formula : data.formula,
					sp_id:sp_id
				},
			success: function(response)
			{
				Unloading();
				location.reload();
			}
		});
}