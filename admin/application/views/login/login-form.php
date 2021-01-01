<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gentelella Alela! | </title>

    <!-- Bootstrap -->
    <link href="<?php echo base_url('assets/vendors/bootstrap/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo base_url('assets/vendors/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?php echo base_url('assets/vendors/nprogress/nprogress.css') ?>" rel="stylesheet">
    <!-- Animate.css -->
    <link href="<?php echo base_url('assets/vendors/animate.css/animate.min.css'); ?>" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?php echo base_url('assets/build/css/custom.css') ?>" rel="stylesheet">
  </head>

  <body class="login">
        <div class="login_wrapper">
            <div class="animate form login_form">
            <section class="login_content">
            <?php if(isset($error)){  ?>
                <div class="alert alert-danger alert-dismissible " role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                    </button>
                    <?php echo $error; ?>
                </div>
            <?php } ?>
                <form method="post" id="login-form" action="<?php echo base_url('login'); ?>">
                <h1>Login</h1>
                <div>
                    <input type="email" class="form-control" placeholder="Email Addrress" name="email" required="" />
                </div>
                <div>
                    <input type="password" class="form-control" placeholder="Password" name="password" required="" />
                </div>
                <div>
                    <button type="submit" name="login-form" class="btn btn-primary submit">Log in</a>
                </div>

                <div class="clearfix"></div>

                <div class="separator">
                    <div class="clearfix"></div>
                    <br />
                    <div>
                    <h1><i class="fa fa-paw"></i> Gentelella Alela!</h1>
                    <p>©2016 All Rights Reserved. Gentelella Alela! is a Bootstrap 3 template. Privacy and Terms</p>
                    </div>
                </div>
                </form>
            </section>
            </div>
      </div>
  </body>
</html>
