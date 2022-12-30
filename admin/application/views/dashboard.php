<?php $this->load->view('header-layout/header'); 

?>

<!-- page content -->
<div class="right_col" role="main">
    <!-- top tiles -->
    <div class="row" style="display:block;" >
    <div class="tile_count">
      <div class="col-md-4 col-sm-4  tile_stats_count">
        <span class="count_top"><i class="fa fa-users"></i> <?php echo TOTAL_INDIVIDUALS; ?></span>
        <div class="count"><?php echo $total_individuals; ?></div>
      </div>
      <div class="col-md-4 col-sm-4  tile_stats_count">
        <span class="count_top"><i class="fa fa-users"></i> <?php echo TOTAL_AGENTS; ?></span>
        <div class="count"><?php echo $total_agents; ?></div>
      </div>
      <div class="col-md-4 col-sm-4  tile_stats_count">
        <span class="count_top"><i class="fa fa-home"></i> <?php echo ALL_PROPERTIES; ?></span>
        <div class="count"><?php echo $total_properties; ?></div>
      </div>
      
    </div>
  </div>
  <!-- /top tiles -->
  <!-- /page content -->

<?php 
$this->load->view('footer-layout/footer');
?>