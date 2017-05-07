<?php
   session_start();
   unset($_SESSION["username"]);
   unset($_SESSION["password"]);
   
   echo 'You have cleaned session';
  // header('Refresh: 2; URL = login.php');
?>
<?php
$servername = "localhost";
$username = "root";
$password = "123";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "<br>Connected successfully";
?>
<html>
<script>
function myFunction() {
    var x;
    var text = "Input OK...lets proceed.........";

    //Get the value of input field with id="numb"
    x = document.getElementById("numb").value;

    // If x is Not a Number, or x is less than one, or x is grather than 10 then
    if (isNaN(x) || x < 1 || x > 10) {
        text = "Not valid...retry";
        window.location='4.html';
	}
    document.getElementById("demo").innerHTML = text;
	window.location='home.html';
}
</script>
<body>
<p>LEts learn how we are gonna do this!!</p>
<input type="text" id="numb">
<button type="submit" onclick="myFunction()">Next PAge</button>
<p id="demo"></p>

</body>
</html>