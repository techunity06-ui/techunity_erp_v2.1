//var datatable;


function load_price_list_detail()
{
	var bom_version_id = $('#bom_version_id').val();
	var bom_product_id = $('#bom_product_id').val();
	var eid = $('#eid').val();
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{bom_product_id:bom_product_id,bom_version_id:bom_version_id,mode:"load_tempoutward",eid:eid},
		success:function(result)
		{
			console.log(result);
			$('#bom_productdata').html(result);
		}
	})
}



function highlightEdit(editableObj) {
	$(editableObj).css("background","#FFF");
} 

function saveInlineEdit(editableObj,column,id) {
	//console.log(id);
	//alert('hii');
	// no change change made then return false
	
	// send ajax to update value
	$(editableObj).css("background","#FFF no-repeat right");
	$.ajax({
		type:'POST',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:'column='+column+'&value='+editableObj.innerHTML+'&id='+id+'&mode=saveInlineEdit',
		success: function(response)  {
			console.log(response);
			// set updated value as old value
			$(editableObj).attr('data-old_value',editableObj.innerHTML);
			$(editableObj).css("background","#FDFDFD");			
		}          
   });
}

function saveInlineEdit1(text_id,text_value) {
	
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{text_id:text_id,text_value:text_value,mode:"save_pro_price"},
		success:function(response)
		{
			console.log(response);
			//alert(response);
		}
	})
	
}



function show_alloted_data()
{
	//var form_mode= $("#mode").val();
	var sel_product_id= $("#bom_product_id").val();
	var sel_bom_version_id= $("#bom_version_id").val();
	var eid= $("#eid").val();
	var main_product_id= $("#main_product_id").val();
	//alert(eid);
	//get_bom_id(sel_product_id,sel_bom_version_id);

	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/price_list/',
		data: { mode : "load_alloted_tempoutward",sel_product_id:sel_product_id,bom_version_id:sel_bom_version_id,eid:eid,main_product_id:main_product_id },
		success: function(data){
			console.log(data);
			$('#bom_productdata').html(data);		
			Unloading();
		}		
	});
}


function get_bom_id(pro_id,bom_version_id)
{
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_id", pro_id : pro_id, bom_version_id:bom_version_id },
		success: function(resnse)
		{
			if(resnse>=1)
			{

				$('#bom_id').val(resnse);
				$('#parent_id').val(resnse);
				// console.log('bom id ->' + resnse)
			}
		}
	});
}
