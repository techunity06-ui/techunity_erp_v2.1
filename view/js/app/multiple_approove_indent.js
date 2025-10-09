$(document).ready(function() {
	get_pending_work_order_no();
	get_pending_indent_no();
	get_pending_product();
	load_pending_indent();
	//check_box_limit();
	//all_check_box();
});

function get_pending_work_order_no(){
	var branch_id = $("#branch_id").val();
	var val1 = '';
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "work_order_no",  branch_id : branch_id},
		success: function(responce){
			
			$('#work_order_no').html(responce);
			$("#work_order_no").select2("val",val1);
		}
	});
}

function get_pending_indent_no(){
	var branch_id = $("#branch_id").val();
	var val1 = '';
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "get_indent_no",  branch_id : branch_id},
		success: function(responce){
			
			$('#indent_no').html(responce);
			$("#indent_no").select2("val",val1);
		}
	});
}
function get_pending_product(){
	var branch_id = $("#branch_id").val();
	var val1 = '';
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "get_pro",  branch_id : branch_id},
		success: function(responce){
			
			$('#product_id').html(responce);
			$("#product_id").select2("val",val1);
		}
	});
}
function load_pending_indent(){
	var work_ono = $('#work_order_no').val();
	var indent_no = $('#indent_no').val();
	var product_id = $('#product_id').val(); 
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "load_pending_indent", work_ono:work_ono, indent_no:indent_no, product_id:product_id},
		success: function(responce){
			//alert(responce);
			var resp=JSON.parse(responce);
			$('#multiple_appr_data').html(resp.html_resp);
			//$("#indent_no").select2("val",val1);
		}
	});
}


function check_all()
{
	var max_limit=50;
	
	if($("#all_chk_box").is(':checked')){
		$('.chk_box').each(function(){
			var chelen = $(".chk_box:checked").length;
	//alert(chelen);
			if (chelen < max_limit){
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		});
	}else{
		$('.chk_box').each(function(){
			this.checked = false;
		});
	}
}


function check_box_limit(cid){
	var max_limit = 50;
	var chelen = $(".chk_box:checked").length;
	if (chelen > max_limit){
		$('#'+cid).attr('checked', false);
	}
}



function get_indent_no(id){
	var val1 = '';
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "work_or_in",  id : id},
		success: function(responce){
			
			$('#indent_no').html(responce);
			$("#indent_no").select2("val",val1);
		}
	});
}
function get_inden_pro(id){
	var val1 = '';
	$.ajax({
		type: "POST",
		url: root_domain+'app/multiple_approove_indent/',
		data: { mode : "get_indentnowise_pro",  id : id},
		success: function(responce){
			
			$('#product_id').html(responce);
			$("#product_id").select2("val",val1);
		}
	});
}
function updateCounter() {
    var numberOfChecked = $('input[name="che_box[]"]:checked').length;
	$('#chk_sel_count').html(numberOfChecked);
	if(numberOfChecked != ""){
		$("#save").show();
	}else{
		$("#save").hide();
	}
}

$("#multiple_indent_approove").on('submit',function(e) {
	var	approove_id = $("input[name='che_box[]']:Checked").map(function(){return $(this).val();}).get();
	var approve_qty_arr=[];
	var quotation_requirement_arr=[];
	var indent_id_arr=[];
	var max_approve_qty_arr=[];
	
	var approve_qty = $('input[name="approve_qty[]"]').val();
	var quotation_requirement = $('select[name="quotation_requirement[]"]').val();
	var indent_id = $('input[name="che_box[]"]:Checked').val();
	var max_approve_qty = $('input[name="max_approve_qty[]"]').val();
	
	i = 0;j =0; k = 0; l = 0;
	$("input[name='che_box[]']:Checked").each(function(){
		
		var ch = this.id;
		var no = ch.slice(7,8);
		
		if($('#approve_qty'+no).val() != ""){
			approve_qty_arr[i++]=$('#approve_qty'+no).val();
		}
		if($('#quotation_requirement'+no).val() != ""){
			quotation_requirement_arr[j++]=$('#quotation_requirement'+no).val();
		}
		if($("#"+ch).val() != ""){
			indent_id_arr[k++]=$("#"+ch).val();
		}
		
		if($("#max_approve_qty"+no).val() != ""){
			max_approve_qty_arr[l++]=$("#max_approve_qty"+no).val();
		}

		//right code for array
		/*$('input.approve_qty').each(function(){
			approve_qty_arr[i++]=$(this).val();
		});*/
	
		//right code for array
		/*$('select.quotation_requirement').each(function(){ 
			quotation_requirement_arr[i++]=$(this).val();
		});*/
	});
	if(approove_id==""){
		toastr.warning("Please Select Product", "ERROR")
		return false;
	}else{
		var form = this;
		e.preventDefault();
		e.stopPropagation();	
		if (!$("#multiple_indent_approove").valid()) {
			return false;
		}
		form.submitted = true;	
		Loading(true);	
		$(this).attr("disabled","disabled");
		
		var form_data = {
			approve_qty: approve_qty_arr,
			quotation_requirement: quotation_requirement_arr,
			indent_id: indent_id_arr,
			max_approve_qty : max_approve_qty_arr,
			mode:'multiple_indent_approove',
		};
		
		
		$.ajax({
			cache:false,
			url: root_domain+'app/multiple_approove_indent/',
			type: "POST",
			data: form_data,
			//contentType: false,
			//processData:false,
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					toastr.success("Indent Approve SuccessFully", "SUCCESS");
					window.location=root_domain+'indent_list';
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
				}
				
				$('#multiple_indent_approove').trigger('reset');	

				Unloading();
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}
	//return false;
});
