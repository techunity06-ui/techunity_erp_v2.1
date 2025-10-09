<?php
$qry='select sp.po_req_no,sp.sp_id,j.rp_pid from tbl_request_product as j 
		left join tbl_set_main_process as sp on sp.sp_id=j.sp_id
		where job_card_status=1 and main_request=1 order by rp_id desc limit 1';
$qry_res=$dbcon->query($qry);
$result=brp_mysqli_fetch_assoc($qry_res);

$wo_product_id = "";
$wo_po_req_no = "";

$wo_product_id = $result['rp_pid'];
$wo_sp_id =$result['sp_id'];



$qry2='select job_card_no,rp_id,j.rp_pid from tbl_request_product as j 
		where job_card_status=1 order by rp_id asc limit 1';
$qry_jc=$dbcon->query($qry2);
$result2=brp_mysqli_fetch_assoc($qry_jc);

$jc_product_id = "";
$jc_rp_id = "";

$jc_product_id = $result2['rp_pid'];
$jc_rp_id =$result2['rp_id'];

?>

<section class="panel">
	<div class="panel-body">

		<div class="row">
			<div class="col-md-12 overflow-auto mtop20 m-bot20">
				<div id="yealy_production_report" style="height: 370px; width: 100%;">
					
				</div>
			</div>
			<div class="col-md-12 overflow-auto mtop20 m-bot20">
				
					<div class='col-lg-4 col-md-4 col-xs-8'>
						<div class="form-group">
							<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
							<div class=" col-lg-8 col-md-8 col-xs-9">
								<div class="input-group date form_datetime-component">
									<?php 
										//$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									?>
									<input type="hidden" id="from_date"  value="<?=$start?>">
									<input type="hidden" id="to_date"  value="<?=$end?>">
									<input type="text" id="rep_date"  class="form-control datepikerdemo" value="" autocomplete="off">
									<span class="input-group-btn">
										<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
									</span>
								</div>
							</div>
						</div>
					</div>	
					<div class='col-lg-8 col-md-8 col-xs-8'>
						<div class="form-group">
							<label class="control-label col-lg-4 col-md-4 col-xs-3">Product</label>
							<div class=" col-lg-8 col-md-8 col-xs-9">
								<input id="product_ids" multiple="multiple"  class="selproduct" name="product_ids" style="width:100%;" placeholder="Select product" onchange="load_complet_vs_reject_report();" value=""/>
							</div>
						</div>
				</div>
				<div class="col-md-12 overflow-auto mtop20 m-bot20">
					<div id="complet_vs_reject_report" style="height: 370px; width: 100%;">

					</div>
				</div>
			</div>
			<div class="col-md-12 overflow-auto mtop20 m-bot20">
				
					<div class='col-lg-4 col-md-4 col-xs-8'>
						<div class="form-group">
							<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
							<div class=" col-lg-8 col-md-8 col-xs-9">
								<div class="input-group date form_datetime-component">
									<?php 
										//$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									?>
									<input type="hidden" id="wo_from_date"  value="<?=$start?>">
									<input type="hidden" id="wo_to_date"  value="<?=$end?>">
									<input type="text" id="wo_rep_date"  class="form-control datepikerdemo" value="" autocomplete="off">
									<span class="input-group-btn">
										<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
									</span>
								</div>
							</div>
						</div>
					</div>	
					<div class='col-lg-8 col-md-8 col-xs-8'>
						<div class="form-group">
							<label class="control-label col-lg-4 col-md-4 col-xs-3">Product</label>
							<div class=" col-lg-8 col-md-8 col-xs-9">
								<input id="wo_product_ids" multiple="multiple"  class="selproduct" name="wo_product_ids" style="width:100%;" placeholder="Select product" onchange="load_workorder_piechart();" value=""/>
							</div>
						</div>
				</div>
				<div class="col-md-12 overflow-auto mtop20 m-bot20">
               	 	<div id="workorder_piechart" style="height: 300px; width: 100%;"></div>
           		 </div>
           		  <div class="col-md-12 overflow-auto mtop20 m-bot20">
                <div id="all_data_report" style="height: 300px; width: 100%;"></div>
           	</div>
			</div>
			
			<div class="col-md-4" style="display:none" >  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Category</label>
					<div class="col-md-8 col-xs-11">
						<select class="select2" name="product_category_w" id="product_category_w" onchange="getproducts_w(this.value)">
							<?=get_all_category($dbcon,'0');?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4" style="display:none" >  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Product</label>
					<div class="col-md-8 col-xs-11">
						<select class="select2" name="product_id1" id="product_id1" onchange="getwork_order_w()">
							<?=getfinishedproducts($dbcon,$wo_product_id);?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Work Order</label>
					<div class="col-md-8 col-xs-11">
						<input type="hidden" name="wo_sp_id" id="wo_sp_id" value="<?=$wo_sp_id?>">
						<select class="select2" name="work_order_id1" id="work_order_id1" onchange="load_work_order_graph();" >
							<?php //=getfinishedproducts($dbcon,'');?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12" style="display:none">
                <div id="lead_by_product_container" style="height: 300px; width: 100%;"></div>
            </div>

           
		</div>
	</div>
</section>

<section class="panel" style="display:none">
	<div class="panel-body">
		<div class="row">
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Category</label>
					<div class="col-md-8 col-xs-11">
						<select class="select2" name="product_category_j" id="product_category_j" onchange="getproducts_j(this.value)">
							<?=get_all_category($dbcon,'0');?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Product</label>
					<div class="col-md-8 col-xs-11">
					
						<select class="select2" name="product_id2" id="product_id2" onchange="getwork_order_j()">
							
							<?=getfinishedproducts($dbcon,$jc_product_id);?>
						</select>

					</div>
				</div>
			</div>
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Job Card</label>
					<div class="col-md-8 col-xs-11">
						<input type="hidden" name="jc_rp_id" id="jc_rp_id" value="<?=$jc_rp_id?>">
						<select class="select2" name="job_work_id" id="job_work_id" onchange="load_job_work_graph();" >
							<?php //=getfinishedproducts($dbcon,'');?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12" style="display:none">
                <div id="lead_by_job_work_container" style="height: 300px; width: 100%;"></div>
            </div>
		</div>
	</div>
</section>

<section class="panel" style="display:none">
	<div class="panel-body">
		<div class="row">
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Select Category</label>
					<div class="col-md-8 col-xs-11">
						<select class="select2" name="product_category" id="product_category" onchange="getproducts(this.value)">
							<?=get_all_category($dbcon,'0');?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4" style="display:none">  
				<div class="form-group">
					<label for="opening stock" class="col-md-4 control-label">Select Product</label>
					<div class="col-md-8 col-xs-11">
						<select class="select2" name="product_id" id="product_id" onchange="productselect()">
							<?=getfinishedproducts($dbcon,'');?>
						</select>
					</div>
				</div>
			</div>
			<!-- Yearly Inquiry Chart Start -->
			<div class="col-md-12 overflow-auto" style="display:none">

				<div id="chartContainer" style="height: 370px; width: 100%;"></div>

			</div>
			<!-- Yearly Inquiry Chart End -->
			<div class="clearfix"></div>
			<div class="clearfix"></div>

		

			<div class="col-md-12 overflow-auto" style="margin-top: 20px;display: none;" >
				<div class="col-md-4">  
					<div class="form-group">
						<label for="opening stock" class="col-md-4 control-label">Start Date</label>
						<div class="col-md-8 col-xs-11">
							<input type="date" name="start_date" id="start_date" class="form-control" onchange="multicolchart()" value="<?=date('Y-m-01')?>">
						</div>
					</div>
				</div>
				<div class="col-md-4" style="display:none">  
					<div class="form-group">
						<label for="opening stock" class="col-md-4 control-label">End Date</label>
						<div class="col-md-8 col-xs-11">
							<input type="date" name="end_date" id="end_date" class="form-control" onchange="multicolchart()" value="<?=date('Y-m-d')?>">
						</div>
					</div>
				</div>
				<div class="col-md-4" style="display:none">  
					<div class="form-group">
						<label for="opening stock" class="col-md-4 control-label">Choose Product</label>
						<div class="col-md-8 col-xs-11">
							<select class="select2" name="product_id" id="product_id" onchange="multicolchart()">
								<?=getfinishedproducts($dbcon,'');?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-12 overflow-auto" style="display:none">
				<div id="chartContainer2" style="height: 370px; width: 100%;"></div>
			</div>


			<div class="col-md-12 overflow-auto" style="display:none">
				<div id="chartContainer3" style="height: 370px; width: 100%;"></div>
			</div>
			<!-- Target Chart End -->
			<div class="clearfix"></div>


			<div class="clearfix"></div>


		</div>
	</div>
</section>
<script type="text/javascript">
	$(document).ready(function() {
		Loading(true);	
		Unloading();
	});
</script>
