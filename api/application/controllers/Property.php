<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');
require_once APPPATH.'vendor/jwt/JWT.php';
require_once APPPATH.'vendor/jwt/SignatureInvalidException.php';
require_once APPPATH.'vendor/jwt/BeforeValidException.php';
require_once APPPATH.'vendor/jwt/ExpiredException.php';
require_once APPPATH.'vendor/jwt/JWK.php';
use \Firebase\JWT\JWT;

class Property extends CI_Controller {

    public function __construct(){
		parent::__construct();
		$this->load->model('PropertyModel'); //Load the Model hash(algo, data)ere 
        $this->load->model('UserModel');
        $this->load->helper('cookie');
        $this->load->helper(array('form', 'url'));
        $this->load->library('s3_upload');
        $this->load->library('s3_delete');
	}
  
	//add property
	public function add()
	{
        $headers = $_SERVER;
        //check for authorization header
        if (isset($headers['HTTP_AUTHORIZATION'])){
            //decode auth header token
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
            //get current user using token email
            $user = $this->UserModel->getUserByTokens($decode->user);

            //check if auth header access token is equal to original token 
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
                $current_time_stamp = strtotime(date('Y-m-d H:i:s'));
                if($decode->exp > $current_time_stamp){ //check token expire time
                    if($this->input->server('REQUEST_METHOD') !== 'POST'){
                        echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
                        return false;
                    }
                    
                     $json = file_get_contents('php://input');
                  
                    // Converts it into a PHP object
                    $_POST = (array)json_decode($json);

                    $errors = [];
            		if(isset($_POST) && !empty($_POST)){
                        //validate title
                        if(!isset($_POST['title']) || empty($_POST['title'])){
                            $errors['error'] = true;
                            $errors['message'][] = 'Titre';
                        }
                        //validate description
                        if(!isset($_POST['description']) || empty($_POST['description'])){
                            $errors['error'] = true;
                            $errors['message'][] = 'Description';
                        }
                        //validate price
                        if($_POST['purpose'] == 'sale'){
                            $_POST['price_per_month'] = '';
                            if(!isset($_POST['price']) || empty($_POST['price'])){
                                $errors['error'] = true;
                                $errors['message'][] = 'Prix';
                            }
                        }
                        //validate price per month
                        if($_POST['purpose'] == 'rent'){
                            $_POST['price'] = '';
                            if(!isset($_POST['price_per_month']) || empty($_POST['price_per_month'])){
                                $errors['error'] = true;
                                $errors['message'][] = 'Prix/Mois';
                            }
                        }
                         if(!isset($_POST['featured']) || empty($_POST['featured'])){
                            $errors['error'] = true;
                            $errors['message'][] = 'Image à la une';
                        }

                                               
                        //get current user
                        $userdata = $this->UserModel->getUserByEmail($decode->user);
                        $_POST['listed_by'] = $userdata[0]['id'];
                        $_POST['user_role'] = $userdata[0]['role'];
                        $_POST['username'] = $userdata[0]['name'];
                        if($_POST['user_role'] == 'agent'){
                            $_POST['approve'] = false;
                        }elseif($_POST['user_role'] == 'individual'){
                            $_POST['approve'] = false;
                        }else{
                            $_POST['approve'] = false;
                        }
                        //if no errors
                        if(empty($errors)){
                            $allFiles = [];
                           
                            if (isset($_POST['image']) && !empty($_POST['image'])) {
                                $_POST['image'] = $_POST['image'];
                                $filesCount = count($_POST['image']);
                                //callx upload function    
                                
                                for($i = 0; $i < $filesCount; $i++){ 

                                    $image_parts = explode(";base64,", $_POST['image'][$i]);
                                    $image_type_aux = explode("image/", $image_parts[0]);
                                    $image_type = $image_type_aux[1];
                                    $image_base64 = base64_decode($image_parts[1]);
                                    $image_name = uniqid() . '.png';
                                    $file = UPLOAD_PROPERTIES_DIR . $image_name;
                                    file_put_contents($file, $image_base64);
                                    $this->s3_upload->upload_file($file, 'properties'); //upload to S3
                                    if(file_exists(UPLOAD_PROPERTIES_DIR . $image_name)) {
                                        unlink(UPLOAD_PROPERTIES_DIR . $image_name); //delete from local folder
                                    }
                                    $image_url = AWS_PROPERTIES_URL.$image_name;

                                    $allFiles[$i]['name'] = $image_name;
                                    $allFiles[$i]['type'] = "";
                                    $allFiles[$i]['url'] = $image_url;                                    
                                } 

                                if(count($allFiles) > 0){
                                    $_POST['image'] = json_encode($allFiles, JSON_UNESCAPED_SLASHES);
                                }else{
                                    $_POST['image'] = "";
                                }
                            }else{
                                $_POST['image'] = "";
                            }
                            if (isset($_POST['featured']) && !empty($_POST['featured'])) {
                                
                                $image_parts = explode(";base64,", $_POST['featured']);
                                $image_type_aux = explode("image/", $image_parts[0]);
                                $image_type = $image_type_aux[1];
                                $image_base64 = base64_decode($image_parts[1]);
                                $image_name = uniqid() . '.png';
                                $file = UPLOAD_PROPERTIES_DIR . $image_name;
                                file_put_contents($file, $image_base64);  //move to local folder
                                $this->s3_upload->upload_file($file, 'properties'); //upload to S3
                                if(file_exists(UPLOAD_PROPERTIES_DIR . $image_name)) {
                                    unlink(UPLOAD_PROPERTIES_DIR . $image_name); //delete from local folder
                                }
                                $_POST['featured'] = $image_name;
                            }
                            if(isset($_POST['location_tags'])){

                                $_POST['location_tags'] = implode(',',$_POST['location_tags']);
                            }
                            if(isset($_POST['type'])){

                                $_POST['type'] = implode(',',$_POST['type']);
                            }
                           
                            if(isset($_POST['type_of_rental'])){

                                $_POST['type_of_rental'] = $_POST['type_of_rental'];
                            }
                            if(isset($_POST['amenities'])){

                                $_POST['amenities'] = implode(',',$_POST['amenities']);                             
                            }
                            //insert property here
                            $this->PropertyModel->insert($_POST);
                             
            			}else{
            				if(isset($errors['message'])){
                                $errors['message'] = implode(', ', $errors['message']).' est requis.'; 
                            }
                            echo json_encode($errors);
            			}
                    }else{
                        //if error then show message
                        echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
                    }
                }else{ //if token expired
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
        }else{ //if no token exists
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            echo json_encode($validation);
        }
    }
    //update property
    public function update()
    {
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
                        if(!isset($_POST['title']) || empty($_POST['title'])){
                            $errors['error'] = true;
                            $errors['message'][] = 'Titre';
                        }
                        //validate description
                        if(!isset($_POST['description']) || empty($_POST['description'])){
                            $errors['error'] = true;
                            $errors['message'][] = 'Description';
                        }
                        //validate price
                        if($_POST['purpose'] == 'sale'){
                            $_POST['price_per_month'] = "";
                            if(!isset($_POST['price']) || empty($_POST['price'])){
                                $errors['error'] = true;
                                $errors['message'][] = 'Prix';
                            }
                        }
                        //validate price per month
                        if($_POST['purpose'] == 'rent'){
                            $_POST['price'] = "";
                            if(!isset($_POST['price_per_month']) || empty($_POST['price_per_month'])){
                                $errors['error'] = true;
                                $errors['message'][] = 'Prix/Mois';
                            }
                        }
                        // if(empty($_POST['featured']) && empty($_POST['featured_image'])){
                        //     $errors['error'] = true;
                        //     $errors['message'][] = 'Image à la une';
                        // }
                      
                        if(empty($errors)) {
                            $allFiles = [];
                            if (isset($_POST['image']) && !empty($_POST['image'])) {
                                $_POST['image'] = $_POST['image'];
                                $filesCount = count($_POST['image']);
                                //callx upload function    
                                
                                for($i = 0; $i < $filesCount; $i++){ 

                                    $image_parts = explode(";base64,", $_POST['image'][$i]);
                                    $image_type_aux = explode("image/", $image_parts[0]);
                                    $image_type = $image_type_aux[1];
                                    $image_base64 = base64_decode($image_parts[1]);
                                    $image_name = uniqid() . '.png';
                                    $file = UPLOAD_PROPERTIES_DIR . $image_name;
                                    file_put_contents($file, $image_base64);
                                    $this->s3_upload->upload_file($file, 'properties'); //upload to S3
                                    if(file_exists(UPLOAD_PROPERTIES_DIR . $image_name)) {
                                        unlink(UPLOAD_PROPERTIES_DIR . $image_name); //delete from local folder
                                    }
                                    $image_url = AWS_PROPERTIES_URL.$image_name;

                                    $allFiles[$i]['name'] = $image_name;
                                    $allFiles[$i]['type'] = "";
                                    $allFiles[$i]['url'] = $image_url;                                    
                                } 

                                if(count($allFiles) > 0){
                                    foreach ($allFiles as $key => $value) {
                                        if(!empty($_POST['gallery'])){
                                            array_push($_POST['gallery'], $value);
                                        }else{
                                            $_POST['gallery'] = [];
                                            array_push($_POST['gallery'], $value);
                                        }
                                    }
                                    $_POST['image']  = json_encode($_POST['gallery']);
                                    
                                }else{
                                    unset($_POST['image']);
                                }
                            }else{
                                //unset($_POST['image']);
                                $_POST['image'] = json_encode($_POST['gallery']);
                            }                    
                           
                            if (isset($_POST['featured']) && !empty($_POST['featured'])) {
                                
                                $image_parts = explode(";base64,", $_POST['featured']);
                                $image_type_aux = explode("image/", $image_parts[0]);
                                $image_type = $image_type_aux[1];
                                $image_base64 = base64_decode($image_parts[1]);
                                $image_name = uniqid() . '.png';
                                $file = UPLOAD_PROPERTIES_DIR . $image_name;
                                file_put_contents($file, $image_base64);
                                $this->s3_upload->upload_file($file, 'properties'); //upload to S3
                                if(file_exists(UPLOAD_PROPERTIES_DIR . $image_name)) {
                                    unlink(UPLOAD_PROPERTIES_DIR . $image_name); //delete from local folder
                                }   
                                $property = $this->PropertyModel->getPropertyByID($_POST['id']);
                                if($property[0]['featured']){
                                    $old_featured = $property[0]['featured'];
                                    if(file_exists(UPLOAD_PROPERTIES_DIR . $old_featured)) {
                                        unlink(UPLOAD_PROPERTIES_DIR . $old_featured);
                                    }
                                }
                               // $this->s3_delete->delete_file($old_featured, 'properties'); //delete from s3 bucket
                                $_POST['featured'] = $image_name;
                            }

                            if(isset($_POST['location_tags']) && !empty($_POST['location_tags'])){

                                $_POST['location_tags'] = implode(',',$_POST['location_tags']);
                            }else{
                                $_POST['location_tags'] = "";
                            }
                            if(isset($_POST['type']) && !empty($_POST['type'])){

                                $_POST['type'] = implode(',',$_POST['type']);
                            }
                           
                            if(isset($_POST['type_of_rental']) && !empty($_POST['type_of_rental'])){

                                $_POST['type_of_rental'] = $_POST['type_of_rental'];
                            }
                            if(isset($_POST['amenities']) && !empty($_POST['amenities'])){

                                $_POST['amenities'] = implode(',',$_POST['amenities']);                             
                            }else{
                                $_POST['amenities'] = NULL;
                            }    
                            if(isset($_POST['featured_image'])){
                                if($_POST['featured'] == ""){
                                    $_POST['featured'] = explode('/',$_POST['featured_image']);
                                    $_POST['featured'] = end($_POST['featured']);
                                }
                                unset($_POST['featured_image']);
                            }
                            unset($_POST['featured_image']);
                            unset($_POST['gallery']);                            
                            $this->PropertyModel->update($_POST, $_POST['id']);
                             
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
    public function getproperty()
    {
        $headers = $_SERVER;
        
        if (isset($headers['HTTP_AUTHORIZATION']) && !empty($headers['HTTP_AUTHORIZATION'])){
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
            $user = $this->UserModel->getUserByTokens($decode->user);

            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
                $current_time_stamp = strtotime(date('Y-m-d H:i:s'));
                if($decode->exp > $current_time_stamp){ 
                    if($this->input->server('REQUEST_METHOD') !== 'POST'){
                        echo json_encode(['error' => true, 'status' => 405, 'type' => 'method', 'message' => 'Method not allowed!']);
                        return false;
                    }
                }else{
                    $validation = [
                        "type" => "Expired",
                        "error" => "Token Expired"
                    ];
                   // echo json_encode($validation);
                }

                }else{
                
                $validation = [
                    "type" => "Invalid",
                    "error" => "Token Invalid"
                ];
               /// echo json_encode($validation);
            }
                     }else{
            $validation = [
                "status" => 401,
                "type" => "Unauthorized"
            ];
            //echo json_encode($validation);
        }
                    
                    $errors = [];
                     $json = file_get_contents('php://input');
                  
                    // Converts it into a PHP object
                    $_POST = (array)json_decode($json);

                    if(isset($_POST) && !empty($_POST)){
                        $property = $this->PropertyModel->getPropertyByID($_POST['id']);

                        if($property[0]['featured']){
                            if(!$property[0]['wp_id']){
                                if(file_exists(UPLOAD_PROPERTIES_DIR.$property[0]['featured'])) {
                                    $property[0]['featured'] = base_url('uploads/properties/'.$property[0]['featured']);
                                }else{
                                    $property[0]['featured'] = AWS_PROPERTIES_URL.$property[0]['featured'];
                                }
                            }
                        }
                        
                        $propertyOwner = $this->UserModel->getUserByID($property[0]['listed_by']);
                        $property[0]['user_email'] = $propertyOwner[0]['email'];
                        //decode json images to arary
                        $property[0]['image'] =  json_decode($property[0]['image'], JSON_UNESCAPED_SLASHES);

                        if(!empty($property[0]['image'])){
                            foreach ($property[0]['image'] as $key2 => $image) {

                                if($image['name']){
                                    if(file_exists(UPLOAD_PROPERTIES_DIR.$image['name'])){
                                        $property[0]['image'][$key2]['url'] = base_url('uploads/properties/'.$image['name']);
                                    }else{
                                        $property[0]['image'][$key2]['url'] = AWS_PROPERTIES_URL.$image['name'];
                                    }
                                    
                                } 
                            }
                        }else{
                            $property[0]['image'] = null;
                        }
                        $featured_array = [];

                        if($property[0]['featured']){               
                            $featured_array = [
                                "name" => "",
                                "type" => "",
                                "url" => $property[0]['featured']
                            ];
                        }
                        if(!empty($featured_array)){
                            if(!empty($property[0]['image'])){
                                array_unshift($property[0]['image'], $featured_array);
                            }else{
                                $property[0]['image'][] = $featured_array;
                            }
                        }

                        $purpose = '';
                        if($property[0]['purpose'] == 'sale'){
                            $purpose = 'À Vendre';
                        }elseif($property[0]['purpose'] == 'rent'){
                            $purpose = 'À Louer';
                        }else{
                            $purpose = '';
                        }

                        $property[0]['purposeName'] = $purpose;
                        //check if price per month exists
                        if($property[0]['price_per_month']){
                            $property[0]['currency'] = $property[0]['currency'].'/Mois'; //set currency in per month format
                        }
                        $property[0]['price'] = (int)$property[0]['price'] > 0 ? str_replace(',', " ", number_format((int)$property[0]['price'])) : "";
                        $property[0]['price_per_month'] = (int)$property[0]['price_per_month'] > 0 ? str_replace(',', " ", number_format((int)$property[0]['price_per_month'])): "";
                        //make type string to array
                        $property[0]['location_tags'] =  $property[0]['location_tags'] ? explode(',' , $property[0]['location_tags']) : [];
                        $property[0]['type'] =  explode(',' , $property[0]['type']); 
                        //set approve property response as bolean 
                        $property[0]['approve'] = $property[0]['approve'] == "1" ? true: false; 
                        $property[0]['amenities'] = $property[0]['amenities'] ? explode(',' , $property[0]['amenities']) : [];

                        echo json_encode(["details" => $property[0]]);
                        
                    }else{
                        echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
                    }
             
           
       
    }
    
    //update views count
    public function viewcount()
    {
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
                        $user = $this->UserModel->getUserByEmail($decode->user);
                        $viewcount = $this->PropertyModel->updateViewCount($user[0]['id'], $_POST['property_id']);
                     
                        echo json_encode($viewcount);
                        
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
            
            $json = file_get_contents('php://input');
              
            // Converts it into a PHP object
            $_POST = (array)json_decode($json);

            if(isset($_POST) && !empty($_POST)){
                $viewcount = $this->PropertyModel->updateViewCount($_POST['uuid'], $_POST['property_id']);
             
                echo json_encode($viewcount);
                
            }else{
                echo json_encode(['error' => true, 'message' => 'Veuillez remplir les champs obligatoires!']);
            }
        }
    }

    //delete property
    public function delete()
    {
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
                        $delete = $this->PropertyModel->deleteProperty($_POST['property_id']);
                        echo $delete;
                        
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

    //search properties 
	public function search()
	{

        $errors = [];
        $headers = $_SERVER;
        $fav_list = "";
        $error = false;
        if (isset($headers['HTTP_AUTHORIZATION']) && !empty($headers['HTTP_AUTHORIZATION'])) {
            $decode = JWT::decode($headers['HTTP_AUTHORIZATION'], SECRET_KEY, array(ALGORITHM));
            $user = $this->UserModel->getUserByTokens($decode->user);
            if($headers['HTTP_AUTHORIZATION'] == $user[0]['access_token']){
                $current_time_stamp = strtotime(date('Y-m-d H:i:s'));
                if($decode->exp > $current_time_stamp){ 
                    $user = $this->UserModel->getUserByEmail($decode->user);
                    $favorite_list = $user[0]['favorites'];
                    if(!empty($favorite_list)){
                        $fav_list = $favorite_list;
                    }else{
                        $fav_list = "";
                    }
                    
                }else{
                    $error = true;
                     $validation = [
                        "type" => "Expired",
                        "error" => "Token Expired"
                    ];
                    echo json_encode($validation);
                }
            }else{
                $error = true;
                $validation = [
                    "type" => "Invalid",
                    "error" => "Token Invalid"
                ];
                echo json_encode($validation);
            }
        }else{
            $fav_list = "";
            $error = false;
        }
        if($error == false){
            $json = file_get_contents('php://input');
            $_POST = (array)json_decode($json);
            // Converts it into a PHP object
            if($this->input->server('REQUEST_METHOD') == 'POST'){


                $_POST['fav_list'] = $fav_list ? explode(",", $fav_list): "";
                $type = 0;
                $bedroom_min = 0;
                $bedroom_max = 0;
                $bathroom_min = 0;
                $bathroom_max = 0;
                $location_tags = 0;
                $area_min = 0;
                $area_max = 0;
                $price_min = 0;
                $price_max = 0;
                $latitude = 0;
                $longitude = 0;
                $purpose = 0;
                $user_role = 0;
                $type_of_rental = 0;
                $name = 0;

                if(isset($_POST['number_of_bedrooms']) && !empty($_POST['number_of_bedrooms'])){
                    $_POST['bedroom_max'] = $_POST['number_of_bedrooms'];
                    $_POST['bedroom_min'] = "1";
                }
                if(isset($_POST['number_of_bathrooms']) && !empty($_POST['number_of_bathrooms'])){              
                    $_POST['bathroom_max'] = $_POST['number_of_bathrooms'];
                    $_POST['bathroom_min'] = "1";
                }
                
                if(isset($_POST['name']) && !empty($_POST['name'])){
                    $name = 1;
                }

                if(isset($_POST['type']) && !empty($_POST['type'])){
                    $type = 1;
                }
                if(isset($_POST['type_of_rental']) && !empty($_POST['type_of_rental'])){
                    $type_of_rental = 1;
                }
                if(isset($_POST['purpose']) && !empty($_POST['purpose'])){
                    $purpose = 1;
                }
                if(isset($_POST['user_role']) && !empty($_POST['user_role'])){
                    $user_role = 1;
                }
                if(isset($_POST['location_tags']) && !empty($_POST['location_tags'])){
                    $location_tags = 1;
                }
                if(isset($_POST['area_min']) && !empty($_POST['area_max'])){
                    $area_min = 1;
                }
                if(isset($_POST['area_max']) && !empty($_POST['area_max'])){
                    $area_max = 1;
                }
                if(isset($_POST['bedroom_min']) && !empty($_POST['bedroom_min'])){
                    $bedroom_min = 1;
                }
                if(isset($_POST['bedroom_max']) && !empty($_POST['bedroom_max'])){
                    $bedroom_max = 1;
                }
                if(isset($_POST['bathroom_min']) && !empty($_POST['bathroom_min'])){
                    $bathroom_min = 1;
                }
                if(isset($_POST['bathroom_max']) && !empty($_POST['bathroom_max'])){
                    $bathroom_max = 1;
                }
                if(isset($_POST['price_min']) && !empty($_POST['price_min'])){
                    $price_min = 1;
                }
                if(isset($_POST['price_max']) && !empty($_POST['price_max'])){
                    $price_max = 1;
                }
                if(isset($_POST['latitude']) && !empty($_POST['latitude'])){
                    $latitude = 1;
                }
                if(isset($_POST['longitude']) && !empty($_POST['longitude'])){
                    $longitude = 1;
                }

                
                $this->PropertyModel->search($_POST, 'searchForm');
                
            }else{
                $this->PropertyModel->search($_GET, 'getAll');
            }
        }
        
           
    }

    public function types(){
        $types = $this->PropertyModel->getPropTypes();
        echo json_encode($types);
    }
    public function rentaltypes(){
        $rentaltypes = $this->PropertyModel->getTypeOfRentals();
        echo json_encode($rentaltypes);
    }
    public function amenities(){
        $amenities = $this->PropertyModel->getPropAmenities();
        echo json_encode($amenities);  
    }

    /*public function getFromWP(){
        
        $properties = file_get_contents('https://www.yedexpertises.com/wp-json/wp/v2/property?per_page=50');
        $properties = json_decode($properties);
        $site_props = [];
        $allFiles = [];
        
        foreach ($properties as $key => $property) {
            
            $site_props[$key]['title'] = $property->title->rendered;
            $property->content->rendered = strip_tags($property->content->rendered);
            $property->content->rendered = trim(str_replace(array("\n", "\r"), ' ', $property->content->rendered));
            $site_props[$key]['description'] = $property->content->rendered;
            $site_props[$key]['price'] = $property->real_estate_property_price_short ? $property->real_estate_property_price_short * $property->real_estate_property_price_unit : 0;
            $site_props[$key]['area'] = $property->real_estate_property_size;
            $site_props[$key]['number_of_bedrooms'] = $property->real_estate_property_bedrooms;
            $site_props[$key]['number_of_bathrooms'] = $property->real_estate_property_bathrooms;
            $site_props[$key]['location'] = $property->real_estate_property_address;

            $featured_image = isset($property->_links->{'wp:featuredmedia'}) ? file_get_contents($property->_links->{'wp:featuredmedia'}[0]->href) : 0;
            
            if($featured_image){
                $featured_image = json_decode($featured_image);
                $allFiles[0]['name'] = '';
                $allFiles[0]['type'] = '';
                $allFiles[0]['url'] = $featured_image->guid->rendered;
            }
            $gallery_images = $property->real_estate_property_images;
            if($gallery_images){
                $gallery_images =  explode('|', $gallery_images);
                foreach ($gallery_images as $key => $value) {
                    $image = file_get_contents('https://www.yedexpertises.com/wp-json/wp/v2/media/'.$value);
                    $image = json_decode($image);
                    if(!empty($allFiles)){
                        $allFiles[$key + 1]['name'] = '';
                        $allFiles[$key + 1]['type'] = '';
                        $allFiles[$key + 1]['url'] = $image->guid->rendered;
                    }
                }
            }
            $site_props[$key]['image'] = json_encode($allFiles, JSON_UNESCAPED_SLASHES);   
        }

       
        foreach ($site_props as $key => $value) {
            $this->PropertyModel->insert($value);
        }
    }*/
    public function insertfromwp()
    {
       
        $json = file_get_contents('php://input');
                  
        // Converts it into a PHP object
        $_POST = (array)json_decode($json);
        $_POST['type'] = implode(',',$_POST['type']);
        $_POST['amenities'] = implode(',',$_POST['amenities']); 
        $userdata = $this->UserModel->getAdmin();
        $_POST['listed_by'] = $userdata[0]['id'];
        $_POST['user_role'] = $userdata[0]['role'];
        $_POST['approve'] = true;
        $_POST['username'] = $userdata[0]['name'];
        $this->PropertyModel->insertFromWP($_POST);             
    }
    public function updatefromwp()
    {
        $json = file_get_contents('php://input');                  
        // Converts it into a PHP object
        $_POST = (array)json_decode($json);
        $_POST['type'] = implode(',',$_POST['type']);
        $_POST['amenities'] = implode(',',$_POST['amenities']); 
        $this->PropertyModel->updateFromWP($_POST);             
    }
    public function deletefromwp()
    {
        $json = file_get_contents('php://input');                  
        // Converts it into a PHP object
        $_POST = (array)json_decode($json); 
        $this->PropertyModel->deleteFromWP($_POST);             
    }

}

?>