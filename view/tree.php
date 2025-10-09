<?php 
include("../config/config.php");
include("../config/session.php");
include("../include/common_functions.php");
include("../include/function_database_query.php");

$sql = "SELECT * FROM tbl_bomtrn where bom_id='9'";
$res = $dbcon->query($sql);
    //iterate on results row and create new index array of data
    while( $row = mysqli_fetch_assoc($res) ) { 
        $data[] = $row;
    }
    $itemsByReference = array();
 
// Build array of item references:
foreach($data as $key => &$item) {
   $itemsByReference[$item['product_id']] = &$item;
   // Children array:
   $itemsByReference[$item['product_id']]['children'] = array();
   // Empty data class (so that json_encode adds "data: {}" ) 
   $itemsByReference[$item['product_id']]['data'] = new StdClass();
}
 
// Set items as children of the relevant parent item.
foreach($data as $key => &$item)
   if($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
      $itemsByReference [$item['parent_id']]['children'][] = &$item;
 
// Remove items that were added to parents elsewhere:
foreach($data as $key => &$item) {
   if($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
      unset($data[$key]);
}
// Encode:
echo json_encode($data);

?>