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
	
</head>
<body>
	<div class="container">
		<div class="col-md-12">
			<div class="col-md-3">&nbsp;</div>
			<div class="col-md-6">
				<div class="logo" style="text-align: center;">
					<img width="100%" src="<?php echo base_url('assets/images/logo.jpg'); ?>">
				</div>
				<?php 
				
				if(isset($_SESSION['update']) && $_SESSION['update'] == true){ 
				echo '<div class="alert alert-success">
					  Félicitations! Votre compte à été vérifié avec succès.
					</div>';
				
				}?>
			</div>
			<div class="col-md-3">&nbsp;</div>

		</div>
	</div>
</body>
</html>