<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminController extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('AdminModel'); //Load the Model here 
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->library('upload');
	}
	public function index(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['total_users'] = count($this->AdminModel->getAllUsers());
			$data['total_agents'] = count($this->AdminModel->getAllAgents());
			$data['total_individuals'] = count($this->AdminModel->getAllIndividuals());
			$data['total_properties'] = count($this->AdminModel->getAllProperties());
			return $this->load->view('dashboard', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	
	public function login(){
		if(isset($_POST['login-form'])){
			$errors = [];
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
	
	public function allIndividuals(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['individualsList'] = $this->AdminModel->getAllIndividuals();
			return $this->load->view('users/all-individuals', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	public function allAgents(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['agentsList'] = $this->AdminModel->getAllAgents();
			return $this->load->view('users/all-agents', $data);
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
				$errors['field']['email'] = [ EMAIL_ADDRESS.' address should not be empty!'];
			}else{
				//check if user is already exists
				$user_exist = $this->AdminModel->getUserByEmail($_POST['email']);
				
				if($user_exist){
					$errors['error'] = true;
					$errors['already_exists'] = EMAIL_ADDRESS." address already exists.";
				}
			}
			//validate password
			if(!isset($_POST['password']) || empty($_POST['password'])){
				$errors['error'] = true;
				$errors['field']['password'] = [PASSWORD.' should not be empty!'];
			}
			//validate phone
			if(!isset($_POST['phone']) || empty($_POST['phone'])){
				$errors['error'] = true;
				$errors['field']['phone'] = [PHONE.' should not be empty!'];
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
			$image_url = $user->profile_image ? UPLOADS_URL.'/profiles/'. $user->profile_image : base_url('assets/images/user.png') ;
			 echo '<div class="well profile_view">
			 <div class="col-sm-12">
				 <div class="left col-sm-8">
					 <h2>'.$user->name .'</h2>
					 <ul class="list-unstyled">
						 <li><i class="fa fa-user"></i> Role : '.ucfirst($user->role) .'</li>
						 <li><i class="fa fa-phone"></i> '.PHONE.' : '.$user->phone .'</li>
						 <li><i class="fa fa-envelope"></i> '.EMAIL_ADDRESS.' : '.$user->email .'</li>
						 <li><i class="fa fa-building"></i> '.TOTAL_PROPERTIES.' : '.$user->total_properties .'</li>
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

	public function activateUserToggle(){
		if(isset($_POST)){
			$this->AdminModel->activateUserByID($_POST);		
		}
	}	
	public function activatePropertyToggle(){
		if(isset($_POST)){
			$this->AdminModel->activatePropertyByID($_POST);		
		}
	}	

	public function approveproperties(){
		if(isset($_POST)){
			$this->AdminModel->approvePropertiesByID($_POST);		
		}
	}	
	
	public function allProperties(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['propertiesList'] = $this->AdminModel->getAllProperties();			
			$data['types'] = $this->AdminModel->getAllTypes();	
			$data['amenities'] = $this->AdminModel->getAllAmenities();			
			return $this->load->view('properties/all-properties', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	
	public function getProperty(){
		if(isset($_POST)){
			$property = (object)$this->AdminModel->getPropertyByID($_POST['id'])[0];
			$types = $this->AdminModel->getAllTypes();	
			$amenities = $this->AdminModel->getAllAmenities();	
			$typesData = [];
            if(!empty($types)){
                $typeArr = explode(',',$property->type);
               
                foreach ($types as $key => $value) {
                    if(in_array($value->id, $typeArr)){
                        $typesData[] = $value->name;
                    }
                }
            }
            $amenitiesData = [];
            if(!empty($amenities)){
                $amenitiesArr = explode(',',$property->amenities);
               
                foreach ($amenities as $key => $value) {
                    if(in_array($value->id, $amenitiesArr)){
                        $amenitiesData[] = $value->name;
                    }
                }
            }
            $images = json_decode($property->image);
            $image_url = '';
            $list = '';
            $sliders = '';
            if(!empty($images)){

            	foreach ($images as $key => $value) {
            		$sliders .= '<div class="item" style="width: 48%;margin-right: 2%;float: left;min-height: 125px;"><img src="'.$value->url.'"></div>';
            	}
                
            }
            $purpose = '';
            if($property->purpose == 'sale'){
            	$purpose =  "Vendre";
            }elseif($property->purpose == 'rent'){ 
            	$purpose =  "Louer"; 
            }else{ 
            	$purpose = '';
            } 
			
			 echo '<div>
			 <div class="col-md-12">
			 	'.$sliders.'
				 <div class="col-md-12">
					 <h2>'.$property->title .'</h2>
					 <label><b>Description:</b></label>
					 <p>'.$property->description .'</p>
					 <ul class="list-unstyled">
						 <li><b>'.LISTED_BY.' :</b> '.ucfirst($property->listedByName) . ' ('.ucfirst($property->userRole).') </li>
						 <li><b>Purpose :</b> '.$purpose.' </li>

						 <li><b>'.PRICE.' :</b> $'.$property->price .' '.$property->currency.'</li>
						 <li><b>Type :</b> '.implode(', ', $typesData) .'</li>
						 <li><b>'.AMENITIES.' :</b> '.implode(', ', $amenitiesData) .'</li>
					 </ul>
				 </div>
				 
			 </div>
		 </div>';
			
		}
	}
	public function deleteProperty(){
		if(isset($_POST)){
			$response = $this->AdminModel->deletePropertyByID($_POST);		
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
	public function profile(){
		if(isset($_POST['update-profile'])){
			//$image_name = '';
			/*if(isset($_FILES) && !empty($_FILES['profile_image']['tmp_name'])){
				$image_name = uniqid() . '.png';
				$this->do_upload('profile_image');
			}*/
			
			unset($_POST['update-profile']);
			//$_POST['profile_image'] = $image_name;
			$user = $this->AdminModel->getAdminbyEmail($_POST['email']);
			/*if($image_name !== ''){
				unlink(UPLOADS_URL.$user[0]['profile_image']);
			}*/
			$update = $this->AdminModel->updateUser($_POST);
			if($user){
				$this->session->set_userdata('userdata',$user);
				redirect(base_url('profile?success=true'));
			}

		}else{
			if($this->session->userdata('userdata')){
				$data['loggedinuser'] = $this->session->userdata('userdata');	
				return $this->load->view('users/profile', $data);
			}else{
				redirect(base_url());
			}
		}
	}
	public function do_upload($file){

        $config['upload_path']          = UPLOADS_URL;
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = 10000;

        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        if ( ! $this->upload->do_upload($file)){
            return array( 'status'=> 0, 'error' => $this->upload->display_errors());
        }else{
         	return array('status'=> 1, 'upload_data' => $this->upload->data());    
        }
    }
	public function amenities(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['amenities'] = $this->AdminModel->getAllAmenities();			
			return $this->load->view('properties/all-amenities', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	public function types(){
		if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['types'] = $this->AdminModel->getAllTypes();			
			return $this->load->view('properties/all-types', $data);
		}else{
			return $this->load->view('login/login-form');
		}
	}
	public function addType(){
		if(isset($_POST) && !empty($_POST)){
			unset($_POST['submit']);
			$this->AdminModel->insertType($_POST);
			redirect(base_url('properties/types?success=true'));
		}
	}
	public function addAmenities(){
		if(isset($_POST) && !empty($_POST)){
			unset($_POST['submit']);
			$this->AdminModel->insertAmenities($_POST);
			redirect(base_url('properties/amenities?success=true'));
		}
	}
	public function deletetype($id){

		if($id){
			$this->AdminModel->deletetypes($id);
			redirect(base_url('properties/types?delete=true'));
		}
	}
	public function deleteamenity($id){
		if($id){
			$this->AdminModel->deleteamenities($id);
			redirect(base_url('properties/amenities?delete=true'));

		}
	}
	public function updatetype(){
		if(isset($_POST)){
			$this->AdminModel->updateType($_POST);
			echo base_url('properties/types?update=true');
		}
	}
	public function updateamenity(){
		if(isset($_POST)){
			$this->AdminModel->updateAmenities($_POST);
			echo base_url('properties/amenities?update=true');
		}
	}
	public function contactinfo(){
		if(isset($_POST['update-contactinfo'])){
		
			
			unset($_POST['update-contactinfo']);
			$this->AdminModel->UpdateYedContactInfo($_POST, $_POST['id']);
			redirect(base_url('contactinfo?success=true'));

		}else{
			if($this->session->userdata('userdata')){
			$data['loggedinuser'] = $this->session->userdata('userdata');
			$data['contactInfo'] = $this->AdminModel->getYedContactInfo();			
					
				return $this->load->view('contact-info', $data);
			}else{
				return $this->load->view('login/login-form');
			}
		}
		
	}
	public function updateUserSession(){
		$email = $_POST['email'];
		$user = $this->AdminModel->getUserByEmail($email);
		$this->session->set_userdata('userdata',$user);
		echo base_url('profile?success=true');
	}


}
?>