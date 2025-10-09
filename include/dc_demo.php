<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<div class="">
  <div class="col-lg-12">
	  <section class="panel">
		
		  <div class="panel-body" >
		  
			<div class="row state-overview">
              <div class="col-lg-3 col-sm-6 col-md-offset-4"  style="margin-bottom:50px !important;">
                      <section class="panel" >
                          <label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">Select Financial Year</label>
								<div class="col-md-12 col-xs-11">
								<?
							$minyear= 2016;
							$maxyear=(date('m')<'04') ? date('Y',strtotime('-1 year')) : date('Y');
							$end = $start+1;
		
								?>
								<form>
								<select class="form-control" name="c_year" id="c_year" onchange="get_value();" >
								<?
								for($y=$minyear;$y<=$maxyear;$y++)
								{
								$sel='';
								if($maxyear==$y)
								{
									$sel='selected="selected"';
								}
								?>
								<option <?=$sel?> value="<?=$y?>"><?phpecho $y.'-'.($y+1)?></option>	
								
								<?php}?>
								</select>
								</form>
                          		</div>
                      </section>
				  </div>
				  
                  
				  
				  

				  <div class="col-lg-12 col-sm-12">
						<div class="">
							<div class="row">
								<div class="col-lg-8" style="">
									<div class="col-md-12">
										<div class="col-lg-3">
											<label class="control-label" style="font-weight: bold;font-size: 13px;color: black;">Select Followup Status:</label>
										</div>
										<div class="col-lg-3">
											<select class="form-control" id="status_graph1" onchange="load_graph();">
												<option>--Select Status--</option>
												<?=getAllStatus($dbcon);?>
												<option value="0" selected>All</option>
											</select>
										</div>
										<div class="col-lg-3">
											<?
											$minyear= 2016;
											$maxyear=(date('m')<'04') ? date('Y',strtotime('-1 year')) : date('Y');
											$end = $start+1;
						
												?>
												<form>
												<select class="form-control" name="c_year" id="year_graph1" onchange="load_graph();" >
												<?
												for($y=$minyear;$y<=$maxyear;$y++)
												{
												$sel='';
												if($maxyear==$y)
												{
													$sel='selected="selected"';
												}
												?>
												<option <?=$sel?> value="<?=$y?>"><?phpecho $y.'-'.($y+1)?></option>	
												
												<?php}?>
												</select>
										</div>
									</div>
									
									<div class="col-md-12">
										<div id="chart-3"></div>
									</div>
									
								</div>
								<div class="col-lg-4">
									<div class="symbol red">
										<i class="fa fa-suitcase"></i>
									</div>
									<a href="<?php echo ROOT.'complaint_list/4' ?>"><div class="value">
                          
									<h1 class="count2"><span id="bussiness" style="font-size:20px;color:black;margin-left:-84px"></span></h1>
									<p style="color:black;margin-left:-84px">Complaint Done</p>
									</div></a>
									<div style="height:10px"></div>
									
									<div class="symbol blue">
										<i class="fa fa-money"></i>
									</div>
									
									<a href="<?php echo ROOT.'complaint_list/5' ?>"><div class="value">
                          
									<h1 class="count2"><span id="turnover" style="font-size:20px;color:black;margin-left:-84px"></span></h1>
									<p style="color:black;margin-left:-84px">Complaint Not Done</p>
									</div></a>
									
									<div style="height:10px"></div>
									
									<div class="symbol yellow">
										<i class="fa fa-money"></i>
									</div>
									
									<a href="<?php echo ROOT.'complaint_list/2' ?>"><div class="value">
                          
									<h1 class="count2"><span id="outstanding" style="font-size:20px;color:black;margin-left:-84px"></span></h1>
										<p style="color:black;margin-left:-84px">Complaint Assigned<br>But NotDone</p>	 
									</div></a>
								</div>
 							</div>
						</div>
				</div>
				
				
		    </div>
			
			
			  <div class="col-lg-12 col-sm-12">
						<div class="">
							<div class="row">
								<div class="col-lg-12" style="">
									<div class="col-md-12">
										<div class="col-lg-3">
											<label class="control-label" style="font-weight: bold;font-size: 13px;color: black;">Select Followup Status:</label>
										</div>
										<div class="col-lg-3">
											<select class="form-control" id="status_graph2" onchange="load_graph_emp();">
												<option>--Select Status--</option>
												<?=getAllStatus($dbcon);?>
												<option value="0" selected>All</option>
											</select>
										</div>
										<div class="col-lg-3">
											<?
											$minyear= 2016;
											$maxyear=(date('m')<'04') ? date('Y',strtotime('-1 year')) : date('Y');
											$end = $start+1;
						
												?>
												<form>
												<select class="form-control" name="c_year" id="year_graph2" onchange="load_graph();" >
												<?
												for($y=$minyear;$y<=$maxyear;$y++)
												{
												$sel='';
												if($maxyear==$y)
												{
													$sel='selected="selected"';
												}
												?>
												<option <?=$sel?> value="<?=$y?>"><?phpecho $y.'-'.($y+1)?></option>	
												
												<?php}?>
												</select>
										</div>
									</div>
									
									<div class="col-md-12">
										<div id="chart-4"></div>
									</div>
									
								</div>
							</div>
						</div>
				</div>
				
				
		    </div>
			  
			
			
			
	  	  </div>
		  
			  
	  </section>
  </div>
 </div>
 
<script type="text/javascript">
function get_value()
{
 Loading(true);	

$('#title_chart').html('');
$('#year_graph1').val($('#c_year').val());
$('#year_graph2').val($('#c_year').val());
$('#chart-3').html('');
load_value();
load_graph(); 
load_graph_emp(); 
//load_excisepichart();
 
 Unloading();
}
$(document).ready(function() {
 Loading(true);	
 load_value();
 load_graph();
 load_graph_emp();
 
Unloading();
});

function load_fivecust()
{
	var c_year=$('#c_year').val();
  $.ajax({
	type: "POST",
	url: root_domain+'app/dashboard/',
	data: { mode : "getcust", c_year : c_year},
	success: function(response){
				$('#top_5_cust').html(response);
	}
	});
}  
function load_value()
{
 var c_year=$('#c_year').val();
  $.ajax({
	type: "POST",
	url: root_domain+'app/dashboard/',
	data: { mode : "getyear", c_year : c_year},
	success: function(response){
		console.log(response);
		var data = JSON.parse(response);
		$('#bussiness').html(data.cdone);
		$('#turnover').html(data.cndone);
		$('#outstanding').html(data.cassign);
	}
	});
Unloading();
}  

function load_graph()
{
	$('#chart-3').html('');
	Loading(true);	
	//var c_year=$('#c_year').val();
	var status_graph1=$('#status_graph1').val();
	var year_graph1=$('#year_graph1').val();
	var mainurl = root_domain+'app/dashboard/index.php?mode=dynamic_chart&status='+status_graph1+'&year_graph1='+year_graph1;
	//alert(mainurl);
	$.getJSON(mainurl, function(json) {
	var arr=new Array();
		for(var i=0;i<12;i++)
		{	
			arr[i]=json[i];	
		}
		Morris.Bar({
        element: 'chart-3',
        data: arr,
		barSizeRatio:0.55,
        xkey: 'device',
        ykeys: ['geekbench'],
        labels: ['complaint'],
        barRatio: 0.4,
        xLabelAngle: 35,
        hideHover: 'auto',
        barColors: ['#6883a3'],
		lineWidth:25
      });
	});
Unloading();
}



function load_graph_emp()
{
	$('#chart-4').html('');
	Loading(true);	
	//var c_year=$('#c_year').val();
	var status_graph2=$('#status_graph2').val();
	var year_graph2=$('#year_graph2').val();
	var mainurl = root_domain+'app/dashboard/index.php?mode=dynamic_chart_emp&status='+status_graph2+'&year_graph2='+year_graph2;
	//alert(mainurl);
	$.getJSON(mainurl, function(json1) {
		count_loop=json1.count;
	var arr1=new Array();
		for(var j=0;j<count_loop;j++)
		{	
			arr1[j]=json1[j];	
		}
		Morris.Bar({
        element: 'chart-4',
        data: arr1,
		barSizeRatio:0.1,
        xkey: 'device',
        ykeys: ['geekbench'],
        labels: ['complaint'],
        barRatio: 0.1,
        xLabelAngle: 35,
        hideHover: 'auto',
        barColors: ['#6883a3'],
		lineWidth:25
      });
	});
Unloading();
}
 </script>
 