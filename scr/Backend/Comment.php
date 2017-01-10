<?php
$con = mysqli_connect("localhost","root","","");
// Check connection
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
// /Comment.php POST loadFileComment: return the existing comments of the file
if(isset($_POST['loadFileComment']) && !empty($_POST['loadFileComment'])) {
  $fileId = $_POST['loadFileComment'];

  $sql=mysqli_prepare($con,"SELECT * FROM Comments WHERE FileId=? ORDER BY Id");
  mysqli_stmt_bind_param($sql,"s",$fileId);
  mysqli_stmt_execute($sql);

  //transfer information in resulting table into $comments
  //citation:http://stackoverflow.com/questions/750648/select-from-in-mysqli

  $meta = $sql->result_metadata();
  while ($field = $meta->fetch_field()) {
    $parameters[] = &$row[$field->name];
  }
  call_user_func_array(array($sql, 'bind_result'), $parameters);
  $comments = array();
  while ($sql->fetch()) {
    foreach($row as $key => $val) {
      $x[$key] = $val;
    }
    $comments[] = $x;
  }
echo json_encode($comments);

}

// Insert comment into database
if (!empty($_POST) && empty($_POST['loadFileComment']) && !empty($_POST['message'])){
  $countId=mysqli_query($con,"SELECT * FROM Comments");
  $FileId=$_POST["fileName"];
  //each conmment in each file has unique Id
  $Id=$countId->num_rows+1;
  //trim and prevent cross sricpting
  $cleanData=clean_data($_POST["message"]);
  //if comment is empty don't add it
  if($cleanData!="" ){
    //redflag
    $Text=replace_bad_word($cleanData,$con);
    $ParentId=0;
    if (isset($_POST['parentId']) && !empty($_POST['parentId'])){
      $ParentId = $_POST['parentId'];
    } else {
      $ParentId=0;
    }
    $sql=mysqli_prepare($con,"INSERT INTO Comments (FileId, Id, Text, ParentId) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($sql,"sisi",$FileId,$Id,$Text,$ParentId);
    mysqli_stmt_execute($sql);
    mysqli_stmt_close($stmt);

    $con->close();
  }
}

function clean_data($data){
  $data=trim($data);
  //Convert the predefined characters "<" (less than) and ">" (greater than) to HTML entities:
  $data=htmlspecialchars($data);
  return $data;
}

//replace the bad words defined in the database
function replace_bad_word($str, $con){
  $result=mysqli_query($con,"SELECT * FROM RedFlag");
  if (mysqli_num_rows($result)>0) {
    while ($row = mysqli_fetch_assoc($result) ) {
      $str=str_ireplace("".$row['badWords'],"".$row['replacement'],"".$str);
    }
  }

  return $str;
}
