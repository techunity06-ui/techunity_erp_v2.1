<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
.count , .count2
{
	margin:0px !important;
	padding:0px !important

}
.cc_count
{
	margin-left:5%;
}

.panel-heading
{
	text-align:center;
	font-weight:bold;
	FONT-SIZE:16px;
}

.border_line
{
	border-bottom:dotted blue 2px;
}

.link_dash
{
	border-bottom:dotted blue thin;
}

</style>
<style>
.hh {
	font-family: "Segoe UI",Arial,sans-serif;
	font-weight: 400;
	margin: 10px 0;
	font-size: 25px;
	box-sizing: inherit;
	margin-block-start: -0.5em;
	margin-block-end: 0.0em;
	margin-inline-start: 0px;
	margin-inline-end: 0px;
	color: #fff!important;
	background-color: #009688!important;
}
</style>
<section class="panel">
	<div class="panel-body ">
		<div class="row">
			<!--<div class="col-md-12 hh" style="text-align:center;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/">Transation Dashbord</div>-->
			<div class="col-md-12" style="/*border-left-style: groove;border-right-style: groove;border-top-style: groove;border-bottom-style: groove;*/">
				<div class="col-md-12" style="margin-bottom: 20px;">
					<!--<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-4 control-label" style="white-space:nowrap;">
								Start Dates
							</label> 
							<div class="col-md-8">
								<input id="start_date" name="start_date" type="text" class="form-control default-date-picker valid" title="Date" value="<?echo date('1-m-Y');?>" placeholder="Start Date" onChange="load_trans_datatable()">
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-4 control-label" style="white-space:nowrap;">
								End Date
							</label> 
							<div class="col-md-8">
								<input id="end_date" name="end_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?phpecho date('d-m-Y');?>" placeholder="End Date" onChange="load_trans_datatable()">
							</div>
						</div>
					</div>-->
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-2 control-label" style="white-space:nowrap;">
								User
							</label> 
							<div class="col-md-10">
								<select class="select2" name="cust_id" id="cust_id" onchange="load_trans_datatable()" >
									<?= get_assign_users_inq($dbcon); ?>
								</select>
							</div>
						</div>
					</div>
					
				</div>
				<div class="col-md-12">
					<div class="col-md-10"></div>
					
				</div>
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="transaction-table">
						<thead>
							<tr>
								<th>Userid</th>
								<th>Username</th>
								<th>Login Time </th>
								<th>Logout Time</th>
								
							</tr>
						</thead>
						<tbody>
						</tbody>				 
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

