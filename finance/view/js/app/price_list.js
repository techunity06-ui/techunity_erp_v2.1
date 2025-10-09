//var datatable;

function load_datatable()
{
 	datatable = $("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain + finance_root_domain +'app/price_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}


$("#price_list_add").validate({
	rules: {
		
		branch_id:{
			required: true			
		},
		effective_date: {
			required: true			
		},
		expiry_date: {
			required: true
		},
		price_version: {
			required: true
		},
		
	},
	messages: {
		branch_id:{
			required: "Select Branch"			
		},
		invoice_date: {
			required: "Enter date"
		},
		expiry_date: {
			required: "Select Expiry Date"
		},
		price_version: {
			required: "Enter Price List Version"
		},
		
		
	}
}); 
	


$("#price_list_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#price_list_add").valid()) {
		return false;
	}
	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+ finance_root_domain+'app/price_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+ finance_root_domain+'price_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				
				window.location=root_domain + finance_root_domain +'price_list';
						
			}
			$('#price_list_create').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function load_bom_product_detail()
{
	Loading(true);
	
	var eid = $('#eid').val();
	
	if($("#product_id").val()!='')
	{
		//alert(product);
		var check_price = [];
		$("input:checkbox[name=check_price]:checked").each(function(){
			check_price.push($(this).val());
		});
		
		//console.log(check_price);
		$.ajax({
			
			type:'POST',
			url:root_domain+finance_root_domain+'app/price_list/',
			data:{ mode:"load_bom_product_detail",product:$('#product_id').val(),check_price:check_price,eid:eid},
			success:function(result)
			{
				//alert(result);
				//console.log(result);
				$('.product_details_show').html(result);
				list_price_list_products(eid,vbtn=1);
				//edit_price_product($('#product_id').val(),eid);

			}
		});
		
		$('.error_1').hide();
	}
	else{
		$('.error_1').show();
		$("#product_id").focus();
	}
	
	Unloading(true);
}




function add_product_price()
{
	//alert('hello');
	
	var profit_amt = $('#profit_amt').val();
	var profit_per = $('#profit_per').val();
	var expense_amt = $('#expense_amt').val();
	var expense_per = $('#expense_per').val();
	var total = $('#total').val();
	var disc_amt = $('#disc_amt').val();
	var disc_per = $('#disc_per').val();
	var sale_price = $('#sale_price').val();
	var product_id = $('#product_id').val();
	var landing_cost = $('#landing_cost').val();
	var price_list_id = $('#price_list_id').val();
	
	
	$.ajax({
		
		type:'POST',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{mode:"add_product_price_list",profit_amt:profit_amt,profit_per:profit_per,expense_amt:expense_amt,expense_per:expense_per,total:total,disc_amt:disc_amt,disc_per:disc_per,sale_price:sale_price,product_id:product_id,price_list_id:price_list_id,landing_cost:landing_cost},
		success:function(response)
		{
			if(response > 0)
			{
				toastr.success("Product Added Successfully", "SUCCESS");
			}
			else
			{
				toastr.warning("Something Went Wrong", "WARNING");
			}
			
			$('#profit_amt').val('');
			$('#profit_per').val('');
			$('#expense_amt').val('');
			$('#expense_per').val('');
			$('#total').val('');
			$('#disc_amt').val('');
			$('#disc_per').val('');
			$('#disc_per').val('');
			$('#sale_price').val('');
			
			list_price_list_products(price_list_id);
			//alert(response);
		}
		
	})
	
}


function list_price_list_products(price_list_id,vbtn=0)
{
	
	$.ajax({
		
		type:'POST',
		data:{price_list_id:price_list_id,mode:"list_price_list_products"},
		url:root_domain+finance_root_domain+'app/price_list/',
		success:function(response){
			
			console.log(response);
			
			$('.price_list_details_show').html(response);
			
			if(vbtn==1)
			{
				$(".price_list_details_show").removeClass('col-md-12');
				$(".price_list_details_show").addClass('col-md-8');
			}
			
		}
	})
}

function edit_price_product(product_id,eid)
{
	
	//alert(product_id);
	//alert(eid);
	
	$.ajax({
		
		type:'POST',
		data:{product_id:product_id,mode:"edit_price_product",eid:eid},
		url:root_domain+finance_root_domain+'app/price_list/',
		success:function(response)
		{
			console.log(response);
			
			var obj = JSON.parse(response);
			
			//alert(obj.price_list_id);
			//load_bom_product_detail();
			//$('.product_details_show').html(response);
			var check_price = [];
			$("input:checkbox[name=check_price]:checked").each(function(){
				check_price.push($(this).val());
			});
			
			//console.log(check_price);
			$.ajax({
				
				type:'POST',
				url:root_domain+finance_root_domain+'app/price_list/',
				data:{ mode:"load_bom_product_detail",product:obj.product_id,check_price:check_price,eid:obj.price_list_id},
				success:function(result)
				{
					//alert(result);
					//console.log(result);
					$('.product_details_show').html(result);
					list_price_list_products(eid,vbtn=1);
					
					$('.error_1').hide();
			
					$('#profit_amt').val(obj.profit_amt);
					$('#profit_per').val(obj.profit_per);
					$('#expense_amt').val(obj.expense_amt);
					$('#expense_per').val(obj.expense_per);
					$('#total').val(obj.total);
					$('#disc_amt').val(obj.disc_amt);
					$('#disc_per').val(obj.disc_per);
					$('#sale_price').val(obj.product_sale_price);
					
				}
			});
			
			
			
			//console.log(response);
		}
	})
	
}

function relase_version(id)
{
	//alert(id);
	$.ajax({

		type:'post',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{id:id,'mode':'relase_version'},
		success:function(result)
		{
			toastr.success("This version is released",'success');
			load_datatable();
		}
	})
}

function unrelase_version(id)
{
	//alert(id);
	$.ajax({

		type:'post',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{id:id,'mode':'unrelase_version'},
		success:function(result)
		{
			toastr.success("This version is un-released",'success');
			load_datatable();
		}
	})
}

function get_profit_amt(type)
{
	var landing_cost = parseFloat($('#landing_cost').val());
	var profit_amt = parseFloat($('#profit_amt').val());
	var profit_per = parseFloat($('#profit_per').val());
	var expense_amt = parseFloat($('#expense_amt').val());

	//alert(type);
	console.log(landing_cost);
	if(landing_cost!="")
	{	
		if(type=="amt")
		{
			if($('#profit_amt').val()!='')
			{
				disc=Number(100*parseFloat($('#profit_amt').val())/landing_cost);
				var  disc1=Number(disc.toFixed(2));			
				$('#profit_per').val(disc1);

				var total = profit_amt+landing_cost+expense_amt;
				$('#sale_price').val(total);
				$('#total').val(total);
				console.log(total);
			}
			else
			{
				$('#profit_per').val('');
				$('#sales_price').val(landing_cost+expense_amt);
				$('#total').val(landing_cost+expense_amt);
			}
		}
		else if(type=="per")
		{
			if($('#profit_per').val()!='')
			{
				disc=Number(((landing_cost)*parseFloat($('#profit_per').val()))/100);	
				var	disc1=Number(disc.toFixed(2));
				$('#profit_amt').val(disc1);

				var total = disc1+landing_cost+expense_amt;
				$('#sale_price').val(total);
			}
			else
			{
				$('#profit_amt').val('');
				$('#sales_price').val(landing_cost+expense_amt);
				$('#total').val(landing_cost+expense_amt);
			}
		}
	}
	else
	{
		$('#profit_amt').val('');
		$('#profit_per').val('');
		$('#sales_price').val(landing_cost);
	}
}


function get_expense_amt(type)
{
	var landing_cost = parseFloat($('#landing_cost').val());
	var expense_amt = parseFloat($('#expense_amt').val());
	var expense_per = parseFloat($('#expense_per').val());
	var profit_amt = parseFloat($('#profit_amt').val());

	//alert(type);
	console.log(landing_cost);
	if(landing_cost!="")
	{	
		if(type=="amt")
		{
			if($('#expense_amt').val()!='')
			{
				disc=Number(100*parseFloat($('#expense_amt').val())/landing_cost);
				var  disc1=Number(disc.toFixed(2));			
				$('#expense_per').val(disc1);

				var total = expense_amt+landing_cost+profit_amt;
				$('#sale_price').val(total);
				$('#total').val(total);

				console.log(total);
			}
			else
			{
				$('#expense_per').val('');
				$('#sales_price').val(landing_cost+profit_amt);
				$('#total').val(landing_cost+profit_amt);
			}
		}
		else if(type=="per")
		{
			if($('#expense_per').val()!='')
			{
				disc=Number(((landing_cost)*parseFloat($('#expense_per').val()))/100);	
				var	disc1=Number(disc.toFixed(2));
				$('#expense_amt').val(disc1);

				var total = disc1+landing_cost+profit_amt;
				$('#sale_price').val(total);
				$('#total').val(total);
			}
			else
			{
				$('#expense_amt').val('');
				$('#sales_price').val(landing_cost);
				$('#total').val(landing_cost);
			}
		}
	}
	else
	{
		$('#profit_amt').val('');
		$('#profit_per').val('');
		$('#sales_price').val(landing_cost);
	}
}

function get_disc_amt(type)
{
	var landing_cost = parseFloat($('#total').val());
	var expense_amt = parseFloat($('#expense_amt').val());
	var profit_amt = parseFloat($('#profit_amt').val());
	var disc_amt = parseFloat($('#disc_amt').val());
	var disc_per = parseFloat($('#disc_per').val());

	//alert(type);
	console.log(landing_cost);
	if(landing_cost!="")
	{	
		if(type=="amt")
		{
			if($('#disc_amt').val()!='')
			{
				disc=Number(100*parseFloat($('#disc_amt').val())/landing_cost);
				var  disc1=Number(disc.toFixed(2));			
				$('#disc_per').val(disc1);

				var total = landing_cost-disc_amt;
				$('#sale_price').val(total);

				console.log(total);
			}
			else
			{
				$('#disc_per').val('');
				$('#sales_price').val(landing_cost-disc_amt);
				$('#total').val(landing_cost-disc_amt);
			}
		}
		else if(type=="per")
		{
			if($('#disc_per').val()!='')
			{
				disc=Number(((landing_cost)*parseFloat($('#disc_per').val()))/100);	
				var	disc1=Number(disc.toFixed(2));
				$('#disc_amt').val(disc1);

				var total = landing_cost-disc_amt;
				$('#sale_price').val(total);
			}
			else
			{
				$('#disc_amt').val('');
				$('#sales_price').val(landing_cost);
			}
		}
	}
	else
	{
		$('#profit_amt').val('');
		$('#profit_per').val('');
		$('#sales_price').val(landing_cost);
	}
}

function get_group_customer(type,eid="")
{
	//alert(type);
	$.ajax({

		type:'POST',
		url:root_domain+finance_root_domain+'app/price_list/',
		data:{mode:'get_group_customer',type:type,eid:eid},
		success:function(response)
		{
			var obj = JSON.parse(response);
			console.log(obj.edit_id);
			$('#relase_version').empty().append(obj.result);
			$("#relase_version").select2("val",obj.edit_id);
		}

	})
}