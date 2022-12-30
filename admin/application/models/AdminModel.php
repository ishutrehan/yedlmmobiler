<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_users = 'users';
		$this->table_properties = 'properties_listing';
		$this->table_notifications = 'notifications';
		$this->table_types = 'property_type';
		$this->table_amenities = 'amenities';
		$this->table_notifications = 'notifications';
		$this->table_contact_info = 'yed_contact_info';
	}
	//get all individuals
	public function getAllUsers()
	{
		$this->db->select("*");
		$this->db->from($this->table_users);
		$this->db->order_by("id", "desc");
		$query = $this->db->get();     
		return $query->result();
		
	}
	//get all individuals
	public function getAllIndividuals()
	{
		$this->db->select("*");
		$this->db->where("role", "individual");
		$this->db->from($this->table_users);
		$this->db->order_by("id", "desc");
		$query = $this->db->get();     
		return $query->result();
		
	}
	//get all agents
	public function getAllAgents()
	{
		$this->db->select("*");
		$this->db->where("role", "agent");
		$this->db->from($this->table_users);
		$this->db->order_by("id", "desc");
		$query = $this->db->get();     
		return $query->result();
		
	}
	//insert types
	public function insertType($data = array())
	{
		
		$insert = $this->db->insert($this->table_types, $data);
		
	}
	//update types
	public function updateType($data = array())
	{
		
		$this->db->where('id', $data['id']);
		$update = $this->db->update($this->table_types, array('name' => $data['name']));
		
	}
	//Dlete types
	public function deletetypes($id)
	{

		$this->db->where('id', $id);
		$delete = $this->db->delete($this->table_types);
		if($delete){
			$sql = 'SELECT * FROM `'.$this->table_properties.'`';
			$sql .= ' WHERE CONCAT(",", `type`, ",") REGEXP  ",('.implode('|',[$id]).'),"';
			$results = $this->db->query($sql)->result();
			
			$data = [];
			foreach ($results as $key => $property) {
				$data[$key]['id'] = $property->id;
				$data[$key]['type'] = explode(',',$property->type);			
			}
		
			foreach ($data as $key => $value) {
				$pos = array_search($id, $value['type']);
				unset($value['type'][$pos]);
				$type = implode(',', $value['type']);
				$this->db->where('id', $value['id']);
				$this->db->update($this->table_properties, array('type' => $type));
			}			
		}
		
	}
	//insert amenities
	public function insertAmenities($data = array())
	{
	
		$insert = $this->db->insert($this->table_amenities, $data);
		
	}
	//update amenities
	public function updateAmenities($data = array())
	{
		$this->db->where('id', $data['id']);
		$update = $this->db->update($this->table_amenities, array('name' => $data['name']));
		
	}
	//insert amenities
	public function deleteamenities($id)
	{
	
		$this->db->where('id', $id);
		$delete = $this->db->delete($this->table_amenities);
		if($delete){
			$sql = 'SELECT * FROM `'.$this->table_properties.'`';
			$sql .= ' WHERE CONCAT(",", `amenities`, ",") REGEXP  ",('.implode('|',[$id]).'),"';
			$results = $this->db->query($sql)->result();
			
			$data = [];
			foreach ($results as $key => $property) {
				$data[$key]['id'] = $property->id;
				$data[$key]['amenities'] = explode(',',$property->amenities);			
			}
		
			foreach ($data as $key => $value) {
				$pos = array_search($id, $value['amenities']);
				unset($value['amenities'][$pos]);
				$amenities = implode(',', $value['amenities']);
				$this->db->where('id', $value['id']);
				$this->db->update($this->table_properties, array('amenities' => $amenities));
			}			
		}
		
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
			
			$data = [];
			$adminData = $this->session->userdata('userdata');
				
			$this->db->where('id', $id);
			$delete = $this->db->delete($this->table_users);
			if($delete){
				$properties = $this->getPropertyByUserID($id); //get user properties
				if(!empty($properties)){
					foreach ($properties as $key => $value) {
						$data['listed_by'] = $adminData[0]['id'];
						$data['user_role'] = $adminData[0]['role'];
						$data['username'] = $adminData[0]['name'];
						$this->updatePropertyByID($value['id'], $data); //assign all properties of deleted user to Admin 
					}
				}
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
				$useremail = $this->getUserEmailById($data['id']);
				$content = file_get_contents(APPPATH.'views/email-templates/account-activation-email.php');
				$logo_url = base_url('assets/images/logo.jpg');
				$content = str_replace('{{logo_url}}', $logo_url, $content);
				$subject = 'Toutes nos félicitations! Votre compte Yed Immobilier a été validé.';
				$headers = 'From: YED Immobilier <noreply@yedimmobilier.com>' . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				mail($useremail[0]['email'], $subject, $content, $headers);
				echo json_encode(['success' => true]);
			}else{
				echo json_encode(['success' => false]);
			}		
		}
	}
	public function activateUserByID($data = array())
	{
		if($data){
			$this->db->where('id', $data['id']);
			
			$update = $this->db->update($this->table_users, array('activate' => $data['activate']));
			if($update){
				$propertydata = [];
				$properties = $this->getPropertyByUserID($data['id']); //get user properties
				if(!empty($properties)){
					foreach ($properties as $key => $value) {
						$propertydata['activate'] = $data['activate'] == 'activate' ?  'activated' : 'deactivated';
						$this->updatePropertyByID($value['id'], $propertydata); //assign all properties of deleted user to Admin 
					}
				}
				echo json_encode(['success' => true]);
			}else{
				echo json_encode(['success' => false]);
			}		
		}
	}
	public function activatePropertyByID($data = array())
	{
		if($data){
			$this->db->where('id', $data['id']);
			
			$update = $this->db->update($this->table_properties, array('activate' => $data['activate']));
			if($update){
				echo json_encode(['success' => true]);
			}else{
				echo json_encode(['success' => false]);
			}		
		}
	}

	public function getUserEmailById($id = null)
	{
		if($id){
			$this->db->select('email');
			$this->db->where('id', $id);
			$q = $this->db->get($this->table_users);
			$data = $q->result_array();
			return $data;
		}
	}

	public function approvePropertiesByID($data = array())
	{
		
		if($data){
			$this->db->where('id', $data['id']);
			$update = $this->db->update($this->table_properties, array('approve' => $data['approve']));
			if($update){
				echo json_encode(['success' => true]);
			}else{
				echo json_encode(['success' => false]);
			}		
		}
	}
	public function updateUser($data = array())
	{
		if($data){
			$this->db->where('id', $data['id']);
			$update = $this->db->update($this->table_users, $data);
				
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
	public function getAdminbyEmail($email = null, $role = "admin")
	{
		if($email){
			$this->db->where('email', $email);
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
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();     
		return $query->result();
		
	}
	public function getAllTypes()
	{
		$this->db->select('*');
		$this->db->from($this->table_types);
		$query = $this->db->get(); 		   
		return $query->result();
		
	}
	public function getAllAmenities()
	{
		$this->db->select('*');
		$this->db->from($this->table_amenities);
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
	public function deletePropertyByID($data = [])
	{
		if(!empty($data)){

			if($data['bulk'] == 'true'){
				$ids = explode(',', $data['ids']);
				$this->db->where_in('id', $ids);
				$delete = $this->db->delete($this->table_properties);
				if($delete){
					echo true;
				}else{
					echo false;
				}	

			}else{
				$this->db->where('id', $data['id']);
				$delete = $this->db->delete($this->table_properties);
				if($delete){
					echo true;
				}else{
					echo false;
				}	
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
	//get all notifications
	public function getYedContactInfo()
	{
		$this->db->select("*");
		$this->db->from($this->table_contact_info);
		return $this->db->get()->result_array();   
	}
	//get all notifications
	public function UpdateYedContactInfo($data = [], $id)
	{
		unset($data['id']);
		$this->db->where('id', $id);
		$update = $this->db->update($this->table_contact_info, $data);
	}

	//get property by user id using listed_by column
	public function getPropertyByUserID($listed_by){
		if($listed_by){
			$this->db->where('listed_by', $listed_by);
			$this->db->order_by('id',"desc");
			$q = $this->db->get($this->table_properties);
			$data = $q->result_array();
			return $data;
		}
	}

	public function updatePropertyByID($propertyID, $data){
		$this->db->where('id', $propertyID);
		$this->db->update($this->table_properties, $data);
	}
}

?>
