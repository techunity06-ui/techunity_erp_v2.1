<style>
.scrollbar
{
	height: 700px;
	width: 100%;
	overflow-y: scroll;
	margin-bottom: 25px;
	/*padding-right:10px;*/
}
.force-overflow
{
	/*min-height: 450px;*/
}
.style-11::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	background-color: #F5F5F5;
	border-radius: 10px;
}
.style-11::-webkit-scrollbar
{
	width: 5px;
	background-color: #F5F5F5;
}
.style-11::-webkit-scrollbar-thumb
{
	background-color: #ff0000;
	border-radius: 10px;
	background-image: -webkit-linear-gradient(0deg,
	                                          rgba(255, 255, 255, 0.5) 25%,
											  transparent 25%,
											  transparent 50%,
											  rgba(255, 255, 255, 0.5) 50%,
											  rgba(255, 255, 255, 0.5) 75%,
											  transparent 75%,
											  transparent)
}
@media (max-width:800px){

}
</style>	
<aside>
          <div id="sidebar"  class="nav-collapse scrollbar style-11">
              <!-- sidebar menu start-->
			  <ul class="sidebar-menu" id="nav-accordion">		 
            <?php 
			 $querymenu="select * from tbl_menu as menu 
			 inner join tbl_permission as per on per.menu_id=menu.menu_id 
			 inner join users as type on type.user_id=per.usertype_id 
			 where menu.status=0 and pid=0 and menu.report_status=0 and per.usertype_id=".$_SESSION['user_id']." order by menuorder";
			$result_menu=$dbcon->query($querymenu);		
			while($rel_menu=mysqli_fetch_assoc($result_menu))
			{
				if(!empty($rel_menu['page_name']))
				{
				?>
					<li>
						<a class="" href="<?=ROOT.strtolower($rel_menu['page_name'])?>">
							<i class="fa <?=strtolower($rel_menu['fa_icon'])?>"></i>
					<span style="font-size:14px"><?=ucwords(strtolower($rel_menu['menu_name']))?></span>
					
					</a>
				<?php 
				}
				else
				{
				?>
			      <li class="sub-menu">
				  <a href="javascript:;" >
					<i class="fa <?=strtolower($rel_menu['fa_icon'])?>"></i>
					<span style="font-size:14px"><?=ucwords(strtolower($rel_menu['menu_name']))?></span>
					</a>

					
					<ul class="sub">
				<?php 	
					 $querymenu1="select * from tbl_menu as menu 
					 inner join tbl_permission as per on per.menu_id=menu.menu_id 
					 inner join users as type on type.user_id=per.usertype_id 
					 where menu.status=0 and pid=".$rel_menu['menu_id']." and per.usertype_id=".$_SESSION['user_id']." order by menuorder";
					$result_menu1=$dbcon->query($querymenu1);		
					
					while($rel_menu1=mysqli_fetch_assoc($result_menu1))
					{
				?>
					<li><a  style="font-size:14px" href="<?=ROOT.strtolower($rel_menu1['page_name'])?>"><?=ucwords(strtolower($rel_menu1['menu_name']))?></a></li>
				<?php } ?>
				</ul>
				</li>
				<?php }?>
					
            </li>	
			<?php 	} ?>
				 	
				<!--<li>
                 	   <a class="" href="<?=ROOT.'changepassword/'.$_SESSION['user_id'] ?>">
                         <i class="fa fa-cog"></i>
                          <span style="font-size:14px">Change Password</span>
						</a>
                </li>
				
				<li>
                      <a href="javascript:;" onclick="change_company()">
                          <i class="fa fa-sign-in"></i>
                          <span style="font-size:14px">Change Company</span>
                      </a>
                  </li>-->
				</ul>
				 </li>
				 </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>