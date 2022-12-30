<?php $this->load->view('header-layout/header'); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?php echo CONTACT_DETAILS; ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><small><?php echo CONTACT_DETAILS; ?></small></h2>
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
                                  <strong>Success!</strong> Contact details updated successfully.
                                </div>
                            <?php }
                            ?>
                            <form class="form-horizontal form-label-left" method="post" enctype="multipart/form-data" action="<?php echo base_url('contactinfo'); ?>">
                                <input type="hidden" name="id" value="<?php echo $contactInfo[0]['id']; ?>">
                                <input type="hidden" id="lat" name="lat" value="<?php echo $contactInfo[0]['lat']; ?>">
                                <input type="hidden" id="long" name="lng" value="<?php echo $contactInfo[0]['lng']; ?>">
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Localisation <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" id="location" name="location" value="<?php echo $contactInfo[0]['location']; ?>" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="email"><?php echo EMAIL_ADDRESS ?> <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="email" id="email" name="email" required="required" class="form-control" value="<?php echo $contactInfo[0]['email']; ?>">
                                    </div>
                                </div>
                                <div class="item form-group">
                                    <label class="col-form-label col-md-3 col-sm-3 label-align" for="phone"><?php echo PHONE ?> <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="phone" id="phone" name="phone" required="required" class="form-control"  value="<?php echo $contactInfo[0]['phone']; ?>">
                                    </div>
                                </div>
                            
                                <div class="ln_solid"></div>
                                <div class="item form-group">
                                    <div class="col-md-6 col-sm-6 offset-md-3">
                                        <button type="submit" name="update-contactinfo" class="btn btn-success"><?php echo UPD; ?></button>
                                    </div>
                                </div>

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

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=AIzaSyAhKyqm30Xc-VZB7wAlNVn4dQNL9Heo2JQ"></script>
<script type="text/javascript">
    var geocoder = new google.maps.Geocoder();
    function initialize() {
        var input = document.getElementById('location');
       
        autocomplete = new google.maps.places.Autocomplete(input); 
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var address = document.getElementById("location").value;

            geocoder.geocode( { 'address': address}, function(results, status) {
               
                var latitude = results[0].geometry.location.lat();
                var longitude = results[0].geometry.location.lng();
                document.getElementById('lat').value = latitude;
                document.getElementById('long').value = longitude;
                
            }); 
        })
        //new google.maps.places.Autocomplete(input);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script>