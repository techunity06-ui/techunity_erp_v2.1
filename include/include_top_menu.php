<?php $company_data = get_company_data($dbcon,$_SESSION['company_id'])?>

<header class="header white-bg" onload="startTime()" style="min-height:70px;/*padding:0px 12px;*/"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="sidebar-toggle-box">
		<div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
	</div>
	<!--logo start-->
	<a href="<?=ROOT.'dashboard'?>" class="logo hidden-phone" style="max-width:13%;"><span>
		<img width="60%" height="15%" src="<?=DOMAIN?>view/img/brp.png" alt="BRP Software Solutions llp">
	</span> </a>	
	<a class="logo hidden-phone" style="width:35%;margin-top: 17px;border: none !important;"><span style="color: #2b62b2;font-weight: bolder;"><?=$company_data['company_name']?></span></a>
	<!--logo end-->   

	<!-- Notification Tab Start -->
<!--
		<div class="nav notify-row" id='top_menu' style="margin-left:0px;">
			<ul class="nav top-menu">
			<li id="header_inbox_bar" style="float:left;margin-left:50px;" class="dropdown">
		    <?php 
			/*
			$todoqry='select * from todo_mst where status=0 and date >= CURDATE() and date <= DATE_ADD(CURDATE(),INTERVAL 3 DAY) and company_id='.$_SESSION['company_id'].' order by date ASC';
			 $result_todo=$dbcon->query($todoqry);
			 $notify=mysqli_num_rows($result_todo);
			
			?>
			<a data-toggle="dropdown" <?php if($notify!='0'){ echo 'id=\"pulsate-regular\"'; } ?> class="dropdown-toggle" href="#" aria-expanded="false">
				<i class="fa fa-bell-o"></i>
                <span class="badge bg-important"><?=$notify?></span>
            </a>
             <ul class="dropdown-menu extended inbox" style="min-width: 300px !important;">
             <div class="notify-arrow notify-arrow-red"></div>
                <li>
                 <p class="red">You have <?=$notify?> new notification</p>
				</li>
			<?php 	
			 if(mysqli_num_rows($result_todo)>0)
			 {
				  $i=1;
				  while($rel_todo=mysqli_fetch_assoc($result_todo))
				  {	
			?>
				<li>
                    <a href="javascript:;" onclick="change_top_status(<?=$rel_todo['todo_id']?>,1)" title="Click to Clear Notification">
							<span class="subject">
								<span class="from"><?=date('d-m-Y',strtotime($rel_todo['date']))?></span>
								<span class="time"></span>
                            </span>
                            <span class="message">
							<?=$rel_todo['task_detail']?>.
							</span>
                    </a>
                </li> 
			<?php 
					}
			 }*/
			?>
        
                 </ul>
            </li>
		
		</ul>
	</div>
-->
<!--    //Amish Soni Start 04-03-2021-->


<div class="nav notify-row" id='top_menu' style="margin-left:0px;">
	<ul class="nav top-menu">
		<li class="dropdown" id="header_inbox_bar">
			<?php 
			$fieldd = ($_SESSION['user_type'] == '2') ? 'is_read_by_admin' : 'is_read';
			// $wheres = ($_SESSION['user_type'] != '2') ? " and task.assign_user_ids = ".$_SESSION['user_id'] : "";
			$fis1=" and FIND_IN_SET(".$_SESSION['user_id'].",task.show_user_ids)";
			$todoqrys='SELECT task.*, inq.inquiry_no, cust.cust_name, mcd.mcd_name from tbl_task as task left join tbl_master_category_detail as mcd on mcd.mcd_id=task.task_type_id LEFT JOIN tbl_inquiry as inq ON inq.inquiry_id = task.inquiry_id LEFT JOIN tbl_customer as cust ON cust.cust_id = inq.cust_id where task.task_status = 0 and task.task_type_id != "'.GENERAL_TASK_TYPE.'" AND task.task_type_id != "0" and task.company_id='.$_SESSION['company_id'].$fis1.' AND task.alert_date_time <= "'.date("Y-m-d 23:59:59").'" ORDER BY task.task_id DESC';
			$result_todos=$dbcon->query($todoqrys);
			$notifys=mysqli_num_rows($result_todos);

			?>
			<a data-toggle="dropdown" <?php if($notifys!='0'){ echo 'id="pulsate-regular"'; } ?> class="dropdown-toggle" href="#">
				<i class="fa fa-tasks"></i>
				<span class="badge bg-success"><?=$notifys?></span>
			</a>
			<ul class="dropdown-menu extended inbox" style="min-width: 400px !important;max-height: 480px; overflow-y: auto;">
				<div class="notify-arrow notify-arrow-green"></div>
				<li>
					<p class="green">You have <?=$notifys?> pending tasks</p>
				</li>
				<?php
				if(mysqli_num_rows($result_todos)>0)
				{
					$i=1;
					while($rel_todo=mysqli_fetch_assoc($result_todos))
					{
						?>
						<li class="task_list_<?=$rel_todo['task_id']?>">
							<?php if($rel_todo['task_type_id']==15){ ?>
								<a href="<?=ROOT.CRM_ROOT.'inq_to_quot/'.$rel_todo['inquiry_id']?>" title="<?= $rel_todo['mcd_name'] ?>">
							<?php } else if($rel_todo['task_type_id']==20){
								$chk = $dbcon->query("SELECT inquiry_id,approve_status,quotation_id FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0 AND inquiry_id=".$rel_todo['inquiry_id']);
								$get = brp_mysqli_fetch_assoc($chk); ?>
								<a href="<?=ROOT.CRM_ROOT.'quotation_revise/'.$get['quotation_id']?>" title="<?= $rel_todo['mcd_name'] ?>">
							<?php } else { ?>
								<a href="<?=ROOT.CRM_ROOT.'task_add/'.$rel_todo['inquiry_id']?>" title="<?= $rel_todo['mcd_name'] ?>">
							<?php } ?>
								<span class="subject">
									<span class="from"><?= $rel_todo['mcd_name'] ?><br><?=$rel_todo['cust_name']?></span>
									<span class="time"><?=date("d-M-Y",strtotime($rel_todo['task_due_date']))?></span>
								</span>
								<span class="message">
									<?=$rel_todo['inquiry_no']?>
								</span>
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</li>
		<li id="header_inbox_bar" style="float:left;margin-left:50px;" class="dropdown">
			<?php 
			$field = ($_SESSION['user_type'] == '2') ? 'is_read_by_admin' : 'is_read';
			$where = ($_SESSION['user_type'] != '2') ? " and FIND_IN_SET (".$_SESSION['user_id'].",task.assign_user_ids)" : "";
			// $where.=' ORDER BY followup.flp_id DESC';
			$todoqry='select task.*,gt.general_task_name from tbl_task task left join general_task_mst as gt on gt.gt_id=task.gt_id where task.task_status != 2 and gt.gt_id!=0 and task.'.$field.'=0 and task.task_type_id = "'.GENERAL_TASK_TYPE.'" and task.company_id='.$_SESSION['company_id'].$where." ORDER BY task.task_id DESC";
			$result_todo=$dbcon->query($todoqry);
			$notify=mysqli_num_rows($result_todo);

			?>
			<a data-toggle="dropdown" <?php if($notify != '0') { ?> id="pulsate-regular" <?php } ?> class="dropdown-toggle" href="#" aria-expanded="false">

				<i class="fa fa-bell-o"></i>
				<span class="badge bg-important gt_count"><?=$notify?></span>
			</a>
			<ul class="dropdown-menu extended inbox" style="min-width: 300px !important;max-height: 480px; overflow-y: auto;">
				<div class="notify-arrow notify-arrow-red"></div>
				<li>
					<p class="red">You have <span class="gt_count"><?=$notify?></span> new notification</p>
				</li>
				<?php 
				if(mysqli_num_rows($result_todo)>0)
				{
					$i=1;
					while($rel_todo=mysqli_fetch_assoc($result_todo))
					{
						?>
						<li class="task_list_<?=$rel_todo['task_id']?>">
							<a href="javascript:;" onclick="general_task_mark_read(<?=$rel_todo['task_id']?>)" title="Click to Clear Notification">
								<span class="subject">
									<span class="from"><?= $rel_todo['general_task_name'] ?><br><?=$rel_todo['task_name']?></span>
									<span class="time"><?=date("d-M-Y",strtotime($rel_todo['task_due_date']))?></span>
								</span>
								<span class="message">
									<?=$rel_todo['task_remark']?>.
								</span>
							</a>
						</li>
						<?php 
					}
				}
				?>

			</ul>
		</li>
		<li id="header_inbox_bar" style="float:left;margin-left:50px;" class="dropdown">
			<?php $todays = date("Y-m-d 00:00:00");
			$ends = date("Y-m-d 23:59:59");
			$notesqry=$dbcon->query("SELECT * FROM director_working_notes WHERE notes_status!=2 AND notes_date >= '".$todays."' AND notes_date <= '".$ends."'");
			$count_notes=mysqli_num_rows($notesqry);

			?>
			<a data-toggle="dropdown" <?php if($count_notes!='0'){ echo 'id=\"pulsate-regular\"'; } ?> class="dropdown-toggle" href="#" aria-expanded="false">
				<i class="fa fa-bell-o"></i>
				<span class="badge bg-warning"><?=$count_notes?></span>
			</a>
			<ul class="dropdown-menu extended inbox" style="min-width: 300px !important;max-height: 480px; overflow-y: auto;">
				<div class="notify-arrow notify-arrow-yellow"></div>
				<li>
					<p class="yellow">You have <span class="gt_count"><?=$count_notes?></span> new notes</p>
				</li>
				<?php 
				if(mysqli_num_rows($notesqry)>0)
				{
					$i=1;
					while($rel_notes=mysqli_fetch_assoc($notesqry))
					{
						?>
						<li class="task_list_<?=$rel_notes['notes_id']?>">
							<a href="javascript:;" title="Note">
								<span class="subject">
									<span class="from"><?= $rel_notes['description'] ?></span>
									<span class="time"></span>
								</span>
								<span class="message">
									<?= $rel_notes['notes_date'] ?>
								</span>
							</a>
						</li>
						<?php 
					}
				}
				?>

			</ul>
		</li>
	</ul>
</div>   
<!--    //Amish Soni End 04-03-2021-->
<!-- Notification Tab End -->


<div class="top-nav ">
	<!--search & user info start-->
	<?php
	$setting='';
	/*$support='<li><a class="" href="'.ROOT.'support">
		<i class="fa fa-handshake-o "></i> Support</a>
		</li>'; */
		if($_SESSION['user_type']=="2") {
			$setting ='<li><a class="" href="'.ROOT.'administration/company_configuration">
			<i class="fa fa-cog"></i> Setting</a>
			</li>';
		}
		$top_lead_btn_view='';$top_inq_btn_view='';
			/*$top_led_btn_per=check_permission('lead_add',$_SESSION['user_id'],'edit',$dbcon);	
			if($top_led_btn_per){
				$top_lead_btn_view='
					<li style=" margin-top: 7px;">
						<button class="btn btn-round btn-primary tooltips" data-original-title="Create Lead" data-toggle="tooltip" data-placement="bottom" onclick="open_lead();"><i class="fa fa-plus"></i> <span class="hidden-phone">&nbsp;Lead</span></button>
                    </li>';
			}
			$top_inq_btn_per=check_permission('inquiry_add',$_SESSION['user_id'],'edit',$dbcon);	
			if($top_inq_btn_per){
				$top_inq_btn_view='
					<li style=" margin-top: 7px;">
						<button class="btn btn-round btn-success tooltips" data-original-title="Create Inquiry" data-toggle="tooltip" data-placement="bottom" onclick="open_inquiry();"><i class="fa fa-plus"></i> <span class="hidden-phone">&nbsp;Inquiry</span></button>
                    </li>';
                }*/

                if(!empty($_SESSION['company_name'])) {
                	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
                	$comty=mysqli_fetch_assoc($dbcon->query($com));	

                /*	$com_cond="select * from tbl_company_special_field_permission";
                	$sp_com=mysqli_fetch_assoc($dbcon->query($com_cond));	*/					
                	echo'				 
                	<ul class="nav pull-right top-menu" style="max-width:51%;">
                	<!--<li class=""><a class="tooltips" data-original-title="Change Company" data-toggle="tooltip" data-placement="bottom" style="margin-top: 5px;border: none !important;" href="javascript:;" onclick="change_company()"><i class="fa fa-sign-in"></i></a></li>-->';
                	if($_SESSION['user_type']=="2"){
                		$e='<li><a class="" href="'.ROOT.'backup/2" target="_blank">
                		<i class="fa fa-copy"></i> Database Backup</a>
                		</li>';
                	}else{
                		$e="";
                	}
                	// if($sp_com['rb_auto_permission'] == '1'){
                		
                	// }
                	
                	echo'	<li class="hidden-phone"><a class="logo" style="margin-top: 5px;border: none !important;">'.date("d-m-Y").' <span id="timecounter" style="color:#666666"></span></a></li>'.$top_lead_btn_view.$top_inq_btn_view.'


                	<!-- user login dropdown start-->
                	&nbsp;
                	<li class="dropdown">
                	<a data-toggle="dropdown" class="dropdown-toggle" href="#">
                	<img alt="" src="'.ROOT.'img/admin.jpg">
                	<span class="username">'.$_SESSION['user_name'].'</span>
                	<b class="caret"></b>
                	</a>
                	<ul role="menu" class="dropdown-menu">
                	<li><a class="" href="'.ROOT.'administration/company_configuration">
                	<i class="fa fa-cog"></i> Setting</a>
                	</li>
                	<li><a class="" href="'.ROOT.'changepassword/'.$_SESSION['user_id'].'">
                	<i class="fa fa-user"></i>
                	<span style="font-size:14px">Change Password</span>
                	</a>
                	</li>';
                	echo $e;
                	echo '<li class="divider "></li>
                	<li><a href="'.ROOT.'logout" style="background: #a9d96c;"><i class="fa fa-key"></i> Log Out</a></li>
                	</ul>
                	</div>
                	<ul class="dropdown-menu extended logout">
                	<div class="log-arrow-up"></div>                            
                	<li><a href="'.ROOT.'logout"><i class="fa fa-key"></i> Log Out</a></li>

                	</ul>
                	</li>';}
                	?>					
                	<!-- user login dropdown end -->
                </ul>
                <!--search & user info end-->
            </div>
        </header>

        <!--header end-->
        <script>
        	function paymentremander(id)
        	{
        		Loading(true);		

        		$.ajax({
        			type: "POST",
        			url: root_domain+'app/dashboard/',
        			data: { mode : "paymentremainder", invoiceid :id},
        			success: function(response)
        			{
        				console.log(response);
        				$('#ModalPaymentRemainder').modal();
        				var obj = jQuery.parseJSON(response);
        				if(response != "") {				
        					$("#ModalPaymentRemainder").modal("show");
        					$("#cust_name").html(obj.company_name);
        					$("#cust_address").html(obj.cust_address);
        					$("#city").html(obj.city);
        					$("#mobile").html(obj.cust_mobile);
        					$("#email").html(obj.cust_email);
        					$("#ex_date").html(obj.ex_date);
        					$("#exciseinvoice_date").html(obj.exciseinvoice_date);
        					$("#exciseinvoice_no").html(obj.exciseinvoice_no);
        					$("#message").html(obj.message);
        				}
        				Unloading();

        			}
        		});	
        	}
        	function open_inquiry() {
        		window.location=root_domain+'inquiry_add';
        	}
        	function open_lead() {
        		window.location=root_domain+'lead_add';
        	}
        	function open_purchase() {
        		window.location=root_domain+'purchase_add';
        	}

/* Code By Umaie: 03/10/2020 
    Comment: Set Counter
    */
    startTime();	   
    function startTime() {
    	var today = new Date();
    	var h = today.getHours();
    	var m = today.getMinutes();
    	var s = today.getSeconds();
    	m = checkTime(m);
    	s = checkTime(s);
    	document.getElementById('timecounter').innerHTML =
    	h + ":" + m + ":" + s;
    	var t = setTimeout(startTime, 500);
    }
    function checkTime(i) {
  if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
  return i;
}

</script>