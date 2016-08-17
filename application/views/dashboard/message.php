<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/30/16
 * Time: 6:22 PM
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
            <div class="page-title">
                <div class="title_left">
                    <h3>Broadcast Message</h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_content">

                            <form class="form-horizontal form-label-left" method="post" action="/dashboard/broadcast_message_logic">
                                <div class="item form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Message <span class="required">*</span>
                                    </label>
                                    <div class="col-md-4 col-sm-4 col-xs-12">
                                        <textarea name="message" required="required" class="form-control col-md-7 col-xs-12"></textarea>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button id="send" type="submit" class="btn btn-success">Send</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

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

<script>
    $(function () {
        var ret = <?= $result ?>;
        if(ret == 1) {
            alert('Sent message successfully');
        } else if(ret == -1) {
            alert('Sorry, Message length is too long');
        }
    });
</script>

</body>

</html>