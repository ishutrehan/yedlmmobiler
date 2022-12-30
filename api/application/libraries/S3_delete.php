<?php

class S3_delete {

	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('s3');

		$this->CI->config->load('s3config', TRUE);
		$s3_config = $this->CI->config->item('s3config');
		$this->bucket_name = $s3_config['bucket_name'];
		$this->properties_folder_name = $s3_config['properties_folder_name'];
		$this->profiles_folder_name = $s3_config['profiles_folder_name'];
		$this->s3_url = $s3_config['s3_url'];
	}

	function delete_file($filename, $action)
	{
		$folder_name =  $action == 'properties' ? $this->properties_folder_name : $this->profiles_folder_name;
		// generate unique filename
		$delete = $this->CI->s3->deleteObject(
			$this->bucket_name,
			$folder_name.'/'.$filename
		);
		if ($delete) {
			return true;
		}
	}

}