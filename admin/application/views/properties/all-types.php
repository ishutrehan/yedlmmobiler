<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Types</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <div class="col-md-12">
                        
                        <h2><small><?php echo LIST_OF_ALL_TYPES; ?> <button class="btn btn-info btn-md add-new-button"><?php echo ADD_NEW; ?></button></small></h2>
                    </div>
                    <?php if(isset($_GET['success']) && $_GET['success'] == 'true'){  ?>
                        <div class="col-md-12">
                            <div class="alert alert-success alert-dismissible " role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                </button>
                                <?php echo "Type added successfully."; ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if(isset($_GET['delete']) && $_GET['delete'] == 'true'){  ?>
                        <div class="col-md-12">
                            <div class="alert alert-success alert-dismissible " role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                </button>
                                <?php echo "Type deleted successfully."; ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if(isset($_GET['update']) && $_GET['update'] == 'true'){  ?>
                        <div class="col-md-12">
                            <div class="alert alert-success alert-dismissible " role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                </button>
                                <?php echo "Type updated successfully."; ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row add-form" style="display: none;">
                        <div class="col-md-6">
                            <form method="post" action="<?php echo base_url('addType'); ?>">
                                <div class="form-group">
                                    <label><?php echo NAME; ?></label>
                                    <input type="text" name="name" class="form-control" required="">
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="submit" class="btn btn-success" value="<?php echo ADD_TYPE; ?>">
                                </div>
                                
                            </form>
                        </div>
                    </div>
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
                                        <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($types){ 
                                            foreach ($types as $type) { ?>
                                            <tr>
                                                <td><?php echo $type->id; ?></td>
                                                <td>
                                                    <p class="name"><?php echo $type->name; ?></p>
                                                    <div class="edit-box" style="display: none;">
                                                        <div class="col-md-8">
                                                            <input type="text" name="name" value="<?php echo $type->name; ?>" class="form-control edit-type-input">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-info btn-sm update-type" data-id="<?php echo $type->id; ?>">Save</button>
                                                            <button class="btn btn-link btn-sm cancel-edit">Cancel</button>
                                                        </div>
                                                    </div>
                                                    
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="edit-row" data-id="<?php echo $type->id; ?>"><i class="fa fa-pencil" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"></i></a>
                                                    &nbsp;&nbsp;
                                                    <a href="<?php echo base_url('deletetype/'.$type->id);?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" class="delete-type" data-id="<?php echo $type->id; ?>"><i class="fa fa-trash"></i></a>
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