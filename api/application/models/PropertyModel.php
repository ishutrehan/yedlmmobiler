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

	public function search($data = array())
	{
		
		if(!empty($data)){
			$where_type = '';
			$where_price = '';
			$sql = 'SELECT * FROM '.$this->table_name;
			if(isset($data['type']) && !empty($data['type'])){
				$sql .= ' WHERE type= "'.$data['type'].'"';
			}
			if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && !empty($data['price_max'])){
				if($data['type']){
					$sql .= ' and price ';
				}else
				{
					$sql .= ' WHERE price ';
				}
				
				$sql .=  'BETWEEN '.(int)$data['price_min'].' AND '.  (int)$data['price_max'];
			}
			if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && !empty($data['price_max'])){
				if(isset($data['type'])){
					$sql .= ' and price ';
				}else
				{
					$sql .= ' WHERE price ';
				}
				
				$sql .=  'BETWEEN '.(int)$data['price_min'].' AND '.  (int)$data['price_max'];
			}
			if(isset($data['bedroom_min']) && !empty($data['bedroom_min']) && isset($data['bedroom_max']) && !empty($data['bedroom_max'])){
				if((isset($data['type'])) || (isset($data['price_min']) && isset($data['price_max'])) ){
					$sql .= ' and number_of_bedrooms ';
				}else
				{
					$sql .= ' WHERE number_of_bedrooms ';
				}
				
				$sql .=  'BETWEEN '.(int)$data['bedroom_min'].' AND '.  (int)$data['bedroom_max'];
			}
			$results = $this->db->query($sql)->result();
			
		}else{
			$this->db->select("*");
			$this->db->from($this->table_name);
			$results = $this->db->get()->result();    
			
		}
		$response['properties'] = $results;
		$response['totalResults'] = count($results);
		echo json_encode($response);
	}
	
}

?>
