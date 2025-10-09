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
	.panel-body
	{
		padding: 8px !important;
	}	
</style>
<?php 
	

?>
<section class="panel">
    <div class="panel-body ">
        <div class="row">
            <div class="col-md-12">
            <div class="clearfix"></div>
				<?php
					//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
					$comp_per=1;
					if($comp_per)
					{
						$user_ids=check_user_chein($dbcon,$_SESSION['user_id'],1);
				?>	
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading">STAGE SUMMARY</div>
							<div class="panel-body" id="">
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="stage_summ_start_date" name="stage_summ_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="stage_summ();">
									</div>
								</div>
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="stage_summ_end_date" name="stage_summ_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="stage_summ();">
									</div>
								</div>
								<div id="stage_summ" style="margin-top:50px;overflow-x:scroll;"></div>
							</div>
						</div>
					</div>
				<?php }  ?>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function() {
		
            stage_summ();
	});
	
	function stage_summ(){
		var stage_summ_start_date=$("#stage_summ_start_date").val();
		var stage_summ_end_date=$("#stage_summ_end_date").val();
		var opp_id = '<?php echo $opp_id; ?>'
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/crm_stage_users/',
			data: { mode : "stage_user_sum",stage_summ_start_date:stage_summ_start_date,stage_summ_end_date:stage_summ_end_date, opp_id:opp_id},
			success: function(response){
					//console.log(response);
					//var data = JSON.parse(response);
					$('#stage_summ').html(response);
			}
		});
	}
</script>