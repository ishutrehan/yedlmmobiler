<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_name = 'users';
		$this->table_properties = 'properties';
		$this->primary_key = 'id';
		$this->table_notifications = 'notifications';
		$this->table_contact_info = 'yed_contact_info';

	}
	//insert user query callback
	public function insert($data = array())
	{
		$response = [];
		$insert = $this->db->insert($this->table_name, $data);
		
		if($insert){			
			$response = [
				'success' => true,
				'message' => "Nous venons de vous envoyer un email à l’adresse fournie, merci de vérifier dans votre boîte de réception ou votre dossier spams pour le retrouver, puis de cliquer sur le lien reçu pour activer votre compte."
			];
			echo json_encode($response);
		}
	}
	//user register using social media query callback
	public function socialinsert($data = array(), $token = '', $refresh_token = '')
	{
		$response = [];
		$insert = $this->db->insert($this->table_name, $data);
	
		if($insert){
			if($data['role'] == 'agent'){
				$response = [
					'success' => true,
					'access_token' => $token,
					'refresh_token' => $refresh_token,
					'data' => $this->getUserByID($this->db->insert_id())
				];
			}else{
				$response = [
					'success' => true,
					'access_token' => $token,
					'refresh_token' => $refresh_token,
					'data' => $this->getUserByID($this->db->insert_id())
				];
			}
			
			echo json_encode($response);
		}
	}
	//update user profile
	public function update($data = array(), $user_id, $return = true)
	{
		$response = [];	
		$image_url = '';
		if(isset($data['image_url'])){
			$image_url = $data['image_url'];
			unset($data['image_url']);	
			if(isset($data['imageUpdate']))unset($data['imageUpdate']);	
		}
		$this->db->where($this->primary_key, $user_id);
		$update = $this->db->update($this->table_name, $data);

		if($update){
			if($image_url != ''){
				$response = [
					'success' => true,
					'message' => "Updated successfully.",
					'url' => $image_url
				];
			}else{
				$response = [
					'success' => true,
					'message' => "Updated successfully.",
					'data' => $this->getUserByID($user_id)
				];
			}
			if($return){
				echo json_encode($response);
			}else{
				return true;
			}
			
		}		
	}
	//update usr by email address
	public function updateUserbyEmail($data = array(), $email)
	{
		$response = [];			
		$this->db->where('email', $email);
		$update = $this->db->update($this->table_name, $data);
			
	}


		public function updateVerifiedEmail($data = array(), $email)
	{
		$response = [];			
		$this->db->where('email', $email);
		$update = $this->db->update($this->table_name, $data);
			
	}


	//get user by id
	public function getUserByID($id = null)
	{
		if($id){
			$this->db->select('name, email, phone, role, profile_image, status, city, verified, favorites');
			$this->db->where($this->primary_key, $id);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
	//get user by email
	public function getUserByEmail($email = null)
	{
		if($email){
			$this->db->select('id, name, email, phone, role, profile_image, status, city, favorites');
			$this->db->where('email', $email);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}



	
	//get user by email
	public function getUserTokenByEmail($email = null)
	{
		if($email){
			$this->db->select('refresh_token');
			$this->db->where('email', $email);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
	//get user by email
	public function getUserByPhone($phone = null)
	{
		if($phone){
			$this->db->select('id, name, email, phone, role, profile_image, status, city, favorites');
			$this->db->where('phone', $phone);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}

	//get user by token email
	public function getUserByTokens($email = null)
	{
		if($email){
			$this->db->select('id, refresh_token, access_token, status, phone');
			$this->db->where('email', $email);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}

	//get user by email and password
	public function getUserByEmailandPassword($email = null, $password = null)
	{
		if($email && $password){
			$this->db->select('name, email, phone, role, profile_image, status, city, verified, favorites, activate');
			$this->db->where('email', $email);
			$this->db->where('password', $password);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
	//insert notification query
	public function notification($data = array())
	{
		$this->db->insert($this->table_notifications, $data);
	}

	//insert pasword reset tokens
	public function insertPasswordResetToken($data = []){
		$response = [];
		$insert = $this->db->insert('password_reset_key', $data);
	}

	//get token
	public function getConfirmToken($email){
		$this->db->select('*');
		$this->db->where('email', $email);
		$this->db->order_by('id',"desc")->limit(1);
		$q = $this->db->get('users');
		$data = $q->result_array();
		return $data;
	}
	//get password reset token
	public function getPasswordResetToken($email){
		$this->db->select('*');
		$this->db->where('requested_email', $email);
		$this->db->order_by('id',"desc")->limit(1);
		$q = $this->db->get('password_reset_key');
		$data = $q->result_array();
		return $data;
	}
	//get yed admin contact details 
	public function getYedContactInfo()
	{
		$this->db->select("*");
		$this->db->from($this->table_contact_info);
		return $this->db->get()->result_array();   
	}
	//get yed admin contact details 
	public function getAdmin()
	{
		$this->db->select("*");
		$this->db->where('role', 'admin');
		$this->db->from($this->table_name);
		return $this->db->get()->result_array();   
	}
}

?>
