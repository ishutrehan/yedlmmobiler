<?php  

defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Reset Your Passowrd</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<script src="<?php echo base_url('assets/js/js-validate.js'); ?>"></script>
	<style type="text/css">
		label.error{
			color: #f00000;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="col-md-12">
			<div class="col-md-3">&nbsp;</div>
			<div class="col-md-6">
				<div class="logo" style="text-align: center;">
					<img src="<?php echo base_url('assets/images/logo_expert.png'); ?>">
				</div>
				<?php 
				
				if(!isset($_SESSION['update'])){ 
				$test = explode("resetpassword/",$_SERVER['REQUEST_URI']);


			?>
				
				<form method="post" id="resetForm">
					<div class="form-group">
						<label>New Password</label>
						<input type="password" name="password" id="new_password" class="form-control">
					
					</div>
					<div class="form-group">
						<label>Confirm Password</label>
						<input type="password" name="confirm_password" class="form-control">
					</div>
					<div class="form-group">
						<input type="submit" class="btn btn-warning btn-md" name="reset_password" value="Reset Password">
					</div>
					
				</form>
			<?php }else{
				echo '<div class="alert alert-success">
					  <strong>Success!</strong> Your password reset succussfully.
					</div>';
			} ?>
			</div>
			<div class="col-md-3">&nbsp;</div>

		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function () {

		    $('#resetForm').validate({ // initialize the plugin
		        rules: {
		            'password': {
		                required: true
		            },
		            'confirm_password': {
		                required: true,
		                equalTo: "#new_password"
		            }
		        }
		    });
		});
	</script>
</body>
</html>