<?php $this->load->view('header-layout/header'); ?>


<style type="text/css">
    /* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 20px;
    }

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
width: 20px;
left: 0px;
bottom: 0px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?php echo ALL_INDIVIDUALS; ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small><?php echo LIST_OF_ALL_INDIVIDUALS; ?></small></h2>
                    <ul class="nav navbar-right pull-right">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-box table-responsive">
                                <table id="datatable" class="table table-striped table-bordered all-users-table" style="width:100%">
                                    <thead>
                                        <tr>
                                        <th>ID</th>
                                        <th><?php echo NAME; ?></th>
                                        <th><?php echo EMAIL_ADDRESS; ?></th>
                                        <th><?php echo PHONE; ?></th>
                                        <th>Role</th>
                                        <th><?php echo APPROVE_USER; ?></th>
                                        <th style="min-width: 72px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($individualsList){ 
                                            foreach ($individualsList as $user) { ?>
                                                <tr>
                                                    <td><?php echo $user->id; ?></td>
                                                    <td><?php echo $user->name; ?></td>
                                                    <td><?php echo $user->email; ?></td>
                                                    <td><?php echo $user->phone;  ?></td>
                                                    <td><?php echo $user->role == 'individual' ? 'Particulier' : 'Agent'; ?></td>                                                
                                                    <td>
                                                        <input type="checkbox" class="flat approve_user" data-id="<?php echo $user->id; ?>" <?php if($user->status == 'approved'){ echo "checked"; } ?>/>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="view-user" data-id="<?php echo $user->id; ?>">
                                                            <i class="fa fa-eye" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo VIEW; ?>"></i></a>
                                                        <a href="javascript:void(0)" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo DEL; ?>" data-id="<?php echo $user->id; ?>" class="delete-user"><i class="fa fa-trash"></i></a>
                                                        <!-- Rounded switch -->
                                                        <label class="switch" data-toggle="tooltip" data-placement="top" data-original-title="Activer/Désactiver">
                                                          <input type="checkbox" class="activate-user" <?php if($user->activate == 'activate') echo "checked"; ?> data-id="<?php echo $user->id; ?>">
                                                          <span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                             <?php
                                            } 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
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