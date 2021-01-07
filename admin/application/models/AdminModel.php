<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_users = 'users';
		$this->table_properties = 'properties_listing';
		$this->table_notifications = 'notifications';
	}
	//get all users
	public function getAllUsers()
	{
		$this->db->select("*");
		$this->db->from($this->table_users);
		$this->db->order_by("id", "desc");
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
			$this->db->select($this->table_users.'.*, COUNT('.$this->table_properties.'.listed_by) as total_properties');
			$this->db->from($this->table_users);
			$this->db->where($this->table_users.'.id', $id);
			$this->db->join($this->table_properties, $this->table_users.'.id = '.$this->table_properties .'.listed_by', 'LEFT')->group_by('listed_by');
			$q = $this->db->get();
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
	public function approveUserByID($data = array())
	{
		if($data){
			$this->db->where('id', $data['id']);
			$update = $this->db->update($this->table_users, array('status' => $data['status']));
			if($update){
				echo json_encode(['success' => true]);
			}else{
				echo json_encode(['success' => false]);
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
		$this->db->select($this->table_properties.".*, ".$this->table_users.".name as listedByName, ".$this->table_users.".role as userRole");
		$this->db->from($this->table_properties);
		$this->db->join($this->table_users, $this->table_properties.'.listed_by = '.$this->table_users .'.id', 'LEFT');
		$query = $this->db->get();     
		return $query->result();
		
	}
	
	//get property by id
	public function getPropertyByID($id = null)
	{
		if($id){
			$this->db->select($this->table_properties.'.*, '.$this->table_users.'.name as listedByName, '.$this->table_users.'.role as userRole');
			$this->db->from($this->table_properties);
			$this->db->where($this->table_properties.'.id', $id);
			$this->db->join($this->table_users, $this->table_properties.'.listed_by = '.$this->table_users .'.id', 'LEFT');
			$q = $this->db->get();
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
	//get latest 4 notifications
	public function getnotifications()
	{
		$this->db->select("*");
		$this->db->from($this->table_notifications);
		$this->db->where('receiver', 'admin')->where('status', 'unread')->order_by('id', 'desc')->limit(4);
		return $this->db->get()->result_array();   
	}
	//get all notifications
	public function getallnotifications()
	{
		$this->db->select("*");
		$this->db->from($this->table_notifications);
		$this->db->where('receiver', 'admin')->order_by('id', 'desc');
		return $this->db->get()->result_array();   
	}
}

?>
