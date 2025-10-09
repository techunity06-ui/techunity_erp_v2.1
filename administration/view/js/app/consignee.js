$(document).ready(function() {
show_consignee_data();

});
function add_consignee(){
	if($("#consignee_comp_name").val()===""){		
		toastr.warning("Enter Consignee Company Name", "ERROR");
		return false;
	}
	if($("#consignee_name").val()===""){		
		toastr.warning("Enter Consignee Name", "ERROR");
		return false;
	}
    var comp_name=$('#consignee_comp_name').val();
    var con_name=$('#consignee_name').val();
	var con_mobile=$('#consignee_mobile').val();
	var con_email=$('#consignee_email').val();
    var con_address = $('#consignee_address').val();
    var country_consinee_id = $('#country_consinee_id').val();
    var state_consinee_id = $('#state_consinee_id').val();
    var city_consinee_id = $('#city_consinee_id').val();
    var gst_consinee_no = $('#gst_consinee_no').val();
    var pin_consinee_no = $('#pin_consinee_no').val();
	var model=$('#model').val();
	var cust_id=$('#ledger_id').val();
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "add_consignee",
					edit_id:$("#edit_id_consignee").val(),
                    comp_name:comp_name,
                    con_name:con_name,
                    con_mobile:con_mobile,
                    con_address : con_address,
                    con_email:con_email,
                    cust_id:cust_id,
                    country_consinee_id: country_consinee_id,
                    state_consinee_id: state_consinee_id,
                    city_consinee_id: city_consinee_id,
                    gst_consinee_no: gst_consinee_no,
                    pin_consinee_no: pin_consinee_no,
                    model: model
        },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
            if(data.msg == '1'){
				//console.log(response);
                $("#consignee_comp_name").val("");
				$("#consignee_name").val("");
			    $("#consignee_mobile").val("");
			    $("#consignee_email").val("");
                $("#consignee_address").val("");
                $("#gst_consinee_no").val("");
                $("#pin_consinee_no").val("");
			    $("#add_consignee_btn").val("Add");
			    show_consignee_data();
			    $("#country_consinee_id").val('101').trigger("change");
			    load_consinee_state('101','state_consinee_id','');
			    $("#state_consinee_id").val('1').trigger("change");
			    load_consinee_city('1','city_consinee_id','');
			    $("#city_consinee_id").val('1').trigger("change");
			    toastr.success("Consignee Added SUCCESSFULLY", "success");
            } else if(data.msg == '2'){
                toastr.warning("Consignee already exist", "ERROR");
            }else if(data.msg == '3') {
            	$("#consignee_comp_name").val("");
				$("#consignee_name").val("");
			    $("#consignee_mobile").val("");
			    $("#consignee_email").val("");
                $("#consignee_address").val("");
                $("#gst_consinee_no").val("");
                $("#pin_consinee_no").val("");
                 $("#add_consignee_btn").val("Add");
				$("#bs-consignee-modal-lg").modal("hide");
				$('#consignee_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');	
                $("#consignee_id").trigger('change')
				$('#consignee_id').select2("val",data.cust_id);
				$('#consignee_add').trigger('reset');
				$("#country_consinee_id").val('101').trigger("change");
				load_consinee_state('101','state_consinee_id','');
				$("#state_consinee_id").val('1').trigger("change");
				load_consinee_city('1','city_consinee_id','');
				$("#city_consinee_id").val('1').trigger("change");
				show_consignee_data();
				toastr.success("Consignee Editd SUCCESSFULLY", "success");
				
			}
            Unloading();
		}
	});
}

function show_consignee_data(){
	
    var cust_id=$('#ledger_id').val();
    var form_mode=$('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "load_consignee_detail", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			$('#table_consignee_details').html(data);				
			Unloading();
		}		
	});
}

function edit_consignee_data(id)
{
	
	//Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "preedit_consignee",  id : id },
		success: function(response)
		{
			//console.log(response);
			var data = jQuery.parseJSON(response);
            $('#consignee_comp_name').val(data.company_name);
			$('#consignee_name').val(data.cust_name);
			$('#consignee_mobile').val(data.cust_mobile);
			$("#consignee_email").val(data.cust_email);
            $("#consignee_address").val(data.cust_address);
            $("#country_consinee_id").select2("val",data.countryid);
            $("#state_consinee_id").select2("val",data.stateid);
            $("#city_consinee_id").select2("val",data.cityid);
            $("#gst_consinee_no").val(data.gst_no);
            $("#pin_consinee_no").val(data.cust_pincode);
			$("#edit_id_consignee").val(id);
			$("#country_consinee_id").select2('val',data.countryid);
			/*$("#state_consinee_id").select2('val',data.stateid);
			$("#city_consinee_id").select2('val',data.cityid);*/

			// load_country(data.countryid);
			// load_state(data.countryid,'state_consinee_id',data.stateid);
			// load_city(data.stateid,'city_consinee_id',data.cityid);

			$("#add_consignee_btn").val("Update");
            show_consignee_data();
			//Unloading();
		}
	});
}


function delete_consignee_data(id)
{
	var r= confirm(" Are you sure, you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/ledger_consignee/',
				data: { mode : "delete_consignee",  eid : id },
				success: function(response)
				{
					var data=jQuery.parseJSON(response);
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
                        show_consignee_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}
				}
			});	
		}
	
}
function load_consinee_state(parentid,control,val1)
{	
        $.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function load_consinee_city(parentid,control,val1)
{	
	//alert(parentid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			//alert(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function add_consignee_open(){
	var cust_id = $('#cust_id').val();
	var cust_name = $("#cust_id option:selected").text();
	if(cust_id){
		$('#bs-consignee-modal-lg').modal('show');
		$("#cuname").html(cust_name);
		$("#ledger_id").val(cust_id);
		//$('#preview_cust_dtls_div').html(obj.html_resp);
			
	}else{
            toastr.warning("Select Company First", "ERROR");
        }
}
function load_cust_consignee(cust_id){
if(cust_id){
		Loading(true);
		$.ajax({
			type:'POST',
			url: root_domain +'app/ledger_consignee/',
			data: { mode:"load_cust_consignee", cust_id:cust_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				$('#consignee_id').html(resp.html_resp);
				$('#consignee_id').select2("val",($("#consignee_id option:eq(1)").val())).select2('focus');
                		Unloading();
			}
		});
	}
}

