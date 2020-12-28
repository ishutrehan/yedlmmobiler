<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Content-Type: application/json');
class User extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('UserModel'); //Load the Model here 
	}
	//user register
	public function register()
	{
		$errors = [];
		if(isset($_POST) && !empty($_POST)){
			if(!isset($_POST['role']) || empty($_POST['role'])){
				$errors['error'] = true;
				$errors['message'] = ['Role should not be empty!'];
			}
			if(!isset($_POST['name']) || empty($_POST['name'])){
				$errors['error'] = true;
				$errors['message'] = ['Name should not be empty!'];
			}
			if(!isset($_POST['email']) || empty($_POST['email'])){
				$errors['error'] = true;
				$errors['field']['email'] = ['Email address should not be empty!'];
			}else{
				$user_exist = $this->UserModel->getUserByEmail($_POST['email']);
				
				if($user_exist){
					$errors['error'] = true;
					$errors['already_exists'] = "Email address already exists.";
				}
			}
			if(!isset($_POST['password']) || empty($_POST['password'])){
				$errors['error'] = true;
				$errors['field']['password'] = ['Password should not be empty!'];
			}
			if(!isset($_POST['phone']) || empty($_POST['phone'])){
				$errors['error'] = true;
				$errors['field']['phone'] = ['Phone should not be empty!'];
			}
			
			if(empty($errors)){
				$_POST['password'] = md5($_POST['password']);
				$this->UserModel->insert($_POST);
			}else{
				echo json_encode($errors);
			}
		}
	}
	//user login
	public function login()
	{
		$errors = [];
		if(isset($_POST) && !empty($_POST)){
			
			if(!isset($_POST['email']) || empty($_POST['email'])){
				$errors['error'] = true;
				$errors['field']['email'] = ['Email address should not be empty!'];
			}
			if(!isset($_POST['password']) || empty($_POST['password'])){
				$errors['error'] = true;
				$errors['field']['password'] = ['Password should not be empty!'];
			}
			
			if(empty($errors)){
				//check for user by email and password if exists then login
				$user = $this->UserModel->getUserByEmailandPassword($_POST['email'], md5($_POST['password']));
				if($user){
					echo json_encode($user[0]);
				}
			}else{
				echo json_encode($errors);
			}
		}else{
			echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
		}
	}
}
