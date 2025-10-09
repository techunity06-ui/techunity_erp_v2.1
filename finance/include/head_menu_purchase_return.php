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
							<?php if($page_name!="debit_note_create")
							{?>
							<li class="">
								<a href="<?=ROOT.'finance/debit_note_create'?>">
									<i class="fa fa-pencil text-primary"></i>
										Create Debit Note
								</a>
                            </li>
                            <?php }
							if($page_name!="debitnote")
							{?>
							<li>
								<a href="<?=ROOT.'finance/debitnote'?>">
									<i class="fa fa-envelope text-info"></i>
										Debit Note List
                                </a>
                            </li>
							<?php } ?>
							
                     </ul>
		</div>
						