<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminController extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('AdminModel'); //Load the Model here 
	}
	public function index(){
		$this->load->view('dashboard');
	}

}


?>