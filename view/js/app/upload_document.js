//var datatable;
$(document).ready(function() {
	/*$('#product_amount').hover(function(){
       var pro_amt = $('#product_amount').val();
		$('#product_amount').attr("title",pro_amt);
	});*/
	show_upload_docs();
	// validate vendor add form on keyup and submit
	$("#upload_add").validate({
		rules: {
			docs_id: {
				required: true			
			},
			doc_file: {
				required: true			
			}
		},
		messages: {
			docs_id: {
				required: "Select Document Type"
			},
			doc_file: {
				required: "Select File"
			}
		}
	}); 
});


function upload_docs(id) 
{ 
	//check if form is valid
	if (!$("#upload_add").valid()) {
		return false;
	}

	//check valid type of uploaded file
    // var ext = $('#doc_file').val().split('.').pop().toLowerCase();
    // if($.inArray(ext, ['gif','png','jpg','jpeg','pdf']) === -1) {
    //         toastr.warning("Only image type jpg/png/jpeg/gif/pdf is allowed", "ERROR");
    //         $("#doc_file").focus();
    //         return false;
    // } 

    var data = new FormData();
    data.append('file', $('#doc_file').prop('files')[0]);
    data.append("mode",$('#img_mode').val());
    data.append("l_id",$('#l_id').val());
    data.append("docs_id",$('#docs_id').val());

	// alert(form_data);
	$.ajax({
		url: root_domain+administration_domain+'app/ledger/',
		method:"POST",
		data: data,
		contentType: false,
		cache: false,
		processData: false,
		beforeSend:function(){
		 //$('#uploaded_image').html("<label class='text-success'>Image Uploading...</label>");
		 Loading(true);	
		},   
		success:function(data)
		{
			Unloading();
			show_upload_docs();
		}
	});
}

function show_upload_docs()
{
	var l_id = $('#l_id').val();
	//alert(l_id);
	
	$.ajax({

		url: root_domain+administration_domain+'app/ledger/',
		method:"POST",
		data: { mode : "show_upload_docs", l_id:l_id },
		success: function(data){
			//console.log(data);
			$('#show_document').html(data);				
			Unloading();
		}		
		
	});
}
function delete_docs(ed_id){
	// alert(ed_id);
	$.ajax({
		url: root_domain+administration_domain+'app/ledger/',
		method:"POST",
		data: { mode : "delete_docs", ed_id:ed_id },
		success: function(data){
			//console.log(data);
			show_upload_docs();				
			Unloading();
		}
	});
}