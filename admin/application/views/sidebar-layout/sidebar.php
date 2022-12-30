<!-- sidebar menu -->
<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="<?php echo base_url(); ?>" class="site_title" style="height:75px; padding-left:0;">
                <img src="<?php echo base_url('assets/images/logo-new-white.png'); ?>" alt="Logo" width="100%">
            </a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile clearfix" style="margin-top: 16px;">
            <div class="profile_pic">
                <?php 
                    $profile_url  = '';
                    if(file_exists(UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'])){
                        $profile_url = UPLOADS_URL."profiles/".$loggedinuser[0]['profile_image'];
                    }else{
                        $profile_url = AWS_PROFILES_URL.$loggedinuser[0]['profile_image'];
                    }

                ?>
            <img src="<?php echo $profile_url; ?>" alt="<?php echo $loggedinuser[0]['profile_image']; ?>" class="img-circle profile_img">
            </div>
            <div class="profile_info">
            <span><?php echo WELCOME; ?>,</span>
            <h2><?php echo $loggedinuser[0]['name']; ?></h2>
            </div>
        </div>
        <!-- /menu profile quick info -->

        <br />

    <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
            <h3>General</h3>
            <ul class="nav side-menu">
                <li><a><i class="fa fa-users"></i> <?php echo USERS; ?> <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                    <li><a href="<?php echo base_url(); ?>individuals"><?php echo ALL_INDIVIDUALS; ?></a></li>
                    <li><a href="<?php echo base_url(); ?>agents"><?php echo ALL_AGENTS; ?></a></li>
                    <li><a href="<?php echo base_url(); ?>add-user"><?php echo ADD_NEW_USER; ?></a></li>
                </ul>
                </li>
                <li><a><i class="fa fa-home"></i> <?php echo PROPERTIES; ?> <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                        <li><a href="<?php echo base_url(); ?>properties"><?php echo ALL_PROPERTIES; ?></a></li>
                        <li><a href="<?php echo base_url(); ?>properties/types">Types</a></li>
                        <li><a href="<?php echo base_url(); ?>properties/amenities"><?php echo AMENITIES; ?></a></li>
                    </ul>
                </li>
                <li><a href="<?php echo base_url('contactinfo') ?>"><i class="fa fa-phone"></i> <?php echo CONTACT_DETAILS; ?> </a></li>
            </ul>
            </div>
        </div>
    
        <!-- /sidebar menu -->
        <!-- /menu footer buttons -->
        <div class="sidebar-footer hidden-small">
            <a data-toggle="tooltip" data-placement="top" title="<?php echo SETTINGS; ?>">
            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" onClick="openFullscreen()" data-placement="top" title="<?php echo FULL_SCREEN; ?>">
            <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="<?php echo LOCK; ?>">
            <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="<?php echo LOG_OUT; ?>" href="<?php echo base_url('logout'); ?>">
            <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
            </a>
        </div>
        <!-- /menu footer buttons -->
    </div>
</div>