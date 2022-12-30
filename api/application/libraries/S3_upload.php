<?php

/**
 * Amazon S3 Upload PHP class
 *
 * @version 0.1
 */
class S3_upload {

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

	function upload_file($file_path, $action)
	{
		// generate unique filename
		$file = pathinfo($file_path);
		$s3_file = $file['filename'].'.'.$file['extension'];
		$mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file_path);

		$folder_name =  $action == 'properties' ? $this->properties_folder_name : $this->profiles_folder_name;
		$saved = $this->CI->s3->putObjectFile(
			$file_path,
			$this->bucket_name,
			$folder_name.'/'.$s3_file,
			S3::ACL_PUBLIC_READ,
			array(),
			$mime_type
		);
		if ($saved) {
			return $this->s3_url.$this->bucket_name.'/'.$folder_name.'/'.$s3_file;
		}
	}

}