<?php 
$start_date= date('d-m-Y', strtotime('first day of last month'));
$end_date=date("d-m-Y");
?>
<div class="">
	<div class="row">
		<div class="col-4 col-md-4">
			<!-- small box -->
			<div class="small-box bg-info">
				<div class="inner">
					<h3 class="live_complaint_cnt">0</h3>
					<p>Live Complaint</p>
				</div>
				<div class="icon">
					<i class="ion fa fa-cog"></i>
				</div>
				<!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
			</div>
		</div>
		<!-- ./col -->

		<div class="col-4 col-md-4">
			<!-- small box --> 
			<div class="small-box bg-success">
				<div class="inner">
					<h3 class="inst_done_cnt">0</h3>
					<p>Installation Done</p>
				</div>
				<div class="icon">
					<i class="ion fa fa-cog"></i>
				</div>
				<!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
			</div>
		</div>
		<!-- ./col -->

		<div class="col-4 col-md-4">
			<!-- small box -->
			<div class="small-box bg-danger">
				<div class="inner">
					<h3 class="inst_pending_cnt">0</h3>
					<p>Pending Installation</p>
				</div>
				<div class="icon">
					<i class="ion fa fa-cog"></i>
				</div>
				<!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
			</div>
		</div>
		<!-- ./col -->
	</div>

	<div class="row setMargin">
		<div class="col-md-6">
			<div id="today_complaints_chart" class="chartStyle"></div>
		</div>
		<div class="col-md-6">
			<div id="total_complaints_chart" class="chartStyle"></div>
		</div>
	</div>

	<div class="row setMargin">
        <div class="col-md-6">
        	<div class="col-md-12">
				<div class="col-md-6"><strong>Category</strong></div>
		        <div class="col-md-6">
		            <select class="select2" id="category" name="category" onChange="changeCategoryVal();">
		                <?=get_all_category($dbcon,"","");?>
		            </select>
		        </div>
		        <div class="col-md-3"><strong>Start Date</strong></div>
		        <div class="col-md-3">
		        	<input type="text" id="start_date" class="form-control show-date-picker" />
		        </div>
		        <div class="col-md-3"><strong>End Date</strong></div>
		        <div class="col-md-3">
		        	<input type="text" id="end_date" class="form-control show-date-picker" onChange="changeCategoryVal();" />
		        </div>
	        </div>
			<div class="col-md-12">
				<div id="category_complaints_chart" class="chartStyle"></div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="col-md-2"><strong>Select Product</strong></div>
	        <div class="col-md-4">
	            <select class="select2" name="product" id="product" onChange="weekendchange();">
	            	<option value="">Select Product</option>
	            	<?= load_all_product($dbcon,'',''); ?>
	            </select>
	        </div>
	        <div class="col-md-2"><strong>Select Week</strong></div>
	        <div class="col-md-4">
	        	<?php  
	        		$cur_weekend = date('d-m-Y',strtotime('this Sunday'));
	                $start_date = date("d-M-Y", strtotime(" -1 months"));
	                $end_date = date("d-M-Y", strtotime(" +1 months"));
	                $weekend_dates = getWeekendDates($start_date, $end_date); ?>
	            <select class="select2" name="weekdate" id="weekdate" onChange="weekendchange();">
	            	<?php foreach ($weekend_dates as $key => $value) { ?>
	                	<option value="<?php echo $value ?>" <?php echo $cur_weekend == $value ? 'selected' : '' ?>><?php echo $value ?></option>
	                <?php } ?>
	            </select>
	        </div>
			<div class="col-md-12">
				<div id="weekend_complaints_chart" class="chartStyle"></div>
			</div>
		</div>
	</div>

	<div class="row setMargin">
		<div class="col-md-12">
			<div id="profit_loss_chart" class="chartStyle"></div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3"><strong>Employee</strong></div>
        <div class="col-md-3">
            <select class="select2" name="user_id" onchange="load_employee_chart(this.value);">
            	<option value="">Select Employee</option>
                <?=getAllEmployeeUser($dbcon);?>
            </select>
        </div>
		<div class="col-md-12">
			<div id="employee_chart" class="chartStyle"></div>
		</div>
	</div>
</div>