<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
require_once APPPATH.'vendor/jwt/JWT.php';
require_once APPPATH.'vendor/jwt/SignatureInvalidException.php';
require_once APPPATH.'vendor/jwt/BeforeValidException.php';
require_once APPPATH.'vendor/jwt/ExpiredException.php';
require_once APPPATH.'vendor/jwt/JWK.php';
require_once APPPATH .'third_party/Facebook/autoload.php';  
use \Firebase\JWT\JWT;


class User extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('UserModel'); //Load the User Model here 
		$this->load->helper('cookie');
	  	$this->load->config('facebook');
	  	$this->load->model('PropertyModel'); //Load the Properties Model here 
	  	$userdata = $this->UserModel->getAdmin();
  	 	$this->load->library('s3_upload');
        	$this->load->library('s3_delete');	
		$this->config->load('s3config', TRUE);
		$s3_config = $this->config->item('s3config');
		$this->load->library('email');
	}
	
	//user register
	public function register()
	{
		header('Content-Type: application/json');
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }

		$errors = [];

		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);
		if(isset($_POST) && !empty($_POST)){			
			
			if(!isset($_POST['is_social']) || !$_POST['is_social']){ //if not a social login

				//validate role
				if(!isset($_POST['role']) && empty($_POST['role'])){
					$errors['error'] = true;
					$errors['message'][] = 'Rôle';
				}
				//validate name
				if(!isset($_POST['name']) && empty($_POST['name'])){
					$errors['error'] = true;
					$errors['message'][] = 'Nom';
				}
				//validate email address
				if(!isset($_POST['email']) && empty($_POST['email'])){
					$errors['error'] = true;
					$errors['message'][] = 'e-mail';
				}else{
					//check if user is already exists
					$user_exist = $this->UserModel->getUserByEmail($_POST['email']);
					
					if($user_exist){
						$errors['error'] = true;
						$errors['email'] = "L'adresse e-mail existe déjà";
					}
				}
				//validate password
				if(!isset($_POST['password']) && empty($_POST['password'])){
					$errors['error'] = true;
					$errors['message'][] = 'Mot de passe';
				}
				//validate terms and conditions
				if(!isset($_POST['terms']) || !$_POST['terms']){
					$errors['error'] = true;
					$errors['terms']= 'veuillez vérifier la case à cocher des termes et conditions';
				}
				//validate phone
				if(!isset($_POST['phone']) && empty($_POST['phone'])){
					$errors['error'] = true;
					$errors['message'][] = 'Téléphone';
				}else{
					//check if user is already exists
					$user_exist = $this->UserModel->getUserByPhone($_POST['phone']);
					
					if($user_exist){
						$errors['error'] = true;
						$errors['phone'] = "Le téléphone existe déjà";
					}
				}

				if(empty($errors)){
					$_POST['password'] = md5($_POST['password']);

					$notificationData = [
						"message" => "Un nouvel utilisateur ".$_POST['name']."(".$_POST['role'].") a été ajouté.",
						'type' => 'new_user',
						'receiver' => 'admin',
						'sender' => 'system'
					];
					$this->UserModel->notification($notificationData); // create notification for admin
					
					if($_POST['role'] == 'agent'){
						$_POST['status'] = 'pending';
					}else{
						$_POST['status'] = 'approved';
					}
					unset($_POST['terms']);
					$this->UserModel->insert($_POST);
					$this->confirmUserAccount($_POST['email']);
				}else{
					
					if(isset($errors['message'])){
						$errors['message'] = implode(', ', $errors['message']).' est requis.'; 
					}
					if(!isset($errors['message'])){
						if(isset($errors['email']))
						$errors['message'] = $errors['email']; 
						unset($errors['email']);
					}
					if(!isset($errors['message'])){
						if(isset($errors['phone']))
						$errors['message'] = $errors['phone']; 
						unset($errors['phone']);
					}
					if(!isset($errors['message'])){
						if(isset($errors['terms']))
						$errors['message'] = $errors['terms']; 
						unset($errors['terms']);
					}
					
					echo json_encode($errors);
				}

			}else{ //if social login
				$fb_user = false;
				if(isset($_POST['access_token']) && !empty($_POST['access_token'])){
					$fb_user = true;	
				}

				if($fb_user){
					$social_user = $this->social_auth($_POST['access_token']);
					$_POST['name'] = $social_user['name'] ? $social_user['name'] : '';
					$_POST['email'] = $social_user['email'] ? $social_user['email']: '';
				}else{
					$_POST['email'] = $_POST['email'];
				}
				$refresh_token = '';
				$jwt = '';
				if(isset($_POST['email'])){
					if($_POST['role'] == 'agent'){
						$_POST['status'] = 'pending';
						$payload = [
					        "user" => $_POST['email'],
					        "iat" => time(),
					        "exp" => time() + 604800,  // Maximum expiration time is 7 days
					    ];
					    $jwt = JWT::encode($payload, SECRET_KEY);
				     	$payload2 = [
					        "user" => $_POST['email'],
					        "iat" => time(),
					        "exp" => time() + 31556926  // Maximum expiration time is 1 year
					    ];
					    $refresh_token = JWT::encode($payload2, SECRET_KEY2);
						
					}else{
						$_POST['status'] = 'approved';
						$payload = [
					        "user" => $_POST['email'],
					        "iat" => time(),
					        "exp" => time() + 604800,  // Maximum expiration time is 7 days
					    ];
					    $jwt = JWT::encode($payload, SECRET_KEY);
				     	$payload2 = [
					        "user" => $_POST['email'],
					        "iat" => time(),
					        "exp" => time() + 31556926  // Maximum expiration time is 1 year
					    ];
					    $refresh_token = JWT::encode($payload2, SECRET_KEY2);
					}
				    
				    $_POST['refresh_token'] = $refresh_token;
				    $_POST['access_token'] = $jwt;
				    $user = $this->UserModel->getUserByEmail($_POST['email']);
				    if($user){
				    	unset($user[0]['id']);
				    	$res = [
					        "access_token" => $jwt,
					        "refresh_token" => $refresh_token,
					        "data" => $user,
					        "success" => true
					    ];
					    $this->UserModel->updateUserbyEmail(array('refresh_token' => $refresh_token, 'access_token' => $jwt), $_POST['email']);
					    echo json_encode($res);
				    }else{
				    	$this->UserModel->socialinsert($_POST, $jwt, $refresh_token);
				    }
				}			
			}
		}else{
			echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
		}
	}

	//verify email confirmation link
	function confirmUserAccount($email){
		$token = base64_encode($email.'splithere'.SECRET_KEY2);
		$content = file_get_contents(APPPATH.'views/email-templates/email-verification-template.php');
		$page_link = base_url('user/confirmaccount/'.$token);
		$content = str_replace('{{link}}', $page_link, $content);
		$subject = 'Confirmez votre compte YED Immobilier';
		
		//$headers = 'From: YED Immobilier <contact@yedexpertises.com>' . "\r\n"."Content-type:text/html;charset=UTF-8" . "\r\n";
		//mail($email, $subject, $content, $headers, 'contact@yedexpertises.com');
		$this->sendEmail($email, $subject, $content);
		$this->UserModel->updateUserbyEmail(array('verification_token' => $token), $email);

		//$this->UserModel->updateVerifiedEmail(array('verified' => 1), $email);
	}

	//send emails for registration
	function sendEmail($to, $subject, $message){
	 	$config = array(
		  'protocol' => 'smtp',
		  'smtp_host' => 'smtp-relay.sendinblue.com',
		  'smtp_port' => 587,
		  'smtp_user' => 'kamaldeepkaur.itsmiths@gmail.com', // change it to yours
		  'smtp_pass' => 'Ptn08fAaD3RmWsx9', // change it to yours
		  'mailtype' => 'html',
		  'charset' => 'iso-8859-1',
		  'wordwrap' => TRUE
		);

	      $this->email->initialize($config);
	      $this->email->set_newline("\r\n");
	      $this->email->to($to);// change it to yours
	      $this->email->from('contact@yedexpertises.com', 'Yed Immobilier');
	      $this->email->subject($subject);
	      $this->email->message($message);
	      $this->email->send();

	}


	//send email confirmation link to user
	public function confirmaccount($token){
		if($token){					
			$decoded_email = explode('splithere', base64_decode($token));
			$record = $this->UserModel->getConfirmToken($decoded_email[0])[0];
			$error = false;
			if(isset($record['verification_token']) && $record['verification_token'] == $token){
				
				$user = $this->UserModel->getUserByEmail($decoded_email[0]);
				$update = $this->UserModel->update(['verified' => 1], $user[0]['id'], false);
				if($update){
					$_SESSION['update'] = true;
					return  redirect('https://yedimmobilier.com/login', 'refresh');
				}else{
					$_SESSION['update'] = false;
				}
				
			}else{
				echo "Le lien n'est pas valide";
			}
		}else{
			echo "Le lien n'est pas valide";
		}
	}

	//user login
	public function login()
	{
		header('Content-Type: application/json');
	
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }
		$errors = [];
		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);
	
		if(isset($_POST) && !empty($_POST)){
			
			if(!isset($_POST['email']) || empty($_POST['email'])){
				$errors['error'] = true;
				$errors['message'][] = 'Email';
			}
			if(!isset($_POST['password']) || empty($_POST['password'])){
				$errors['error'] = true;
				$errors['message'][] = 'Password';
			}
			$now_seconds = time();
			if(empty($errors)){
				//check for user by email and password if exists then login
				$user = $this->UserModel->getUserByEmailandPassword($_POST['email'], md5($_POST['password']));
				$payload = [
			        "user" => $_POST['email'],
			        "iat" => $now_seconds,
			        "exp" => time() + 604800  // Maximum expiration time is 7 days
			    ];
			    $jwt = JWT::encode($payload, SECRET_KEY);

			    $payload2 = [
			        "user" => $_POST['email'],
			        "iat" => $now_seconds,
			        "exp" => time()+31556926  // Maximum expiration time is 1 year
			    ];
			    $refresh_token = JWT::encode($payload2, SECRET_KEY2);

				if($user){

					if($user[0]['verified']){
						if($user[0]['status'] == 'approved'){
							if($user[0]['activate'] == 'activate'){
								$res = [
							        "access_token" => $jwt,
							        "refresh_token" => $refresh_token,
							        "user" => $user[0]
							    ];
							    $this->UserModel->updateUserbyEmail(array('refresh_token' => $refresh_token, 'access_token' => $jwt), $_POST['email']);							
								echo json_encode($res);	
							}else{
								echo json_encode(['error' => true, 'message'=> 'Votre compte a été désactivé par L\'administrateur Yed expertises.']);
							}
						}else{
							echo json_encode(['error' => true, 'message'=> 'Votre compte est en cours d\'examen. L\'administrateur de Yed expertises vous avertira lorsque votre compte sera activé']);
						}
													
					}else{
						echo json_encode(['error' => true, 'message'=> 'Veuillez confirmer votre adresse e-mail.']);
					}
					
				}else{
					echo json_encode(['error' => true, 'message'=> 'L\'adresse e-mail ou le mot de passe est incorrect.  Réessayer!']);
				}
			}else{
				if(isset($errors['message'])){
					$errors['message'] = implode(', ', $errors['message']).' est requis.'; 
				}
				echo json_encode($errors);
			}
		}else{
            echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
        }
	}

	//get refresh token
	public function getRefreshToken()
	{
		header('Content-Type: application/json');
	
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }
		$errors = [];
		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);
	
		if(isset($_POST) && !empty($_POST)){
			$token = $this->UserModel->getUserTokenByEmail($_POST['email']);
			if($token){
				echo json_encode($token[0]);	
			}else{
				echo  json_encode(["success" => false]);
			}
		}
	}
	//update user profile
	public function update()
	{
		header('Content-Type: application/json');
		$headers = $_SERVER;
        if (isset($headers['HTTP_AUTHORIZATION'])){
        	$decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
            	$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					
					
					$errors = [];
					$json = file_get_contents('php://input');

					// Converts it into a PHP object
					$_POST = (array)json_decode($json);
					if(isset($_POST) && !empty($_POST)){
						if(isset($_POST['password'])){
							$_POST['password'] = md5($_POST['password']);
						}
						if(isset($_POST['email'])){
							echo json_encode(['error' => true, 'type' => 'Not Allowed', 'message' => 'You cannot update your email address']);
						}else{
							$image_url = '';
							$image_name = '';
							if(isset($_POST['profile_image']) && $_POST['profile_image'] !== null){
							    $image_parts = explode(";base64,", $_POST['profile_image']);
							    $image_type_aux = explode("image/", $image_parts[0]);
							    $image_type = $image_type_aux[1];
							    $image_base64 = base64_decode($image_parts[1]);
							    $image_name = uniqid() . '.png';
							    $file = UPLOAD_PROFILES_DIR . $image_name;
							    $userdata = $this->UserModel->getUserByEmail($decode->user);
							    $old_image = $userdata[0]['profile_image'];
							   
							    file_put_contents($file, $image_base64);
							    $this->s3_upload->upload_file($file, 'profiles'); //upload to S3
						     	if(file_exists(UPLOAD_PROFILES_DIR . $old_image)) {
							    	 unlink(UPLOAD_PROFILES_DIR.$old_image);
						     	}
						     	if(file_exists(UPLOAD_PROFILES_DIR . $image_name)) {
 									unlink(UPLOAD_PROFILES_DIR.$image_name);
						     	}
						     	$this->s3_delete->delete_file($old_image, 'profiles'); 
							    $image_url = AWS_PROFILES_URL.$image_name;
							    $_POST['profile_image'] = $image_name;
							    $_POST['image_url'] = $image_url;
							}
							if(!$_POST['profile_image']){
								unset($_POST['profile_image']);
							}
							$user_id = $user[0]['id'];
							$user_properties = $this->PropertyModel->getPropertyByUserID($user_id);
							if(isset($_POST['name'])){
								if(!empty($user_properties)){
									foreach ($user_properties as $key => $property) {
										$this->PropertyModel->updatePropertyUserName($_POST['name'], $property->id);
									}
								}
							}
							$this->UserModel->update($_POST, $user[0]['id']);
						}
						
					}else{
			            echo json_encode(['error' => true, 'message' => 'Nothing to update!']);
			        }
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				
		    }else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
	}
	//get user status
	public function status()
	{
		header('Content-Type: application/json');
		$headers = $_SERVER;
        if (isset($headers['HTTP_AUTHORIZATION'])){
        	$decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
            	$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$userStatus = $user[0]['status'];
					echo json_encode(["status" => $userStatus]);				
					
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				
		    }else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
	}
	//get user phone status
	public function checkphone()
	{
		header('Content-Type: application/json');
		$headers = $_SERVER;
        if (isset($headers['HTTP_AUTHORIZATION'])){
        	$decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
            	$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$userphoneStatus = $user[0]['phone'];
					if($userphoneStatus){
						echo json_encode(["phone" => true]);	
					}else{
						echo json_encode(["phone" => false]);	
					}
								
					
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				
		    }else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
	}

	//refresh JWT access_token
	public function refresh()
	{
		header('Content-Type: application/json');
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
		}
		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);
		$refresh_token = $_POST['refresh_token'];
		$decode = JWT::decode($refresh_token, SECRET_KEY2, array(ALGORITHM));
		$user = $this->UserModel->getUserByTokens($decode->user);

        if(!empty($user) && $refresh_token == $user[0]['refresh_token']){
        	$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
			if($decode->exp < $current_time_stamp){ //check if refresh token is expired
			 	$validation = [
	                "type" => "Expired",
	                "error" => "Token Expired"
	            ];
			    echo json_encode($validation);
			}else{
				$payload = [
			        "user" => $decode->user,
			        "iat" => time(),
			        "exp" => time() + 604800,  // Maximum expiration time is 7 days
			    ];
			    $jwt = JWT::encode($payload, SECRET_KEY);
			    $res = [
			        "access_token" => $jwt
			    ];
			    $this->UserModel->updateUserbyEmail(array('access_token' => $jwt), $decode->user);
			    echo json_encode($res);
			}
        }else{
        	$validation = [
                "type" => "Invalid",
                "error" => "Token Invalid"
            ];
		    echo json_encode($validation);
        }
		
	}

	//verify password reset token
	public function resetpassword($email){
		if($email){					
			$decoded_email = explode('splithere', base64_decode($email));
			$record = $this->UserModel->getPasswordResetToken($decoded_email[0])[0];
			$error = false;
			if(isset($record['token']) && $record['token'] == $email){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($record['exp_date'] < $current_time_stamp){
					echo "Reset password link expired";
					$error = true;
				}
				if(!$error){
					$json = file_get_contents('php://input');

					// Converts it into a PHP object
					$_POST = (array)json_decode($json);
					if(isset($_POST) && !empty($_POST)){
						$_POST['password'] = md5($_POST['password']);
						unset($_POST['confirm_password']);
						unset($_POST['reset_password']);
						$user = $this->UserModel->getUserByEmail($decoded_email[0]);
						$update = $this->UserModel->update($_POST, $user[0]['id'], false);
						if($update){

							$_SESSION['update'] = true;
							return $this->load->view('email-templates/password-reset-form');
						}else{
							$_SESSION['update'] = false;
						}
					}else{
						
						return $this->load->view('email-templates/password-reset-form');
					}
				}
				
			}else{
				echo "Le lien n'est pas valide";
			}
		}else{
			echo "Le lien n'est pas valide";
		}
	}

	//reset password action
	public function passwordReset(){
		header('Content-Type: application/json');
		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
		}
		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);
		if(isset($_POST) && !empty($_POST)){
			$user = $this->UserModel->getUserByEmail($_POST['email']);
			if(!empty($user)){
				$email = base64_encode($_POST['email'].'splithere'.SECRET_KEY2);
				$content = file_get_contents(APPPATH.'views/email-templates/password-reset-email-template.php');
				$logo_url = base_url('assets/images/logo.jpg');
				$content = str_replace('{{logo_url}}', $logo_url, $content);
				$form_link = base_url('user/resetpassword/'.$email);
				$content = str_replace('{{link}}', $form_link, $content);
				$subject = 'Réinitialiser le mot de passe du compte YED Immobilier';
				//$headers = 'From: YED Immobilier <noreply@yedimmobilier.com>' . "\r\n";
				//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			
				$data = [
					'token' => $email,
					'exp_date' => time() + 86400, //expired in 24 hour
					'requested_email' => $_POST['email']
				];
				$this->UserModel->insertPasswordResetToken($data);
				$this->sendEmail($_POST['email'], $subject, $content);
				
				//setcookie('reset-key', $email, time()+86400 , '/', "", false, true);		

				//echo json_encode(['success' => true, 'message' => 'Veuillez vérifier votre boîte de réception ou votre dossier spam et suivez les instructions pour réinitialiser votre mot de passe.' ]);
				echo json_encode(['success' => true, 'msg' => 'Veuillez vérifier votre boîte de réception ou votre dossier spam et suivez les instructions pour réinitialiser votre mot de passe.' ]);
			}else{
				//echo json_encode(['error' => true, 'message' => 'Cette adresse e-mail est inexistante.' ]);
				echo json_encode(['error' => true, 'msg' => 'Cette adresse e-mail est inexistante.' ]);
			}
			
		}

	}

	//get user profile data
 	public function getprofile(){
 		header('Content-Type: application/json');
		$headers = $_SERVER;
        if (isset($headers['HTTP_AUTHORIZATION'])){
        	$decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
	        	$userdata = $this->UserModel->getUserByEmail($decode->user);
				if($decode->exp > $current_time_stamp){ 
			       	unset($userdata[0]['id']);
			       	if($userdata[0]['profile_image']['role']){
			       		if(file_exists(UPLOAD_PROFILES_DIR.$userdata[0]['profile_image'])) {
			       			$userdata[0]['profile_image'] =  base_url('uploads/profiles/'.$userdata[0]['profile_image']);
			       		}else{
			       			$userdata[0]['profile_image'] = AWS_PROFILES_URL.$userdata[0]['profile_image'];
			       		}
			       	}
			       
			        echo json_encode($userdata[0]);	
			    }else{
			    	$validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
				    echo json_encode($validation);
			    }		

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
 	}
 	//contact form action
 	public function contact(){
 		header('Content-Type: application/json');
		$headers = $_SERVER;
        if (isset($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$contact_info = $this->UserModel->getYedContactInfo();
					if(!empty($contact_info)){
						unset($contact_info[0]['id']);
						echo json_encode($contact_info[0]);
					}
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
 	}

 	//get particular profile Post
 	function getProfilePost(){
 		  	header('Content-Type: application/json');
    		$headers = $_SERVER;
    		if (isset($headers['HTTP_AUTHORIZATION'])){
	            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
		        $user = $this->UserModel->getUserByTokens($decode->user);
	            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
					$current_time_stamp1 = strtotime(date('Y-m-d H:i:s'));
					if($decode->exp > $current_time_stamp1){ 
						if($this->input->server('REQUEST_METHOD') !== 'POST'){
				            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
				            return false;
						}
						$response = [];
						$user_id = $user[0]['id'];
	 	    			$user_property = $this->PropertyModel->getPostByUsrId($user_id);
	 	    			$responses['properties'] = $user_property;
						$responses['totalResult'] = count($user_property);
						echo json_encode($responses);
	 		
	 	}else{

		            $validations = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validations);
			    }
				

			}else{

	            $validations = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validations);
		    }
		}else{            
            $validations = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validations);  
        }

    }
 

 	//contact form action
 	public function webcontact(){
 		header('Content-Type: application/json');
		$headers = $_SERVER;
		$contact_info = $this->UserModel->getYedContactInfo();
		if(!empty($contact_info)){
			unset($contact_info[0]['id']);
			echo json_encode($contact_info[0]);
		}
 	}

 	//Get Facebook Social Auth Data
 	function social_auth($token){ 
        $userData = array(); 
        
        $fb = new \Facebook\Facebook([
		  	'app_id'                => $this->config->item('facebook_app_id'), 
            'app_secret'            => $this->config->item('facebook_app_secret'), 
            'default_graph_version' => $this->config->item('facebook_graph_version') 
		  //'default_access_token' => '{access-token}', // optional
		]);
        $response = [];
        try {
        	
		  	// Get the \Facebook\GraphNodes\GraphUser object for the current user.
		  	// If you provided a 'default_access_token', the '{access-token}' is optional.
		  	$res = $fb->get('/me?fields=id,name,email', $token);
		  	$me = $res->getGraphUser();
		 
			$response['success'] = true;
			$response['email'] = $me->getEmail();
			$response['name'] = $me->getName();
			return $response; 
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			
		  // When Graph returns an error
			$response['error'] = true;
			$response['message'] = $e->getMessage();
		   	
		  
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			
		  // When validation fails or other local issues
			$response['error'] = true;
			$response['message'] = $e->getMessage();
		  //return 'Facebook SDK returned an error: ' . $e->getMessage();
		  
		}		
		return $response;             
    }

    //get google addresses using keywords
 	public function addresses(){ 
 		header('Content-Type: application/json');
 		if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
		}
 		
		$json = file_get_contents('php://input');

		// Converts it into a PHP object
		$_POST = (array)json_decode($json);	
		$keywords = implode('+',explode(' ',$_POST['keywords']));
   		$data = file_get_contents('https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$keywords.'&key='.GOOGLE_API_KEY);  
   		
   		$data =  json_decode($data);
   		$newdata = [];
   	
   		if(!empty($data->predictions)){
	   		foreach ($data->predictions as $key => $value) {
	   			$getPlace = file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$value->place_id.'&key='.GOOGLE_API_KEY);  
		   		$getPlace =  json_decode($getPlace);
	   			$newdata[$key]['name'] = $value->description;
	   			$newdata[$key]['location'] = $getPlace->result->geometry->location;
	   		}
	   	
   			echo json_encode($newdata);  
	   		
   		}else{
   			echo json_encode(['error' => true, "message" => "Aucun résultat trouvé!"]);
   		}
    } 

    //get current user properties
    public function properties(){
    	header('Content-Type: application/json');
    	$headers = $_SERVER;
    	if (isset($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$response = [];
					$user_id = $user[0]['id'];
					$user_properties = $this->PropertyModel->getPropertyByUserID($user_id);
					$response['properties'] = $user_properties;
					$response['totalResults'] = count($user_properties);
					echo json_encode($response);

				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }

    }
    //add property to favorites
    public function togglefav(){
    	header('Content-Type: application/json');
    	$headers = $_SERVER;
    	if (isset($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$json = file_get_contents('php://input');

					// Converts it into a PHP object
					$_POST = (array)json_decode($json);

					if(isset($_POST) && !empty($_POST)){
						$property_id = $_POST['property_id'];
						$user = $this->UserModel->getUserByEmail($decode->user);
						$favorites = $user[0]['favorites'];
						if($favorites){
							$favorites = explode(',',$favorites);
							
							if(in_array($property_id, $favorites)){
								foreach ($favorites as $key => $value) {
									if($value == $property_id){
										unset($favorites[$key]);
									}
								}
							}else{
								array_push($favorites,$property_id);
							}
						}else{
							$favorites = [$property_id];
						}
						$favorites = implode(',',$favorites);
						$update = $this->UserModel->update(['favorites' => $favorites], $user[0]['id'], false);
						if($update){
							echo json_encode(['success' => true, 'message' => 'Ajouté aux favoris.']);
						}

					}

				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }

    }
    public function updateadminprofile()
	{
		
		$errors = [];
				
		if(isset($_POST) && !empty($_POST)){
			
			$image_url = '';
			$image_name = '';
			if(isset($_FILES['profile_image']) && isset($_FILES['profile_image']['name'])){
				define('UPLOAD_DIR', './uploads/profiles/');
				
			    $target_dir = "./uploads/profiles/";
				$image_name = uniqid() . '.png';
				$target_file = $target_dir . $image_name;
			    $userdata = $this->UserModel->getUserByEmail($_POST['email']);
			    $old_image = $userdata[0]['profile_image'];
			    
			    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
			    	
			    	$this->s3_upload->upload_file($target_file, 'profiles'); //upload to S3
			     	if(file_exists(UPLOAD_PROFILES_DIR . $old_image)) {
			    	 	unlink(UPLOAD_PROFILES_DIR.$old_image);
			     	}
			     	if(file_exists(UPLOAD_PROFILES_DIR . $image_name)) {
						unlink(UPLOAD_PROFILES_DIR.$image_name);
			     	}
			     	$this->s3_delete->delete_file($old_image, 'profiles'); 
				    
			    }
			    $image_url = AWS_PROPERTIES_URL.$image_name;
			}
			if($image_name){

				$_POST['profile_image'] = $image_name;
				$_POST['image_url'] = $image_url;
			}else{
				unset($_POST['profile_image']);
			}

			$this->UserModel->update($_POST, $_POST['id']);
			
		}else{
            echo json_encode(['error' => true, 'message' => 'Nothing to update!']);
        }		
	}
    public function enquiry()
	{
		header('Content-Type: application/json');
		$errors = [];
		$headers = $_SERVER;
    	if (isset($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$json = file_get_contents('php://input');

					// Converts it into a PHP object
					$_POST = (array)json_decode($json);
				
					if(isset($_POST) && !empty($_POST)){
						if(!isset($_POST['phone']) || empty($_POST['phone'])){
							$errors['error'] = true;
							$errors['message'][] = 'Téléphone';
						}
						if(!isset($_POST['email']) || empty($_POST['email'])){
							$errors['error'] = true;
							$errors['message'][] = 'e-mail';
						}
						if(!isset($_POST['message']) || empty($_POST['message'])){
							$errors['error'] = true;
							$errors['message'][] = 'Message';
						}
						if(empty($errors)){

							$phone =  $_POST['phone'];
							$email =  $_POST['email'];
							$message =  $_POST['message'];

							$yed_contact_details = $this->UserModel->getYedContactInfo();
							$admin_contact_email = $yed_contact_details[0]['email'];
							$headers = 'From: YED Immobilier <noreply@yedimmobilier.com>' . "\r\n";
							$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
							$content = "<p>Vous avez une nouvelle requête de YED Immobilier.</p>";
							$content .= "<p><b>Contact téléphonique:</b> ".$phone."</p>";
							$content .= "<p><b>E-mail de contact:</b> ".$email."</p>";
							$content .= "<p><b>Message:</b> ".$message."</p>";
							mail($admin_contact_email, "Une nouvelle requête de YED Immobilier.", $content, $headers);
							echo json_encode(["success" => true, "message"=> "Votre demande à été envoyé. L'administrateur de Yed Immobilier vous contactera prochainement."]);			
						}else{
							if(isset($errors['message'])){
			                    $errors['message'] = implode(', ', $errors['message']).' est requis.'; 
			                }
			                echo json_encode($errors);
						}
					}else{
						echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
					}	
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
	}
    public function fav()
	{
		header('Content-Type: application/json');
		$errors = [];
		$headers = $_SERVER;
    	if (isset($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
	        $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
				$current_time_stamp = strtotime(date('Y-m-d H:i:s'));
				if($decode->exp > $current_time_stamp){ 
					if($this->input->server('REQUEST_METHOD') !== 'POST'){
			            echo json_encode(['error' => true, 'status' => 405,  'type' => 'method', 'message' => 'Method not allowed!']);
			            return false;
					}
					$user = $this->UserModel->getUserByEmail($decode->user);
					$response = [];
					$favorites = $user[0]['favorites'];
					if($favorites){
						$favs = explode(',', $favorites);
						$fav_props = $this->PropertyModel->getPropertyByIDs($favs);
						$response['properties'] = $fav_props;
						$response['totalResults'] = count($fav_props);
					}else{
						$response['properties'] = [];
						$response['totalResults'] = 0;
					}
					echo json_encode($response);
					
				}else{

		            $validation = [
		                "type" => "Expired",
		                "error" => "Token Expired"
		            ];
		            echo json_encode($validation);
			    }
				

			}else{

	            $validation = [
	                "type" => "Invalid",
	                "error" => "Token Invalid"
	            ];
	            echo json_encode($validation);
		    }
		}else{            
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);  
        }
	}
}


?>