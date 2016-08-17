<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/7/16
 * Time: 9:03 AM
 */
?>

<div class="col-md-3 left_col">
    <div class="left_col scroll-view">

        <!-- menu profile quick info -->
        <div class="profile">
            <div class="profile_pic">
                <img src="<?= asset_base_url() ?>/images/avatar.png" alt="..." class="img-circle profile_img">
            </div>
            <div class="profile_info">
                <span>Welcome,</span>
                <h2>Administrator</h2>
            </div>
        </div>
        <!-- /menu profile quick info -->

        <br/>
        <br/>
        <br/>
        <br/>
        <br/>

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li><a href="/dashboard/user/listing"><i class="fa fa-users"></i> Mobile Users </a></li>
                    <li><a href="/dashboard/broadcast_message"><i class="fa fa-bell"></i> Broadcast Message </a></li>
                    <li><a href="/admin/update_password"><i class="fa fa-key"></i> Update Password </a></li>
                    <li><a href="/dashboard/documentation"><i class="fa fa-file-o"></i> Documentation </a></li>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>
