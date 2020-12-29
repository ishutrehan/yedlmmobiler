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
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }
		$errors = [];
		if(isset($_POST) && !empty($_POST)){
			//validate role
			if(!isset($_POST['role']) || empty($_POST['role'])){
				$errors['error'] = true;
				$errors['message'] = ['Role should not be empty!'];
			}
			//validate name
			if(!isset($_POST['name']) || empty($_POST['name'])){
				$errors['error'] = true;
				$errors['message'] = ['Name should not be empty!'];
			}
			//validate email address
			if(!isset($_POST['email']) || empty($_POST['email'])){
				$errors['error'] = true;
				$errors['field']['email'] = ['Email address should not be empty!'];
			}else{
				//check if user is already exists
				$user_exist = $this->UserModel->getUserByEmail($_POST['email']);
				
				if($user_exist){
					$errors['error'] = true;
					$errors['already_exists'] = "Email address already exists.";
				}
			}
			//validate password
			if(!isset($_POST['password']) || empty($_POST['password'])){
				$errors['error'] = true;
				$errors['field']['password'] = ['Password should not be empty!'];
			}
			//validate phone
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
		}else{
            echo json_encode(['error' => true, 'message' => 'Please fill the required fields!']);
        }
	}
	//user login
	public function login()
	{
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }
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
				}else{
					echo json_encode(['error' => true, 'message'=> 'Email or password is wrong. Try Again!']);
				}
			}else{
				echo json_encode($errors);
			}
		}else{
            echo json_encode(['error' => true, 'message' => 'Please fill the required fields!']);
        }
	}
	//update user profile
	public function update($user_id)
	{
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
		}
		
		if(!$user_id){
			echo json_encode(['error' => true, 'message' => 'Please provide user id as a parameter!']);
			return false;
		}
		$errors = [];
		if(isset($_POST) && !empty($_POST)){
			$this->UserModel->update($_POST, $user_id);
		}else{
            echo json_encode(['error' => true, 'message' => 'Nothing to update!']);
        }
	}
	//change password stuff
	public function changepassword($user_id)
	{
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
		}
		
		if(!$user_id){
			echo json_encode(['error' => true, 'message' => 'Please provide user id as a parameter!']);
			return false;
		}
		$errors = [];
		if(isset($_POST) && !empty($_POST)){
			$this->UserModel->update($_POST, $user_id);
		}else{
            echo json_encode(['error' => true, 'message' => 'Nothing to update!']);
        }
	}
}


?>