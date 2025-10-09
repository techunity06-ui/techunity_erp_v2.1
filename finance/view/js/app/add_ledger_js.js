	show_div_ledger(0);
	jQuery('#emp_password').bind('cut copy paste', function(e) {
		e.preventDefault();
		toastr.warning("Cut / Copy / Paste Disabled", "WARNING");
	});

	// $(".select2").select2({
	// 	width: '100%',

	// });
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});

	$('#ledger_name').keyup(function(e) {
		var txtVal = $(this).val();
		var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
		$('#alias_name').val(txtVal);
		if(group_form=='customer_form'){
			$('#company_name').val(txtVal);
		}

	});
	function showPswdFunction() {
		var x = document.getElementById("emp_password");
		if (x.type === "password") {
			x.type = "text";
		} else {
			x.type = "password";
		}
	}

	function show_div_ledger(gid)
	{
		Loading();
		$.ajax({

			type:'post',
			url: root_domain+administration_domain+'app/ledger/',
			type: "POST",
			data: { mode : "get_open_form", gid : gid },
			success: function(response)
			{
				//alert(response);
				var obj = JSON.parse(response);

				if(obj.form_id=='customer_form'){
					$('#company_name').val($('#ledger_name').val());
				}
			
			$("#customer_form").hide();
			$("#bank_form").hide();
			$("#expense_form").hide();
			$("#income_form").hide();
			$("#emp_form").hide();
			$("#tax_form").hide();
			$('#'+obj.form_id).show();
			
			$('#'+obj.form_id).removeClass("ledger_forms");
			$('#form_type').val(obj.form_id);

			$('#group_id').val(obj.group_id);
			$('#parent_group_id').val(obj.group_parent_id);

			ledger_grp_change();
			ledger_grp_change_fix_assets();
			ledger_grp_change_Tax_type();
			ledger_monthly_budget_change();
			ledger_chequebank_change();
			ledger_tcs_tds_change();
			get_party_by_ledger();
		}
	});

		Unloading();
	}

	$('.billSundry').hide();

