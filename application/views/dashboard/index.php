<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 5:04 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<?php $this->load->view('master/head') ?>
    <link rel="stylesheet" type="text/css" href="<?= asset_base_url() ?>/css/maps/jquery-jvectormap-2.0.3.css" />
    <link href="<?= asset_base_url() ?>/css/floatexamples.css" rel="stylesheet" type="text/css" />
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

