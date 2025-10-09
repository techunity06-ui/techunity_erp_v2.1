//CoRo Technologies
function Loading(top) {
	if(top)
		// $("html, body").animate({ scrollTop: 0 }, "fast");
	
	$("#mask").show();
}
function Unloading() {
	$("#mask").hide();
}

jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
};

//function for converting string into indian currency format
function ToInNumber(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	var z = 0;
	var len = String(x1).length;
	var num = parseInt((len/2)-1);

	while (rgx.test(x1))
	{
		if(z > 0)
		{
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		else
		{
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
			rgx = /(\d+)(\d{2})/;
		}
		z++;
		num--;
		if(num == 0)
		{
			break;
		}
	}
	return x1 + x2;
}
$(document).ready(function(){
	/*Select2*/
	$(".payee").select2({
		width: '70%'
	});
	$(".select2").select2({
		width: '100%'
	});
	
});
