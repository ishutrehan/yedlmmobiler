<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_users = 'users';
		$this->table_properties = 'properties_listing';
	}
	//insert user
	public function insert($data = array())
	{
		$response = [];
		$insert = $this->db->insert($this->table_name, $data);
		if($insert){
			$response = [
				'success' => true,
				'data' => $this->getUserByID($this->db->insert_id())
			];
			echo json_encode($response);
		}
	}
	//update user profile
	public function update($data = array(), $user_id)
	{
		$response = [];		
		$this->db->where($this->primary_key, $user_id);
		$update = $this->db->update($this->table_name, $data);
		if($update){
			$response = [
				'success' => true,
				'data' => $this->getUserByID($user_id)
			];
			echo json_encode($response);
		}		
	}
	public function updatePassword($data = array(), $user_id)
	{
		$response = [];		
		$this->db->where($this->primary_key, $user_id);
		$update = $this->db->update($this->table_name, $data);
		if($update){
			$response = [
				'success' => true,
				'data' => $this->getUserByID($user_id)
			];
			echo json_encode($response);
		}		
	}
	//get user by id
	public function getUserByID($id = null)
	{
		if($id){
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
			$this->db->where('email', $email);
			$this->db->where('password', $password);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
}

?>
