$(document).ready(function() {
    $(".btn_save_extra_bom_no").on("click", function () {
        
        var obj = $(this).closest(".div_extra_bom_no").find(".extra_bom_no");
        var extra_no = obj.val();
        var main_bom_id = obj.attr('data-main_bom_id');
        var bom_id = obj.attr('data-bom_id');
        var bom_version = obj.attr('data-bom_version');
        var parent_bom_id = obj.attr('data-parent_bom_id');
        var product_id = obj.attr('data-product_id');
        var edit_id  = obj.attr('data-edit_id');
        
        if(extra_no == ""){
        	   toastr.warning("Enter Extra BOM No", "ERROR")
               return false;
        }
       
       Loading();
       $.ajax({
            type: "POST",
            url: root_domain+production_domain+'app/bom/',
            data: { 
                    mode : "add_extra_bom_no",
                    extra_no : extra_no,
                    main_bom_id:main_bom_id,
                    bom_id : bom_id,
                    bom_version:bom_version,
                    parent_bom_id:parent_bom_id,
                    product_id:product_id,
                    edit_id : edit_id
                },
            success: function(response)
            {
                    //console.log(response)
                    if(response.trim() == "1") {
                        toastr.success("EXTRA BOM NO ADDED SUCCESSFULLY", "SUCCESS");
                        Unloading();
                    }else if(response.trim() == "update") {
                        toastr.success("EXTRA BOM NO UPDATED SUCCESSFULLY", "SUCCESS");
                        Unloading(); 
                    }
                    else if(response.trim() == "0") {
                        toastr.warning("SOMETHING WRONG", "WARNING");
                        Unloading();
                    }     

                    setTimeout(function(){
                        location.reload();
                    },800)                      
                }
            }); 
    });
});
