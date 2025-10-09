<?php 
   session_start();
   include('../include/urlfile.php');
   
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   $form='Material Specification ';
   $branch_id = $_SESSION['branch_id'];
   //check permission for process type add
      $bulkAccessArray = canCheckPermissionAccess($dbcon, [
      	ADMINISTRATOR_MSPEC_LIST,
          ADMINISTRATOR_MSPEC_CREATE
      ]);
   
      if(!in_array(ADMINISTRATOR_MSPEC_LIST,$bulkAccessArray)){
          header("Location: ".DOMAIN."permission_access");
      }
      $param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
      $companyConfiguration=getCompanyConfiguration($dbcon);
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>MATERIAL SPECIFICATION</title>
      <?php include_once($include.'include_css_file.php');?>
   </head>
   <body>
      <section id="container" >
         <?php include_once($include.'include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once($include.'left_menu.php');?>
         <!--sidebar end-->
         <!--main content start-->
         <section id="main-content">
            <section class="wrapper">
               <div class="row">
                  <div class="col-lg-12">
                     <!--breadcrumbs start -->
                     <section class="panel">
                        <header class="panel-heading">
                           <h3>New <?=$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
                              <li class="active"><?=$form?></li>
                           </ul>
                        </div>
                     </section>
                     <!--breadcrumbs end -->
                  </div>
               </div>
               <!--unit overview start-->
               <?php include_once($include.'country_unit_city.php');?>
               <div class="row">
               <?php if(in_array(ADMINISTRATOR_MSPEC_CREATE,$bulkAccessArray)){ ?>
               <div class="col-sm-3">
                  <section class="panel">
                     <header class="panel-heading">
                        New <?=$form ?> LIST
                        
                     </header>
                     <div class="panel-body">
                     	<div class="alert alert-info" role="alert">
						  <a href="javascript:void(0)" class="alert-link">NOTE :</a>Please set blank those parameter, which parameter will not use in the calculation.
						</div>
                        <form role="form" id="mspec_add" action="javascript:;" method="post" name="mspec_add">
                           <?php //if($branch_id=='0'){ ?>
                           <?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
                           <div class="form-group">
                              <label>Branch *</label>
                             
                              <select class="branch_validate" name="branch_id" id="abranch_id" required >
                                 <?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
                                          <?=getBranchBox_new($dbcon, $branch,'all');?>
                              </select>
                           </div>
                           <?php } ?>
                           <div class="form-group">
                              <label>Material Type</label>
                              <input class="form-control" type='text' name='m_type_name' id='m_type_name' value='' />
                           </div>
                           <?php 
                              $rs_parameter=$dbcon->query($param_sql);	
                              while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
                              $parameter_name = ucfirst(strtolower($rel_param['material_parameter_name']));	
                              $parameter_id = 'param_'.$rel_param['material_parameter_id'];	
                              
                              ?>
                           <div class="form-group">
                              <label><?=$parameter_name?></label>
                              <input class="form-control" type='text' name='<?=$parameter_id?>' id='<?=$parameter_id?>' value='' onkeypress="return isNumberKey(event)" />
                           </div>
                           <?php } ?>
                           <div class="form-group">
                              <label>Formula</label>
                              <input class="form-control" type='text' name='formula' id='formula_id' value='' required="" />
                              <a class="btn btn-xs btn-primary" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="get_formula()"><i class="fa fa-plus"></i>Add Formula</a>
                           </div>
                           <!--<div class="form-group">
                              <label>Thickness</label>
                              <input class="form-control" type='text' name='m_type_thick' id='m_type_thick' value='1' onkeypress="return isNumberKey(event)" />
                               </div>
                              
                              
                              <div class="form-group">
                              <label>Density</label>
                              <input class="form-control" type='text' name='m_type_density' id='m_type_density' value='0' onkeypress="return isNumberKey(event)" />
                               </div> -->
                           <input type='hidden' name='mode' id='mode' value='add' />
                           <button type="submit" class="btn btn-info">Submit</button>
                        </form>
                     </div>
                  </section>
               </div>
               <?php } ?>
               <?php if(in_array(ADMINISTRATOR_MSPEC_CREATE,$bulkAccessArray)){ ?>	
               <div class="col-sm-9">
                  <?php }else{ ?>	
                  <div class="col-sm-12">
                     <?php } ?>
                     <section class="panel">
                        <header class="panel-heading">
                           Material Specification List
                           <span class="tools pull-right">
                           <a href="javascript:;" class="fa fa-chevron-down"></a>
                           
                           </span>
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table  class="display table table-bordered table-striped" id="dynamic-table">
                                 <div class="col-md-12">
                                    <div class="col-md-6">
                                       
                                       <div class="form-group">
                                 <label class="col-md-4" style="text-align: right">Branch *</label>
                                 <div class="col-md-6">
                                    <select class="select2" name="branch_id" id="branch_id" onchange="load_mspec_datatable()" required <?php if($companyConfiguration['branch_wise_manage']=='0'){ ?>disabled <?php } ?>>
                                             <?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
                                          <?=getBranchBox_new($dbcon, $branch,'all');?>
                                       </select>
                                    </div>
                                 </div>
                                 
                              </div>
                                 </div>
                                 <thead>
                                    <tr>
                                       <th>Sr. NO.</th>
                                       <th>Material Type</th>
                                       <!-- <?php 
                                          $rs_parameter_list=$dbcon->query($param_sql);	
                                          while($rel_list=brp_mysqli_fetch_assoc($rs_parameter_list)){
                                          	$parameter_name = ucfirst(strtolower($rel_list['material_parameter_name']));
                                          ?>
                                       <th><?=$parameter_name?></th>
                                       <?php } ?> -->
                                       <th>Formula</th>
                                       <th class="hidden-phone">Action</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </section>
                  </div>
               </div>
               <!--unit overview end-->
            </section>
         </section>
         <!--main content end-->
         <!--footer start-->
         <?php include_once($include.'footer.php');?>
         <!--footer end-->
      </section>
      <!-- Modal -->
      <div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog custom-width">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h3>Edit Material Type</h3>
               </div>
               <div class="modal-body form">
                  <form id="FormEditunit" role="form" method="post" novalidate>
                     <?php //if($branch_id=='0'){ ?>
                     <?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
                     <div class="form-group">
                        <label>Branch *</label>
                       
                        <select class="branch_validate" name="branch_id" id="e_branch_id" required>
                          <?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
                                          <?=getBranchBox_new($dbcon, $branch,'all');?>
                        </select>
                     </div>
                     <?php } ?>
                     <div class="form-group">
                        <label for="unitid">Material Type *</label>
                        <input type="text" class="form-control" name="e_m_type_name" id="e_m_type_name" />
                     </div>
                     <?php 
                        $rs_parameter_edit=$dbcon->query($param_sql);	
                        while($rel_param=brp_mysqli_fetch_assoc($rs_parameter_edit)){
                        $parameter_name = ucfirst(strtolower($rel_param['material_parameter_name']));	
                        $parameter_id = 'edit_param_'.$rel_param['material_parameter_id'];	
                        ?>
                     <div class="form-group">
                        <label><?=$parameter_name?></label>
                        <input class="form-control" type='text' name='<?=$parameter_id?>' id='<?=$parameter_id?>' onkeypress="return isNumberKey(event)" />
                     </div>
                     <?php } ?>
                     <div class="form-group">
                      <label>Formula</label>
                      <input class="form-control" type='text' name='edit_formula' id='edit_formula_id' value='' required="" />
                      <a class="btn btn-xs btn-primary" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="get_formula()"><i class="fa fa-plus"></i>Add Formula</a>
                   </div>
               </div>
               <div class="modal-footer">
	               <input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
	               <input type="hidden" name="edit_id" id="edit_id" value="" />
	               <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
	               <button class="btn btn-info btn-flat" type="submit">Update</button>
               </div>
               </form>
            </div>
            <!-- /.modal-content -->
         </div>
         <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
      <div class="modal colored-header info" id="bs-example-component_code" role="dialog" data-keyboard="false" data-backdrop="static">
      	  <div class="modal-dialog custom-width">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h3>Set Formula</h3>
               </div>
               <div class="modal-body form">
               		<table  class="display table table-bordered table-striped" id="dynamic-table">
           			 <thead>
                        <tr>
                           <th>Sr. NO.</th>
                           <th>Parameter Name</th>
                           <th>Parameter Code</th>
                        </tr>
                      </thead>
                      <tbody id="parameter_data">
                      </tbody>             
               		</table>		
               </div>	
            </div>
          </div>     
      </div>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/material_mst.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
         		width: '100%'
         	});
         $(".branch_validate").select2({
   width: '100%'
}).on('change', function() {
    $(this).valid();
});
      </script>
   </body>
</html>