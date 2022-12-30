<!-- top navigation -->
<div class="top_nav">
  <div class="nav_menu">
      <div class="nav toggle">
        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
      </div>
      <nav class="nav navbar-nav">
      <ul class=" navbar-right">
        <li class="nav-item dropdown open" style="padding-left: 15px;">
          <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
            
              <?php 
                  $profile_url  = '';
                  if(file_exists(UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'])){
                      $profile_url = UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'];
                  }else{
                      $profile_url = AWS_PROFILES_URL.$loggedinuser[0]['profile_image'];
                  }

              ?>
            <img src="<?php echo $profile_url; ?>" alt=""><?php echo $loggedinuser[0]['name']; ?>
          </a>
          <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item"  href="<?php echo base_url('profile'); ?>"> <?php echo PROFILE; ?></a>                     
            <a class="dropdown-item"  href="<?php echo base_url('logout'); ?>"><i class="fa fa-sign-out pull-right"></i> <?php echo LOG_OUT; ?></a>
          </div>
        </li>
        <li role="presentation" class="nav-item dropdown open">
          <a href="javascript:;" class="dropdown-toggle info-number" id="navbarDropdown1" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-envelope-o"></i>
            <span class="badge bg-green noti_count">0</span>
          </a>
          <ul class="dropdown-menu list-unstyled msg_list" role="menu" aria-labelledby="navbarDropdown1">
            <li class="nav-item">
              <div class="text-center">
                <a class="dropdown-item">
                  <strong><?php echo SEE_ALL_ALERTS; ?></strong>
                  <i class="fa fa-angle-right"></i>
                </a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>
<!-- /top navigation -->