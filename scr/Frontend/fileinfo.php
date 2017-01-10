<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

  <title>File Information</title>

  <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->
  <!-- <link href="https://fonts.googleapis.com/css?family=Raleway|Roboto" rel="stylesheet"> -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <div class="container-fluid">
    <div class="page-header">
      <h1>File Info</h1>
    </div>

    <?php
    require 'parser.php';
    $file =  unserialize($_GET['file']);

    echo "<div class = 'fileSize'>File size: $file->size KB</div>";
    echo "<div class = 'fileType'>File type: $file->type</div>";
    echo "<div class = 'filePath'>File Path: $file->path</div>";
    ?>

    <div class = 'versionInfo'>
      <div class="page-header">
        <h1>Version Info</h1>
      </div>
      <?php
      echo "<table class='table table-striped'>";
      echo "<thead><tr><th>Verision</th><th>Author</th><th>Date</th><th>Message</th></tr></thead>";
      echo "<tbody>";
      foreach($file->versions as $version){
        echo "<tr>";
        echo "<td>$version->number</td>";
        echo "<td>$version->author</td>";
        //format date
        echo "<td>". substr($version->date,0,10) . "</td>";
        echo "<td>$version->commitMessage</td>";
        echo "</tr>";
      }
      echo "</tbody>";
      echo "</table>";
      ?>
    </div>

    <div class="page-header">
      <h1>File Detail</h1>
    </div>
    <button id="myButton" type="button" class="btn btn-default">Click Me!</button>
    <?php
    $iframeUrl = "https://subversion.ews.illinois.edu/svn/fa16-cs242/jhuang67/" . $file->path;
      echo "<div class = 'sourceCode' id='showIframe'> <iframe src='$iframeUrl' height='500' width = '700'></iframe></div>";
    ?>
    <div class="page-header">
      <h1>File Content</h1>
    </div>
    <iframe id="fileContent" height='500' width = '700'> </iframe>
  <!-- <div id="fileContent">
   </div>-->
    <button id="myButton2" type="button" onclick=loadFileContent() class="btn btn-default">Click Me2!</button>

      <div class="page-header">
        <h1>Comments</h1>
      </div>
      <div id="comments">
      </div>
      <form role="form" action="Comment.php" method="POST" target="_parent">
        <h3>Leave a comment:</h3>
        <textarea type="text" id="message" name="message" rows="10" cols="300" value="">
        </textarea><br>
        <?php
        echo "<input type='hidden' id='fileName' name='fileName' value='$file->path' >";
        ?>
      </form>
      <button id="submitButton" type="button" class="btn btn-default">Sumbit Comment</button>
    </div>
    <script>
    // Call back when clicking reply comment
    function replyComment(id){
      var fileName=$('#fileName').val();
      var reply = prompt("Please entery your reply", "");
      $.ajax({
        type: "POST",
        url: "Comment.php",
        data: { message: reply, fileName: fileName,parentId:id}
      }).success(function() {
        reloadComments(fileName);
      });
    }

    // format the HTML for a single comment
    function formatComment(text,  currId, isRoot){
      var divId = 'comment' + currId;
      var rootClass = '';
      if (isRoot) rootClass = "rootComment";
      var result = "<div class='commentContainer " + rootClass + "' id='" + divId +"'><li>" +
      "<span>" + text + "</span>" + "<button class='commentReply' onclick='replyComment(this.id)' id = '" + currId + "'>Reply</button></li>" +
      "</div>";
      return result;
    }

    function reloadComments(fileName){
      var fileName=$('#fileName').val();
      $.ajax({
        type: "POST",
        url: "Comment.php",
        data: { loadFileComment: fileName}
      }).success(function(data) {
        $("#comments").empty();
        var comments = $.parseJSON(data);
        var commentHTML = "";           // A string representation of the final comment HTML
        for (var index in comments){
          var parentId = comments[index]["ParentId"];
          var formattedComment = "";
          if (parentId == 0){
            formattedComment = formatComment(comments[index]["Text"],comments[index]["Id"],true);
            commentHTML += formattedComment;
          } else {
            // Find the closing tag of the parent div, inster the formated div before the closing tag
            formattedComment = formatComment(comments[index]["Text"],comments[index]["Id"],false);
            var parentDivId = "comment" + parentId;

            // Convert the current comment string to html object
            // Need to append <ul> when parsing, otherwise would mess up the html
            var $htmlDoc = $( "<ul>" + commentHTML  );
            var parentHtml = $($htmlDoc).find("#" + parentDivId).append("<ul>" + formattedComment + "</ul>").html();

            // Convert the html objet back to string
            commentHTML = $htmlDoc.html();
          }
        }
        commentHTML = "<ul>" + commentHTML + "</ul>";
        //change content of #comment element
        $("#comments").html(commentHTML);
      });
    }

    function loadFileContent(){
      var fileName=$('#fileName').val();
      $.ajax({
        type: "POST",
        url: "fileContent.php",
        data: { fileName: fileName}
      }).success(function(data) {
        $("#fileContent").empty();
      //  var lines=$.parseJSON(data);
        //var s = document.getElementById('fileContent').src="debug.txt";
        var s = document.getElementById('fileContent');
        s.contentDocument.write(data);

      });
    }

    $(document).ready(function(){
      var fileName=$('#fileName').val();
      reloadComments(fileName);
      // When clicking each display iframe, toggle the visibility of the info div
      $('#myButton').click(function() {
        $('#showIframe').toggle();
      });
      // Submit button clicked
      $('#submitButton').click( function() {
        var comment = $('#message').val();
        var fileName=$('#fileName').val();
        $.ajax({
          type: "POST",
          url: "Comment.php",
          data: { message: comment, fileName: fileName}
        }).success(function() {
          reloadComments(fileName);
        });
        $("#message").val('');
       });
    });
    </script>

    <script src="js/bootstrap.min.js"></script>
  </body>
  </html>
