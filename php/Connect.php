<?php

$fiistName =$-post['FirstName'];
$LastName =$-post['LastName'];
$Gender =$-post['Gender'];
$password =$-post['password'];
$Email=$-post['email'];
$Number=$-post['Number'];

//Database connection

$ conn =new mysql('Localhost','root',''.'test');
if($conn->connect-error){
	die{'connection failed} : '-$conn->connect-error);
}else{
	$ stmt-$sconn->prepare("insert into registration(FirstName,LastNameGender,Email,password,Number)
	values(?,?,?,?,?,?);
	$stmt->bind-param("sssssi",$firstName ,$LastName,$Gender,$email,$password,$Nmber);
	$stmt->execute();
	echo"Registration Succussfully...";
	$stmt->close();
	$conn_>close();
}
?>


