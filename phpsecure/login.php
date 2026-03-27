<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data=json_decode(file_get_contents("php://input"),true);
$email=trim($data['email'] ?? '');
$password=$data['password'] ?? '';
$role=strtolower(trim($data['role'] ?? 'student'));

// Validation
if(!$email||!$password){ echo json_encode(['success'=>false,'message'=>'Fill all fields']); exit;}
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ echo json_encode(['success'=>false,'message'=>'Invalid email']); exit;}
$allowed_roles=['student','company','admin'];
if(!in_array($role,$allowed_roles)){ echo json_encode(['success'=>false,'message'=>'Invalid role']); exit;}

// Fetch user
$stmt=$pdo->prepare("SELECT id,email,password,role,first_name FROM users WHERE email=? LIMIT 1");
$stmt->execute([$email]);
$user=$stmt->fetch();
if(!$user||$user['role']!=$role){ echo json_encode(['success'=>false,'message'=>'Incorrect credentials']); exit;}
if(!password_verify($password,$user['password'])){ echo json_encode(['success'=>false,'message'=>'Incorrect credentials']); exit;}

// Store session
session_regenerate_id(true);
$_SESSION['user_id']=$user['id'];
$_SESSION['role']=$user['role'];
$_SESSION['first_name']=$user['first_name'];

echo json_encode(['success'=>true,'message'=>'Login successful']);
?>
