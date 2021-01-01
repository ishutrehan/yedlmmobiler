<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_users = 'users';
		$this->table_properties = 'properties_listing';
	}
	//get all users
	public function getAllUsers()
	{
		$this->db->select("*");
		$this->db->from($this->table_users);
		$query = $this->db->get();     
		return $query->result();
		
	}
	
	//add new user
	public function addNewUser($data = array())
	{
		$response = [];
		$this->db->insert($this->table_users, $data);
	}
	//get user by id
	public function getUserByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$q = $this->db->get($this->table_users);
			$data = $q->result_array();
			return $data;
		}
	}
	//get user by email
	public function getUserByEmail($email = null)
	{
		if($email){
			$this->db->where('email', $email);
			$q = $this->db->get($this->table_users);
			$data = $q->result_array();
			return $data;
		}
	}

	public function deleteUserByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$delete = $this->db->delete($this->table_users);
			if($delete){
				echo true;
			}else{
				echo false;
			}			
		}
	}

	//get user by email and password
	public function getAdmin($email = null, $password = null, $role = "admin")
	{
		if($email && $password){
			$this->db->where('email', $email);
			$this->db->where('password', $password);
			$this->db->where('role', $role);
			$q = $this->db->get($this->table_users);
			$data = $q->result_array();
			return $data;
		}
	}
	
	//get all properties
	public function getAllProperties()
	{
		$this->db->select("*");
		$this->db->from($this->table_properties);
		$query = $this->db->get();     
		return $query->result();
		
	}
	
	//get property by id
	public function getPropertyByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$q = $this->db->get($this->table_properties);
			$data = $q->result_array();
			return $data;
		}
	}
	public function deletePropertyByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$delete = $this->db->delete($this->table_properties);
			if($delete){
				echo true;
			}else{
				echo false;
			}			
		}
	}
}

?>
