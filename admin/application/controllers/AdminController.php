<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminController extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('AdminModel'); //Load the Model here 
		$this->load->library('session');
		$this->load->helper('url');
	}
	public function index(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['total_users'] = count($this->AdminModel->getAllUsers());
			$data['total_properties'] = count($this->AdminModel->getAllProperties());
			return $this->load->view('dashboard', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	public function login(){
		if(isset($_POST['login-form'])){
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
				$user = $this->AdminModel->getAdmin($_POST['email'], md5($_POST['password']));
				if($user){
					$this->session->set_userdata('userdata',$user);
					redirect(base_url());
				}else{
					$data['error'] = 'The information you entered doesn\'t match our records. Please try again.';
					return $this->load->view('login/login-form', $data);
				}
			}
		}else{
			return $this->load->view('login/login-form');
		}
	}
	public function logout(){
		$this->session->unset_userdata('userdata');
		if(!$this->session->userdata('userdata')){
			redirect(base_url());
		}
	}
	
	public function allUsers(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['usersList'] = $this->AdminModel->getAllUsers();
			return $this->load->view('users/all-users', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	
	//add new user
	public function addUser(){
		$errors = [];
		
		if(isset($_POST['add-user-form'])){
			
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
				$user_exist = $this->AdminModel->getUserByEmail($_POST['email']);
				
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
			$profile_image = '';
			if(isset($_FILES['profile_image'])){
				$target_dir = "./assets/uploads/profiles/";
				$target_file = $target_dir .time().'_'. basename($_FILES["profile_image"]["name"]);
				if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
					$profile_image = time().'_'. basename($_FILES["profile_image"]["name"]);
				}
			}
			$_POST['profile_image'] = $profile_image;
			
			if(empty($errors)){
				$_POST['password'] = md5($_POST['password']);
				$_POST['status'] = 'approved';				
				unset($_POST['add-user-form']);
				$addUser = $this->AdminModel->addNewUser($_POST);
				redirect(base_url().'add-user?success=true');
			}else{
				$data['errors'] = $errors;
				$this->load->view('users/add-new-user', $data);
			}
		}else{
			if($this->session->userdata('userdata')){
				$data['loggedinuser'] = $this->session->userdata('userdata');				
				$this->load->view('users/add-new-user', $data);
			}else{
				return $this->load->view('login/login-form');
			}
		}
	}
	public function getUser(){
		if(isset($_POST)){
			$user = (object)$this->AdminModel->getUserByID($_POST['id'])[0];		
			$image_url = $user->profile_image ? base_url('assets/uploads/profiles/'. $user->profile_image) : base_url('assets/images/user.png') ;
			 echo '<div class="well profile_view">
			 <div class="col-sm-12">
				 <div class="left col-sm-8">
					 <h2>'.$user->name .'</h2>
					 <ul class="list-unstyled">
						 <li><i class="fa fa-user"></i> Role : '.ucfirst($user->role) .'</li>
						 <li><i class="fa fa-phone"></i> Phone : '.$user->phone .'</li>
						 <li><i class="fa fa-envelope"></i> Email : '.$user->email .'</li>
						 <li><i class="fa fa-building"></i> Total Properties : '.$user->total_properties .'</li>
					 </ul>
				 </div>
				 <div class="right col-sm-4 text-center">
					 <img src="'.$image_url.'" alt="" class="img-circle img-fluid">
				 </div>
			 </div>
		 </div>';
			
		}
	}
	public function deleteUser(){
		if(isset($_POST)){
			$this->AdminModel->deleteUserByID($_POST['id'])[0];
		
		}
	}	
	public function approveUser(){
		if(isset($_POST)){
			$this->AdminModel->approveUserByID($_POST);		
		}
	}	
	
	public function allProperties(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['propertiesList'] = $this->AdminModel->getAllProperties();			
			return $this->load->view('properties/all-properties', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	
	public function getProperty(){
		if(isset($_POST)){
			$property = (object)$this->AdminModel->getPropertyByID($_POST['id'])[0];
		
			$image_url = $property->image ? base_url('assets/uploads/properties/'. $property->image) : base_url('assets/images/user.png') ;
			 echo '<div>
			 <div class="col-md-12">
				 <div class="left col-md-8">
					 <h2>'.$property->title .'</h2>
					 <label><b>Description:</b></label>
					 <p>'.$property->description .'</p>
					 <ul class="list-unstyled">
						 <li><b>Listed By :</b> '.ucfirst($property->listedByName) . ' ('.ucfirst($property->userRole).') </li>
						 <li><b>Price :</b> $'.$property->price .'</li>
						 <li><b>Type :</b> '.ucfirst($property->type) .'</li>
					 </ul>
				 </div>
				 <div class="right col-md-4 text-center">
					 <img src="'.$image_url.'" alt="" class="img-fluid">
				 </div>
			 </div>
		 </div>';
			
		}
	}
	public function deleteProperty(){
		if(isset($_POST)){
			$this->AdminModel->deletePropertyByID($_POST['id'])[0];		
		}
	}
	public function getNotifications(){
		$notifications = $this->AdminModel->getnotifications();	
		echo json_encode(["totalResults"=> count($notifications), "notifications" => $notifications]);
	}
	public function allNotifications(){
		$data['notifications'] = $this->AdminModel->getallnotifications();	
		return $this->load->view('notifications', $data);
	}
}
?>