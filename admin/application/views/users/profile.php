<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?php echo MY_PROFILE; ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small><?php echo MY_PROFILE_DATA; ?></small></h2>
                    <ul class="nav navbar-right pull-right">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>


                <div class="x_content">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php 
                            if(isset($_GET) && isset($_GET['success'])){ ?>
                                <div class="alert alert-success alert-dismissible">
                                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                  <strong>Success!</strong> Profile updated successfully.
                                </div>
                            <?php }
                            ?>
                            <form id="user-profile-form" class="form-horizontal form-label-left" method="post" enctype="multipart/form-data">
                                <input type="hidden" id="user_id" name="id" value="<?php echo $loggedinuser[0]['id']; ?>">
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Role <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 ">
                                        <select name="role" id="role" class="form-control" required="required" disabled="disabled" style="cursor: not-allowed;">
                                            <option value="">-- Select Role --</option>
                                            <option value="admin" <?php if($loggedinuser[0]['role'] == 'admin') { echo "selected"; } ?>>Admin</option>
                                            <option value="individual" <?php if($loggedinuser[0]['role'] == 'individual') { echo "selected"; } ?>>Individual</option>
                                            <option value="agent" <?php if($loggedinuser[0]['role'] == 'agent') { echo "selected"; } ?>>Agent</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="name"><?php echo PROFILE_IMAGE; ?> <span class="required">*</span>
                                    </label>
                                    <?php 
                                        $profile_url  = '';
                                        if(file_exists(UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'])){
                                            $profile_url = UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'];
                                        }else{
                                            $profile_url = AWS_PROFILES_URL.$loggedinuser[0]['profile_image'];
                                        }

                                    ?>
                                    <div class="col-md-6 col-sm-6 ">
                                        <input type="file" id="profile_image" name="profile_image" />
                                        <img src="<?php echo $profile_url; ?>" width="100px;" id="user_img_display" />
                                    </div>
                                </div>
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name"><?php echo NAME; ?>  <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 ">
                                        <input type="text" id="name" name="name" required="required" class="form-control" value="<?php echo $loggedinuser[0]['name']; ?>">
                                    </div>
                                </div>
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="email"><?php echo EMAIL_ADDRESS; ?>  <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 ">
                                        <input type="email" id="email" name="email" required="required" class="form-control" value="<?php echo $loggedinuser[0]['email']; ?>" disabled="disabled">
                                    </div>
                                </div>
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="phone"><?php echo PHONE; ?>  <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 ">
                                        <input type="phone" id="phone" name="phone" required="required" class="form-control"  value="<?php echo $loggedinuser[0]['phone']; ?>">
                                    </div>
                                </div>
                            
                                <div class="ln_solid"></div>
                                <div class="item form-group">
                                    <div class="col-md-6 col-sm-6 offset-md-3">
                                        <button type="submit" name="update-profile" class="btn btn-success"><?php echo UPDATE_PROFILE; ?></button>
                                    </div>
                                </div>
                                <p class="loading" style="text-align: center; display: none;">Please wait...</p>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php 
$this->load->view('footer-layout/footer');
?>