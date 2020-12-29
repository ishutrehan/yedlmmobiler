<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PropertyModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_name = 'properties_listing';
	}
	public function insert($data = array())
	{
		$response = [];
		$insert = $this->db->insert($this->table_name, $data);
		if($insert){
			$response = [
				'success' => true,
				'data' => $this->getPropertyByID($this->db->insert_id())
			];
			echo json_encode($response);
		}
	}
	public function getPropertyByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
	
}

?>
