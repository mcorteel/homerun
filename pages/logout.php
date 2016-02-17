<p>Logging out...</p>
<?php
unset($_SESSION['authUser']);
setcookie("login", "", 1);
setcookie("password", "", 1);
header("Location:home.html");
?>