<?php

/**
 * Restricted to dean level users, provides interface and back end routines to manage departments, faculty, batches and rooms
 * @author Avin E.M
 */

require_once('functions.php');
if(!sessionCheck('level','dean'))
{
    header("Location: ./login.php");
    die();
}
require_once ('connect_db.php');
?>


<!DOCTYPE html>
<html>
<head>
<style>
body {
    margin: 0;
}

ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 25%;
    background-color: #f1f1f1;
    position: fixed;
    height: 100%;
    overflow: auto;
}

li a {
    display: block;
    color: #000;
    padding: 8px 16px;
    text-decoration: none;
}

li a.active {
    background-color: #4CAF50;
    color: white;
}

li a:hover:not(.active) {
    background-color: #555;
    color: white;
}
</style>
</head>
<body>

<ul>
<li><a class="active" href="main.php">Manage Timetables</a></li>
  <li><a href="home.php?action=department">Manage Departments</a></li>
  <li><a href="home.php?action=faculty">Manage Faculty</a></li>
  <li><a href="home.php?action=batches">Manage Batches</a></li>
  <li><a href="home.php?action=room">Manage Room</a></li>
  <li><a href="home.php?action=courses">Manage Courses</a></li>
</ul>

<div style="margin-left:25%;padding:1px 16px;height:1000px;">
 <?php if(valueCheck('action','department')) : ?>
 <form method="post" action="home.php?action=add">
<input type="text" name="dName" placeholder="Department Name"><br>
<input type="text" name="dept_code" placeholder="Department Code"><br>
<button type="submit">ADD_DEPT</button>
</form>

 <?php elseif(valueCheck('action','faculty')) : ?>
<form method="post" action="faculty.php">
<input type="text" placeholder="Faculty Name" name="fullName"><br>
<input type="text" placeholder="User Name" name="uName"><br>
<select  name="dept" data-placeholder="Choose Department..." required>
            <option label="Choose Department..." disabled selected>Choose any of these...</option>
            <?php
            foreach($db->query('SELECT * FROM depts') as $dept)
              echo "<option value=\"{$dept['dept_code']}\">{$dept['dept_name']} ({$dept['dept_code']})</option>";
            ?>
          </select><br>
<input type="password" placeholder="Choose Password" name="pswd"><br>
<input type="password" placeholder="Confirm Password"><br>
<button type="submit">Register</button>
</form>

<?php elseif(valueCheck('action','batches')) : ?>
<form method="post" action="batches.php?action=add">
<input type="text" placeholder="Batch Name" name="batch_name"><br>
<select  name="dept" data-placeholder="Choose Department..." required>
            <option label="Choose Department..." disabled selected>Choose any of these...</option>
            <?php
            foreach($db->query('SELECT * FROM depts') as $dept)
              echo "<option value=\"{$dept['dept_code']}\">{$dept['dept_name']} ({$dept['dept_code']})</option>";
            ?>
          </select> <br>
<input type="text" placeholder="Batch Size" name="size"><br>
<button type="submit">Add batch</button>
</form>
<?php elseif(valueCheck('action','room')) : ?>
<form method="post" action="rooms.php?action=add">
<input type="text" placeholder="Room Name" name="room_name"><br>
<input type="text" placeholder="Capacity" name="capacity"><br>
<button type="submit">Add batch</button>
</form>


<?php else: ?>
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

  <?php endif; ?>

</div>

</body>
</html>