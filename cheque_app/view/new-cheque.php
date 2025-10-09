<?php
	session_start();
	include("../../config/config.php");
	$backurl = $_SERVER['HTTP_REFERER'];
	include("../../config/session.php");
	include("../../include/common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	if(strpos($_SERVER['REQUEST_URI'], "generate-cheque")==true)
	{
		$mode="direct_cheque";		
		$event_date=date('d-m-Y');	
		$id=$dbcon->real_escape_string($_REQUEST['id']);
                $query="SELECT pay.*,acc.acc_name,pay.vender_id,acc.branch_name,bank.bank_name,ven.l_name,banktrn.credit_accid 
                    FROM `tbl_payment_cheque_generate` as pay 
                    left join account_mst as acc on acc.acc_id=pay.acc_id 
                    left join bank_mst as bank on bank.bankid=acc.bankid 
                    left join tbl_ledger as ven on ven.l_id=pay.vender_id 
                    left join tbl_banktransaction as banktrn on banktrn.tansactionid=pay.tansactionid
                    where chequegenerateid=".$id;
                
		$rs=$dbcon->query($query);
		$rel=mysqli_fetch_assoc($rs);
                //echo '<pre>'; print_r($rel);exit;
		$acc_id=$rel['acc_id'];
		$amount=$rel['amount'];
		$cheque_num=$rel['cheque_num'];
		if($rel['vender_id']!=""){
                    $payeeid = $rel['vender_id'];
                    $payename = $rel['l_name'];
                    $payeetype = "Ven";
		}else{
			$payeeid=$rel['credit_accid'];
			$craditid=$rel['credit_accid'];
			if($craditid==$rel['acc_id']){
				$payename = "SELF";
			}else{
				$qu="select * from account_mst where acc_id=".$craditid;
				$rse=$dbcon->query($qu);
                                $req=mysqli_fetch_assoc($rse);
                                $payename=$req['acc_name'];
                                $payeetype="Acc";
			}
		}
		//$payename=$rel['company_name'];
                $date=date('d-m-Y', strtotime($rel['cheque_date']));;
	}
	else
	{
		$acc_id=0;
		$amount=0;
		$payeeid=0;
		$date=date('d-m-Y');
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Generate New Cheque";
		include("../common/head.php");
	?>
	<link href='<?=ROOT_CHEQUE?>js/jquery.crop/css/jquery.Jcrop.css' rel='stylesheet' type='text/css'>
	<style type="text/css">
		#cheque {
			margin:0px;
			padding:0px;
			font-family: 'Roboto', sans-serif;
			/*background:rgba(255,255,255,0.5) url(upload/check/hdfc_bank.png) no-repeat;*/
			width:8.05in;
			height:3.85in;
		}
		.cheque-item {
			display: inline-block;
			margin:0px;
			padding:0px;
			/*background:rgba(0,0,0,0.2);
			cursor:pointer;
			color:#fff;*/
			background:rgba(0,0,0,0);
			color:#000;
			font-size:12px;
			line-height: 12px;
		}
		
		#amount-number {
			font-size:16px;
			line-height: 16px;
		}
		
		.vertical {
			top:220px;
			/* FF Chrome Opera etc */
			-webkit-transform: rotate(90deg); 
			-moz-transform: rotate(90deg);
			-o-transform: rotate(90deg);
			/* IE */
			filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
		}
		
		#img_nn {
			position: absolute;
			top : 15px;
			left : 300px;
		}
		
		#img_ac_f {
			position: absolute;
			top : 15px;
			left : 300px;
		}
		
		#img_ac_e {
			position: absolute;
			top : 0px;
			left : 0px;
		}
		
		#not_more_than {
			position: absolute;
			font-size:12px;
			line-height: 12px;
		}
		
		.cmark {
			border-top:solid 1px #000;border-bottom:solid 1px #000;
			font-size:12px;
			line-height: 13px;
			text-align:center;
		}

	</style>
</head>
<body>
<div id="cl-wrapper" class="sb-collapsed">
	<?php
		include("../common/menu.php");
	?>
	<div class="container-fluid" id="pcont">
		<?php
			include("../common/header.php");
		?>
    
		<div class="cl-mcont">
			<?php
				include("../common/user_steps.php");
			?>
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li><a href="<?php echo DOMAIN_CHEQUE.'cheque'; ?>">Cheques</a></li>
				  <li class="active">New Cheque</li>
				</ol>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
					<div class="block-flat">
						<div class="content">
							<form method="post" id="FormGenerateCheque" role="form" parsley-validate novalidate> 
								<div class="form-group">
									<?php 
									if($mode=="direct_cheque")
									{ ?>
									<label>ACCOUNT</label>
									<input type="text" readonly class="form-control"  value="<?=$rel['acc_name'].' ('.$rel['bank_name'].'-'.$rel['branch_name'].') ';?>"/>
									<input type="hidden" id="bank" value="<?=$acc_id?>"/>
									<?php }
									else
									{
									?>
									<label>SELECT ACCOUNT</label>
									<select id="bank" class="select2" parsley-trigger="change" required>
										<?=getaccount($dbcon,$acc_id,'acc_id>0')?>
									</select>
									<div>
										<label id="message_cheque_left" class="hidden  text-primary"></label>
										<a href="#" id="add_new_chequebook" data-toggle="modal" data-target="#ModalNewCBook" class="hidden text-primary">Add chequebook from here.</a>
									</div>
									<?php }?>
								</div>
								<div id="cheque_number_div" class="hidden form-group">
									<label>CHEQUE NUMBER : </label>
									<!--<span id="cheque_number_text"></span>-->
									<input type="text" id="cheque_number_text" class="form-control" required value="<?=$cheque_num?>">
								</div>
								<div class="form-group">
									<label>DATE</label>
									<input type="text" id="cheque-date" onKeyUp="UpdateDate()" value="<?php echo $date ?>" class="form-control datetime" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<?php 
									if($mode=="direct_cheque")
									{ ?>
									<label>PAYEE</label>
									<input type="text" readonly class="form-control"  value="<?=$payename;?>"/>
									<input type="hidden" id="payee_select" value="<?=$payeeid?>"/>
									<input type="hidden" id="payee_type" value="<?=$payeetype?>"/>
									<input type="hidden" id="cradit_id" value="<?=$craditid?>"/>
									
									<?php }
									else
									{
									?>
									<label class="col-lg-12" style="padding-left: 0px">SELECT PAYEE</label>
									<div class="col-lg-12" style="padding-left:0px;">
									<select id="payee_select" class="payee col-lg-9 float-left" parsley-trigger="change" required style="padding-left:0px;">
										<?=
//											$query = $dbcon->query("SELECT `cust_id`,`company_name`,`city_name` FROM `tbl_customer` as ven left join city_mst as mst on mst.cityid=ven.cityid WHERE `cust_status` !=2 and  ven.`user_id` = $_SESSION[user_id] ORDER BY `company_name`");
//											while($r = $query -> fetch_assoc()) {
//												$sel='';
//												if($r['cust_id']==$payeeid)
//												{
//													$sel='selected="selected"';
//												}
//												echo '<option '.$sel.' value="'.$r['cust_id'].'" data-name="'.$r['company_name'].'" >'.$r['company_name'].' - '.$r['city_name'].'</option>';
//											}
                                                                                get_ledger($dbcon,$rel['vender_id'],' and l_group IN ('.SUNDRY_CREDITORS.')');
										?>
									</select>
									<a class="btn btn-success col-lg-3 float-left" href="javascript:;" onclick="add_customer();" title="Add Payee"><i class="fa fa-plus"></i> Payee</a>
								</div>
								<ul class="parsley-error-list col-lg-12"><li class="required" style="display: list-item;"></li></ul>	<?php }?>
								</div>
								
								<div class="form-group ">
									<label>ENTER AMOUNT</label>
									<input type="text" id="cheque-amount" onKeyUp="UpdateAmount()" placeholder="Upto 99 Crore" class="form-control" parsley-trigger="change" required value="<?=$amount?>" <?=($mode=="direct_cheque"?'readonly':'')?>>
								</div>
								<div class="form-group">
									<label class="control-label">CHEQUE TYPE</label>
									<div class="col-sm-12">
										<div class="radio"> 
											<label> <input name="pay_mode" type="radio" value="1"> BEARER</label> 
										</div>
										<div class="radio"> 
											<label> <input name="pay_mode" type="radio" value="2"> AC PAYEE ONLY (CROSS)</label> 
										</div>
										<div class="radio"> 
											<label> <input name="pay_mode" type="radio" value="3"> AC PAYEE (CENTRE)</label> 
										</div>
										<div class="radio"> 
											<label> <input name="pay_mode" type="radio" value="4"> AC PAYEE & NOT NEGOTIABLE (CENTRE)</label> 
										</div>
									</div>
								</div>
								<br>
								<div class="form-group ">
									<label class="control-label"> <input name="cbnotmore" type="checkbox" checked> ADD NOT OVER THAN</label>
								</div>
								<div class="form-group">
									<label>ADD NOTE :</label>
									<textarea id="cheque_note" style="width: 100%;"></textarea>
								</div>
								<div class="form-group ">
									<input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />
									<input type="hidden" name="purchase_cheque" id="purchase_cheque" value="<?=($mode=="direct_cheque"?$id:'0')?>" />
									<input type="hidden" id="cheque_num" name="cheque_num" value="" />
									<input type="hidden" id="backurl" name="backurl" value="<?=$backurl?>" />
									<button type="button" id="save_print" class="btn btn-success"><i class="fa fa-print"></i> Save &amp; Print</button>
									<!--<button type="button" id="save_print_email" class="btn btn-info"><i class="fa fa-envelope-o"></i> Save &amp; Email Print</button>-->
									<?php 
									if($mode!="direct_cheque")
									{ ?>
									<button type="button" id="save_only" class="btn btn-primary"><i class="fa fa-save"></i> Save Only</button>
									<?php }?>
								</div>
							</form>
						</div>
					</div>
				</div>
				
				<div class="hidden-xs hidden-sm col-md-7 col-lg-7">
					<div class="row">
						<div id="cheque" class="hidden-xs hidden-sm col-md-12 col-lg-12">
							<div id="print_preview" class="hidden">
								<div id="payee" class="cheque-item"></div>
								<div id="date" class="cheque-item"></div>
								<div id="bearer" class="cheque-item hidden">************</div>
								<div id="amount-number" class="cheque-item"></div>
								<div id="amount-text" class="cheque-item"></div>
								<img src="<?php echo DOMAIN_CHEQUE; ?>cheque_marks/ac_payee_e.png" id="img_ac_e" class="hidden" />
								<div id="img_ac_f" class="cmark hidden">A/C PAYEE ONLY</div>
								<div id="img_nn" class="cmark hidden">NOT NEGOTIABLE<br>A/C PAYEE ONLY</div>
								<!--<img src="<?php echo DOMAIN_CHEQUE; ?>cheque_marks/ac_payee_f.png" id="img_ac_f" class="hidden" />
								<img src="<?php echo DOMAIN_CHEQUE; ?>cheque_marks/ac_nn_f.png" id="img_nn" class="hidden" />-->
								<div id="not_more_than" class="hidden"></div>
							</div>
							<div id="no_preview">
								<center><h3><i class="fa fa-warning"></i> SELECT BANK ACCOUNT TO GENERATE PREVIEW</h3></center>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
							<div class="block-flat">
								<div class="header">
									<h3>TDS Calculator</h3>
								</div>
								<div class="content">
									<form method="post" id="FormTDSCalc" role="form" parsley-validate novalidate> 
										<div class="form-group">
											<label>ENTER AMOUNT</label>
											<input type="text" name="tds_amount" id="tds_amount" class="form-control" value="" onkeyup="CalculateTDS()"  />
										</div>
										<div class="form-group">
											<label>ENTER TDS (%)</label>
											<input type="text" name="tds_per" id="tds_per" class="form-control" value="0" onkeyup="CalculateTDS()" />
										</div>
										<div class="form-group">
											<label>PAYABLE AMOUNT</label>
											<input type="text" name="payable_amount" id="payable_amount" value="" class="form-control" disabled="disabled" />
											<input type="hidden" id="payable_amount_h" name="payable_amount_h" value="0" />
										</div>
										<div class="form-group">
											<button type="button" id="update_tds" class="btn btn-default">Update</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
	</div>
	
<!-- Add New Cheque book -->
<!-- Modal -->
<div class="modal colored-header" id="ModalNewCBook" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>New Chequebook</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormNewCBook" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">NEW SERIES NUMBER</label>
					<input type="text" id="new_cbook_series" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">NUMBER OF CHEQUES</label> 
					<input type="text" id="new_cbook_number" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
			</div>
			<div class="modal-footer">
				
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<input type="hidden" name="bank_account_for_cbook" id="bank_account_for_cbook" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Add Chequebook</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Add Customer Modal -->
<div class="modal colored-header" id="customer_add" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>New Payee</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormNewCustomer" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">NAME</label>
					<input type="text" name="name"  id="name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
									<label class="control-label">ADDRESS</label>
									<textarea name="address"  id="address" class="form-control parsley-validated" parsley-trigger="change"></textarea>
								</div>
								<div class="form-group">
									<label class="control-label">STATE</label>
									<select id="stateid" class="select2" parsley-trigger="change" required onchange="load_city(this.value,'cityid','')" title="Choose State">
										<option selected disabled value="">SELECT STATE</option>
										<?=getstate($dbcon,0)?>
									</select>
									
								</div>
								<div class="form-group">
									<label class="control-label">CITY</label>
									<select id="cityid" class="select2" parsley-trigger="change" required title="Choose City">
										<option selected disabled value="">SELECT CITY</option>
										
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">PHONE</label>
									<input type="text" name="phone"  id="phone" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<label class="control-label">EMAIL</label>
									<input type="text" name="mail"  id="mail" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email">
								</div>				
			</div>
			<div class="modal-footer">
				
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />				
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Add Payee</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->	
	<?php include("../common/js.php"); ?>
	
<script type="text/javascript">
	var pm = 1; //payment mode
$("#pannumber").mask("aaaaa-9999-a");	
		function UpdateDate() {
			var this_date = $("#cheque-date").val().split('-');
                        $("#date").html(this_date[0]+this_date[1]+this_date[2]);
		}
	var reply;
		$(document).ready(function() {
			UpdateDate();

			$("#cheque_preview").hide();
			
			$(".datetime").datepicker({
				format: 'dd-mm-yyyy',
				startDate: '-365d',
				autoclose: true,
				todayHighlight: true,
				calendarWeeks: true
			});
			
			//tds value update
			$("#update_tds").click(function(e) {
				e.preventDefault();
				console.log("Update Mount");
				$("#cheque-amount").val($("#payable_amount_h").val());
				UpdateAmount();
			});
			
			$('#cheque-date').datepicker().on("changeDate", function(e){
				console.log($(this).val());
				/*var date = $(this).val().split('-');
				$("#date").html(date[2]+date[1]+date[0]);*/
				UpdateDate();
			});
			
			$("#bank").change(function(event){
				event.preventDefault();
					bank_change();	//call function
				});
			
			$("#payee_select").change(function(event){
				event.preventDefault();
				payee_change();//call function	
			});
			
			$('input[name=cbnotmore]').change(function() { console.log('hello') 
				changeMorethan();
			});
			
			function changeMorethan() {
				var checked = $('input[name=cbnotmore]').is(":checked");
				if(checked) {
					$("#not_more_than").removeClass("hidden").attr("style","width:"+reply.design_notmoreWidth+"px;top:"+reply.design_notmore.top+"px;left:"+reply.design_notmore.left+"px;");;
				}
				else {
					$("#not_more_than").addClass("hidden").removeAttr("style");
				}
			}
			
			$("input[name='pay_mode']").change(function(){
				var mode = $(this).val();
				if(mode == 1) {
					$("#img_ac_e").addClass("hidden");
					$("#img_ac_f").addClass("hidden");
					$("#img_nn").addClass("hidden");
					$("#bearer").addClass("hidden");
					pm = 1;
				}
				else if(mode == 2) {
					$("#img_ac_f").addClass("hidden").removeAttr("style");;
					$("#img_nn").addClass("hidden").removeAttr("style");;
					$("#bearer").removeClass("hidden");
					$("#img_ac_e").removeClass("hidden");
					pm = 2;
				}
				else if(mode == 3) {
					$("#img_ac_e").addClass("hidden");
					$("#img_nn").addClass("hidden").removeAttr("style");
					$("#bearer").removeClass("hidden");
					$("#img_ac_f").removeClass("hidden").attr("style","position:absolute;top:"+reply.design_mark.top+"px;left:"+reply.design_mark.left+"px;");
					pm = 3;
				}
				else if(mode == 4) {
					$("#img_ac_e").addClass("hidden");
					$("#img_ac_f").addClass("hidden").removeAttr("style");;
					$("#bearer").removeClass("hidden");
					$("#img_nn").removeClass("hidden").attr("style","position:absolute;top:"+reply.design_mark.top+"px;left:"+reply.design_mark.left+"px;");;
					pm = 4;
				}
			});
			
			//add cbook
			$("#FormNewCBook").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading();
					var form_data = {
						type: "newcbook",
						acc : $("#bank").val(),
						series :  $("#new_cbook_series").val(),
						number : $("#new_cbook_number").val()
					};
					
					//console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/cheque/new',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							Unloading();
							response=response.trim();
							if(response == "1") {
								bootbox.alert("NEW CHEQUEBOOK ADDED SUCCESSFULLY.",function(e) {
									window.location.reload();
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
							}
							else {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
								//console.log(response);
							}
						}
					});
				}
			});
			
			//generate
			$('#FromGenerateDesign').submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading();
					
					var form_data = {
						type: "generate",
						token :  $("#token").val(),
						bank : $("#bank").val(),
						payee : $("#payee").position(),
						payee_w : $("#payee").width(),
						date : $("#date").position(),
						date_w : $("#date").width(),
						date_lspace : $("#date-letter-space").val(),
						amounttext : $("#amount-text").position(),
						amounttext_indent : $("#amount-indent").position(),
						amounttext_lheight : $("#amount-line-height").position(),
						amounttext_w : $("#amount-text").width(),
						amountnumber : $("#amount-number").position(),
						amountnumber_w : $("#amount-number").width(),
						bearer : $("#bearer").position(),
						bearer_w : $("#bearer").width(),
						cheque_preview : $("#cheque_preview").val()
					};
					
					//console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/cheque/design',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							Unloading();
							if(response == "1") {
								bootbox.alert("Design Added Successfully.",function(e) {
									window.location.reload();
								});
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'Design already exists !',
									class_name: 'danger',
									time: 1500,
								});
							}
							else {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1500,
								});
							}
							//console.log(response);
						}
					});
				}
			});
		});
		
		function UpdateAmount() {
			var value = $("#cheque-amount").val().replace(/\,/g,'');
			$("#amount-number").html(ToInNumber(value)+"/-");
			
			var fraction = Math.round(frac(value)*100);
			var f_text  = "";
			
			if(fraction > 0) {
				console.log(fraction);
				f_text = "AND "+convert_number(fraction)+" PAISE";
			}
			
			$("#amount-text").html(convert_number(value)+" RUPEE "+f_text+" ONLY");
			$("#not_more_than").html("NOT OVER THAN "+ToInNumber(value)+"/-");
		}
		
		function frac(f) {
			return f % 1;
		}
		
		function convert_number(number)
		{
			if ((number < 0) || (number > 999999999)) 
			{ 
				return "NUMBER OUT OF RANGE!";
			}
			var Gn = Math.floor(number / 10000000);  /* Crore */ 
			number -= Gn * 10000000; 
			var kn = Math.floor(number / 100000);     /* lakhs */ 
			number -= kn * 100000; 
			var Hn = Math.floor(number / 1000);      /* thousand */ 
			number -= Hn * 1000; 
			var Dn = Math.floor(number / 100);       /* Tens (deca) */ 
			number = number % 100;               /* Ones */ 
			var tn= Math.floor(number / 10); 
			var one=Math.floor(number % 10); 
			var res = ""; 

			if (Gn>0) 
			{ 
				res += (convert_number(Gn) + " CRORE"); 
			} 
			if (kn>0) 
			{ 
				res += (((res=="") ? "" : " ") + 
				convert_number(kn) + " LAC");
						
			} 
			if (Hn>0) 
			{ 
				res += (((res=="") ? "" : " ") +
					convert_number(Hn) + " THOUSAND"); 
			} 
			if (Dn) 
			{ 
				res += (((res=="") ? "" : " ") + 
					convert_number(Dn) + " HUNDRED"); 
			} 
			var ones = Array("", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX","SEVEN", "EIGHT", "NINE", "TEN", "ELEVEN", "TWELVE", "THIRTEEN","FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN","NINETEEN"); 
		var tens = Array("", "", "TWENTY", "THIRTY", "FOURTY", "FIFTY", "SIXTY","SEVENTY", "EIGHTY", "NINETY"); 
			
			if (tn>0 || one>0) 
			{ 
				if (!(res=="")) 
				{ 
					res += " AND "; 
				} 
				if (tn < 2) 
				{ 
					res += ones[tn * 10 + one]; 
				} 
				else 
				{ 

					res += tens[tn];
					if (one>0) 
					{ 
						res += ("-" + ones[one]); 
					} 
				} 
			}
			
			if (res=="")
			{ 
				res = "ZERO"; 
			} 
			return res;
		}
		
		function format_series(number, width) {
			return new Array(width + 1 - (number + '').length).join('0') + number;
		}
		
		$("#save_only").click(function() {
			var check = $("#FormGenerateCheque").parsley('validate');
			if(check) {
				Loading();
				save_cheque_record(1);
			}				
		});
		
		$("#save_print_email").click(function() {
			var check = $("#FormGenerateCheque").parsley('validate');
			if(check) {
				Loading();
				save_cheque_record(2);
			}				
		});
		
		$("#save_print").click(function() {
			var check = $("#FormGenerateCheque").parsley('validate');
			if(check) {
				Loading();
				save_cheque_record(3);
			}				
		});
		
		
		function save_cheque_record(id) {
			var mt = 0;
			var checked = $('input[name=cbnotmore]').is(":checked");
			if(checked) {
				mt = 1;
			}
			
			var form_data = {
				type: "newcheque",
				id : id,
				token :  $("#token").val(),
				bank : $("#bank").val(),
				cnum : $("#cheque_num").val(),
				cnumtext : $("#cheque_number_text").val(),
				payee : $("#payee_select").val(),
				date : $("#cheque-date").val(),
				amount : $("#cheque-amount").val(),
				note: $("#cheque_note").val(),
				paytype: $("#payee_type").val(),
				purchase_cheque:$("#purchase_cheque").val(),
				pymode : pm,
				overthan : mt
			};
			
			//console.log(form_data);
			
			$.ajax({
				type: "POST",
				cache:false,
				url: root_domain+'app/cheque/new',
				data: form_data,
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON( response );
					Unloading();
					if(typeof obj == 'object') {
						if(obj.status == "1") {
							if (id == 1) {
								bootbox.alert("Cheque Saved Successfully.",function(e) {
									window.location.reload();
								});	
								if(typeof obj.purchase != 'undefined')
								{
									window.location.href='<?=ROOT_F?>purchasepayment_list';
								}
							}
							else if (id == 2) {
								bootbox.alert("<h4>Cheque Saved & Emailed Successfully.</h4>",function(e) {
									window.location.reload();
								});	
							}
							else if (id == 3) {
								bootbox.alert("<h4>Cheque Saved Successfully. Click ok to print.</h4>",function(e) {
									window.open(root_domain+'<?=$_SESSION['print_page']?>?id='+obj.id);
									if(typeof obj.purchase != 'undefined')
									{
										window.location.href=$("#backurl").val();
									}
									//window.location.reload();
								});	
							}
							else {
								alert("3333");
							}
						
						}
						else {
							$.gritter.add({
								text: 'Error Occured while adding record.',
								class_name: 'danger',
								time: 1500,
							});
						}
					}
					else {
						$.gritter.add({
							text: 'SERVER ERROR !',
							class_name: 'danger',
							time: 1500,
						});
						//console.log(response);
					}
				}
			});
		}
		
		function CalculateTDS() {
			var amnt = Number($("#tds_amount").val());
			var tds = Number($("#tds_per").val())
			var famnt = amnt - ( (amnt * tds) / 100 );
			$("#payable_amount").val(ToInNumber(parseFloat(famnt).toFixed(2)));
			$("#payable_amount_h").val(famnt);
		}
		function add_customer()
		{
			$('#customer_add').modal('show');
		}
		$("#FormNewCustomer").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading();
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						name : $("#name").val(),
						address : $("#address").val(),
						stateid : $("#stateid").val(),
						cityid : $("#cityid").val(),
						phone : $("#phone").val(),
						mail : $("#mail").val(),
						modaladd:'1'
						//panphotoid : $("#panphotoid").val()
					};
					
					//console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/customer/',
						data: form_data,
						success: function(response)
						{
							response=response.trim();
							var arr=response.split('@@');
							console.log(arr);
							if(arr[0] == "1") {
								$.gritter.add({
									text: 'CUSTOMER ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1500,
								});						
								
								$('#payee_select').append("<option value='"+arr[1]+"'>"+$("#name").val().toUpperCase()+"</option>");
								$('#payee_select').trigger("change");
								$('#payee_select').select2("val",arr[1]);
								Unloading();
								$("#FormNewCustomer").reset();
								$('#customer_add').modal('hide');
								$("#payee_select").change();
							}
							else if(arr[0] == "-1") {
								$.gritter.add({
									text: 'Customer already exists in the system.',
									class_name: 'info',
									time: 1500,
								});
							}
							else if(arr[0] == "0") {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1500,
								});
							}
							else {
								console.log(response);
							}
							Unloading();
						}
					});
				}
			});
function load_city(parentid,control,val1)
{	
	$.ajax({
	type: "POST",
	url: root_domain+'app/customer/',
	data: { type : "load_city",  id : parentid},
	success: function(responce){
				//console.log(responce);
				$('#'+control).select2({
					width: '100%'
				});
				$('#'+control).html(responce);
				$("#"+control).select2("val",val1);
			}
	});

}	
function bank_change()
{
$("#bank_account_for_cbook").val($('#bank').val());
Loading(true);
var form_data = {
	type: "fetch_design",
	id : $('#bank').val()
};
$.ajax({
	type: "POST",
	cache:false,
	url: root_domain+'app/cheque/new',
	data: form_data,
	success: function(response)
	{
		console.log(response);
		reply = jQuery.parseJSON(response);
		Unloading();
		if(reply.response == 0) {
			//console.log("SECTION 0");
			$("#save_print").addClass("hidden");
			$("#save_print_email").addClass("hidden");
			$("#save_only").removeClass("hidden");
			$("#no_preview").show();
			$("#no_preview").html('<center><h3><i class="fa fa-danger"></i> NO DESIGN FOUND ! YOU WILL NOT ABLE TO PRINT.</h3></center>');
			$("#print_preview").hide();
			$("#cheque").addClass("hidden");
			
			$("#message_cheque_left").removeClass("hidden");
			$("#message_cheque_left").removeClass("text-danger").addClass("text-primary").html(reply.checkleft+" cheques left in current chequebook");
			$("#add_new_chequebook").addClass("hidden");
			
			$.gritter.add({
				text: 'NO DESIGN ADDED. COULD NOT PRINT !',
				class_name: 'warning',
				time: 1500,
			});
		}
		else if(response == -1) {
			console.log("SECTION -1");
			$("#save_print").addClass("hidden");
			$("#save_print_email").addClass("hidden");
			$("#save_only").addClass("hidden");
			$("#cheque").addClass("hidden");
			
			$("#message_cheque_left").removeClass("hidden");
			$("#message_cheque_left").removeClass("text-primary").addClass("text-danger").html("No cheques Available. ");
			$("#add_new_chequebook").removeClass("hidden");
			
			$.gritter.add({
				text: 'NO CHEQUE IN CHEQUEBOOK. PLEASE ADD NEW !',
				class_name: 'danger',
				time: 2000,
			});
		}
		else {
			//changeMorethan();
			console.log("SECTION SUCCESS");
			$("#save_print").removeClass("hidden");
			$("#save_print_email").removeClass("hidden");
			$("#save_only").removeClass("hidden");
			$("#cheque").removeClass("hidden");
			$("#print_preview").show();
			$("#print_preview").removeClass("hidden");
			$("#no_preview").hide();
			$("#payee").attr("style","position: absolute;width:"+reply.design_payeeWidth+"px;top:"+reply.design_payee.top+"px;left:"+reply.design_payee.left+"px;");
			$("#date").attr("style","position: absolute;width:"+reply.design_dateWidth+"px;top:"+reply.design_date.top+"px;left:"+reply.design_date.left+"px;letter-spacing:"+reply.design_dateLspace+"px;");
			$("#amount-text").attr("style","position: absolute;width:"+reply.design_amount_textWidth+"px;top:"+reply.design_amount_text.top+"px;left:"+reply.design_amount_text.left+"px;text-indent:"+reply.design_amount_textIndent+"px;line-height:"+reply.design_amount_textLHeight+"px;");
			$("#amount-number").attr("style","position: absolute;width:"+reply.design_amount_numberWidth+"px;top:"+reply.design_amount_number.top+"px;left:"+reply.design_amount_number.left+"px;");
			$("#bearer").attr("style","position: absolute;width:"+reply.design_bearerWidth+"px;top:"+reply.design_bearer.top+"px;left:"+reply.design_bearer.left+"px;");
			$('#cheque').css("background", "rgba(255,255,255,0.5) url("+root_domain+"upload/check/"+reply.design_preview+") no-repeat");
			$('#cheque').css("background-size", "100% 100%");
			$("#cheque_num").val(reply.checknum);
			//cheque number
			$("#cheque_number_div").removeClass("hidden");
			//$("#cheque_number_text").val(format_series(reply.checknum,6));
			
			$("#message_cheque_left").removeClass("hidden");
			$("#message_cheque_left").removeClass("text-danger").addClass("text-primary").html(reply.checkleft+" cheques left in current chequebook");
			$("#add_new_chequebook").addClass("hidden");
				
		}
	}					
});
			
}
function payee_change()
{
	//alert($('#payee_type').val());
	Loading();
				var form_data = {
					type: "fetch_payee",
					id : $('#payee_select').val(),
					payee_type : $('#payee_type').val(),
					cradit_id : $('#cradit_id').val(),
				};
				$.ajax({
					type: "POST",
					cache:false,
					url: root_domain+'app/cheque/new',
					data: form_data,
					success: function(response)
					{
						Unloading();
						if(response == 0) {
						//alert('123');
						}
						else {
							//alert(response);
							$("#payee").html(response);
						}
					}
				});
			
}
</script>
<?php 
	if($mode=="direct_cheque")
	{
			//$('#bank').select2('val','.$acc_id.');$('#bank').trigger('change');
		echo "<script type='text/javascript'>bank_change();UpdateAmount();payee_change();</script>";
	}
	?>
</body>
</html>
