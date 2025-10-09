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
		url: root_domain+production_domain+'app/job_card_list/',
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
				window.location=root_domain+production_domain+arr.back;
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
				window.location=root_domain+production_domain+'po_list';
				
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
		"sAjaxSource": root_domain+production_domain+'app/job_card_list/',
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

function change_process_type(rp_id,product_id,process_id,process_type,done_status){

		if(done_status == '1'){
			toastr.info("PROCESS ALREADY STARTED. YOU CAN'T TRANSFER.", "INFO");
			return false;
		}else if(done_status == '3'){
			toastr.info("PROCESS ALREADY DONE. NOW YOU CAN'T TRANSFER.", "INFO");
			return false;
		}
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
			url: root_domain+production_domain+'app/job_card_list/',
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
			url: root_domain+production_domain+'app/job_card_list/',
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
			url: root_domain+production_domain+'app/job_card_list/',
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
		url: root_domain+production_domain+'app/job_card_list/',
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
	if(isNaN(total_stock)){
		total_stock = 0;
	}
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
		url: root_domain+production_domain+'app/job_card_list/',
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
		url: root_domain+production_domain+'app/job_card_list/',
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
		url: root_domain+production_domain+'app/job_card_list/',
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
			url: root_domain+production_domain+'app/job_card_list/',
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
			url: root_domain+production_domain+'app/job_card_list/',
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
			url: root_domain+production_domain+'app/job_card_list/',
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

function delete_jobcard(rp_id){
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/job_card_list/',
			data: { mode : "delete_jobcard",  rp_id : rp_id },
			success: function(response)
			{

					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("JOBCARD DELETE SUCCESSFULLY", "SUCCESS");
						load_po_req_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}							
				}
			});	
	}
}


function view_documents(bom_id,bom_version_id)
{
	var id = $("#eid").val()
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "view_document_data", bom_id : bom_id,bom_version_id : bom_version_id },
		success: function(response)
		{
			$('#documents_data_list').empty().html(response);
			$("#preview_bom_document_upload").modal("show");
			Unloading();
		}
	});	
}


function open_priority_alert(rp_id){
	Swal.fire({
		title: 'You want to change workorder priority ?',
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
			Swal.fire({
			title: 'Please Select Priority.',
		  // text: "You won't be able to revert this!",
		  icon: 'question',
		  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
		  customClass: {
		    icon: 'no-border'
		  }*/
		  showCancelButton: true,
		  showDenyButton: true,
		  confirmButtonColor: '#ff0000',
		  denyButtonColor: '#ff8d8d',
		  cancelButtonColor: '#e5b8b8',
		  confirmButtonText: 'High',
		  denyButtonText: 'Medium',
		  cancelButtonText: 'Low',
		  allowOutsideClick: false,
		  allowEscapeKey : false,
		  
		}).then((result1) => {
			if (result1.isConfirmed) {
			    // High
			    change_jobcard_priority(rp_id,'High');
			  } else if (result1.isDenied) {
			    // Medium
			    change_jobcard_priority(rp_id,'Medium');
			  }else{
			  	// Low
			  	change_jobcard_priority(rp_id,'Low');
			  }
		})
		}
	})
}


function change_jobcard_priority(rp_id,priority){
	Loading();

	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/job_card_list/',
			data: { mode : 'change_jobcard_priority', rp_id : rp_id, priority:priority},
			success: function(data){
				if(data=='1'){
					toastr.success("JOBCARD PRIORITY HAS BEEN CHANGED SUCCESSFULLY", "SUCCESS");
				}else{
					toastr.warning("SOMETHING WRONG", "WARNING");
				}

				Unloading();
				load_po_req_datatable();
			}		
	});
}
function process_edit(rp_id='',product_id,version_id='')
{
	
	var bom_version_id = version_id;
	if(version_id == ""){
		bom_version_id = $('#bom_version_id').val();
	}
								// alert(version_id)
	$("#mask1").removeClass('hidden');
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : 'get_product_process_data',
			product_id:product_id,
			rp_id : rp_id,
			bom_version_id:bom_version_id,
			edit_id:'1'
			
		},
		success: function(data){
			
			$('#mod_per_div_add_process').empty();
			$('#mod_per_div_add_process').html(data);
			$('#rp_id').val(rp_id);

			CKEDITOR.replace( 'process_desc', {
				enterMode: CKEDITOR.ENTER_BR
			});
			
			
			var current_number = $('.process_row').last().attr('data-cid');	

			current_number = current_number ? current_number : 0;
			var new_number = parseInt(current_number) + 1;
			
			$('.process_priority').val(new_number);
			$('.process_priority_label').html(new_number);
			
			load_multislect_process();
			
			$(".ms-container").css('width',"100% !important");
			$('#direct_product_id').val(product_id);
			$('#preview_bom_add_process_modal').modal('show');
			
			if($("#multiple_value").val().length > 0){
				var selProcess = $("#multiple_value").val();
				
				const myArr = selProcess.split(",");
				$("#multiple_value").val('');
					for (const item of myArr) { // You can use `let` instead of `const` if you like
						$('#process_item').multiSelect('select', item);
						console.log(item)
					}
					
				}
				
				$("#mask1").addClass('hidden');
				updateIDs();
			}		
		});
	
}
function load_multislect_process(){
	$('#process_item').multiSelect({
		keepOrder: true,
		selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		afterInit: function (ms) {
			var that = this,
			$selectableSearch = that.$selectableUl.prev(),
			$selectionSearch = that.$selectionUl.prev(),
			selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
			selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

			that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
			.on('keydown', function (e) {
				if (e.which === 40) {
					that.$selectableUl.focus();
					return false;
				}
			});

			that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
			.on('keydown', function (e) {
				if (e.which == 40) {
					that.$selectionUl.focus();
					return false;
				}
			});
		},
		afterSelect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			var get_val = $("#multiple_value").val();         
			var hidden_val = (get_val != "") ? get_val+"," : get_val;
			$("#multiple_value").val(hidden_val+""+value);
		},
		afterDeselect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			//alert("test");
			var get_val = $("#multiple_value").val();
			var new_val = get_val.replace(value, "");
			$("#multiple_value").val(new_val);
		}
		
	});	
	
}
$("body").on("click","#process_left li",function(){
	$("#process_left li").removeClass('selected');
	$("#process_right li").removeClass('selected')
	$(this).addClass('selected');

	$('#row_process_desc').hide();
	$("#process_save").show();
	$("#selected_process_id").val('');
	$("#chk_leftside_process").prop('checked',false)
});
$("body").on("click","#process_right li",function(){
   // $("#process_right li").on('click',function(e){
   	$("#process_left li").removeClass('selected');
   	$("#process_right li").removeClass('selected');
   	$(this).addClass('selected');

   	$('#row_process_desc').show();
   	$("#process_save").hide();
   	var selectedOpts = $('#process_right li.selected');
   	var process_id = selectedOpts.attr('id');
   	$("#selected_process_id").val(process_id);
   	var rp_id = $("#selected_rp_id").val();
   	$("#btProcessDesc").html("Save");
   	get_process_desc(rp_id,process_id);
 	$("#chk_rightside_process").prop('checked',false)

   });
$("body").on("click","#moveRight",function(e){
   // $("#moveRight").on('click',function(e){
   	var selectedOpts = $('#process_left li.selected');
   	if (selectedOpts.length == 0) {
   		alert("Nothing to move.");
   		e.preventDefault();
   	}else{
   		selectedOpts.each(function(){ 
		   		var process_id = $(this).attr('id')
		   		var process_name = $(this).text();
		   		
		   		var html = "<li id='"+process_id+"'>" + process_name + "</li>";
		   		$('#process_right').append(html);
		   		$(this).remove();
   		   });
   		e.preventDefault();
   		updateIDs();
   		$("#chk_leftside_process").prop('checked',false)
   	}
   	
   });
$("body").on("click","#moveLeft",function(e){
     // $("#moveLeft").on('click',function(e){
     	var selectedOpts = $('#process_right li.selected');
     	console.log(selectedOpts.length);
     	if (selectedOpts.length == 0) {
     		alert("Nothing to move.");
     		e.preventDefault();
     	}else{
     		selectedOpts.each(function(){ 
		 		var process_id = $(this).attr('id')
	     		var process_name = $(this).text();
	     		var process_name = process_name.replace('+','');
	     		var html = "";
	     		html = "<li id='"+process_id+"'>" + process_name.trim() + "</li>";
	     		$('#process_left').append(html);
	     		$(this).remove();
	     		$('#row_process_desc').hide();
	     		$("#selected_process_id").val('');
	     		$("#process_save").show();
	     		$("#chk_rightside_process").prop('checked',false)
     		});
     		e.preventDefault();
     		updateIDs();
     	}
     });


function updateIDs() {
	$('#selected_process_ids').val('');
	$('#process_right li').each(function(index) {
		console.log($(this).attr('id'));
		$('#selected_process_ids').val($('#selected_process_ids').val() +  $(this).attr('id') + ",");
	});

	$('#process_ids').val('');
	$('#process_left li').each(function(index) {
		console.log($(this).attr('id'));
		$('#process_ids').val($('#process_ids').val() + $(this).attr('id') + ",");
	});
}

function save_process_desc(rp_id){
	var process_id = $("#selected_process_id").val();
	// var desc = $("#process_desc").val()
	var desc = CKEDITOR.instances['process_desc'].getData();
	var eid = $("#selected_desc_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "save_process_desc",rp_id:rp_id,process_id:process_id,desc:desc,eid,eid},
		success: function(response)
		{
			
			if(response.trim() == '1')
			{
				toastr.success("DESCRIPTION ADDED SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}else if(response.trim() == 'update') {
				toastr.success("DESCRIPTION UPDATE SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}
			else{
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			$("#selected_desc_id").val('');
			$("#process_right li").removeClass('selected')
			Unloading();
			
		}
	});	

}

function get_process_desc(rp_id,process_id){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "get_process_desc",rp_id:rp_id,process_id:process_id},
		success: function(response)
		{
			var data=JSON.parse(response);
			if(data.desc !== ""){
				// console.log(response);
				CKEDITOR.instances['process_desc'].setData(data.desc.description);
				$("#selected_desc_id").val(data.desc.id);
				$("#btProcessDesc").html("Update");
			}else {
				CKEDITOR.instances['process_desc'].setData("");
				$("#selected_desc_id").val('');
				$("#btProcessDesc").html("Save");
			}

			if(data.is_process_start > 0){
				$("#moveLeft").hide();
			}else{
				$("#moveLeft").show();
			}
		Unloading();
	}
});	
}


function select_all_left_side_process(){
	var process_left = $('#process_left li');
	if (process_left.length == 0) {
     		alert("No Process added.");
     		$("#chk_leftside_process").prop('checked',false)
     	}else{
     		if($("#chk_leftside_process").prop('checked')){
     			$("#process_left li").addClass('selected');
     		}else{
     			$("#process_left li").removeClass('selected');
     		}
     	}
}

function select_all_right_side_process(){

	var process_right = $('#process_right li');
     	if (process_right.length == 0) {
     		alert("No Process added.");
     		$("#chk_rightside_process").prop('checked',false);
     	}else{
     		if($("#chk_rightside_process").prop('checked')){
     			$("#process_right li").addClass('selected');
     		}else{
     			$("#process_right li").removeClass('selected');
     		}
     	}
}
function bom_process_add(rp_id='') {
	var counter = $("#process_right li").length;

	if(counter == 0){
		toastr.warning("PLEASE SELECT ANY ONE PROCESS", "ERROR")
		return false;
	}

	var form_data = new FormData();
	var product_id = $("#direct_product_id").val();
	
	if(rp_id!= '')
	{
		var rp_id = rp_id;
	}
	var sel_process = $("#selected_process_ids").val();
	var unsel_process = $("#process_ids").val();
	
	form_data.append('mode','bom_process_add');
	form_data.append('sel_process',sel_process);
	form_data.append('unsel_process',unsel_process);
	form_data.append('branch_id',$("#branch_id").val());
	form_data.append('rp_id',rp_id);
	form_data.append('product_id',product_id);
	
		// form_data.append('multiple_value',$("#multiple_value").val());
		var edit_id =  $('#edit_id').val();
		if(typeof edit_id != 'undefined')
		{
			form_data.append('edit_id',$('#edit_id').val());
		}
		

		$.ajax({		
			url: root_domain+production_domain+'app/job_card_list/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,	
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					
					
					$('#preview_bom_add_process_modal').modal('hide');
					toastr.success("WORK ORDER PROCESS ADDED SUCCESSFULLY", "SUCCESS");
					$('#in_process_qty_main').attr("readonly", false); 
				//$('#add_wo_prd').css('display','block');
				// if($('#process_sel_product_id').val() ==""){
					//return false;
					// add_field();
			// }
			process_reset();
			//location.href="";
			Unloading();

		}
		else if(arr.msg == 'update') {
				// if($('#process_sel_product_id').val() ==""){
					// add_field();
			// }
			process_reset();
			$('#preview_bom_add_process_modal').modal('hide');
			toastr.success("WORK ORDER PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
			$('#in_process_qty_main').attr("readonly", false); 
			$('#add_wo_prd').css('display','block');
				// add_field();
				Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
		//	get_tree_request();
			load_po_req_datatable();
			
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
		
		
	}
	function process_reset(){

	$("#prod_process_id").select2("val","");
	$("#process_rate").val('');
	$("#process_priority").val('');
	$("#edit_id_process").val('')
	$("#process_type").val('');
	$("#process_time").val('');
	$("#process_sel_product_id").val('');
	$("#direct_product_id").val('');
	/*$("#direct_product_id").val('');
	$("#direct_version_id").val('');*/
	// $("#add_process").val("Add");
	$("#resource_id").select2("val","");
	$("#process_loss").val('');
	$("#process_scrap_tolerance_plus").val('');
	$("#process_scrap_tolerance_minus").val('');
}

function add_process_value()
{
	var resource_id = '';
	if($("#prod_process_id").val()==="")
	{		
		toastr.warning("Select Process Name", "ERROR");
		$("#prod_process_id").select2("focus");
		return false;
	}
	
	if($("#process_priority").val()==="")
	{		
		toastr.warning("Enter Process Priority", "ERROR");
		$("#process_priority").focus();
		return false;
	}
	if($("#process_type_m").val()==="")
	{		
		//alert($("#process_type").val());
		toastr.warning("Select Process Type", "ERROR");
		$("#process_type_m").focus();
		return false;
	}
	if($("#process_time").val()==="")
	{		
		toastr.warning("Select Process Time", "ERROR");
		$("#process_time").focus();
		return false;
	}
	if($("#process_type_m").val()=="1"){
		
		if($("#resource_id").val()==="" || $("#resource_id").val()==null)
		{		
			toastr.warning("Select Resource", "ERROR");
			$("#resource_id").focus();
			return false;
		}else{
			resource_id = $('#resource_id').val();
		}
	}

	if($("#process_loss").val()!=''){
		var value = $("#process_loss").val();
		if(value<0 || value>100){
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_plus").val()!=''){
		var value = $("#process_scrap_tolerance_plus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_minus").val()!=''){
		var value = $("#process_scrap_tolerance_minus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}
	var product_id="";

	if($("#direct_product_id").val()==""){
		if($("#process_sel_product_id").val() != "")
		{
			product_id = $("#process_sel_product_id").val();
		}else
		{
			product_id = $("#product_id").val();
		}
	}
	else{
		product_id = $("#direct_product_id").val();
	}

	
	var process_id = $("#prod_process_id").val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "add_process_value",
			edit_id:$("#edit_id").val(),
			process_id:process_id,
			process_rate:$("#process_rate").val(),
			process_priority:$("#process_priority").val(),
			product_id:product_id,
			process_type:$('#process_type_m').val(),
			process_time:$('#process_time').val(),
			process_opening:$('#process_opening').val(),
			process_loss:$('#process_loss').val(),
			process_scrap_tolerance_plus:$('#process_scrap_tolerance_plus').val(),
			process_scrap_tolerance_minus:$('#process_scrap_tolerance_minus').val(),
			resource_id:resource_id 
		},
		success: function(response)
		{
			var rp_id = $('#rp_id').val();
			
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {

				var process_id = arr.process_id;
				toastr.success("PROCESS ADDED SUCCESSFULLY", "SUCCESS");

			
				show_product_process(1,product_id,'','',rp_id);
			
			process_reset();
			var r= confirm("Are you want to add QC ?");

			if(r) {
				Unloading();
				show_qc_modal(process_id,product_id);
			}

		}
		else if(arr.msg == '0') {
			toastr.warning("SOMETHING WRONG", "ERROR")

		}else if(arr.msg == 'exist'){
			toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
		}
		

		Unloading();

	}
});
}
function manage_resource(type){
		if(type=='2'){
			$('.resource_label_manage').addClass('hide');
			$('.processRate_label_manage').removeClass('hide');
		}else{
			$('.resource_label_manage').removeClass('hide');
			$('.processRate_label_manage').addClass('hide');
		}
	}

	function check_process_loss(param1){
		
		if(param1.value<0 || param1.value>100){
			$("#"+param1.id).val('100');
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}
	}


	function check_scrap_tolerance(param1){
		if(param1.value<0 || param1.value>100){
			$("#"+param1.id).val('100');
			toastr.warning("SCRAP tolerance value should be between 0 to 100.", "WARNING");
			return false;
		}
	}
	function check_duplicate_process(process_id)
{
	// console.log('check_duplicate_process');
	//alert(pro_id);
	if($("#direct_product_id").val()==""){
		var product_id = $("#product_id").val();
	}else{
		var product_id = $("#direct_product_id").val();
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_duplicate_process", product_id : product_id, process_id: process_id },
		success: function(resnse)
		{
			
			if(resnse>0)
			{
				toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
				return false;
			}
			
		}
	});
}
function show_product_process(show_popup,product_id="",bom_version_id="",edit_id="",rp_id='')
{
	if(product_id != ""){
		$("#direct_product_id").val(product_id);	
	}
							// $("#direct_product_id").val('');
							
							//	$("#rp_id").val('');
							
							if(rp_id != '')
							{
								rp_id = rp_id;
							}
							else{
								rp_id = '';
							}
							

							$("#mask1").removeClass('hidden');

							setTimeout(function(){ 
								if(product_id != ""){
									product_id = product_id;
								}
								if(product_id == ""){
									product_id = $("#product_id").val();
								}
								if(bom_version_id == ""){
									bom_version_id = $("#pro_version_id").val();
								}
							/*if(edit_id == ""){
								
							}*/
							edit_id = 1;
								// var product_id = $("#product_id").val();
								// var bom_version_id = $("#pro_version_id").val();
								
							//alert("tets");
							

							
							$.ajax({
								type: "POST",
								url: root_domain+production_domain+'app/request_product/',
								data: { 
									mode : 'get_product_process_data',
									product_id:product_id,
									rp_id:rp_id,
									bom_version_id:bom_version_id,
									edit_id :edit_id
								},
								success: function(data){

									
									$('#mod_per_div_add_process').empty();
									$('#mod_per_div_add_process').html(data);
									CKEDITOR.replace( 'process_desc', {
										enterMode: CKEDITOR.ENTER_BR
									});

									var current_number = $('.process_row').last().attr('data-cid');	

									current_number = current_number ? current_number : 0;
									var new_number = parseInt(current_number) + 1;

									$('.process_priority').val(new_number);
									$('.process_priority_label').html(new_number);
									if(show_popup){
										load_multislect_process();
										
										
										$(".ms-container").css('width',"100% !important");
										$('#preview_bom_add_process_modal').modal('show');
										if($("#multiple_value").val().length > 0){

											var selProcess = $("#multiple_value").val();
											console.log(selProcess);
												// console.log(selProcess);
												const myArr = selProcess.split(",");
												$("#multiple_value").val('');
												for (const item of myArr) { // You can use `let` instead of `const` if you like
												//alert(item);
												$('#process_item').multiSelect('select', item);

											}

										}

									}else{
										bom_process_add();
									}


									$("#mask1").addClass('hidden');
								}		
							});
						},500);

						}