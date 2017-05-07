
<?php
require_once('functions.php');
if(!sessionCheck('level','dean'))
{
  header("Location: ./login.php");
  die();
}
require_once('connect_db.php');


if($_POST)
{
    if(valueCheck('action','setSlots'))
    {
        if(empty($_POST["allowConflicts"]))
          $_POST["allowConflicts"] = 0;
        if(empty($_POST["active"]))
          $_POST["active"] = 0;
        if(empty($_POST["frozen"]))
          $_POST["frozen"] = 0;
        if($_POST["days"]<0 || $_POST["days"] > 7)
          postResponse("error", "Number of days cannot be more than 7");
        if(!$current['table_name'])
          postResponse("error", "Please select a timetable");
        $query = $db->prepare('UPDATE timetables SET
            days=?,
            slots=?,
            duration=?,
            start_hr=?,
            start_min=?,
            start_mer=?,
            allowConflicts=?,
            frozen = ?
            WHERE table_name=?');
        try {
            $query->execute([
              $_POST['days'],
              $_POST['slots'],
              $_POST['duration'],
              $_POST['start_hr'],
              $_POST['start_min'],
              $_POST['start_mer'],
              $_POST['allowConflicts'],
              $_POST['frozen'],
              $current['table_name']
            ]);
        }
        catch(PDOException $e)
        {
          postResponse("error", json_encode($e->errorInfo));
        }
        if($_POST['active'])
        {
          $query = $db->query('UPDATE timetables SET active=0 where active=1');
          $query = $db->prepare('UPDATE timetables SET active=1 where table_name=?');
          $query->execute([$current['table_name']]);
        }
        if($current["days"]<$_POST["days"])
        {
          $query = $db->prepare('INSERT INTO slots VALUES (?,?,?,?)');  
          for($d=$current["days"]+1;$d<=$_POST["days"];$d++)
            for($s=1;$s<=$current["slots"];$s++)
                $query->execute([$current['table_name'],$d,$s,'active']);
        }
        else
        {
          $query = $db->prepare('DELETE FROM slots WHERE day > ? AND table_name = ?');
          $query->execute([$_POST["days"],$current['table_name']]);
        }
        if($current["slots"]<=$_POST["slots"])
        {
          $query = $db->prepare('INSERT INTO slots VALUES (?,?,?,?)');
          for($d=1;$d<=$_POST["days"];$d++)
              for($s=$current["slots"]+1;$s<=$_POST["slots"];$s++)
                    $query->execute([$current['table_name'],$d,$s,'active']);
        }
        else
        {
          $query = $db->prepare('DELETE FROM slots WHERE slot_num > ? AND table_name = ?');
          $query->execute([$_POST["slots"],$current['table_name']]);
        }
        postResponse("updateGrid",'Timetable saved');
        die();
    }
 /* if(valueCheck('action','updateSlots'))
  {
    $query = $db->prepare('UPDATE slots SET state= ? WHERE day = ? AND slot_num = ? AND table_name = ?');
    $deleteAllocs = $db->prepare('DELETE FROM slot_allocs WHERE day = ? AND slot_num = ? AND table_name = ?');
    foreach ($_POST as $slotStr => $state)
    {
      $slot = explode('_', $slotStr);
      $query->execute([$state,$slot[0],$slot[1],$current['table_name']]);
      if($state=='disabled')
        $deleteAllocs->execute([$slot[0],$slot[1],$current['table_name']]);
    }
    postResponse("info",'Slots updated');
    die();
  }*/
  /*if(valueCheck('action','deleteTimetable'))
  {

    $query = $db->prepare('DELETE from timetables where table_name=? AND active=0');
    $query->execute([$_POST['table_name']]);
    if($query->rowCount())
    {
      postResponse("removeOpt",'Timetable deleted');
      die();
    }
    else
      postResponse("error",'Slot is the current active slot, choose another slot as active before deleting');
  }*/
}
?>
<html>
<head>
<title>Manage Timetables
</title>

  <link rel="stylesheet" type="text/css" href="css/styles.css">
  <link rel="stylesheet" type="text/css" href="css/dashboard.css">
  <link rel="stylesheet" type="text/css" href="css/table.css">
  <link rel="stylesheet" type="text/css" href="css/chosen.css">
  <script type="text/javascript"  src="js/jquery.min.js" ></script>
  <script type="text/javascript" src="js/chosen.js"></script>
  <script type="text/javascript" src="js/grid.js"></script>
  <script type="text/javascript">
  $(function()
  {
      $("#main_menu a").each(function() {
          if($(this).prop('href') == window.location.href || window.location.href.search($(this).prop('href'))>-1)  
          {
              $(this).parent().addClass('current');
              document.title+= " | " + this.innerHTML;
              $(this).click(function(){return false;})
              return false;
          }
      })
      $("option[value='<?=$current['table_name']?>']","#table_name").attr('selected','selected');
      $("#table_name").chosen({
        no_results_text: 'No timetable named ',
        create_option : function(opt){
          this.append_option({
            value: opt,
            text: opt
          });
        },
        create_option_text: 'Add timetable ',
        persistent_create_option: true
      }).change(function(){
        window.location.href='dean.php?table='+this.value;
      })
      $("#delete_table").prop("selectedIndex",-1).chosen({ no_results_text: 'No timetable named '});
      $("#start_hr").val("<?=$current['start_hr']?>");
      $("#start_min").val("<?=$current['start_min']?>");
      $("#start_mer").val("<?=$current['start_mer']?>");
      $("select","#table_conf").chosen({no_results_text: "Invalid Time"});
      <?php 
        echo "drawGrid('{$current['table_name']}');\n";
      ?>
      $("#timetable").on("click", ".cell.blue", function()
      {
        changes = true;
        $(this).removeClass('blue').addClass('disabled');
        if(!$("input[name="+ this.id +"]")[0])
            $("#disabledSlots").append($('<input type="hidden" name="' + this.id + '" value="active">'));
        $("input[name="+ this.id +"]").val('disabled');
      })
      $("#timetable").on("click", ".cell.disabled", function()
      {
        changes = true;
        $(this).removeClass('disabled').addClass('blue');
        $("input[name="+ this.id +"]").val('active');
      })
      $("#snapshot").change(function(){
        $("#filename").val(this.value);
      })
    <?php if(valueCheck('status','restoreComplete')): ?>
      var msg=$('<div class="blocktext info" style="display:none;margin-top:10px;"><b>&#10004; </b>&nbsp;Database restored, please logout and login again.</div>');
      $("#content").prepend(msg);
      msg.show(400,function(){
        setTimeout(function(){
          msg.hide(400);
        },5000)
      })
    <?php endif; ?>
    var changes = false;
    window.onbeforeunload = function(e) {
      message = "There are unsaved changes in the timetable, are you sure you want to navigate away without saving them?.";
      if(changes)
      {
        e.returnValue = message;
        return message;
      }
    }
  })
  </script>
</head>

<body>
<select id="table_name" name="table_name" class="updateSelect" style="width: 170px" data-placeholder="Add a timetable...">
          <?php
            foreach($db->query('SELECT * FROM timetables') as $timetable)
            {
              $active = $timetable['active']?' (active)':'';
              echo "<option value=\"{$timetable['table_name']}\">{$timetable['table_name']}{$active}</option>";
            }
          ?>
        </select>
		<?php if(!sessionCheck('level','faculty')) : ?>
      <div class="title">Faculty</div>
      <select id="faculty" class="stretch">
        <?php
          $query = $db->prepare('SELECT * FROM faculty where dept_code=?');
          $query->execute([$_SESSION['dept']]);
          foreach($query->fetchall() as $fac)
            echo "<option value=\"{$fac['uName']}\">{$fac['fac_name']} ({$fac['uName']})</option>"
        ?>
      </select>
		
        <form method="post" action="main.php?action=setSlots" id="table_conf">

		<label for="numSlots">Start Time: </label>
            <select id="start_hr" name="start_hr" style="width:60px">
              <option value="01">01</option>
              <option value="02">02</option>
              <option value="03">03</option>
              <option value="04">04</option>
              <option value="05">05</option>
              <option value="06">06</option>
              <option value="07">07</option>
              <option value="08" selected>08</option>
              <option value="09">09</option>
              <option value="10">10</option>
              <option value="11">11</option>
              <option value="12">12</option>
            </select>
            <select id="start_min" name="start_min" style="width:60px">
              <option value="00">00</option>
              <option value="15">15</option>
              <option value="30" selected>30</option>
              <option value="45">45</option>
            </select>
            <select id="start_mer" name="start_mer" style="width:60px">
              <option value="AM" selected>AM</option>
              <option value="PM">PM</option>
            </select><br>
			<label for="numSlots">Number of Slots: </label><input type="text" name="slots" id="numSlots" class="short inline" required pattern="[0-9]{1,2}" value="<?=$current["slots"]?>" title="Number" /><br>
            <label for="numSlots">Number of Days: </label><input type="text" name="days" id="numDays" class="short inline" required pattern="[0-7]{1,2}" value="<?=$current["days"]?>" title="Number: 0-7" /><br>
            <label for="duration">Duration: </label><input type="text" name="duration" id="duration" class="short inline" required pattern="[0-9]{2,}" value="<?=$current["duration"]?>" title="Number >= 10"/>
            <label for="duration">mins</label><br>
             <input type="checkbox" class="styled" name="allowConflicts" value="1" id="allowConflicts" <?=($current["allowConflicts"]=="1")?"checked":""?>>
            <label for="allowConflicts">Allow conflicting allocations</label>
			<button type="submit">Save Timetable</button>
			      <div id="timetable" class="table"></div>

		
		</form>		
		
	  
</body> 

</html>


