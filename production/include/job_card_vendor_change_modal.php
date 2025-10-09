<style>
	
 .middle {
	 width: 100%;
	 text-align: center;
	/* Made by */
}
 /*.middle h1 {
	 font-family: "Inter", sans-serif;
	 color: #fff;
}*/
 .middle input[type="radio"] {
	 display: none;
}
 .middle input[type="radio"]:checked + .box {
	 background-color: #337ab7;
}
 .middle input[type="radio"]:checked + .box.inhouse {
	 background-color: #449d44;
}
 .middle input[type="radio"]:checked + .box span {
	 color: white;
	 transform: translateY(70px);
}
 .middle input[type="radio"]:checked + .box span:before {
	 transform: translateY(0px);
	 opacity: 1;
}
 .middle .box {
	 width: 200px;
	 height: 200px;
	 background-color: #fff;
	 transition: all 250ms ease;
	 will-change: transition;
	 display: inline-block;
	 text-align: center;
	 cursor: pointer;
	 position: relative;
	 font-family: "Inter", sans-serif;
	 font-weight: 900;
}
 .middle .box:active {
	 transform: translateY(10px);
}
 .middle .box span {
	 position: absolute;
	 transform: translate(0, 60px);
	 left: 0;
	 right: 0;
	 transition: all 300ms ease;
	 font-size: 1.5em;
	 user-select: none;
	 color: #007e90;
}
 .middle .box span:before {
	 font-size: 1.2em;
	 font-family: FontAwesome;
	 display: block;
	 transform: translateY(-80px);
	 opacity: 0;
	 transition: all 300ms ease-in-out;
	 font-weight: normal;
	 color: white;
}
 .middle .inhouse span:before {
	 content: "\f015";
}
 .middle .outside span:before {
	 content: "\f0d1";
}
 .middle p {
	 color: #fff;
	 font-family: "Inter", sans-serif;
	 font-weight: 400;
}
 .middle p a {
	 text-decoration: underline;
	 font-weight: bold;
	 color: #fff;
}
 .middle p span:after {
	 content: "\f0e7";
	 font-family: FontAwesome;
	 color: yellow;
}
 

 .yes_no_toggle {
	 background-color: #f1f1f1;
	 border: 1px solid #ddd;
	 width: 100px;
	 border-radius: 2em;
	 padding: 5px;
	 margin: 0 auto;
	 position: relative;
	 margin-top: 15px;
}
 .yes_no_toggle span {
	 text-transform: uppercase;
	 font-weight: bold;
	 position: absolute;
	 top: 5px;
     font-size: 20px;
}
 .yes_no_toggle span.yes_span {
	 left: -45px;
}
 .yes_no_toggle span.no_span {
	 left: 115px;
}
 .yes_no_toggle .toggle_icon {
	 position: relative;
	 z-index: 2;
	 cursor: pointer;
	 -webkit-transition: color 0.5s ease;
	 -moz-transition: color 0.5s ease;
	 -o-transition: color 0.5s ease;
	 transition: color 0.5s ease;
}
 .yes_no_toggle .toggle_icon.yes {
	 margin-left: 2px;
	 float: left;
	 width: 50%;
}
 .yes_no_toggle .toggle_icon.yes.selected {
	 color: #39bf3f;
}
 .yes_no_toggle .toggle_icon.no {
	 margin-right: 2px;
	 float: right;
	 width: 45%;
}
 .yes_no_toggle .toggle_icon.no.selected {
	 color: #bf002d;
}
 .yes_no_toggle .toggle {
	 width: 42px;
	 height: 40px;
	 border-radius: 42px;
	 background-color: #ddd;
	 position: absolute;
	 z-index: 1;
	 left: 0px;
	 top: 0px;
	 -webkit-transition: background-color 0.5s ease;
	 -moz-transition: background-color 0.5s ease;
	 -o-transition: background-color 0.5s ease;
	 transition: background-color 0.5s ease;
}
 .yes_no_toggle .toggle.yes {
	 background-color: rgba(57, 191, 63, 0.5);
}
 .yes_no_toggle .toggle.no {
	 background-color: rgba(191, 0, 45, 0.3);
}
 .yes_no_toggle .clearfix {
	 clear: both;
	 float: none;
}
 .yes_no_wrap {
	 display: none;
}
 .fa-times-rectangle:before, .fa-window-close:before {
    margin-left: 12px;
}
.fa-check-square:before {
    content: "\f14a";
    margin-left: -15px;
}
 
</style>

<div class="modal colored-header info" id="preview_job_card_vendor_change_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Job card action</h3>
			</div>
			<div class="modal-body form">
				<div class="row">

					<!-- <div class="col-md-12 m-bot15" id="">
						<h3 class="m-bot15">Please select option</h3>
						<label class="radio-inline"><input type="radio" name="optradio" value="change_process" checked>Change Process Outside to Inside</label>
						<label class="radio-inline"><input type="radio" name="optradio" value="change_vendor">Change Vendor</label>

					</div> -->

					<div class="col-md-12 m-bot15 middle div_process_type">
					  <h3 class="m-bot15">Select Process Type</h3>
					  <label>
					  <input type="radio" onchange ="toggle_vendor(1)" name="process_type" value="1" id="inhouse_process" checked/>
					  <div class="inhouse box">
					    <span>Inhouse</span>
					  </div>
					</label>

					  <label>
					  <input type="radio" onchange ="toggle_vendor(2)" name="process_type" value="2" id="outside_process"/>
					  <div class="outside box">
					    <span>Outside</span>
					  </div>
					</label>
					</div>
<!-- <div class="col-md-12 m-bot15 text-center div_vendor">
	<h3 class="m-bot15">Are you want to change vendor?</h3>
					<div class="yes_no_toggle">
	<span class="yes_span">Yes</span>
	<span class="no_span">No</span>
	<i class="fa fa-check-square fa-2x toggle_icon yes" aria-hidden="true"></i>
	<i class="fa fa-times-rectangle fa-2x toggle_icon no selected" aria-hidden="true"></i>
	
	<div class="toggle selected no" style="left: 56px;"></div>
	<div class="clearfix"></div>
</div>

<div class="yes_no_wrap">
	<input checked="checked" type="radio" value="yes" onchange="toggle_process_type(1)" id="change_vendor_yes" name="yes_no" /> Yes
	<input type="radio" checked="checked" onchange="toggle_process_type(0)" id="change_vendor_no" name="yes_no" value="no" /> No
</div>
</div> -->

					<div class="clearfix" ></div>
						<div class="col-md-12 text-center" style="margin-top:10px;">
							<button type="button" onclick="show_qty_msg()" class="btn btn-success">Save</button> &nbsp;
							<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
				</div>
				<input type="hidden" id="product_id" name="product_id" value="">
				<input type="hidden" id="rp_id" name="rp_id" value="">
				<input type="hidden" id="process_id" name="process_id" value="">
				<input type="hidden" id="process_type" name="process_type" value="">
				<input type="hidden" id="job_work_id" name="job_work_id" value="">
				<input type="hidden" id="p_id" name="p_id" value="">
			</div>	
		</div>
		
		
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
	$(function() {
	var $icon = $(".toggle_icon");
	var $toggle = $(".toggle")
	var $sad = $(".no");
	var $happy = $(".yes");
	var $yes = $("#change_vendor_yes");
	var $no = $("#change_vendor_no");
	
	$icon.on("click", function() {
		var $this = $(this);
		if ($this.hasClass("yes")) {
			$sad.removeClass("selected");
			$happy.addClass("selected");
			$toggle.removeClass("no");
			$toggle.addClass("yes");
			$yes.prop("checked", "checked");
			$(".div_process_type").slideUp();
			$toggle.animate({
				left: "0px"
			}, {
				queue: false,
				ease: 'easeInSine'
			});
		}
		else {
			$no.prop("checked", "checked");
			$sad.addClass("selected");
			$happy.removeClass("selected");
			$toggle.addClass("no");
			$toggle.removeClass("yes");
			$(".div_process_type").slideDown();
			$toggle.animate({
				left: "56px"
			}, {
				queue: false,
				ease: 'easeInSine'
			});
		};
	});
});
</script>