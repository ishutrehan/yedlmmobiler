<?php $this->load->view('header-layout/header'); 

?>

<!-- page content -->
<div class="right_col" role="main">
    <!-- top tiles -->
    <div class="row" style="display:block;" >
    <div class="tile_count">
      <div class="col-md-6 col-sm-6  tile_stats_count">
        <span class="count_top"><i class="fa fa-users"></i> Total Users</span>
        <div class="count"><?php echo $total_users; ?></div>
      </div>
      <div class="col-md-6 col-sm-6  tile_stats_count">
        <span class="count_top"><i class="fa fa-home"></i> All Properties</span>
        <div class="count"><?php echo $total_properties; ?></div>
      </div>
      
    </div>
  </div>
  <!-- /top tiles -->
  <!-- /page content -->

<?php 
$this->load->view('footer-layout/footer');
?>