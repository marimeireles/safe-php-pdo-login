 <?php  
session_start();
$host = "127.0.0.1";  
$username = "agencia-life";  
$password = "";  
$database = "login";

date_default_timezone_set('America/Sao_Paulo');   

if(isset($_SESSION["usuario_logado"])) {  

	$connect = new PDO("mysql:host=$host; dbname=$database", $username, $password);  
	$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

	#Setting date
	$date = date('Y-m-d G:i:s');

	#Inserting current access into the history table
	$history = "INSERT INTO log_acesso(data,idusuario,nome) VALUES (:data,:idusuario,:nome)";
	$history_statement = $connect->prepare($history);  
	$history_statement->execute(  
	  array(  
	  	'data' => $date,
	    'idusuario' => $_SESSION["idusuario"],
	    'nome'=> $_SESSION["nome"]  
	  )  
	); 

	#Updating active status into the user table to active
	$active = "UPDATE usuario SET ativo = '1' WHERE idusuario = :idusuario";
	$active_statement = $connect->prepare($active);  
	$active_statement->execute(  
		array(
		'idusuario' => $_SESSION["idusuario"]
		)
	);
	
	#Needed to print history table
	$copy = "SELECT data,idusuario,nome FROM log_acesso";
	$history_copy = $connect->prepare($copy);  
	$history_copy->execute();
 
}  
else {  
  header("location:pdo_login.php");  
}  

 ?> 	

 <!DOCTYPE html>
 <html>
  <head>  
    <title>PHP Login usando PDO</title>  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />  
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>  
  </head>  
 <body>
 	<div class="container"> 
 		<?php
 			echo '<h3>Login bem sucedido, Bem vinda(o), '.$_SESSION["usuario_logado"].'</h3>';  
 			echo '
 			<br/><br/>
		  <table class="table">
		    <thead>
		    <tr>
		    	<th scope="col">#</th>
		      <th scope="col">Data</th>
		      <th scope="col">ID</th>
		      <th scope="col">Usuário</th>
		    </tr>
		    </thead>
		    <tbody>';
		    	$i = 0;
		    	while ($row = $history_copy->fetch(PDO::FETCH_ASSOC)) {
						$id = $row['idusuario'];
						$name = $row['nome'];
						$date = $row['data'];
						$i++;
					echo '<tr>';
		      echo '<th scope="row">'.$i.'</th>';
		      echo '<td>'.$date.'</td>';
		      echo '<td>'.$id.'</td>';
		      echo '<td>'.$name.'</td>';
		      echo '</tr>';
		    	}
		    	echo '
		  </tbody>
		</table> ';
		echo '<br/><br/><a class="btn btn-info" href="logout.php">Logout</a>
		<br/><br/>'; 
 		?>

 	</div>
 </body>
 </html>