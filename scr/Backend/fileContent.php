<?php

//get file content 
if (!empty($_POST)){
  $username = '';
$password = '';
$address='';

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$password")
    )
));
  $filePath=$_POST["fileName"];
  $fileUrl =$address. $filePath;
  $content = file_get_contents($fileUrl, false, $context);
  $content=explode("\n",$content);
  $proccessedContent="";
  foreach($content as $line){
    $proccessedContent=$proccessedContent.htmlspecialchars($line)."<br>";
  }

  echo $proccessedContent;
}
