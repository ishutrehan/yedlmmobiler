<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?php echo ADD_NEW_USER; ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small><?php echo ADD_NEW_USER; ?></small></h2>
                    <ul class="nav navbar-right pull-right">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                <?php if(isset($_GET['success']) && $_GET['success'] == 'true'){  ?>
                    <div class="alert alert-success alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                        </button>
                        <?php echo "User Added Successfully."; ?>
                    </div>
                <?php } ?>
                <?php if(isset($errors)){  ?>
                    <div class="alert alert-danger alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                        </button>
                        <?php foreach ($errors as $error) {
                            echo $error['message'];
                        } ?>
                    </div>
                <?php } ?>
                    <form id="add-user-form" class="form-horizontal form-label-left" method="post" enctype="multipart/form-data" action="<?php echo base_url('add-user'); ?>">

                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Role <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <select name="role" id="role" class="form-control" required="required">
                                    <option value="">-- Select Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="individual">Individual</option>
                                    <option value="agent">Agent</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="name"><?php echo PROFILE_IMAGE; ?> <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <input type="file" id="profile_image" name="profile_image" />
                                <img src="" width="100px;" id="user_img_display" />
                            </div>
                        </div>
                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name"><?php echo FULL_NAME; ?> <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <input type="text" id="name" name="name" required="required" class="form-control ">
                            </div>
                        </div>
                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="email"><?php echo EMAIL_ADDRESS; ?> <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <input type="email" id="email" name="email" required="required" class="form-control ">
                            </div>
                        </div>
                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="password"><?php echo PASSWORD; ?> <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <input type="password" id="password" name="password" required="required" class="form-control ">
                            </div>
                        </div>
                        <div class="item form-group">
                            <label class="col-form-label col-md-3 col-sm-3 label-align" for="phone"><?php echo PHONE; ?> <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 ">
                                <input type="phone" id="phone" name="phone" required="required" class="form-control ">
                            </div>
                        </div>
                    
                        <div class="ln_solid"></div>
                        <div class="item form-group">
                            <div class="col-md-6 col-sm-6 offset-md-3">
                                <button type="submit" name="add-user-form" class="btn btn-success"><?php echo ADD_USER; ?></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php 
$this->load->view('footer-layout/footer');
?>