<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Purchase Bill";
	if(strpos($_SERVER[REQUEST_URI], "purchasebilledit")==false)
	{
		$mode="Add";
		$date=date('d-m-Y');
	}
	else
	{
		$mode="Edit";
		$purchasebillid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_purchasebill where purchasebill_id=$purchasebillid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>

</head>
<body>
  <section id="container">
      <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">
			<?php //include_once('../include/equick_link.php');?>
    		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?></h3>
						</header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?=ROOT.'purchasebill_list'?>"><?=$form?> List</a></li>
							  
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		 <div class="row">			
			
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
				<div class="panel-body">
				<form class="form-horizontal" role="form" id="purchasebill_add" action="javascript:;" method="post" name="purchasebill_add">
						<div class="row">
						<div class="form-group">
                                      <label class="col-sm-2 control-label" for="inputSuccess">Purchase Bill</label>
                                      <div class="col-md-3 col-xs-11" >
                                          <label class="checkbox-inline">
										  <?php
										  $ck='';$ck1='';
										  if($rel['purchasebill'] == "YES")
										  {
										  	 $ck='checked="checked"';
										  }
										  if($rel['purchasebill']== "NO")
										  {
										  	$ck1='checked="checked"';
										  
										  }
										  ?>
                                          <input  type="radio" <?=$ck?> id="purchasebill" onClick="show_vender(1);" checked="checked" value="YES" name="purchasebill"> Yes
                                          </label>
                                          <label class="checkbox-inline">
                                              <input type="radio" <?=$ck1?>  id="purchasebill" onClick="show_vender(0);"  value="NO" name="purchasebill">  No
                                          </label>
                                       
                                      </div>
                             	</div>
						<div id="vender">		
						<div class="form-group">  	
							<label class="col-md-2 control-label" style="margin-left:">Select Vender *</label>
							<div class="col-md-3 col-xs-11">
							<select class="select2" name="vender_id" id="vender_id" onChange="" >
									<?php 
												getvender($dbcon,$rel['vender_id']);
									?>	
							</select>
								</div>
								<input type="button"  name="addvender" id="addvender" data-toggle="modal" data-target="#bs-example-modal-lg"  class="btn btn-primary" value="+"/>
	    				</div>
						</div>
						<div class="form-group">  	
						 <label class="col-md-2 control-label" >Purchase Bill date </label>
							  <div class="col-md-3 col-xs-11">
								<input id="purchasebill_date" name="purchasebill_date" type="text" class="form-control default-date-picker" title="Date" value="<?php if($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['purchasebill_date']));}?>" placeholder="Purchse Bill Date">
								</div>
                             </div>	
					<div class="form-group">
					  <label class="col-md-2 control-label">Item Data</label>
								<div class="col-md-9 col-xs-11">
									<table width="80%">
										<tbody id="item_data">
									<?php if($mode=='Add')
									{ ?>
										 <input type="hidden" value="1" name="fieldcnt" id="fieldcnt">
								<tr id="fieldtr1">
									<td style="padding:0px 5px 5px 0px;">
										<select class=" col-md-9 form-control" id="item_id1" name="item_id[]" onChange="" required="" title="Select Item">
										<?=getitem1($dbcon,0)?>
										</select>
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="number" class="form-control" id="qty1" name="qty[]" style="width:125px;" placeholder="No. of QTY" onBlur="get_amount();" required="" title="Enter QTY" min="0">
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="number" class="form-control" id="rate1" name="rate[]" style="width:125px;" placeholder="No. of RATE" onBlur="get_amount();" required="" title="Enter RATE" min="0">
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="text" class="form-control" id="amount1" name="amount[]" style="width:125px;" placeholder="Amount" readonly="">
									</td>
									<td style="padding-right:5px;">
										<button type="button" id="addfield" name="addfield" class="btn btn-round btn-primary" onClick="return add_field();">Add</button>
									</td>							
									</tr>
									<?php }
									if($mode=='Edit')
									{ 
								$query="select * from tbl_purchasebilltranction where purchasebill_id=$purchasebillid";
								$rs_trn=($dbcon->query($query));
								echo '<input type="hidden" value="'.mysqli_num_rows($rs_trn).'" name="fieldcnt" id="fieldcnt"/>';
								$i=1;
								while($rel_trn=mysqli_fetch_assoc($rs_trn))
								{								
								?>
									<tr id="fieldtr<?=$i?>">
									<td style="padding:0px 5px 5px 0px;">
										<select class=" col-md-9 form-control" id="item_id<?=$i?>" name="item_id[]" onChange="" required="" title="Select Item">
										<?=getitem1($dbcon,$rel_trn['item_id'])?>
										</select>
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="number" class="form-control" id="qty<?=$i?>" name="qty[]" style="width:125px;" placeholder="No. of QTY" onBlur="get_amount();" required="" value="<?=$rel_trn['qty']?>" title="Enter QTY" min="0">
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="number" class="form-control" value="<?=$rel_trn['rate']?>" id="rate<?=$i?>" name="rate[]" style="width:125px;" placeholder="No. of RATE" onBlur="get_amount();" required="" title="Enter RATE" min="0">
									</td>
									<td style="padding:0px 5px 5px 0px;">
										<input type="text" class="form-control" id="amount<?=$i?>" value="<?=$rel_trn['amount']?>" name="amount[]" style="width:125px;" placeholder="Amount" readonly="">
									</td>
									<?php if($i==1){?>
										<td style="padding-right:5px;">
											<button  type="button" id="addfield" name="addfield" class="btn btn-round btn-primary" onClick="return add_field();">Add</button>
										</td>
										<?php } else {?>
											<td style="padding-right:5px;">
											<button  type="button" id="removefield" name="removefield" class="btn btn-round btn-warning" onClick="field_remove(<?=$i?>);">Remove</button>
										</td>
										<?php }?>
										</tr>
								<?php $i++;}
								}
									?>
																	</tbody>
										<tbody><tr>
										<td colspan="3" style="text-align:right;padding-right:10px;"><b>Total</b></td>
										<td>
											<input type="text" name="total" id="total"  class="form-control" style="width:125px;" readonly="" value="<?=$rel['total']?>">
										</td>
										</tr>
										</tbody></table>
									
								</div>
							</div>
						</div>
								<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
							<a href="<?=ROOT.'purchasebill_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					</div>
							<!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['purchasebill_id']?>' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							
						  </form>
</div>
						
					</section>
			 </div>
		 </div>		
			 
			  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	  <?php include_once('../include/add_vender.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/purchase.js"></script>
   <script src="<?=ROOT?>js/app/vender.js"></script>
    <!--<script src="js/count.js"></script>-->
<script>
//$('#container').addClass('sidebar-closed');
$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
function paymentmode(id)
{
	if(id=="2")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
	}
	else
		$('#cheque_data').hide();
}
$(".form_datetime").datetimepicker({
    format: 'dd-mm-yyyy hh:ii',
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left"

});

</script>
<?php
		if($mode=='Add')
		{
			echo "<script>show_vender(1)</script>";
		}
		else if($mode=="Edit")
		{
				if($rel['purchasebill'] == "YES")
				{
					echo "<script>show_vender(1)</script>";
				}
				if($rel['purchasebill']== "NO")
				{
						echo "<script>show_vender(0)</script>";
				}
													  
		}

?>
  </body>
</html>
