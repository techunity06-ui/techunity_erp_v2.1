<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
// include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$tables = array('tbl_tax_category');
echo getallColumnsFromTable($dbcon, $tables);
function getallColumnsFromTable($dbcon, $table_name, $id = false) {
    $str = '';
    foreach($table_name as $table){
        $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'test' AND TABLE_NAME = '$table'";
        $q = $dbcon->query($query);
    // $str .=$table.'<br><br>';
        while($rel = brp_mysqli_fetch_assoc($q)) {
            $sel = ''; 
        // if($rel['COLUMN_NAME'] == $id) {
        //     $sel = "selected='selected'";
        // }
            $str .= $rel['COLUMN_NAME'].',';
        }
        $str = trim($str,",");
        $str = str_replace('tax_cat_id,','',$str);
        $strs = str_replace('company_id','4',$str);
        $qry = "INSERT INTO `$table` ($str) SELECT $strs FROM `$table` WHERE company_id = 1";
    $str .='=======================<br>';
    }

    return $qry;
}
?>