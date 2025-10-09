<?php
var_dump ($_GET['page']);
try{
    $page= $_GET['page'];
    $resultCount = 10;
    $end = ($page - 1) * $resultCount;       
    $start = $end + $resultCount;

    $stmt = $db_con->query("SELECT col,col FROM table WHERE col LIKE '".$_GET['term']."%' LIMIT {$end},{$start}");
    $stmt->execute();
    $count = $stmt->rowCount();
        $data = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = ['id'=>$row['id'], 'col'=>$row['col'], 'total_count'=>$count];
        }
        // IF SEARCH TERM IS NOT FOUND DATA WILL BE EMPTY SO
        if (empty($data)){
            $empty[] = ['id'=>'', 'col'=>'', 'total_count'=>'']; 
            echo json_encode($empty);
        }else{ 
            echo json_encode($data);
        }
}
catch(PDOException $e){
  //  echo $e->getMessage();
  echo "123";
}

?>