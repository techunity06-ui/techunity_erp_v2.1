<?php 
	$arr=explode("/",$_SERVER['PHP_SELF']);
	$page_name=end($arr);
	$page_name=basename($page_name, '.php');
?>
<style>
ul.summary-list > li {
	width:15%;
}
</style>
<div style="text-align:right" class="hidden-phone">
						<ul class="summary-list" >
							<?phpif($page_name!="purchase_add")
							{?>
							<li class="">
								<a href="<?=ROOT.'purchase/purchase_add'?>">
									<i class="fa fa-pencil text-primary"></i>
										Create Purchase
								</a>
                            </li>
                            <?php}
							if($page_name!="purchase_list")
							{?>
							<li>
								<a href="<?=ROOT.'purchase/purchase_list'?>">
									<i class="fa fa-envelope text-info"></i>
										Purchase List
                                </a>
                            </li>
							<?php} ?>
							
                     </ul>
		</div>
						