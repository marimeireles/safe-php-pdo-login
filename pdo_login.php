<?php  
session_start();  
$host = "127.0.0.1";  
$username = "agencia-life";  
$password = "";  
$database = "login";  

#$_SESSION["counter"] indicates if the user typed the login informations correctly or not
if (!isset($_SESSION["counter"])) { $_SESSION["counter"] = 0; }; 

#captcha is validated if the information entered by the user doesn't match the information in the database
$captcha = false; 

  try {  

    $connect = new PDO("mysql:host=$host; dbname=$database", $username, $password);  
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

    if(!empty($_POST["login"])) {  
      if(empty($_POST["nome"]) || empty($_POST["senha"])) {
        $message = '<label>Todos os campos têm que ser preenchidos</label>';  
      }  
      else {
        #Checks if user exists in the database
        $query = "SELECT idusuario,salt,senha FROM usuario WHERE nome = :nome";

        $usuario_statement = $connect->prepare($query);  
        $usuario_statement->execute(  
          array(  
            'nome' => $_POST["nome"],
          )  
        );  

        #If user exists than count > 0 and the program proceeds to check user's password 
        $count = $usuario_statement->rowCount(); 

        #Checks if it's necessary to require captcha from user
        if ($_SESSION["counter"] >= 2) {
          # Verify captcha
          $post_data = http_build_query(
            array(
              'secret' => "6Ldqjz8UAAAAAD5dXQZYCOG3FMAYFUQq60MFdwqk",
              'response' => $_POST['g-recaptcha-response'],
              'remoteip' => $_SERVER['REMOTE_ADDR']
            )
          );
          $opts = array('http' =>
            array(
               'method'  => 'POST',
               'header'  => 'Content-type: application/x-www-form-urlencoded',
               'content' => $post_data
            )
          );
          $context  = stream_context_create($opts);
          $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
          $result = json_decode($response);

          if ($result->success == true) {
              $captcha = true;
          }
          else {
            throw new Exception('A verificação do captcha falhou.', 1);
          }
        }
        else {
          $captcha = true;
        }

        #If the captcha was resolved or was never activated the program proceeds its checks
        if(($count > 0) && ($captcha == true)) {

          $row = $usuario_statement->fetch();
          #Creating hash from the password entered
          $hash = hash_pbkdf2("sha256", $_POST["senha"], $row["salt"], 1000, 60);

          #Checking match between the recently created hash and the hash in the database
          if($hash == $row["senha"]) {
            $_SESSION["usuario_logado"] = $_POST["nome"]; 
            $_SESSION["idusuario"] = $row["idusuario"];
            $_SESSION["nome"] = $_POST["nome"];

            header("location:login_success.php");  
          }  
          else {    

            $message = '<label>As informações fornecidas estão incorretas</label>';
            $_SESSION["counter"]++;  
          }
        }
        else {  
            $message = '<label>As informações fornecidas estão incorretas</label>';
            $_SESSION["counter"]++;  
        }
      }  
    }  
  }  

  catch(PDOException $error) {  
    $message = $error->getMessage();  
  }  
?>  

<!DOCTYPE html>  
<html>  
  <head>  
    <title>PHP Login usando PDO</title>  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous"> 
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>  
    <script src='https://www.google.com/recaptcha/api.js'></script>
  </head>  
  <body>  
    <br />  
    <div class="container" style="width:500px;">  
      <?php  
        if(isset($message)) {  
          echo '<label class="text-danger">'.$message.'</label>';  
        }  
      ?>  
      <h3 align="">PHP Login usando PDO</h3><br/>  
      <form method="post">  
        <label>Nome</label>  
        <input type="text" name="nome" class="form-control"/>  
        <br/>  
        <label>Senha</label>  
        <input type="password" name="senha" class="form-control"/>  
        <br/>  
        <?php if ($_SESSION["counter"] >= 2): ?>
          <div class="g-recaptcha" data-sitekey="6Ldqjz8UAAAAAB-y0dfPdI_q3V9i4D4vbN-hdhUD"></div>
          <br/>
        <?php endif; ?>
        <input type="submit" name="login" class="btn btn-info" value="Login"/>  
      </form>  

    </div>  
    <br/>  
  </body>  
</html>  