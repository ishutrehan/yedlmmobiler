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
                <h3><?php echo ALL_PROPERTIES; ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small><?php echo LIST_OF_ALL_PROPERTIES; ?></small></h2>
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
                                <table id="datatable" class="table table-striped table-bordered all-users-table bulk_action" style="width:100%">
                                    <thead>
                                        <tr>
                                        <th>
                                          <input type="checkbox" id="check-all" class="flat">
                                        </th>
                                        <th class="column-title">ID</th>
                                        <th class="column-title">Image</th>
                                        <th class="column-title"><?php echo TITLE; ?></th>
                                        <th class="column-title">Description</th>
                                        <th class="column-title"><?php echo PRICE; ?></th>
                                        <th class="column-title">Purpose</th>
                                        <th class="column-title">Type</th>
                                        <th class="column-title"><?php echo AREA; ?></th>
                                        <th class="column-title"><?php echo APPROVE_USER; ?></th>
                                        <th class="column-title"><?php echo LISTED_BY; ?></th>
                                        <th class="column-title"><?php echo CREATED_AT; ?></th>
                                        <th class="column-title" style="min-width: 72px;">Actions</th>
                                        <th class="bulk-actions" colspan="11">
                                          <a href="javascript:;" class="antoo dropdown-toggle" data-toggle="dropdown" style="font-weight:500;"><?php echo BULK_ACTION; ?> ( <span class="action-cnt"> </span> )</a>
                                          <ul class="dropdown-menu list-unstyled" role="menu" style="width: 21%;">
                                            <li class="nav-item" style="padding-left: 15px; padding-top: 15px; height:50px;">
                                                <a href="javascript:;" class="bulk-delete-properties"><i class="fa fa-trash"></i> <?php echo DELETE_SELECTED; ?></a>
                                            </li>
                                          </ul>
                                        </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        
                                        if($propertiesList){ 
                                            foreach ($propertiesList as $property) {
                                                $images = json_decode($property->image);

                                                $image_url = 'https://yedimmobiler.s3.us-east-2.amazonaws.com/properties/'.$property->featured;
                                                /*if(isset($images[0])){
                                                    if(!empty($images[0]->name)){

                                                        $image_url = UPLOADS_URL.'properties/'.$images[0]->name;
                                                    }else{
                                                        $image_url = $images[0]->url;
                                                    }
                                                }*/
                                                $typesData = [];
                                                if(!empty($types)){
                                                    $typeArr = explode(',',$property->type);
                                                   
                                                    foreach ($types as $key => $value) {
                                                        if(in_array($value->id, $typeArr)){
                                                            $typesData[] = $value->name;
                                                        }
                                                    }
                                                }

                                             ?>
                                            <tr>
                                                <td class="a-center ">
                                                  <input type="checkbox" class="flat" name="table_records" data-id="<?php echo $property->id; ?>">
                                                </td>
                                                <td><?php echo $property->id; ?></td>
                                                <td><img src="<?php echo $image_url ?>" width="50px;"></td>
                                                <td><?php echo $property->title; ?></td>
                                                <td><?php echo substr($property->description, 0, 10).'...'; ?></td>
                                                <td><?php echo $property->price.' '.$property->currency;  ?></td>
                                                <td><?php if($property->purpose == 'sale'){echo "Vendre";}elseif($property->purpose == 'rent'){ echo "Louer"; }else{ echo "";} ?></td>
                                                <td><?php echo implode(', ', $typesData); ?></td>
                                                <td><?php echo $property->area; ?></td>
                                                <td>
                                                    <input type="checkbox" class="flat approve_properties" data-id="<?php echo $property->id; ?>" <?php if($property->approve){ echo "checked"; } ?>/>
                                                </td>
                                                <td><?php echo $property->listed_by ? '<a href="javascript:void(0)" class="view-user" data-id="'.$property->listed_by.'">'. $property->listedByName.' ('.$property->userRole.')</a>' : ''; ?></td>
                                                <td><?php echo $property->created_at;  ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" class="view-property" data-id="<?php echo $property->id; ?>"><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo VIEW; ?>"></i></a>
                                                    <a href="javascript:void(0)" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo DEL; ?>" class="delete-property" data-id="<?php echo $property->id; ?>"><i class="fa fa-trash"></i></a>
                                                    <label class="switch" data-toggle="tooltip" data-placement="top" data-original-title="Activer/Désactiver">
                                                          <input type="checkbox" class="activate-property" <?php if($property->activate == 'activated') echo "checked"; ?> data-id="<?php echo $property->id; ?>">
                                                          <span class="slider round"></span>
                                                        </label>
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