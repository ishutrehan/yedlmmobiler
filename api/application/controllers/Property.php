<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Content-Type: application/json');
class Property extends CI_Controller {

    public function __construct(){
		parent::__construct();
		$this->load->model('PropertyModel'); //Load the Model here 
	}
	//add property
	public function add()
	{
        if($this->input->server('REQUEST_METHOD') !== 'POST'){
            echo json_encode(['error' => true, 'type' => 'method', 'message' => 'Method not allowed!']);
            return false;
        }
        $errors = [];
        
		if(isset($_POST) && !empty($_POST)){

            if(empty($errors)){
				$this->PropertyModel->insert($_POST);
			}else{
				echo json_encode($errors);
			}
        }else{
            echo json_encode(['error' => true, 'message' => 'Please fill the required fields!']);
        }
    }

}

?>