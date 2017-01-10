<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Potofolio</title>

<!-- Citation: http://getbootstrap.com/getting-started/, https://jquery.com/    -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <div class="container-fluid">
    <div class="jumbotron">
      <h1> Portfolio: Jingjing Huang</h1>
    </div>

    <div class="row">
      <div class="col-md-1"></div>
      <div class="mainContent col-md-10">
        <div class="page-header">
          <h1>Projects</h1>
        </div>
        <?php
        require 'parser.php';

        $svnData = new data();
        $svnData->initialize();

        # Each project is displayed as display name with info div
        # Display name has id $projectName + $i
        # Info div has id displayName + "Info"
        $i = 0;
        foreach ($svnData->getProjects() as $project){
          $projectName = $project->name;
          $id = "project$i";
          echo "<div class= 'projectNameDisplay' id = '$id'>" . $projectName . "</div>";
          $infoDivId = $id . "Info";

          echo "<div class = 'projectInfo well' id = '$infoDivId'>";
          # Project info
          echo "<div class = 'projectDate'>Date: $project->date</div>";
          echo "<div class = 'projectVersion'>Version: $project->version</div>";
          echo "<div class = 'projectSummary'>Summary: $project->summary</div>";

          $filesDisplayId = $id . "Files";
          echo "<div class = 'filesDisplay' id = $filesDisplayId> Files </div>";
          $filesInfoDivId = $filesDisplayId . "Info";
          echo "<div class = 'filesInfo' id = '$filesInfoDivId'>";
          # File info
          foreach ($project->files as $file){
            $serializedFile = serialize($file);
              # cite http://stackoverflow.com/questions/2533093/store-objects-in-get
            echo "<div><a href = 'fileinfo.php?file=$serializedFile'>$file->path</a></div>";
          }
          # File info end
          echo "</div>";

          # Project info end
          echo "</div>";
          $i = $i + 1;
        }
        ?>
        <div class="page-header">
          <h1>About Jingjing</h1>
            <p>HAHAHAHAHAHAHHAAH</p>
        </div>
      </div>
      <div class="col-md-1"></div>
    </div>

  </div>



  <script>
      //jquery
  $(document).ready(function(){
    // When clicking each display name, toggle the visibility of the info div
    $(".projectNameDisplay").click(function(event) {
      var infoDivId = event.target.id + "Info";
      console.log(infoDivId);
      $('#' + infoDivId).slideToggle();
    });

    $(".filesDisplay").click(function(event) {
      var infoDivId = event.target.id + "Info";
      console.log(infoDivId);
      $('#' + infoDivId).slideToggle();
    });
  });
  </script>
  <script src="js/bootstrap.min.js"></script>
</body>

</html>
