# PHP login seguro
Algumas escolhas foram tomadas fora da métrica especificada pelo desafio, por causa de  escolhas pessoais como uso de criptografia e captcha. Explico minha escolha de implementação em detalhes nesse arquivo.

## Configurando o ambiente
Logue no mysql como root
`mysql -u root -p`
Dentro do mysql insira um novo usuário e garanta privilégios a ele como indicado
```
GRANT ALL PRIVILEGES ON login.* TO "agencia-life"@"localhost" IDENTIFIED BY "";
FLUSH PRIVILEGES;
EXIT;
```
No diretório do programa você deve rodar o seguinte comando no shell
`mysqldump -u agencia-life -p login < login.sql` 

Além disso para que o programa funcione é necessário ter PHP 5 ou superior e as seguintes bibliotecas:
* PDO
* date

Para rodar o código localmente é necessário usar o endereço
`http://127.0.0.1/login-screen/pdo_login.php`
Note que o 127.0.0.1 é necessário em detrimento do usual localhost, isso acontece porque a API do captcha exige.

Esse código foi desenvolvido e testado usando PHP 7.2.0, MariaDB 10.2.12 e Apache 2.4.29 no SO Arch Linux.

## Escolhas de implementação

### Criptografia
Segurança é essencial para um site, por isso escolhi a função [hash_pbkdf2](http://php.net/manual/en/function.hash-pbkdf2.php) do PHP. O motivo principal pelo qual a escolhi é que, ao contrário da maior parte das funções que encontrei disponíveis, para ser quebrada por meio de força é necessário que se invista no processamento de memória e, atualmente, é mais fácil e barato obter poder de processamento de CPUs do que de memória, tornando assim, o site menos aprazível para ataques que usam força bruta. 
Outro motivo relevante é que a implementação da função é simples, utilizando apenas de uma variável a mais, o salt.
O salt é uma string gerada aleatoriamente que permite que a função criptografe senhas iguais em chaves diferentes. Essencialmente se existir no banco de dados mais de um usuário com a mesma senha e um ataque por força conseguir descobrir uma senha de um usuário, ele não conseguirá descobrir dos demais usuários, porque o salt gerará um hash diferente para cada usuário.
Além disso, a função usa o sha256 para criptografar a senha que é amplamente utlizado por ser reconhecido pela sua confiabilidade.
Mesmo tendo que adicionar mais um dado à tabela SQL (salt) acredito que foi um preço aceitável a se pagar.

### Captcha
Ainda buscando diminuir a efetividade de ataques brutos implementei o [reCaptcha do Google](https://developers.google.com/recaptcha/intro).
Escolhi essa ferramenta por ser fácil de implementar e segura. Se o usuário erra a senha ou o nome de usuário por duas vezes ou mais será necessário inserir o captcha.

## Dados adicionais

Para fins de teste as senhas dos usuários padrões se encontram na tabela, assim como outras informações que podem ser de interesse

| Usuário       | Senha         | Senha criptografada | Salt |
| ------------- | ------------- | ------------- | ------------- |
| mariana    | rainbowdrops  | 71d4ea1835f57024dd1906150666082dd1db607ede94fc396e271e0dec6e~ | ban´ana98|
| bananinha  | rainbowdrops  | 4c2d34799133484804d1dbb274e921c6aed693570d6999af2ea01b61bb39~ | 2387*RTDFSATD|
| reuben  | carvao  | edbd00b20507ec0c3717dad16e75a5b23fad2469fd6e0c45a9e5e08be0af | udshs87G= |


Caso não tenha sido possível adicionar as tabelas pelo método descrito acima insira o que se segue enquanto logado como `agencia-life`
```
CREATE TABLE usuario (
  idusuario int(11) NOT NULL AUTO_INCREMENT,
  nome varchar(60) NOT NULL,
  senha varchar(60) NOT NULL,
  salt varchar(60) NOT NULL,
  ativo char(1) NOT NULL,
  PRIMARY KEY(idusuario)
);

INSERT INTO usuario (nome, senha, salt)
VALUES
("reuben", "edbd00b20507ec0c3717dad16e75a5b23fad2469fd6e0c45a9e5e08be0af", "udshs87G=");

INSERT INTO usuario (nome, senha, salt)
VALUES
("mariana", "71d4ea1835f57024dd1906150666082dd1db607ede94fc396e271e0dec6e~", "ban´ana98");

INSERT INTO usuario (nome, senha, salt)
VALUES
("bananinha", "4c2d34799133484804d1dbb274e921c6aed693570d6999af2ea01b61bb39~", "2387*RTDFSATD");

CREATE TABLE log_acesso (
  idhistorico int(11) NOT NULL AUTO_INCREMENT,
  data DATETIME NOT NULL,
  idusuario int(11) NOT NULL,
  nome varchar(60) NOT NULL,
  PRIMARY KEY(idhistorico)
);
```





