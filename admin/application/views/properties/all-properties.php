<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>All Properties</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small>List of all properties</small></h2>
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
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Type</th>
                                        <th>Area</th>
                                        <th>Listed By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($propertiesList){ 
                                            foreach ($propertiesList as $property) { ?>
                                            <tr>
                                                <td><?php echo $property->id; ?></td>
                                                <td><img src="<?php echo base_url('assets/uploads/properties/'.$property->image); ?>" alt="<?php echo $property->image; ?>" width="50px;"></td>
                                                <td><?php echo $property->title; ?></td>
                                                <td><?php echo substr($property->description, 0, 10).'...'; ?></td>
                                                <td><?php echo $property->price;  ?></td>
                                                <td><?php echo ucfirst($property->type); ?></td>
                                                <td><?php echo $property->area; ?></td>
                                                <td><?php echo '<a href="javascript:void(0)" class="view-user" data-id="'.$property->listed_by.'">'. $property->listedByName.' ('.$property->userRole.')</a>'; ?></td>
                                                <td><?php echo $property->created_at;  ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" class="view-property" data-id="<?php echo $property->id; ?>"><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"></i></a>
                                                    &nbsp;&nbsp;
                                                    <a href="javascript:void(0)" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" class="delete-property" data-id="<?php echo $property->id; ?>"><i class="fa fa-trash"></i></a>
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