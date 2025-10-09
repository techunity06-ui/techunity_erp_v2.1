<footer class="site-footer">
  <div class="text-center">
	  <?php echo date('Y');?> &copy; BRP Software Solutions LLP.
	  <a href="#" class="go-top">
		  <i class="fa fa-angle-up"></i>
	  </a>
  </div>
</footer>
<div class="modal colored-header info" id="ModalPaymentRemainder" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Payment Detail  <span id="exciseinvoice_no"></h3>				
			</div>
			<div class="col-md-10">
			<div class="modal-body form" id="payment_remainder">								
			<div class="form-group">
					<label class="col-sm-6">Customer Name :</label>
					<label class="col-sm-6" id="cust_name"></label>
				</div>			
				<div class="form-group">
					<label class="col-sm-6">Address :</label>
					<label class="col-sm-6" id="cust_address"></label>
				</div>
                <div class="form-group">
					<label class="col-sm-6">Email :</label>
					<label class="col-sm-6" id="email"></label>
				</div>
				<div class="col-md-12"></div>
                <div class="form-group">
					<label class="col-sm-6">Mobile No :</label>
					<label class="col-sm-6" id="mobile"></label>
				</div>
				<div class="form-group">
					<label class="col-sm-6">Excise invoice Date :</label>
					<label class="col-sm-6" id="exciseinvoice_date"></label>
				</div><div class="form-group">
					<label class="col-sm-6">Last Payment Date :</label>
					<label class="col-sm-6" id="ex_date"></label>
				</div>
				
			</div>
			</div>
				<div class="col-md-12"></div>
            <div class="modal-footer">				
				<button type="button" class="btn btn-default btn-flat md-close col-md-10" data-dismiss="modal">Close</button>				
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
