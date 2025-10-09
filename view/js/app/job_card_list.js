//var datatable;
$(document).ready(function() {
	load_po_req_datatable();

	$("#process_transfer").validate({
		rules: {
			inhouse_qty: {
				required: true,
				min: 0
   		 
			},
			outside_qty: {
				required: true,
				min: 0
			}
		},
		messages: {
			inhouse_qty: {
				required: "Enter Inhouse Qty"
			},
			outside_qty: {
				required: "Enter Inhouse Qty"
			}
		}
	}); 

});
function reload_data()
{
	load_po_req_datatable();
}	
$("#approve_indent_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#approve_indent_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/job_card_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			// console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'po_list';
				
			}
			$('#purchaseorder_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	//alert(branch_id);
	//alert(po_type_status);
	datatable = $("#po-req-table").dataTable({
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
		"sAjaxSource": root_domain+'app/job_card_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "po_type_status", "value": po_type_status },{ "name": "date", "value": date },{ "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}


/*
	START :: Code by Sanat :: 27-08-2021
	comment :: change work order process inhouse to outside 
	*/

function change_process_type(rp_id,product_id,process_id,process_type){
		var msg = "";
		$(".div_vendor").slideDown();
		$('#rp_id').val(rp_id);
		$('#product_id').val(product_id);
		$('#process_id').val(process_id);
		$('#process_type').val(process_type);
		$('#p_id').val('');

		if(process_type == '1'){
			$('#inhouse_process').prop('checked',true);
		}else{
			$('#outside_process').prop('checked',true);
		}

		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "check_multiple_allocate_process",  
				rp_id : rp_id,
				product_id : product_id, 
				process_id : process_id,
				process_type : process_type
			},
			success: function(response){
				var arr = jQuery.parseJSON(response);
				if(arr.msg == '1') {
					$("#div_multiple_process").empty().append(arr.html);
					$('#multi_job_card_process_transfer_modal').modal('show');
					Unloading();
				}else{
					check_work_order_grn(rp_id,product_id,process_id,process_type);	
				}
			}
	});

			
}


	function check_work_order_grn(rp_id,product_id,process_id,process_type){

		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "check_work_order_grn",  
				rp_id : rp_id,
				product_id : product_id, 
				process_id : process_id,
				process_type : process_type
			},
			success: function(response)
			{

				var arr = jQuery.parseJSON(response);
				if(arr.job_work == '1') {
					$("#job_work_id").val(1);
					if($('#process_type').val() == '2'){
					// if(process_type == '1'){
					// 	$(".div_vendor").slideUp();
					// }else{
						$(".div_vendor").slideDown();
					// }
				}
			}else{
				$(".div_vendor").slideUp();
				$("#job_work_id").val("");
			}

			setTimeout(function(e){
				$('#preview_job_card_vendor_change_modal').modal('show');
				Unloading();
			},800)

		}
	});

		
	}

	function save_change_process_type(){

		var rp_id = $('#rp_id').val();
		var product_id =	$('#product_id').val();
		var process_id	= $('#process_id').val();
		var process_type =	$('#process_type').val();

		var selected_option = $("input[name='process_type']:checked").val();


		if(selected_option == process_type){

			toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
			$('#preview_job_card_vendor_change_modal').modal('hide');
			return;
		}

		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "change_process_type",  
				rp_id : rp_id,
				product_id : product_id, 
				process_id : process_id,
				process_type : process_type
			},
			success: function(response)
			{

				var arr = jQuery.parseJSON(response);
				if(arr.msg == '1') {
					toastr.success("PROCESS VENDOR UPDATED SUCCESSFULLY", "SUCCESS");
					Unloading();
					$('#preview_job_card_vendor_change_modal').modal('hide');
					reload_data();
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
					Unloading();
				}

			}
		});

	}


	function toggle_vendor(process_type){
		if($("#job_work_id").val()==""){
			return false;
		}
		if($('#process_type').val() == '1'){
			return false;	
		}
		if(process_type == '1'){
			$(".div_vendor").slideUp();
		}else{
			$(".div_vendor").slideDown();
		}
	}


/*
$('input[type=radio][name=yes_no]').change(function() {

    if (this.value == 'yes') {
        $(".div_process_type").slideUp();
    }
    else if (this.value == 'transfer') {
        $(".div_process_type").slideDown();
    }
});
*/

/*function reset_change_process_modal(){
	$('#rp_id').val('');
	$('#product_id').val('');
	$('#process_id').val('');
	$('#process_type').val('');
	$('#job_work_id').val('');
}*/

function show_qty_msg(){
	var rp_id = $('#rp_id').val();
	var product_id =	$('#product_id').val();
	var process_id	= $('#process_id').val();
	var process_type =	$('#process_type').val();

	var selected_option = $("input[name='process_type']:checked").val();
	

	if(selected_option == process_type){
		
		toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
		$('#preview_job_card_vendor_change_modal').modal('hide');
		return;
	}

	Loading();
		
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_list/',
		data: { 
			mode : "get_pending_qty",  
			rp_id : rp_id,
			product_id : product_id, 
			process_id : process_id,
			process_type : process_type
		},
		success: function(response)
		{

			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				$('#inhouse_qty').val(0);
				$('#outside_qty').val(0);
				$('#pending_qty').val(arr.process.pen_qty);
				$('#product_name').text(arr.process.product_name);
				$('#process_name').text(arr.process.process_name);

				if(process_type == '1'){
						$('#inhouse_qty').val(arr.process.pen_qty);
						$('.process_type').empty().append("<span class='label label-success padd7px'> Inhouse </span>");
				}else{
						$('#outside_qty').val(arr.process.pen_qty);
						$('.process_type').empty().append("<span class='label label-primary padd7px'> Outside </span>");
				}
				Unloading();
				$('#preview_job_card_vendor_change_modal').modal('hide');
				Swal.fire({
					title: 'You want to transfer full quantity ?',
				  // text: "You won't be able to revert this!",
				  icon: 'question',
				  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
				  customClass: {
				    icon: 'no-border'
				  }*/
				  showCancelButton: true,
				  confirmButtonColor: '#5cb85c',
				  cancelButtonColor: '#d9534f',
				  cancelButtonText: 'No',
				  confirmButtonText: 'Yes',
				  allowOutsideClick: false,
				  allowEscapeKey : false,
				  /*showClass: {
				    popup: 'animate__animated animate__fadeInDown'
				  },
				  hideClass: {
				    popup: 'animate__animated animate__fadeOutUp'
				  }*/
				  
				}).then((result) => {
					if (result.isConfirmed) {
						save_change_process_type();
					}else{
						check_reserve_stock();
						// $('#preview_job_card_process_transfer_modal').modal('show');
					}
				})

				}else{
					Unloading();
					save_change_process_type();
				}

				}
		});
}


$("#process_transfer").on('submit',function(e) { // transfer qty 
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#process_transfer").valid()) {
		return false;
	}

	var rp_id = $('#rp_id').val();
	var product_id =	$('#product_id').val();
	var process_id	= $('#process_id').val();
	var process_type =	$('#process_type').val();
	var pen_qty = parseInt($("#pending_qty").val());
	var inhouse_qty = parseInt($("#inhouse_qty").val());
	var outside_qty = parseInt($("#outside_qty").val());

	var total_stock = parseInt($("#total_stock").val());
	var inhouse_stock = parseInt($("#inhouse_stock").val());
	var outside_stock = parseInt($("#outside_stock").val());

	if((inhouse_qty + outside_qty) > pen_qty){
		$('#preview_job_card_process_transfer_modal').modal('hide');
		toastr.warning("YOU CAN'T SET QTY MORE THAN PENDING QTY!", "WARNING");
		return false;
	}

	if($('#p_id').val() != ""){
		var p_id = $('#p_id').val(); 
		if(process_type == '1' && (pen_qty == inhouse_qty)){
			toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
			$('#preview_job_card_process_transfer_modal').modal('hide');
			return;
		}else if(process_type == '2' && (pen_qty == outside_qty)){
			toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
				$('#preview_job_card_process_transfer_modal').modal('hide');
				return;
		}else if(process_type == '1' && (pen_qty == outside_qty)){
				save_change_process_type_by_p_id(p_id);
				$('#preview_job_card_process_transfer_modal').modal('hide');
				return;
		}else if(process_type == '2' && (pen_qty == inhouse_qty)){
			save_change_process_type_by_p_id(p_id);
			$('#preview_job_card_process_transfer_modal').modal('hide');
				return;
		}

		const form = new FormData();
		  form.append('mode', 'process_transfer_qty_by_p_id');
		  form.append('p_id',p_id);
		  form.append('pen_qty', pen_qty);
		  form.append('inhouse_qty', inhouse_qty);
		  form.append('outside_qty', outside_qty);
 	      form.append('total_stock', total_stock);
		  form.append('inhouse_stock', inhouse_stock);
		  form.append('outside_stock', outside_stock);

		  Loading();
		$.ajax({
			cache:false,
		url: root_domain+'app/job_card_list/',
		type: "POST",
		 processData: false,
		 contentType: false,
		data: form,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
					$('#preview_job_card_process_transfer_modal').modal('hide');
					toastr.success("PROCESS TRANSFER SUCCESSFULLY!", "SUCCESS");
				
			}
			else if(arr.msg == '0') {
				$('#preview_job_card_process_transfer_modal').modal('hide');
				toastr.warning("SOMETHING WRONG", "ERROR")

				Unloading();

			}
			$('#preview_job_card_process_transfer_modal').modal('hide');
			$('#process_transfer').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			Unloading();
			console.log(textStatus, errorThrown);
		}
	});
	}else{

		if(process_type == '1' && (pen_qty == inhouse_qty)){
			toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
			$('#preview_job_card_process_transfer_modal').modal('hide');
			return;
		}else if(process_type == '2' && (pen_qty == outside_qty)){
			toastr.success("NO ANY CHANGES FOR THIS PROCESS", "SUCCESS");
			$('#preview_job_card_process_transfer_modal').modal('hide');
			return;
		}else if(process_type == '1' && (pen_qty == outside_qty)){
			save_change_process_type();
			$('#preview_job_card_process_transfer_modal').modal('hide');
			return;
		}else if(process_type == '2' && (pen_qty == inhouse_qty)){
			save_change_process_type();
			$('#preview_job_card_process_transfer_modal').modal('hide');
			return;
		}

		const form = new FormData();
		form.append('mode', 'process_transfer_qty');
		form.append('rp_id',rp_id );
		form.append('product_id',product_id );
		form.append('process_id',process_id );
		form.append('process_type',process_type);
		form.append('pen_qty', pen_qty);
		form.append('inhouse_qty', inhouse_qty);
		form.append('outside_qty', outside_qty);
		form.append('total_stock', total_stock);
		form.append('inhouse_stock', inhouse_stock);
		form.append('outside_stock', outside_stock);
		
		Loading();
		$.ajax({
			catch : false,
		url: root_domain+'app/job_card_list/',
		type: "POST",
		data:form,
		processData: false,
		 contentType: false,

		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
					$('#preview_job_card_process_transfer_modal').modal('hide');
					toastr.success("PROCESS TRANSFER SUCCESSFULLY!", "SUCCESS");
				
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			$('#process_transfer').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
	}
});


function update_qty(process_type){
	var pen_qty = parseInt($("#pending_qty").val());
	var inhouse_qty = $("#inhouse_qty").val();
	var outside_qty = $("#outside_qty").val();
	if(inhouse_qty == ""){
		$("#inhouse_qty").val(0);
	}
	if(outside_qty == ""){
		$("#outside_qty").val(0);
	}
	 inhouse_qty = parseInt($("#inhouse_qty").val());
	 outside_qty = parseInt($("#outside_qty").val());
	$("#inhouse_qty").val(inhouse_qty);
	$("#outside_qty").val(outside_qty);
	
	if(process_type == '1'){
		if(inhouse_qty > pen_qty){
			inhouse_qty = pen_qty;
			$("#inhouse_qty").val(inhouse_qty).trigger('onkeyup');;
		}
		var qty = pen_qty - inhouse_qty;
		if(qty < 0){
			qty = 0;
		}
		
		$("#outside_qty").val(qty);

		if(qty < parseInt($("#outside_stock").val())){
			$("#outside_stock").val(qty).trigger('onkeyup',2);
		}
	}else{
		if(outside_qty > pen_qty){
			outside_qty = pen_qty;
			$("#outside_qty").val(outside_qty).trigger('onkeyup');
		}
		var qty = pen_qty - outside_qty;
		if(qty < 0){
			qty = 0;
		}
		
		$("#inhouse_qty").val(qty);

		if(qty < (parseInt($("#inhouse_stock").val()))){
			
			$("#inhouse_stock").val(qty).trigger('onkeyup',1);
		}
	}
}
function update_stock(process_type){
	
	var total_stock = parseInt($("#total_stock").val());
	var inhouse_stock = $("#inhouse_stock").val();
	var outside_stock = $("#outside_stock").val();

	if(inhouse_stock == ""){
		$("#inhouse_stock").val(0);
	}
	if(outside_stock == ""){
		$("#outside_stock").val(0);
	}
	 
	 inhouse_stock = parseInt($("#inhouse_stock").val());
	 outside_stock = parseInt($("#outside_stock").val());
	var inhouse_qty = parseInt($("#inhouse_qty").val());
	var outside_qty = parseInt($("#outside_qty").val());
	$("#inhouse_stock").val(inhouse_stock);
	$("#outside_stock").val(outside_stock);
	
	if(process_type == '1'){
		if(inhouse_stock > inhouse_qty){
			$("#inhouse_stock").val(inhouse_qty);
			toastr.warning("RESERVE STOCK CAN'T TRANSFER MORE THAN QTY", "ERROR")
			$("#outside_stock").val(total_stock - inhouse_qty);

			return;
		}
		if(inhouse_stock > total_stock){
			inhouse_stock = total_stock;
			$("#inhouse_stock").val(inhouse_stock);
		}
		var stock = total_stock - inhouse_stock;
		if(stock < 0){
			stock = 0;
		}
		
		$("#outside_stock").val(stock);
		if(outside_stock > outside_qty){
			$("#outside_stock").val(outside_qty).trigger('onkeyup',2);
			toastr.warning("RESERVE STOCK CAN'T TRANSFER MORE THAN QTY", "ERROR")
			return;
		}
	}else{
		if(outside_stock > outside_qty){
			$("#outside_stock").val(outside_qty);
			$("#inhouse_stock").val(total_stock - outside_qty);
			toastr.warning("RESERVE STOCK CAN'T TRANSFER MORE THAN QTY", "ERROR")
			return;
		}
		if(outside_stock > total_stock){
			outside_stock = total_stock;
			$("#outside_stock").val(outside_stock);
		}
		var stock = total_stock - outside_stock;
		if(stock < 0){
			stock = 0;
		}
		
		$("#inhouse_stock").val(stock);

		if(stock > inhouse_qty){
			$("#inhouse_stock").val(inhouse_qty).trigger('onkeyup',1);
			toastr.warning("RESERVE STOCK CAN'T TRANSFER MORE THAN QTY", "ERROR")
			return;
		}
		
	}
}

function multi_transfer_process(p_id,process_type){
	$('#multi_job_card_process_transfer_modal').modal('hide');
	$("#process_type").val(process_type);
	Loading();
	$('#p_id').val(p_id);
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_list/',
		data: { 
			mode : "get_pending_qty_by_p_id",  
			p_id : p_id
		},
		success: function(response)
		{

			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				$('#inhouse_qty').val(0);
				$('#outside_qty').val(0);
				$('#pending_qty').val(arr.process.pen_qty);
				$('#product_name').text(arr.process.product_name);
				$('#process_name').text(arr.process.process_name);

				if(arr.process.pr_process_type == '1'){
						$('#inhouse_qty').val(arr.process.pen_qty);
						$('.process_type').empty().append("<span class='label label-success padd7px'> Inhouse </span>");
				}else{
						$('#outside_qty').val(arr.process.pen_qty);
						$('.process_type').empty().append("<span class='label label-primary padd7px'> Outside </span>");
				}
				Unloading();
				$('#preview_job_card_vendor_change_modal').modal('hide');
				Swal.fire({
					title: 'You want to transfer full quantity ?',
				  // text: "You won't be able to revert this!",
				  icon: 'question',
				  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
				  customClass: {
				    icon: 'no-border'
				  }*/
				  showCancelButton: true,
				  confirmButtonColor: '#5cb85c',
				  cancelButtonColor: '#d9534f',
				  cancelButtonText: 'No',
				  confirmButtonText: 'Yes',
				  allowOutsideClick: false,
				  allowEscapeKey : false,
				  /*showClass: {
				    popup: 'animate__animated animate__fadeInDown'
				  },
				  hideClass: {
				    popup: 'animate__animated animate__fadeOutUp'
				  }*/
				  
				}).then((result) => {
					if (result.isConfirmed) {
						save_change_process_type_by_p_id(p_id);
					}else{
						check_reserve_stock();
						// $('#preview_job_card_process_transfer_modal').modal('show');
					}
				})

				}else{
					Unloading();
					save_change_process_type();
				}

				}
		});
}

function save_change_process_type_by_p_id(p_id){

		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "change_process_type_by_p_id",  
				p_id : p_id
			},
			success: function(response)
			{

				var arr = jQuery.parseJSON(response);
				if(arr.msg == '1') {
					toastr.success("PROCESS VENDOR UPDATED SUCCESSFULLY", "SUCCESS");
					Unloading();
					$('#preview_job_card_vendor_change_modal').modal('hide');
					reload_data();
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
					Unloading();
					reload_data();
				}

			}
		});

	}

function check_reserve_stock(){
	$(".stock_details").hide();
	var rp_id = $('#rp_id').val();
	var product_id =	$('#product_id').val();
	var process_id	= $('#process_id').val();
	var process_type =	$('#process_type').val();
	var p_id = $('#p_id').val();

	if(p_id != ""){
		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "check_reserve_stock_by_p_id",  
				p_id : p_id
			},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == '1') {

					if(arr.total_stock > 0){
						$("#total_stock").val(arr.total_stock);
						$("#inhouse_stock").val(arr.inhouse_stock);
						$("#outside_stock").val(arr.outside_stock);	
						$(".stock_details").show();
					}
				}
				Unloading();
				$('#preview_job_card_process_transfer_modal').modal('show');
			}
		});
	}else{
		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_list/',
			data: { 
				mode : "check_reserve_stock",  
				rp_id : rp_id,
				product_id : product_id, 
				process_id : process_id,
				process_type : process_type
			},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == '1') {
					if(arr.total_stock > 0){
						$("#total_stock").val(arr.total_stock);
						$("#inhouse_stock").val(arr.inhouse_stock);
						$("#outside_stock").val(arr.outside_stock);	
						$(".stock_details").show();
					}
				}
				Unloading();
				$('#preview_job_card_process_transfer_modal').modal('show');
			}
		});
	}
}

/*
END :: Code by Sanat :: 27-08-2021
*/

