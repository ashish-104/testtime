<?php
require_once('functions.php');
require_once('connect_db.php');
?>
<html>
<head>
<title>Manage Courses
</title>
</head>
<body>
<form method="post" action="courses.php?action=add">
<select name="fac_id" id="faculty"  data-placeholder="Choose Faculty...">
<option label="Choose Department..." disabled selected>Choose any of these...</option>
          <?php
            $query = $db->prepare('SELECT * FROM faculty where dept_code=?');
            $query->execute([$_SESSION['dept']]);
            foreach($query->fetchall() as $fac)
              echo "<option value=\"{$fac['uName']}\">{$fac['fac_name']} ({$fac['uName']})</option>";
          ?>
        </select><br>	
<input type="text" placeholder="Course ID" name="cId"><br>
<input type="text" placeholder="Course Name" name="cName"><br>
<select  name="batch[]" data-placeholder="Choose Department..." required>
            <option label="Choose Department..." disabled selected>Choose any of these...</option>
            <?php
            foreach($db->query('SELECT * FROM batches') as $batch)
             echo "<option value=\"{$batch['batch_name']} : {$batch['batch_dept']}\">{$batch['batch_name']} : {$batch['batch_dept']} ({$batch['size']})</option>";
                ?>
          </select> <br>
		  
		  <input type="checkbox" class="styled" id="allowConflict" value="1" name="allowConflict">
                <label for="allowConflict">Allow conflicting allocations</label><br>
<button type="submit">Add Course</button>
</form>

</body>
</html>



<?php
require_once('functions.php');
if(!sessionCheck('logged_in'))
  postResponse("error","Your session has expired, please login again");
require_once('connect_db.php');


rangeCheck('cId',2,20);
$cId = strtoupper($_POST['cId']);
if(!isset($_SESSION['faculty']))
{
  $_SESSION['faculty'] = $_SESSION['uName'];
 echo "This is executed";
  }
  if(!sessionCheck('level','faculty') && !empty($_GET['faculty']))
  $_SESSION['faculty'] = $_GET['faculty'];
if(valueCheck('action','add'))
{
  rangeCheck('cName',6,100);
  if(empty($_POST["allowConflict"]))
    $_POST["allowConflict"] = 0;
  try
  {
    $query = $db->prepare('INSERT INTO courses(course_Id,course_name,fac_id,allow_conflict) values (?,?,?,?)');
    $query->execute([$cId,$_POST['cName'],$_POST['fac_id'],$_POST["allowConflict"]]);
    $query = $db->prepare('INSERT INTO allowed(course_Id,batch_name,batch_dept) values (?,?,?)');
    foreach ($_POST['batch'] as $batch) 
    {
      $batch = explode(" : ",$batch);
      $query->execute([$cId,$batch[0],$batch[1]]);      
    }
    postResponse("addOpt","Course Added",[$_POST['cName'],$cId]);  
  }
  catch(PDOException $e)
  {
    if($e->errorInfo[0]==23000)
      postResponse("error","Course ID already exists");
    else
      postResponse("error",$e->errorInfo[2]);
  }
}
elseif(valueCheck('action','delete'))
{
  $query = $db->prepare('DELETE FROM courses where course_id =? and fac_id =?');
  $query->execute([$_POST['cId'],$_SESSION['faculty']]);
  postResponse("removeOpt","Course deleted");
}

?>
