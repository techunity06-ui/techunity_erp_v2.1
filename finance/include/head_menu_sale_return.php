<?
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
							<?phpif($page_name!="sale_return_create")
							{?>
							<li class="">
								<a href="<?=ROOT.'finance/sale_return_create'?>">
									<i class="fa fa-pencil text-primary"></i>
										Create Credit Note
								</a>
                            </li>
                            <?php}
							if($page_name!="sale_return_list")
							{?>
							<li>
								<a href="<?=ROOT.'finance/sale_return'?>">
									<i class="fa fa-envelope text-info"></i>
										Sale Return List
                                </a>
                            </li>
							<?php} ?>
							
                     </ul>
		</div>
						