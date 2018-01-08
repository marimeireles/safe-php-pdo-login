 <?php   
session_start();  
$host = "127.0.0.1";  
$username = "agencia-life";  
$password = "";  
$database = "login";

	$connect = new PDO("mysql:host=$host; dbname=$database", $username, $password);  
	$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

	#Updating active status into the user table to non active
	$active = "UPDATE usuario SET ativo = '0' WHERE idusuario = :idusuario";
	$active_statement = $connect->prepare($active);  
	$active_statement->execute(  
		array(
		'idusuario' => $_SESSION["idusuario"]
		)
	);

session_destroy();  
header("location:pdo_login.php");  
 ?>  