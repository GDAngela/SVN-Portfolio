<?php

class version{
  public $number;
  public $author;
  public $commitMessage;
  public $date;
}

class file{
  public $size;
  public $type;
  public $path;
  public $versions=array();
}


class project{
  public $date;
  public $name;
  public $version;
  public $summary;
  public $files=array();
}


//helper to extract the name of the files
function find_name($str){
  $token = strtok($str, "/");
  $secondLast=null;
  while ($token !== false)
  {
    $secondLast=$token;
    $token = strtok("/");
  }
  return $secondLast;
}


//helper function to find type of the files
function find_type($str){
  $ret=null;
  if (strpos(strtolower($str),"test")!==false){
    $ret="Test";
  }else if(strpos(strtolower($str),"gif")!==false ||strpos(strtolower($str),"jpg")!==false ){
    $ret="Image";
  }else if(strpos(strtolower($str),"doxygen")!==false){
    $ret="Documentation";
  }else if(strpos(strtolower(find_name($str)),".java")!==false  || strpos(strtolower(find_name($str)),".c")!==false|| strpos(strtolower(find_name($str)),".rb")!==false || strpos(strtolower(find_name($str)),".txt")!==false ||strpos(strtolower($str),"src")!==false){
    $ret="Code";
  }else{
    $ret="Resources";
  }
  return $ret;
}

class data{
  var $len;
  var $projs;

  public $projects=array();

  function getLength(){
    return $this->len;
  }

  function getProjects(){
    return $this->projs;
  }

  function initialize(){
    $this->len = 5;
    $xmlLog=simplexml_load_file("svn_log.xml") or die("Error: Cannot create object");
    $xmlList=simplexml_load_file("svn_list.xml") or die("Error: Cannot create object");
    $entries=$xmlList->list->entry;
    $length=count($entries);
    $this->len = $length;
    $allfils=array();
    #create dictionary of project and initialize name.date,version
    for ($i = 0; $i<$length; $i++){
      if(strpos($entries[$i]->name,"/")==false){
        $projects[(string) ($entries[$i]->name)]=new project;
        $projects[(string) ($entries[$i]->name)]->date=(string)$entries[$i]->commit->date;
        $projects[(string) ($entries[$i]->name)]->name=(string)$entries[$i]->name;
        $projects[(string) ($entries[$i]->name)]->version=(string )$entries[$i]->commit['revision'];
      }
    }
    #find latest submit comment message for each project
    $logEntries=$xmlLog->logentry;
    $logLength=count($logEntries);
    for ($i = 0; $i<$logLength; $i++){
      foreach($projects as $key => $value){
        if($value->version == $logEntries[$i]['revision']){
          $value->summary=(string) $logEntries[$i]->msg;
          break;
        }
      }
    }
    #create files dictionary for each project and for each file assign size,path,and type
    for ($i = 0; $i<$length; $i++){
      foreach($projects as $key =>$value){
        if(strtok($entries[$i]->name,"/")==$key && strpos($entries[$i]->name,"/")!==false &&
        strcmp((string)$entries[$i]['kind'],"dir")!=0){
          $filename=(string) $entries[$i]->name;
          $allfiles[$filename]=array();
          $value->files[$filename]=new file;
          $value->files[$filename]->size=(string) $entries[$i]->size;
          $value->files[$filename]->path=(string) $entries[$i]->name;
          $value->files[$filename]->type=find_type((string) $entries[$i]->name);
          break;
        }
      }
    }

    #create versions dictionary for each file

    for ($i = 0; $i<$logLength; $i++){
      $pathLength=count($logEntries[$i]->paths->path);
      for($j = 0; $j<$pathLength; $j++){
        $singleFile=$logEntries[$i]->paths->path[$j];
        if($singleFile['action']=="M" || $singleFile['action']=="A"){
          #position of second /
          $position=strpos((string)$singleFile,"/",2);
          $nameLength=strlen($singleFile);
          $name=substr($singleFile,$position+1,$nameLength);
          if(array_key_exists($name,$allfiles)){
            $newversion=new version;
            $newversion->number=(string)$logEntries[$i]['revision'];
            $newversion->author=(string)$logEntries[$i]->author;
            $newversion->commitMessage=(string)$logEntries[$i]->msg;
            $newversion->date=(string)$logEntries[$i]->date;
            array_push($allfiles[$name],$newversion);
          }
        }
      }
    }

    foreach($projects as $key =>$value){
      foreach($value->files as $filekey =>$filevalue){
        $filevalue->versions=$allfiles[$filevalue->path];
      }

    }

    $this->projs = $projects;

  }
}
