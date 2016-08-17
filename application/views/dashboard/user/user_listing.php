<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/10/16
 * Time: 10:11 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<?php $this->load->view('master/head') ?>

<link href="<?= asset_base_url() ?>/css/datatables/tools/css/dataTables.tableTools.css" rel="stylesheet">

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

            <div class="">
                <div class="page-title">
                    <div class="title_left">
                        <h3>
                            Mobile Users
                        </h3>
                    </div>
                    <div class="title_right">
                        <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                            <div class="input-group">
                                <input type="text" class="form-control" id="txtSearch" placeholder="Search User...">
                                <span class="input-group-btn">
                                     <button class="btn btn-default" type="button" onclick="onClickBtnSearch()">Search</button>
                                 </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="row">

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_content">
                                <div class="row" id="viewContainer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <br />
                    <br />
                    <br />

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

<!-- icheck -->
<script src="<?= asset_base_url() ?>/js/icheck/icheck.min.js"></script>

<!-- Datatables -->
<script src="<?= asset_base_url() ?>/js/datatables/js/jquery.dataTables.js"></script>
<script src="<?= asset_base_url() ?>/js/datatables/tools/js/dataTables.tableTools.js"></script>
<script src="<?= asset_base_url() ?>/js/datatables/js/TableTools.min.js"></script>

<!-- pace -->
<script src="<?= asset_base_url() ?>/js/pace/pace.min.js"></script>
<script>

    var aryUsers = <?= json_encode($aryUsers) ?>;
    var asset_base_url = '<?= asset_base_url() ?>';

    $(document).ready(function () {
        onClickBtnSearch();
    });

    function onClickBtnUserView(user_id) {
        location.href = '/dashboard/user/goto_edit?user_id=' + user_id;
    }

    function onClickBtnUserDelete(user_id) {
        if(confirm('Are you sure to delete this user? If you remove user, all data which was created by this user will be removed.') == true) {
            location.href = '/dashboard/user/action_delete?user_id=' + user_id;
        }
    }

    function onClickBtnSearch() {
        var aryTmpUsers = [];
        var strKey = $('#txtSearch').val();
        if(strKey == "") {
            aryTmpUsers = aryUsers;
        } else {
            for (var nIndex = 0; nIndex < aryUsers.length; nIndex++) {
                var userObj = aryUsers[nIndex];
                var user_name = userObj.user_first_name + " " + userObj.user_last_name;

                if (user_name.toLowerCase().indexOf(strKey.toLowerCase()) > -1) {
                    aryTmpUsers.push(userObj);
                }
            }
        }

        //clear container view
        $("#viewContainer").html('');
        var user_html = '';

        for(var nIndex = 0; nIndex < aryTmpUsers.length; nIndex++) {
            var userObj = aryTmpUsers[nIndex];

            console.log(userObj);
            user_html += '<div class="col-md-4 col-sm-4 col-xs-12 animated fadeInDown">';
            user_html +=    '<div class="well profile_view">';
            user_html +=        '<div class="col-sm-12">';
            user_html +=            '<h2>' + userObj.user_first_name + ' ' + userObj.user_last_name + '</h2>';
            user_html +=            '<div class="left col-xs-7">';
            user_html +=                '<ul class="list-unstyled">';
            user_html +=                    '<li><i class="fa fa-envelope-o"></i> Email: ' + userObj.user_email + '</li>';
            user_html +=                    '<li><i class="fa fa-phone"></i> Phone: ' + userObj.user_phone + '</li>';
            user_html +=                    '<li><i class="fa fa-calendar"></i> Events: ' + userObj.user_event_count + '</li>';
            user_html +=                    '<li><i class="fa fa-tablet"></i> Device Type: ' + userObj.user_device_type + '</li>';
            user_html +=                    '<li><i class="fa fa-clock-o"></i> Time Zone: ' + userObj.user_time_zone + '</li>';
            user_html +=                '</ul>';
            user_html +=                '<button type="button" class="btn btn-primary btn-xs" onclick="onClickBtnUserView(' + userObj.user_id + ')">';
            user_html +=                    '<i class="fa fa-user"></i> View More Detail';
            user_html +=                '</button>';
            user_html +=                '<button type="button" class="btn btn-danger btn-xs" onclick="onClickBtnUserDelete(' + userObj.user_id + ')">';
            user_html +=                    '<i class="fa fa-trash"></i> Delete User';
            user_html +=                '</button>';
            user_html +=            '</div>';
            user_html +=            '<div class="right col-xs-5 text-center">';
            if(userObj.user_avatar_url) {
                user_html += '<img class="img-circle img-responsive" src="' + asset_base_url + '/avatar/' + userObj.user_avatar_url + '" width="80%" height="auto">';
            } else {
                user_html +=            '<img class="img-circle img-responsive" src="' + asset_base_url + '/images/placeholder_avatar.png' + '" width="80%" height="auto">';
            }
            user_html +=            '</div>';
            user_html +=        '</div>';
            user_html +=    '</div>';
            user_html += '</div>';
       }

        if(user_html == '') {
            user_html = 'There is no search result.';
        }

        $("#viewContainer").html(user_html);
    }
</script>

</body>

</html>