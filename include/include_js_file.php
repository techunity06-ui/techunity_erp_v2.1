<div id="mask" class="hidden-xs" style="height: auto;">
		<div style="position:fixed;left: 45%;margin-left: -25%px;">
			<img src="<?=ROOT?>img/loading_lg.gif">
			<h1> Loading ... </h1>
		</div>
    </div>
<style>
.swal-text{
	color: red !important;
}	
</style>
 
<script src="<?=ROOT?>js/canvasjs.min.js"></script>

<script src="<?=ROOT?>js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="<?=ROOT?>js/jquery.dcjqaccordion.2.7.js"></script>
<script src="<?=ROOT?>js/jquery.scrollTo.min.js"></script>
<script src="<?=ROOT?>js/jquery.nicescroll.js" type="text/javascript"></script>
<!--form Validation js-->
<script src="<?=ROOT?>js/jquery.steps.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?=ROOT?>js/jquery.validate.min.js"></script>
<!--<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>-->
<!--Gallery-->
<script src="<?=ROOT?>assets/fancybox/source/jquery.fancybox.js"></script>
<script src="<?=ROOT?>js/modernizr.custom.js"></script>
<!--For multiselect-->
<script type="text/javascript" src="<?=ROOT?>assets/jquery-multi-select/js/jquery.multi-select.js"></script>
  <script type="text/javascript" src="<?=ROOT?>assets/jquery-multi-select/js/jquery.quicksearch.js"></script>
<!--For Wysihtml editor-->
  <script type="text/javascript" src="<?=ROOT?>assets/fuelux/js/spinner.min.js"></script>
<!--Datatable js-->

<!--<script type="text/javascript" language="javascript" src="<?=ROOT?>assets/advanced-datatable/media/js/jquery.dataTables.js"></script>-->
<script type='text/javascript' src='<?=ROOT?>assets/data-tables/jquery.datatables.min.js'></script>
<script type='text/javascript' src='<?=ROOT?>assets/data-tables/datatables.js'></script>
<!--<script type="text/javascript" src="<?=ROOT?>assets/data-tables/DT_bootstrap.js"></script>
<script type='text/javascript' src='<?=ROOT?>assets/data-tables/jquery.datatables.min.js'></script>-->

<script src="<?=ROOT?>js/respond.min.js" ></script>
<!--Message-->
<script src="<?=ROOT?>assets/toastr-master/toastr.js"></script>

<script type="text/javascript" src="<?=ROOT?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?=ROOT?>assets/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>
<script type="text/javascript" src="<?=ROOT?>assets/bootstrap-daterangepicker/moment.min.js"></script>

<script type="text/javascript" src="<?=ROOT?>assets/bootstrap-daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="<?=ROOT?>assets/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
<script type="text/javascript" src="<?=ROOT?>js/moment-with-locales.min.js"></script>
<!--<script type="text/javascript" src="<?=ROOT?>js/bootstrap-datetimepicker.min.js"></script>-->
<script type="text/javascript" src="<?=ROOT?>js/summernote.min.js"></script>
<!--common script for all pages-->
<script src="<?=ROOT?>js/common-scripts.js?<?=date('dmy');?>"></script>
<script type='text/javascript' src='<?=ROOT?>js/jquery.select2/select2.min.js' ></script>
<script src="<?=ROOT?>js/jquery.cookies.js"></script>
<script type="text/javascript" src="<?=ROOT?>js/moment.js"></script>
<script type="text/javascript" src="<?=ROOT?>js/daterangepicker.js"></script>
<!--<script type="text/javascript" src="<?=ROOT?>js/shortcut.js"></script>-->
<!--<script type="text/javascript" src="<?=ROOT?>js/ckeditor/ckeditor.js"></script>-->
<!--<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>-->



<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>


<script type="text/javascript" src="<?=ROOT?>js/jquery-clockpicker.min.js"></script>
<script type="text/javascript" src="<?=ROOT?>assets/nestable/jquery.nestable.js"></script>
<script type="text/javascript" src="<?=ROOT?>js/sweetalert.min.js"></script> 
<script type="text/javascript" src="<?=ROOT?>js/jquery.base64.min.js"></script> 
<script type="text/javascript" src="<?=ROOT?>js/jquery.table2excel.js"></script> 
<script src="<?=ROOT?>view/js/lodash.min.js" ></script>
<script src="<?=ROOT?>js/select2.min.js" ></script>
		
<script>


document.addEventListener('DOMContentLoaded', function() {
            if (typeof CKEDITOR !== 'undefined') {
                // Find the instance of CKEditor and set the configuration
                for (var instance in CKEDITOR.instances) {
                    if (CKEDITOR.instances.hasOwnProperty(instance)) {
                        CKEDITOR.instances[instance].config.versionCheck = false;
                    }
                }
            }
        });

function change_company()
{
		var r= confirm(" Are you sure to change company?");
		if(r) {
		open_company_modal(1)
		}
}
/*
shortcut.add("Ctrl+i",function() {
	window.location=root_domain+"invoice";
});
shortcut.add("Ctrl+l",function() {
	window.location=root_domain+"invoice_list";
});
shortcut.add("Ctrl+d",function() {
	window.location=root_domain+"dashboard";
});
shortcut.add("Esc",function() {
	$("#show_todotask").modal("hide");
	$("#add_todotask").modal("hide");
});
*/
function open_company_modal(val)
{
	if(val==1)
	{
		$("#company_modal").modal("show");
	}
	else if(val==2)
	{
		$("#company_modal").modal("hide");
	}
}
function create_com()
{
	window.location=root_domain+"create_company";
	
}
function pass_session(company_name,company_id)
{
	$("#login_company").html(company_name);
	$("#logincompany_id").val(company_id);
	$("#company_modal").modal("show");
	$("#companylogin_modal").modal("show");
	
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/dashboard/',
				data: { mode : "pass_session",  company_name : company_name,company_id:company_id },
				success: function(response)
				{
					$("#loginusername").focus();
					//console.log(response);
					var res=jQuery.parseJSON(response);
					if(res.msg=="1")
					{
						window.location=root_domain+'dashboard';
						$("#company_modal").modal("hide");
						$("#companylogin_modal").modal("hide");
					}
					else if(res.msg=="0")
					{
						$("#loginusertype_id").html(res.response);
					}
					Unloading();
				}
	});	
	/*	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/dashboard/',
				data: { mode : "pass_session",  company_name : company_name,company_id:company_id },
				success: function(response)
				{
					console.log(response);
					$("#company_modal").modal("hide");
					$("#session_com").html(response);
					Unloading();
				}
			});	*/
}
function change_top_status(id,todo_status) 
{
	var r= confirm(" Are you want to Change Task Status ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/todomst/',
				data: { mode : "change_status", eid : id, todo_status:todo_status },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("TASK SUCCESSFULLY COMPLETED", "SUCCESS");
						Unloading();
						location.reload();
						/*show_todolist();
						datatable.fnReloadAjax();*/
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

//Amish Soni Start 04-03-2021
function general_task_mark_read(id)
{
    Loading();
    $.ajax({
        type: "POST",
        url: root_domain+crm_domain+'app/task/',
        data: { mode : "mark_read", eid : id },
        success: function(response)
        {
            prevCount = parseInt($('.bg-important.gt_count').text());
            newCount = prevCount - 1;
            newCount = newCount < 0 ? 0 : newCount;
            $('.gt_count').text(newCount);
            $('.task_list_'+id).hide();
        }
    });
    Unloading();
}
//Amish Soni End 04-03-2021

function isNumberKey(evt)
{
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31 
	&& (charCode < 48 || charCode > 57))
	 return false;

  return true;
}

function validateFloatKeyPress(el) {
    var v = parseFloat(el.value);
    el.value = (isNaN(v)) ? '' : v.toFixed(2);
}


function showAttendance(user)
{
	//alert(user);
	if(user=='3' || user=='5')
	{
		$('#emp_atten').show();
	}
	else
	{
		$('#emp_atten').hide();
	}
	$('#emp_atten').hide();
}
function showipadd(ip_add_login)
{
	//alert(user);
	if(ip_add_login=='1')
	{
		$('#ip_login').show();
	}
	else
	{
		$('#ip_login').hide();
	}
}

function setUsertype(username)
{
	
	var login_company_id=$("#logincompany_id").val();
	if(login_company_id!=""){
	if(username == ''){
		$("#usertype").hide();
		$("#loginusertype_id").val('');
	} else {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard/',
			data: { mode : "get_usertype",  username : username,login_company_id:login_company_id},
			success: function(response)
			{
				//console.log(response);
				var res=jQuery.parseJSON(response);
				if(res.msg=="1")
				{
					$("#loginusertype_id").val(res.usertype_id);
					$("#loginusertype_label").html(res.response);
					showAttendance(res.usertype_id);
					showipadd(res.ip_add_login);

				}
				else if(res.msg=="0")
				{
					$("#loginusername").focus();
					$("#loginusertype_id").val('');
					$("#loginusertype_label").html(res.response);
				}
				Unloading();
				$("#usertype").show();
			}
		});
	}
}
}


/*$(window).resize(function(){

    width = GetWidth();
	
    if ( width == GetWidth() ) {
      return;
    }

    if(width < 600){
      $('#nav-accordion').hide();
    } else {
      $('#nav-accordion').show();
    }

});*/
$(document).on('keyup', '#projectProductTrn', function () {
    var value = this.value.toLowerCase().trim();

    $("#project-product-table tr").each(function (index) {
        if (!index) return;
        $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
        });
    });
});

//Added By Dhruv
//function blockSpecialChar(e){
jQuery('.blockSpecialChar').keypress(function (e) {

	var keyCode = e.keyCode || e.which;

	$("#lblError").html("");

	//Regex for Valid Characters i.e. Alphabets and Numbers.
	var regex = /^[A-Za-z0-9]+$/;

	//Validate TextBox value against the Regex.
	var isValid = regex.test(String.fromCharCode(keyCode));
	if (!isValid) {
		$("#lblError").html("Only Alphabets and Numbers allowed.");
	}

	return isValid;
});

 //End Code By Dhruv


/*  ADDED BY SANAT ::  ONLY DIGIT ALLOWED WITH DECIMAL  */

jQuery('.numbersOnly').keydown(function (evt) {

/*if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }*/
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if ((charCode == 110 || charCode == 190) || (charCode > 95 && charCode < 106)) {
    	
        	return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	

});

/*jQuery('.digitOnly, .numbersOnly, .copyPastNotAllowed').bind('cut copy paste', function(e) {
	 e.preventDefault();
	 toastr.warning("Cut / Copy / Paste Disabled", "WARNING");
 });*/



/* ADDED BY SANAT ::  ONLY DIGIT ALLOWED (NO DECIMAL OR SPECIAL CHARACTER) */
jQuery('.digitOnly').keydown(function (evt) {

/*if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }*/
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if (charCode > 95 && charCode < 106) {
    		return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	

});


jQuery('.dateOnly').keydown(function (evt) {
	//alert(evt.keyCode);

if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if (charCode > 95 && charCode < 106) {
    	
        	return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	

});

function numericonly(evt){
	/*if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }*/
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if ((charCode == 110 || charCode == 190) || (charCode > 95 && charCode < 106)) {
    	
        	return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	
}


function digitonly(evt)
{
	/*
	if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }*/
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if (charCode > 95 && charCode < 106) {
    	
        	return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	
} 

function numbersOnly(evt) {

/*if (evt.shiftKey || evt.ctrlKey || evt.altKey) {
              evt.preventDefault();
              return;
          }*/
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    // alert(charCode);
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    	if ((charCode == 110 || charCode == 190) || (charCode > 95 && charCode < 106)) {
    	
        	return true;
    	}else{
    	
    		return false;
    	}
    }
    return true;	

}


function decimalonly()
{
	if (event.keyCode < 48 || event.keyCode > 57 || event.keyCode == 9 || event.keyCode == 190 || event.keyCode == 46  ) 		{
	toastr.warning("Allow Only Numeric Value 0-9", "WARNING");
	event.preventDefault(); 
	this.value = '';
	}  
} 

/* Only Letter  Allowed */
 $('.txtOnly').keydown(function (e) {  
          if (e.shiftKey || e.ctrlKey || e.altKey) {
              e.preventDefault();
          } else {
              var key = e.keyCode;
              if (!((key == 8) || (key == 32) || (key == 46) || (key >= 35 && key <= 40) || (key >= 65 && key <= 90))) {
                  e.preventDefault();
              }
          }
      });   

/* focus branch */

function focus_branch(branch_id,main_id)
{
	//alert(branch_id);
	if($('#'+branch_id).val()=='')
	{
		toastr.warning("PLEASE SELECT BRANCH", "ERROR")
		$('#'+branch_id).select2("focus");
		$('#'+main_id).select2("val","");
		return false;
	}
}

//Added by dhruv
function validemail(email)
{
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test(email); //this will either return true or false based on validation
}


// added by sanat for check reorder qty
function isInteger(n) {
   return n % 1 === 0;
}


</script>
<?php
			include_once("company_modal.php");
			include_once("companylogin_modal.php");
		
			
			if(empty($_SESSION['company_name']))
			{
				if(strtolower(end(explode("/",$_SERVER['REQUEST_URI'])))!="create_company")
				{
					echo "<script>open_company_modal(1)</script>";
				}
			}
?>