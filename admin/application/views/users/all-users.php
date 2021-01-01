<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>All Users</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small>List of all users</small></h2>
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
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($usersList){ 
                                            foreach ($usersList as $user) { ?>
                                            <tr>
                                                <td><?php echo $user->id; ?></td>
                                                <td><?php echo $user->name; ?></td>
                                                <td><?php echo $user->email; ?></td>
                                                <td><?php echo $user->phone;  ?></td>
                                                <td><?php echo ucfirst($user->role); ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" class="view-user" data-id="<?php echo $user->id; ?>">
                                                        <i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"></i></a>
                                                    &nbsp;&nbsp;
                                                    <a href="javascript:void(0)" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" data-id="<?php echo $user->id; ?>" class="delete-user"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php } } ?>
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