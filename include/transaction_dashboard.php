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
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-4 control-label" style="white-space:nowrap;">
								Start Date
							</label> 
							<div class="col-md-8">
								<input id="start_date" name="start_date" type="text" class="form-control default-date-picker valid" title="Date" value="<?php echo date('1-m-Y');?>" placeholder="Start Date" onChange="load_trans_datatable()">
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-4 control-label" style="white-space:nowrap;">
								End Date
							</label> 
							<div class="col-md-8">
								<input id="end_date" name="end_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?php echo date('d-m-Y');?>" placeholder="End Date" onChange="load_trans_datatable()">
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-2 control-label" style="white-space:nowrap;">
								User
							</label> 
							<div class="col-md-10">
								<select class="select2" name="cust_id" id="cust_id" onchange="load_trans_datatable()" >
									<?= get_assign_users_inq($dbcon,''); ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-2 control-label" style="white-space:nowrap;">
								Type
							</label> 
							<div class="col-md-10">
								<select class="select2" name="type" id="type" onchange="load_trans_datatable()" >
									<option value="" >Select Type</option>
									<option value="Purchase" >Purchase</option>
									<option value="Purchase Order" >Purchase Order</option>
									<option value="Sales Order" >Sales Order</option>
									<option value="Invoice" >Invoice</option>
									<option value="Jobwork" >Jobwork</option>
									<option value="Work Order" >Work Order</option>
									<option value="Job Card" >Job Card</option>
									<option value="Indent" >Indent</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-10"></div>
					<div class="col-md-2">
						<button class="btn btn-success" data-original-title="Task Done" data-toggle="tooltip" data-placement="top" onClick="print_cust_label();"><i class="fa fa-check"></i> Task Done</button>
					</div>
				</div>
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="transaction-table">
						<thead>
							<tr>
								<th>Type</th>
								<th>Transation No</th>
								<th>Description</th>
								<th>Amount</th>
								<th>User</th>
								<th>Last Updated</th>
								<th>Action</th>	
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
<script type="text/javascript">
	function updateTime() {
		var dateInfo = new Date();

		/* time */
		var hr,
		_min = (dateInfo.getMinutes() < 10) ? "0" + dateInfo.getMinutes() : dateInfo.getMinutes(),
		sec = (dateInfo.getSeconds() < 10) ? "0" + dateInfo.getSeconds() : dateInfo.getSeconds(),
		ampm = (dateInfo.getHours() >= 12) ? "PM" : "AM";

  // replace 0 with 12 at midnight, subtract 12 from hour if 13–23
  if (dateInfo.getHours() == 0) {
  	hr = 12;
  } else if (dateInfo.getHours() > 12) {
  	hr = dateInfo.getHours() - 12;
  } else {
  	hr = dateInfo.getHours();
  }

  var currentTime = hr + ":" + _min + ":" + sec;

  // print time
  // document.getElementsByClassName("hms")[0].innerHTML = currentTime;
  // document.getElementsByClassName("ampm")[0].innerHTML = ampm;

  /* date */
  var dow = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday"
  ],
  month = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December"
  ],
  day = dateInfo.getDate();

  // store date
  var currentDate = dow[dateInfo.getDay()] + ", " + month[dateInfo.getMonth()] + " " + day;

  // document.getElementsByClassName("date")[0].innerHTML = currentDate;
};

// print time and date once, then update them every second
updateTime();
setInterval(function() {
	updateTime()
}, 1000);



</script>
