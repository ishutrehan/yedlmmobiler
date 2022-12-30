<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PropertyModel extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->table_name = 'properties_listing';
		$this->table_users = 'users';
		$this->primary_key = 'id';
		$this->table_types = 'property_type';
		$this->table_type_of_rentals = 'type_of_rentals';
		$this->table_amenities = 'amenities';
		$this->load->model('UserModel');
	}
	//insert property query callback
	public function insert($data = array())
	{
		$response = [];
		//insert query
		$insert = $this->db->insert($this->table_name, $data);

		//check if insert
		if($insert){
			$response = [
				'success' => true,
				'message' => "Propriété ajoutée avec succès.",
				'data' => $this->getPropertyByID($this->db->insert_id())
			];
		
			//update featured image object item to image URL
			if($response['data'][0]['featured']){
				if(!$response['data'][0]['wp_id']){
					if(file_exists(UPLOAD_PROPERTIES_DIR.$response['data'][0]['featured'])) {
						$response['data'][0]['featured'] = base_url('uploads/properties/'.$response['data'][0]['featured']);
					}else{
						$response['data'][0]['featured'] = AWS_PROPERTIES_URL.$response['data'][0]['featured'];
					}
					
				}
			}
			$featured_array = [];

			if($response['data'][0]['featured']){	
				//create featured image as image gallery array
				$featured_array = [
					"name" => "",
					"type" => "",
					"url" => $response['data'][0]['featured']
				];
			}
			//decode json images to arary
			$response['data'][0]['image'] =  json_decode($response['data'][0]['image'], JSON_UNESCAPED_SLASHES);

			//check if featured images array is not empty
			if(!empty($featured_array)){
				if(!empty($response['data'][0]['image'])){
					//push fetaured image to image gallery in first position
					array_unshift($response['data'][0]['image'], $featured_array);
				}else{
					$response['data'][0]['image'][] = $featured_array;
				}
				
			}
			//check if price per month exists
			if($response['data'][0]['price_per_month']){
				$response['data'][0]['currency'] = $response['data'][0]['currency'].'/Mois'; //set currency in per month format
			}
			//make type string to array
			$response['data'][0]['location_tags'] =  $response['data'][0]['location_tags'] ? explode(',' , $response['data'][0]['location_tags']) : [];
			$response['data'][0]['type'] =  explode(',' , $response['data'][0]['type']); 
			//set approve property response as bolean 
			$response['data'][0]['approve'] = $response['data'][0]['approve'] == "1" ? true: false; 
			$response['data'][0]['amenities'] =  $response['data'][0]['amenities'] ? explode(',' , $response['data'][0]['amenities']) : [];
			// success response 
			echo json_encode($response);
		}
	}

	//insert query callback for wordpress API
	public function insertFromWP($data = array())
	{
		$response = [];

		$this->db->insert($this->table_name, $data);
		
	}
	//update property query for WP hook
	public function updateFromWP($data = array())
	{
		$response = [];
		$this->db->where('wp_id', $data['wp_id']);
		$this->db->update($this->table_name, $data);
		
	}
	
	public function deleteProperty($property_id)
	{
		$response = [];
		$this->db->where('id', $property_id);
		$this->db->update($this->table_name, ['deleted' => true]);
		return true;
		
	}
	//delete query for WP hook
	public function deleteFromWP($data = array())
	{
		$response = [];
		$this->db->where('wp_id', $data['wp_id']);
		$this->db->delete($this->table_name);
		
	}
	//update property
	public function update($data = array(), $property_id)
	{
		$response = [];
		$this->db->where($this->primary_key, $property_id);
		unset($data['id']);
		
		$update = $this->db->update($this->table_name, $data);
		if($update){
			$response = [
				'success' => true,
				'message' => "Mis à jour avec succés"
			];
		}else{
			$response = [
				'success' => true,
				'message' => "Rien à mettre à jour"
			];
		}
		echo json_encode($response);
	}
	//get property by id
	public function getPropertyByID($id = null)
	{
		if($id){
			$this->db->where('id', $id);
			$q = $this->db->get($this->table_name);
			$data = $q->result_array();
			return $data;
		}
	}
	public function updatePropertyUserName($username, $property_id){
		$this->db->where('id', $property_id);
		$this->db->update($this->table_name, ['username' => $username]);
	}
	public function updateViewCount($userid, $property_id){
		$this->db->where('id', $property_id);
		$q = $this->db->get($this->table_name);
		$data = $q->result_array();
		$propertyViews = $data[0]['viewerIDs'];
		if(!$userid){
			$splitCount = explode(',', $propertyViews);
			$totalViews = ['totalViews' => count($splitCount)];
			return $totalViews;
		}
		$views = [];
		if(!$propertyViews){
			$views[] = $userid;
		}else{
			$splitCount = explode(',', $propertyViews);
			if(!in_array($userid, $splitCount)){
				array_push($splitCount, $userid);
			}
			$views = $splitCount;
		}
		$finalViews = implode(',', $views);
		$this->db->where('id', $property_id);
		$this->db->update($this->table_name, ['viewerIDs' => $finalViews, 'viewCount' => count($views)]);
		$totalViews = ['totalViews' => count($views)];
		return $totalViews;
	}

	//get properties by multiple ids
	public function getPropertyByIDs($ids = array())
	{
		if(!empty($ids)){
			$this->db->where_in('id', $ids);
			$this->db->from($this->table_name);
			$results = $this->db->get()->result();
			
			foreach ($results as $key => $value) {
				$value->favorite = true;
				$user = $this->UserModel->getUserByID($value->listed_by);
				$yed_contact_info = $this->UserModel->getYedContactInfo();
				if(!empty($user)){
					$role = $user[0]['role'];
					if($role == 'agent'){
						$value->contact_email = $user[0]['email'];
						$value->contact_phone = $user[0]['phone'];
					}else{
						$value->contact_email = $yed_contact_info[0]['email'];
						$value->contact_phone = $yed_contact_info[0]['phone'];
					}
				}
				$purpose = '';
				if($value->purpose == 'sale'){
					$purpose = 'À Vendre';
				}elseif($value->purpose == 'rent'){
					$purpose = 'À Louer';
				}else{
					$purpose = '';
				}

				$results[$key]->purpose = $purpose;
				if($results[$key]->featured){
					if(!$results[$key]->wp_id){
						if(file_exists(UPLOAD_PROPERTIES_DIR.$results[$key]->featured)) {
							$results[$key]->featured = base_url('uploads/properties/'.$results[$key]->featured);
						}else{
							$results[$key]->featured = AWS_PROPERTIES_URL.$results[$key]->featured;
						}
					}
				}else{
					$results[$key]->featured = null;
				}
				$results[$key]->image = json_decode($value->image, JSON_UNESCAPED_SLASHES);
				if(!empty($results[$key]->image)){
					foreach ($results[$key]->image as $key2 => $image) {
						if($image['name']){
							if(file_exists(UPLOAD_PROPERTIES_DIR.$image['name'])) {
								$results[$key]->image[$key2]['url'] = base_url('uploads/properties/'.$image['name']);
							}else{
								$results[$key]->image[$key2]['url'] = AWS_PROPERTIES_URL.$image['name'];
							}
						}
					}
				}else{
					$results[$key]->image = null;
				}
				$featured_array = [];

				if($results[$key]->featured){				
					$featured_array = [
						"name" => "",
						"type" => "",
						"url" => $results[$key]->featured
					];
				}
				if(!empty($featured_array)){
					if(!empty($results[$key]->image)){
						array_unshift($results[$key]->image, $featured_array);
					}else{
						$results[$key]->image[] = $featured_array;
					}
				}
				$results[$key]->title = trim(html_entity_decode($value->title));
				$results[$key]->description = trim(html_entity_decode($value->description));
				if($results[$key]->price_per_month){
					$results[$key]->currency = $results[$key]->currency.'/Mois';
				}
				$results[$key]->price = (int)$value->price > 0 ? str_replace(',', " ", number_format((int)$value->price)) : "";
				$results[$key]->price_per_month = (int)$value->price_per_month > 0 ? str_replace(',', " ", number_format((int)$value->price_per_month)): "";
				$results[$key]->type = $value->type ? explode(',',$value->type): [];
				$results[$key]->approve = $value->approve == "1" ? true: false;
				$results[$key]->amenities = $value->amenities ? explode(',', $value->amenities) : [];
			}
			return $results;
		}
	}

	//property search queries
	public function search($data = array(), $searchType)
	{
		
		$results = [];

		$sortBy = 'recent';
		if(isset($_POST['sort_by']) && !empty($_POST['sort_by'])){
            $sortBy = $_POST['sort_by'];
        }
        $order_by = 'id desc';
        if($sortBy == 'recent'){
        	$order_by = 'id DESC';
        }
        if($sortBy == 'rel'){
        	$order_by = 'id DESC';
        }
        if($sortBy == 'low_price'){
        	$order_by = 'price ASC';
        }
        if($sortBy == 'high_price'){
        	$order_by = 'price DESC';
        }
        
        $per_page = isset($data['limit']) ? $data['limit'] :12;
        $limit_query = '';
        $this->db->select('*');
	 	$this->db->from($this->table_name);
	 	$totalProps = $this->db->get()->result();
        $totalPages =  ceil(count($totalProps) / $per_page);
        $last_offset = ($totalPages - 1) * $per_page;
        $offset = '';

        if(isset($data['offset']) && $data['offset'] !== ''){
        	$offset = $data['offset'];        	
    		$limit_query = " LIMIT ".$per_page." OFFSET ".$offset;
        }
       
		if($searchType == "searchForm"){
			$where_type = '';
			$where_price = '';
			$sql = 'SELECT * FROM `'.$this->table_name.'`';
			$locationQuery = '';
			$locationWhere = '';
			$distance = 50;
           
            //search query if we have lat and long
			if(isset($data['latitude']) && !empty($data['latitude']) && isset($data['longitude']) && !empty($data['longitude'])){
				if(isset($data['distance']) && !empty($data['distance'])){
					$distance = (int) $data['distance'];
				}
				
				if(isset($data['type']) && !empty($data['type'])){
					$locationWhere .= ' WHERE CONCAT(",", `type`, ",") REGEXP  ",('.implode('|',$data['type']).'),"';
				}
				
				if(isset($data['type_of_rental']) && !empty($data['type_of_rental'])){
					if(isset($data['type'])){
						$locationWhere .= ' and CONCAT(",", `type_of_rental`, ",") REGEXP  ",('.implode('|',$data['type_of_rental']).'),"';
					}else
					{
						$locationWhere .= ' WHERE CONCAT(",", `type_of_rental`, ",") REGEXP  ",('.implode('|',$data['type_of_rental']).'),"';
					}
				}
				if(isset($data['area_min']) && !empty($data['area_min']) && isset($data['area_max']) && !empty($data['area_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))){
						$locationWhere .= ' and `area` ';
					}else
					{
						$locationWhere .= ' WHERE `area` ';
					}
					
					$locationWhere .=  'BETWEEN '.(int)$data['area_min'].' AND '.  (int)$data['area_max'];
				}
				if(isset($data['area_min']) && !empty($data['area_min']) && isset($data['area_max']) && empty($data['area_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))){
						$locationWhere .= ' and `area` ';
					}else
					{
						$locationWhere .= ' WHERE `area` ';
					}
					
					$locationWhere .=  '>= '.(int)$data['area_min'];
				}
				if(isset($data['area_max']) && !empty($data['area_max']) && isset($data['area_min']) && empty($data['area_min'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))){
						$locationWhere .= ' and `area` ';
					}else
					{
						$locationWhere .= ' WHERE `area` ';
					}
					
					$locationWhere .=  '<= '.(int)$data['area_max'];
				}
				if(isset($data['purpose']) && !empty($data['purpose'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) ||  (isset($data['area_max']) && !empty($data['area_max']))) {
						$locationWhere .= ' and `purpose` ';
					}else
					{
						$locationWhere .= ' WHERE `purpose` ';
					}
					
					$locationWhere .=  '= "'.$data['purpose'].'"';
				}
				if(isset($data['user_role']) && !empty($data['user_role'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) ||  (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose']))) {
						$locationWhere .= ' and `user_role` ';
					}else
					{
						$locationWhere .= ' WHERE `user_role` ';
					}
					
					$locationWhere .=  '= "'.$data['user_role'].'"';
				}
				if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && !empty($data['price_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$locationWhere .= ' and `price` ';
					}else
					{
						$locationWhere .= ' WHERE `price` ';
					}
					
					$locationWhere .=  'BETWEEN '.(int)$data['price_min'].' AND '.  (int)$data['price_max'];
				}
				if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && empty($data['price_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$locationWhere .= ' and `price` ';
					}else
					{
						$locationWhere .= ' WHERE `price` ';
					}
					
					$locationWhere .=  '>= '.(int)$data['price_min'];
				}
				if(isset($data['price_max']) && !empty($data['price_max']) && isset($data['price_min']) && empty($data['price_min'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$locationWhere .= ' and `price` ';
					}else
					{
						$locationWhere .= ' WHERE `price` ';
					}
					
					$locationWhere .=  '<= '.(int)$data['price_max'];
				}
				if(isset($data['bedroom_min']) && !empty($data['bedroom_min']) && isset($data['bedroom_max']) && !empty($data['bedroom_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$locationWhere .= ' and `number_of_bedrooms` ';
					}else
					{
						$locationWhere .= ' WHERE `number_of_bedrooms` ';
					}
					
					$locationWhere .=  'BETWEEN '.(int)$data['bedroom_min'].' AND '.  (int)$data['bedroom_max'];
				}
				if(isset($data['bathroom_min']) && !empty($data['bathroom_min']) && isset($data['bathroom_max']) && !empty($data['bathroom_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$locationWhere .= ' and `number_of_bathrooms` ';
					}else
					{
						$locationWhere .= ' WHERE `number_of_bathrooms` ';
					}
					
					$locationWhere .=  'BETWEEN '.(int)$data['bathroom_min'].' AND '.  (int)$data['bathroom_max'];
				}

				if(isset($data['location_tags']) && !empty($data['location_tags'])) {
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role'])) || (isset($data['bathroom_min']) && !empty($data['bathroom_min'])) || (isset($data['bathroom_max']) && !empty($data['bathroom_max'])) ) {
						$locationWhere .= ' and FIND_IN_SET("'.$data['location_tags'].'",location_tags)';
					}else
					{
						$locationWhere .= ' WHERE FIND_IN_SET("'.$data['location_tags'].'",location_tags)';
					}

				}

				if(isset($data['name']) && !empty($data['name'])) {
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role'])) || (isset($data['bathroom_min']) && !empty($data['bathroom_min'])) || (isset($data['bathroom_max']) && !empty($data['bathroom_max'])) || (isset($data['location_tags']) && !empty($data['location_tags']))) {
						$locationWhere .= ' and `username` ';
					}else
					{
						$locationWhere .= ' WHERE `username` ';
					}
					
					$locationWhere .=  'LIKE "'.$data['name'].'%"';
				}
				if (strpos($locationWhere, 'WHERE') !== false) {
				    $locationWhere.= ' and deleted=false and activate="activated"';
				}else{
					$locationWhere.= ' WHERE deleted=false and activate="activated"';
				}
				$locationQuery = $this->db->query("SELECT *, (6371 * acos(cos(radians('".$data['latitude']."')) * cos(radians(latitude)) * cos( radians(longitude) - radians('".$data['longitude']."')) + sin(radians('".$data['latitude']."')) * sin(radians(latitude)))) AS distance FROM ".$this->table_name.$locationWhere." and deleted = false HAVING distance <= ".$distance." ORDER BY ".$order_by.$limit_query);
				$allResults = $this->db->query("SELECT *, (6371 * acos(cos(radians('".$data['latitude']."')) * cos(radians(latitude)) * cos( radians(longitude) - radians('".$data['longitude']."')) + sin(radians('".$data['latitude']."')) * sin(radians(latitude)))) AS distance FROM ".$this->table_name.$locationWhere." and deleted = false HAVING distance <= ".$distance." ORDER BY ".$order_by);
				$totalProps  = $allResults->result();

				$totalPages =  ceil(count($totalProps) / $per_page);
        		$last_offset = ($totalPages - 1) * $per_page;
				$results = $locationQuery->result(); 
			}else{
				
				if(isset($data['type']) && !empty($data['type'])){
					$sql .= ' WHERE CONCAT(",", `type`, ",") REGEXP  ",('.implode('|',$data['type']).'),"';
				}
				
				if(isset($data['type_of_rental']) && !empty($data['type_of_rental'])){
					if(isset($data['type']) && !empty($data['type'])){
						$sql .= ' and CONCAT(",", `type_of_rental`, ",") REGEXP  ",('.implode('|',$data['type_of_rental']).'),"';
					}else
					{
						$sql .= ' WHERE CONCAT(",", `type_of_rental`, ",") REGEXP  ",('.implode('|',$data['type_of_rental']).'),"';
					}
				}
				if(isset($data['area_min']) && !empty($data['area_min']) && isset($data['area_max']) && !empty($data['area_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))) {
						$sql .= ' and `area` ';
					}else
					{
						$sql .= ' WHERE `area` ';
					}
					
					$sql .=  'BETWEEN '.(int)$data['area_min'].' AND '.  (int)$data['area_max'];
				}
				if(isset($data['area_min']) && !empty($data['area_min']) && isset($data['area_max']) && empty($data['area_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))){
						$sql .= ' and `area` ';
					}else
					{
						$sql .= ' WHERE `area` ';
					}
					
					$sql .=  '>='.(int)$data['area_min'];
				}
				if(isset($data['area_max']) && !empty($data['area_max']) && isset($data['area_min']) && empty($data['area_min'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental']))){
						$sql .= ' and `area` ';
					}else
					{
						$sql .= ' WHERE `area` ';
					}
					
					echo $sql .=  '<= '.(int)$data['area_max'];
				}

				if(isset($data['purpose']) && !empty($data['purpose'])){
					if( (isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max']))){
						$sql .= ' and `purpose` ';
					}else
					{
						$sql .= ' WHERE `purpose` ';
					}
					
					$sql .=  '= "'.$data['purpose'].'"';
				}
				if(isset($data['user_role']) && !empty($data['user_role'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) ||  (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose']))) {
						$sql .= ' and `user_role` ';
					}else
					{
						$sql .= ' WHERE `user_role` ';
					}
					
					$sql .=  '= "'.$data['user_role'].'"';
				}
				if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && !empty($data['price_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$sql .= ' and `price` ';
					}else
					{
						$sql .= ' WHERE `price` ';
					}
					
					$sql .=  'BETWEEN '.(int)$data['price_min'].' AND '.  (int)$data['price_max'];
				}
				if(isset($data['price_min']) && !empty($data['price_min']) && isset($data['price_max']) && empty($data['price_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$sql .= ' and `price` ';
					}else
					{
						$sql .= ' WHERE `price` ';
					}
					
					$sql .=  '>= '.(int)$data['price_min'];
				}
				if(isset($data['price_max']) && !empty($data['price_max']) && isset($data['price_min']) && empty($data['price_min'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$sql .= ' and `price` ';
					}else
					{
						$sql .= ' WHERE `price` ';
					}
					
					$sql .=  '<= '.(int)$data['price_max'];
				}
				if(isset($data['bedroom_min']) && !empty($data['bedroom_min']) && isset($data['bedroom_max']) && !empty($data['bedroom_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$sql .= ' and `number_of_bedrooms` ';
					}else
					{
						$sql .= ' WHERE `number_of_bedrooms` ';
					}
					
					$sql .=  'BETWEEN '.(int)$data['bedroom_min'].' AND '.  (int)$data['bedroom_max'];
				}
				if(isset($data['bathroom_min']) && !empty($data['bathroom_min']) && isset($data['bathroom_max']) && !empty($data['bathroom_max'])){
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role']))) {
						$sql .= ' and `number_of_bathrooms` ';
					}else
					{
						$sql .= ' WHERE `number_of_bathrooms` ';
					}
					
					$sql .=  'BETWEEN '.(int)$data['bathroom_min'].' AND '.  (int)$data['bathroom_max'];
				}

				if(isset($data['location_tags']) && !empty($data['location_tags'])) {
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role'])) || (isset($data['bathroom_min']) && !empty($data['bathroom_min'])) || (isset($data['bathroom_max']) && !empty($data['bathroom_max'])) ) {
						$sql .= ' and FIND_IN_SET("'.$data['location_tags'].'",location_tags) ';
					}else
					{
						$sql .= ' WHERE FIND_IN_SET("'.$data['location_tags'].'",location_tags) ';
					}
					
				}

				if(isset($data['name']) && !empty($data['name'])) {
					if((isset($data['type']) && !empty($data['type'])) || (isset($data['type_of_rental']) && !empty($data['type_of_rental'])) || (isset($data['price_min']) && !empty($data['price_min'])) || (isset($data['price_max']) && !empty($data['price_max'])) || (isset($data['bedroom_min']) && !empty($data['bedroom_min'])) || (isset($data['bedroom_max']) && !empty($data['bedroom_max'])) || (isset($data['area_min']) && !empty($data['area_min'])) || (isset($data['area_max']) && !empty($data['area_max'])) || (isset($data['purpose']) && !empty($data['purpose'])) || (isset($data['user_role']) && !empty($data['user_role'])) || (isset($data['bathroom_min']) && !empty($data['bathroom_min'])) || (isset($data['bathroom_max']) && !empty($data['bathroom_max'])) || (isset($data['location_tags']) && !empty($data['location_tags']))) {
						$sql .= ' and `username` ';
					}else
					{
						$sql .= ' WHERE `username` ';
					}
					
					$sql .=  'LIKE "'.$data['name'].'%"';
				}
				if (strpos($sql, 'WHERE') !== false) {
				    $sql.= ' and deleted=false and activate="activated"';
				}else{
					$sql.= ' WHERE deleted=false and activate="activated"';
				}
				$allResults = $sql;
				$allResults .= ' ORDER BY '.$order_by;
				$sql.=' ORDER BY '.$order_by.$limit_query;
				$totalProps = $this->db->query($allResults)->result();
				$totalPages =  ceil(count($totalProps) / $per_page);
        		$last_offset = ($totalPages - 1) * $per_page;
				$results = $this->db->query($sql)->result();
			}
			
			
			
			
		}else{
			$this->db->select($this->table_name.".*, ".$this->table_users.".email as contact_email, ".$this->table_users.".phone as contact_phone");
		 	$this->db->from($this->table_name);
		 	$this->db->join($this->table_users, $this->table_name.'.listed_by = '.$this->table_users .'.id', 'LEFT');
		 	if($sortBy == 'recent') $this->db->order_by('id',"DESC");
		 	if($sortBy == 'rel') $this->db->order_by('id',"DESC");
		 	if($sortBy == 'low_price') $this->db->order_by('price',"ASC");
		 	if($sortBy == 'high_price') $this->db->order_by('price',"DESC");
		 	if($limit_query){
		 		$this->db->limit($per_page, $_POST['offset']);
		 	}
		 	$results = $this->db->get()->result();
		}
		foreach ($results as $key => $value) {
			$value->favorite = false;
			if(!empty($_POST['fav_list'])){
				if(in_array($value->id, $_POST['fav_list'])){
					$value->favorite = true;
				}
			}
			$user = $this->UserModel->getUserByID($value->listed_by);
			$yed_contact_info = $this->UserModel->getYedContactInfo();
			if(!empty($user)){
				$role = $user[0]['role'];
				if($role == 'agent'){
					$value->contact_email = $user[0]['email'];
					$value->contact_phone = $user[0]['phone'];
				}else{
					$value->contact_email = $yed_contact_info[0]['email'];
					$value->contact_phone = $yed_contact_info[0]['phone'];
				}
			}
			$purpose = '';
			if($value->purpose == 'sale'){
				$purpose = 'À Vendre';
			}elseif($value->purpose == 'rent'){
				$purpose = 'À Louer';
			}else{
				$purpose = '';
			}

			$results[$key]->purpose = $purpose;
			
			if($results[$key]->featured){
				if(!$results[$key]->wp_id){
					if(file_exists(UPLOAD_PROPERTIES_DIR.$results[$key]->featured)){
						$results[$key]->featured = base_url('uploads/properties/'.$results[$key]->featured);
					}else{
						$results[$key]->featured = AWS_PROPERTIES_URL.$results[$key]->featured;
					}
				}
			}else{
				$results[$key]->featured = null;
			}
			
			$results[$key]->image = json_decode($value->image, JSON_UNESCAPED_SLASHES);
			if(!empty($results[$key]->image)){
				foreach ($results[$key]->image as $key2 => $image) {

					if($image['name']){
						if(file_exists(UPLOAD_PROPERTIES_DIR.$image['name'])){
							$results[$key]->image[$key2]['url'] = base_url('uploads/properties/'.$image['name']);
						}else{
							$results[$key]->image[$key2]['url'] = AWS_PROPERTIES_URL.$image['name'];
						}
						
					} 
				}
			}else{
				$results[$key]->image = null;
			}
			$featured_array = [];

			if($results[$key]->featured){				
				$featured_array = [
					"name" => "",
					"type" => "",
					"url" => $results[$key]->featured
				];
			}
			if(!empty($featured_array)){
				if(!empty($results[$key]->image)){
					array_unshift($results[$key]->image, $featured_array);
				}else{
					$results[$key]->image[] = $featured_array;
				}
			}
			$results[$key]->title = trim(html_entity_decode($value->title));
			$results[$key]->description = trim(html_entity_decode($value->description));
			if($results[$key]->price_per_month){
				$results[$key]->currency = $results[$key]->currency.'/Mois';
			}
			$results[$key]->price = (int)$value->price > 0 ? str_replace(',', " ", number_format((int)$value->price)) : "";
			$results[$key]->price_per_month = (int)$value->price_per_month > 0 ? str_replace(',', " ", number_format((int)$value->price_per_month)): "";
			$results[$key]->type = $value->type ? explode(',',$value->type): [];
			$results[$key]->approve = $value->approve == "1" ? true: false;
			$results[$key]->amenities = $value->amenities ? explode(',', $value->amenities) : [];
		}
		
		$response['properties'] = $results;
		$response['totalResults'] = count($totalProps);
		if($offset == $last_offset || $offset > $last_offset){
			$response['next'] = false;
		}else{
			$response['next'] = true;
		}
		echo json_encode($response);
	}

	//get all property types list
	public function getPropTypes(){
		$this->db->select('*');
		$this->db->from($this->table_types);
		$results = $this->db->get()->result(); 
		return $results;
	}

	//get all type of rental list
	public function getTypeOfRentals(){
		$this->db->select('*');
		$this->db->from($this->table_type_of_rentals);
		$results = $this->db->get()->result(); 
		return $results;
	}

	//get all amenties list
	public function getPropAmenities(){
		$this->db->select('*');
		$this->db->from($this->table_amenities);
		$results = $this->db->get()->result(); 
		return $results;
	}

	//get property by user id using listed_by column
	public function getPropertyByUserID($listed_by){
		if($listed_by){
			$this->db->where('listed_by', $listed_by);
			$this->db->where('deleted', false);
			$this->db->where('activate', 'activated');
			$this->db->order_by('id',"desc");
			$q = $this->db->get($this->table_name);
			$results = $q->result();
			foreach ($results as $key => $value) {
				$value->favorite = false;
				if(!empty($_POST['fav_list'])){
					if(in_array($value->id, $_POST['fav_list'])){
						$value->favorite = true;
					}
				}
				$user = $this->UserModel->getUserByID($value->listed_by);
				$yed_contact_info = $this->UserModel->getYedContactInfo();
				if(!empty($user)){
					$role = $user[0]['role'];
					if($role == 'agent'){
						$value->contact_email = $user[0]['email'];
						$value->contact_phone = $user[0]['phone'];
					}else{
						$value->contact_email = $yed_contact_info[0]['email'];
						$value->contact_phone = $yed_contact_info[0]['phone'];
					}
				}
				$purpose = '';
				if($value->purpose == 'sale'){
					$purpose = 'À Vendre';
				}elseif($value->purpose == 'rent'){
					$purpose = 'À Louer';
				}else{
					$purpose = '';
				}

				$results[$key]->purpose = $purpose;
				
				if($results[$key]->featured){
					if(!$results[$key]->wp_id){
						if(file_exists(UPLOAD_PROPERTIES_DIR.$results[$key]->featured)) {
							$results[$key]->featured = base_url('uploads/properties/'.$results[$key]->featured);
						}else{
							$results[$key]->featured = AWS_PROPERTIES_URL.$results[$key]->featured;
						}
					}
				}else{
					$results[$key]->featured = null;
				}

				$results[$key]->image = json_decode($value->image, JSON_UNESCAPED_SLASHES);

				if(!empty($results[$key]->image)){
					foreach ($results[$key]->image as $key2 => $image) {

						if(isset($image['name'])){
							if(file_exists(UPLOAD_PROPERTIES_DIR.$image['name'])) {
								$results[$key]->image[$key2]['url'] = base_url('uploads/properties/'.$image['name']);
							}else{
								$results[$key]->image[$key2]['url'] = AWS_PROPERTIES_URL.$image['name'];
							}
						} 
					}
				}
				$featured_array = [];

				if($results[$key]->featured){				
					$featured_array = [
						"name" => "",
						"type" => "",
						"url" => $results[$key]->featured
					];
				}
				if(!empty($featured_array)){
					if(!empty($results[$key]->image)){
						array_unshift($results[$key]->image, $featured_array);
					}else{
						$results[$key]->image[] = $featured_array;
					}
				}
				$results[$key]->title = trim(html_entity_decode($value->title));
				$results[$key]->description = trim(html_entity_decode($value->description));
				if($results[$key]->price_per_month){
					$results[$key]->currency = $results[$key]->currency.'/Mois';
				}
				$results[$key]->price = (int)$value->price > 0 ? str_replace(',', " ", number_format((int)$value->price)) : "";
				$results[$key]->price_per_month = (int)$value->price_per_month > 0 ? str_replace(',', " ", number_format((int)$value->price_per_month)): "";
				$results[$key]->type = $value->type ? explode(',',$value->type): [];
				$results[$key]->approve = $value->approve == "1" ? true: false;
				$results[$key]->amenities = $value->amenities ? explode(',', $value->amenities) : [];
			}
			return $results;
		}
	}
	// Shorten large numbers into abbreviations (i.e. 1,500 = 1.5k)
	/*function priceNumberAbbreviation($number) {
	    $abbrevs = array(12 => "T", 9 => "B", 6 => "M", 0 => "");

	    foreach($abbrevs as $exponent => $abbrev) {
	        if($number >= pow(10, $exponent)) {
	        	$display_num = $number / pow(10, $exponent);

	        	$decimals = ($exponent >= 3 && round($display_num) < 100) ? 1 : 0;
	        	
            	return number_format($display_num,$decimals) . $abbrev;
	        	
	        }
	    }
	    return $number;
	}*/

public function getPostByUsrId($listed_by){
	if($listed_by){
			$this->db->where('listed_by', $listed_by);
			$this->db->where('deleted', false);
			$this->db->where('activate', 'activated');
			$this->db->order_by('id',"desc");
			$q = $this->db->get($this->table_name);
			$results = $q->result();
			foreach ($results as $key => $value) {
				$value->favorite = false;
				if(!empty($_POST['fav_list'])){
					if(in_array($value->id, $_POST['fav_list'])){
						$value->favorite = true;
					}
				}
				$user = $this->UserModel->getUserByID($value->listed_by);
				$yed_contact_info = $this->UserModel->getYedContactInfo();
				if(!empty($user)){
					$role = $user[0]['role'];
					if($role == 'agent'){
						$value->contact_email = $user[0]['email'];
						$value->contact_phone = $user[0]['phone'];
					}else{
						$value->contact_email = $yed_contact_info[0]['email'];
						$value->contact_phone = $yed_contact_info[0]['phone'];
					}
				}
				$purpose = '';
				if($value->purpose == 'sale'){
					$purpose = 'À Vendre';
				}elseif($value->purpose == 'rent'){
					$purpose = 'À Louer';
				}else{
					$purpose = '';
				}

				$results[$key]->purpose = $purpose;
				
				if($results[$key]->featured){
					if(!$results[$key]->wp_id){
						if(file_exists(UPLOAD_PROPERTIES_DIR.$results[$key]->featured)) {
							$results[$key]->featured = base_url('uploads/properties/'.$results[$key]->featured);
						}else{
							$results[$key]->featured = AWS_PROPERTIES_URL.$results[$key]->featured;
						}
					}
				}else{
					$results[$key]->featured = null;
				}

				$results[$key]->image = json_decode($value->image, JSON_UNESCAPED_SLASHES);

				if(!empty($results[$key]->image)){
					foreach ($results[$key]->image as $key2 => $image) {

						if(isset($image['name'])){
							if(file_exists(UPLOAD_PROPERTIES_DIR.$image['name'])) {
								$results[$key]->image[$key2]['url'] = base_url('uploads/properties/'.$image['name']);
							}else{
								$results[$key]->image[$key2]['url'] = AWS_PROPERTIES_URL.$image['name'];
							}
						} 
					}
				}
				$featured_array = [];

				if($results[$key]->featured){				
					$featured_array = [
						"name" => "",
						"type" => "",
						"url" => $results[$key]->featured
					];
				}
				if(!empty($featured_array)){
					if(!empty($results[$key]->image)){
						array_unshift($results[$key]->image, $featured_array);
					}else{
						$results[$key]->image[] = $featured_array;
					}
				}
				$results[$key]->title = trim(html_entity_decode($value->title));
				$results[$key]->description = trim(html_entity_decode($value->description));
				if($results[$key]->price_per_month){
					$results[$key]->currency = $results[$key]->currency.'/Mois';
				}
				$results[$key]->price = (int)$value->price > 0 ? str_replace(',', " ", number_format((int)$value->price)) : "";
				$results[$key]->price_per_month = (int)$value->price_per_month > 0 ? str_replace(',', " ", number_format((int)$value->price_per_month)): "";
				$results[$key]->type = $value->type ? explode(',',$value->type): [];
				$results[$key]->approve = $value->approve == "1" ? true: false;
				$results[$key]->amenities = $value->amenities ? explode(',', $value->amenities) : [];
			}
			return $results;
		}
}

}
?>
