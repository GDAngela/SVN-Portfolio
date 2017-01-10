<?php
use PHPUnit\Framework\TestCase;
function httpPost($url,$params)
{
  $postData = '';
  //create name value pairs seperated by &
  foreach($params as $k => $v)
  {
    $postData .= $k . '='.$v.'&';
  }
  $postData = rtrim($postData, '&');

  $ch = curl_init();

  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_POST, count($postData));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

  $output=curl_exec($ch);

  curl_close($ch);
  return $output;

}
class parserTest extends TestCase
{
  public function testInsertComment(){
    $params = array(
      "fileName"=>"newFile1",
      "message"=>"hahaha"
    );
    httpPost("http://localhost/portfolio/Comment.php",$params);
    $params = array(
      "loadFileComment"=>"newFile1"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    $this->assertEquals(true,strpos($result,'"FileId":"newFile1"')!==false);
    $this->assertEquals(true,strpos($result,'"Text":"hahaha"')!==false);
  }

  public function testInsertEmptyComment(){
    $params = array(
      "fileName"=>"newFile2",
      "message"=>""
    );
    httpPost("http://localhost/portfolio/Comment.php",$params);
    $params = array(
      "loadFileComment"=>"newFile2"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    //echo $result;
    $this->assertEquals(false,strpos($result,'newFile2'));
  }

  public function testSQLAttacksLoadFile(){
    $params = array(
      "loadFileComment"=>"'' OR '1'='1'"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    //echo $result;
    $this->assertEquals(true,$result=="[]");
  }


  public function testCrossScripting(){
    $params = array(
      "fileName"=>"newFile3",
      "message"=>"<span>haha</span>"
    );
    httpPost("http://localhost/portfolio/Comment.php",$params);
    $params = array(
      "loadFileComment"=>"newFile3"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    //echo $result;
    $this->assertEquals(true,strpos($result,'&lt;span&gt;haha&lt;\/span&gt;')!==false);
  }

  public function testRedFlag(){
    $params = array(
      "fileName"=>"newFile3",
      "message"=>"<Fuck you>"
    );
    httpPost("http://localhost/portfolio/Comment.php",$params);
    $params = array(
      "loadFileComment"=>"newFile3"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    //echo $result;
    $this->assertEquals(true,strpos($result,'f**k you')!==false);
  }

  public function testRedFlag2(){
    $params = array(
      "fileName"=>"newFile4",
      "message"=>"<You are fucking dumb ass. Fuck and skew you!>"
    );
    httpPost("http://localhost/portfolio/Comment.php",$params);
    $params = array(
      "loadFileComment"=>"newFile4"
    );
    $result=httpPost("http://localhost/portfolio/Comment.php",$params);
    //echo $result;
    $this->assertEquals(true,strpos($result,'You are f**king unintelligent a**. f**k and Love you!')!==false);
  }





}
