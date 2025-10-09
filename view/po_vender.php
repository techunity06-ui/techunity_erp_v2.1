<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Purchase Order";
	if(strpos($_SERVER[REQUEST_URI], "povenderedit")==false)
	{
		$mode="Add";
	}
	else
	{
		$mode="Edit";
		$poid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from  tbl_povender where povender_id=$poid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>

</head>
<body>
  <section id="container"  class="sidebar-closed">
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
							  <li ><a href="<?=ROOT.'po_venderlist'?>"><?=$form?> List</a></li>
							  
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
				<form class="form-horizontal" role="form" id="po_add" action="javascript:;" method="post" name="po_add">
						<div class="row">
					 
					 <div class="col-md-12">
								<label class="col-md-2 control-label" style="margin-left:">Select Customer </label>
								<div class="col-md-4 col-xs-11">
								<select class="select2" name="cust_id" id="cust_id" onChange="" >
									<?php 
												getcust($dbcon,$rel['cust_id']);
									?>	
							</select>
								</div>
								<input type="button"  name="addcust" id="addcust" data-toggle="modal" data-target="#bs-example-modal-lg"  class="btn btn-primary" value="+"/>
	    			</div>		
				<div class="col-md-12"  style="margin-top:10px;">
							<div class="form-group">
							  <label class="col-md-2 control-label">Purchase Order No </label>
							  <div class="col-md-4 col-xs-11">
								<input id="po_no" name="po_no" type="text" class="form-control" title="Date" value="<?=$rel['po_no']?>" placeholder="Purchase Order No" >
								</div>
                             </div>
							<div class="form-group">
							  <label class="col-md-2 control-label">Purchase Quotation No </label>
							  <div class="col-md-4 col-xs-11">
								<input id="pq_no" name="pq_no" type="text" class="form-control" title="Date" value="<?=$rel['pq_no']?>" placeholder="Purchase Quotation No" >
								</div>
                             </div>							 
					</div>		 
					<div class="col-md-12" style="margin-top:10px;">
							<div class="form-group">  	
							  <label class="col-md-2 control-label" >Purchse Order date </label>
							  <div class="col-md-4 col-xs-11">
								<input id="po_date" name="po_date" type="text" class="form-control default-date-picker" title="Date" value="<?php if($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_date']));}?>" placeholder="Purchse Order Date">
								</div>
                             </div>	
							</div>		 	
					 		 	
					 <div class="col-md-12">
					<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10px" style="border-collapse:inherit;border-spacing:10px;" cellpadding="10px" id="product_list">
							<tr id="field">
							<th width="15%"> Product Name</th>
							<th> Product Description</th>
						<!--	<th>Drg No</th>
							<th>HSN Classification</th>-->
							<th width="8%">Qty</th>
							<th width="8%">Rate</th>
							<th width="8%">Per</th>
							<th width="8%">KG/NOS</th>
							<th width="8%">Disc.%</th>
							<th> Amount</th>
						</tr>
							<?php 
							if($mode=="Add")
							{ ?>
								<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
								<tr id="field1">
								<td><select class="select2" name="product_id[]" id="product_id1" onChange="load_productdetail(this.value,1)" required title="Select Product"><?php echo getproduct($dbcon); ?></select>
								</td>							
								<td><textarea id="product_des1" name="product_des[]" class="form-control" ></textarea></td>		 							<td><input type="number"  required title="Enter Qty" min="0" id="product_qty1"  name="product_qty[]" onBlur="get_amount();" class="form-control"/></td>		 
								<td><input type="number" min="0" id="product_rate1" name="product_rate[]" onBlur="get_amount();" step="any"  required title="Enter Rate" class="form-control"/></td>
								<td><select class="form-control" id="unitid1" name="unitid[]" >
								<?php echo getpervalue($dbcon,'0');?>
								</select></td>
								<td><input type="text" id="product_noskg1"  name="product_noskg[]" class="form-control"/></td>	
								<td><input type="number"  min="0" id="product_disc1" name="product_disc[]" onBlur="get_amount();" step="any" class="form-control"/></td>
								<td><input type="text" id="product_amount1" readonly="readonly" name="product_amount[]" class="form-control"/></td>
								<td><input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add Product"/></td>
						</tr>
									<?php }?>
							<?php 
							if($mode=="Edit")
							{
							$qry1="select * from  tbl_povendertrancation where povender_id=".$rel['povender_id'];
								$result1=$dbcon->query($qry1);
									$num_rows=mysqli_num_rows($result1);
									?>
									 <input type="hidden" value="<?=$num_rows?>" name="fieldcnt" id="fieldcnt"/>
									<?php 
									$i=1;
									while($row1=mysqli_fetch_assoc($result1))
									{
								?>
							<tr id="fieldtr<?=$i?>">
							<?php 
								$qry2="select * from tbl_product where product_id=".$row1['product_id'];
								$result2=$dbcon->query($qry2);
								$row2=mysqli_fetch_assoc($result2);
							?>
						<td>
								<select class="select2" name="product_id[]" id="product_id1" onChange="load_productdetail(this.value,<?=$i?>)" title="Select Product" required>
							<?=getproductdetail($dbcon,$row2['product_id']);?>
							</select>			</td>
							<td><textarea type="text" id="product_des1"  name="product_des[]" class="form-control"><?=$row1['product_des']?></textarea></td>		 						
								<td><input type="number"  min="0" id="product_qty<?=$i?>"  value="<?=$row1['product_qty']?>"  name="product_qty[]" onBlur="get_amount();"  required title="Enter Qty" class="form-control"/></td>		 
								<td><input type="number"  min="0"  required title="Enter Rate" id="product_rate<?=$i?>" value="<?=$row1['product_rate']?>" name="product_rate[]" step="any" onBlur="get_amount();" class="form-control"/></td>
								<td><select class="form-control" id="unitid<?=$i?>" name="unitid[]"  >
								<?php echo getpervalue($dbcon,$row1['unitid']);?>
								</select></td>
								<td><input type="text" id="product_noskg'.$id.'"  name="product_noskg[]" class="form-control" value="<?=$row1['product_noskg']?>"/></td>
								<td><input type="number"  min="0" value="<?=$row1['product_disc']?>" id="product_disc<?=$i?>" name="product_disc[]" step="any" onBlur="get_amount();" class="form-control"/></td>
									<td><input type="text" value="<?=$row1['product_amount']?>" id="product_amount<?=$i?>" readonly="readonly" name="product_amount[]" class="form-control"/></td>
						<?php
						if($i==1)
						{ ?>
								<td><input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add Prouct"/></td>
								<?php
								}
								else
								{
								?><td><button type="button" class="btn btn-round btn-warning" onClick="field_remove(<?=$i?>);" id="fieldremove" name="fieldremove">Remove</button></td>
								<?php 
								}
								?>
						</tr>

<?php $i++;$e_total=$e_total+$row1['product_amount'];;
}}?>
</table>			
							</div>
                             </div>
							</div>	
					 <div class="col-md-6">
							<div class="form-group">
							  <label class="col-md-3 control-label">Delivery </label>
									<div class="col-md-9 col-xs-11">
									<input id="delivery" name="delivery" type="text"  class="form-control" title="Delivery" value="<?=$rel['delivery']?>"  placeholder="Delivery">
								</div>
                             </div> 
							 <div class="form-group">
							  <label class="col-md-3 control-label">Payment </label>
									<div class="col-md-9 col-xs-11">
									<input id="payment" name="payment" type="text"  class="form-control" title="payment" value="<?=$rel['payment']?>"  placeholder="Payment">
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Freight & transportation </label>
									<div class="col-md-9 col-xs-11">
									<input id="transportation" name="transportation" type="text"  class="form-control" title="transportation" value="<?=$rel['transportation']?>"  placeholder="Freight & transportation">
								</div>
                             </div>
					</div>
					 <div class="col-md-6">
							<div class="form-group">
								<label class="col-md-6 control-label">Packing charges </label>
								<div class="col-md-4 col-xs-11">
								<input id="paking" name="paking" type="number"  min="0"  class="form-control" title="paking" value="<?php if($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['packing'];}?>" onKeyUp="get_amount();" placeholder="paking">
					
								</div>
							</div>	
							<div class="form-group">
								<label class="col-md-6 control-label">Select Formula</label>
								<div class="col-md-4 col-xs-11">
								<select class="form-control" name="formulaid" id="formulaid" onChange="get_gtotal(this.value);">
									<?php 
									echo getformula($dbcon,$rel['formulaid']);
									 ?>
								</select>
								</div>
							</div>							
							<div class="col-md-12">
							<div id="showformulatextbox">
							<?php 
							if($mode=='Edit')
							{
							if(!empty($rel['tax1_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label"><?=$rel['tax1_name']?></label>
								<div class="col-md-6 col-xs-11">
								<input id="taxvalue0" name="taxvalue0" value= "<?=$rel['taxvalue1']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname0" name="taxname0" value= "<?=$rel['tax1_name']?>" type="hidden" class="form-control">
							<?php 
							}
							if(!empty($rel['tax2_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label"><?=$rel['tax2_name']?></label>
								<div class="col-md-6 col-xs-11">
								<input id="taxvalue1" name="taxvalue1" value= "<?=$rel['taxvalue2']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname1" name="taxname1" value= "<?=$rel['tax2_name']?>" type="hidden" class="form-control">
							<?php 
							}if(!empty($rel['tax3_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label"><?=$rel['tax3_name']?></label>
								<div class="col-md-6 col-xs-11">
								<input id="taxvalue2" name="taxvalue2" value= "<?=$rel['taxvalue3']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname2" name="taxname2" value= "<?=$rel['tax3_name']?>" type="hidden" class="form-control">
							<?php 
							}} 
							?>
							</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-6 control-label">Total *</label>
								<div class="col-md-4 col-xs-11">
								
								<input id="g_total" name="g_total" type="text"  class="form-control" title="total" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['amount'];}?>" placeholder="total"readonly="readonly">
					<input id="total" name="total" type="hidden" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">
							
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-6 control-label">Upload Pdf </label>
								<div class="col-md-4 col-xs-11">
								
									<input type="file" class="form-control" placeholder="PO PDF" name="po_pdf" id="po_pdf" accept="application/*" title="Select PDF" />
									<?php
									if($mode=="Edit")
									{
											echo '<a href="'.POPDF.$rel['po_pdf'].'" target="_blank">VIEW PDF</a>';
									}
								?>
								</div>
							</div>	
							</div>	
					</div>
									<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
							<button type="submit" class="btn btn-success" id="save_print" name="save_print">Save and Print</button>
							<a href="<?=ROOT.'po_venderlist'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					</div>
							<!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['povender_id']?>' />
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
	  <?php include_once('../include/add_cust.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/po_vender.js"></script>
   <script src="<?=ROOT?>js/app/customer.js"></script>
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

  </body>
</html>
