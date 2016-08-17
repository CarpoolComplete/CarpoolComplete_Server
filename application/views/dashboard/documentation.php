<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 6/22/16
 * Time: 8:01 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<?php $this->load->view('master/head') ?>
<!-- ion_range -->
<link rel="stylesheet" href="<?= asset_base_url() ?>/css/normalize.css" />
<link rel="stylesheet" href="<?= asset_base_url() ?>/css/ion.rangeSlider.css" />
<link rel="stylesheet" href="<?= asset_base_url() ?>/css/ion.rangeSlider.skinFlat.css" />

<script src="<?= asset_base_url() ?>/js/nprogress.js"></script>
</head>


<body class="nav-md">

<div class="container body">

    <div class="main_container">

        <!-- side menu -->
        <?php $this->load->view('master/sidemenu') ?>
        <!-- /side menu -->

        <!-- top navigation -->
        <?php $this->load->view('master/topbar') ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">

            <iframe src="http://52.37.247.230/api/docs" width="100%" height="700px"></iframe>
            <!-- footer content -->
            <?php $this->load->view('master/footer') ?>
            <!-- /footer content -->
        </div>
        <!-- /page content -->
    </div>

</div>

<script src="<?= asset_base_url() ?>/js/bootstrap.min.js"></script>

<!-- bootstrap progress js -->
<script src="<?= asset_base_url() ?>/js/progressbar/bootstrap-progressbar.min.js"></script>
<script src="<?= asset_base_url() ?>/js/nicescroll/jquery.nicescroll.min.js"></script>
<script src="<?= asset_base_url() ?>/js/custom.js"></script>

</body>

</html>