<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/7/16
 * Time: 11:14 AM
 */
?>

<div class="top_nav">

    <div class="nav_menu">
        <nav class="" role="navigation">
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="<?= asset_base_url() ?>/images/avatar.png" alt="">Administrator
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
                        <li>
                            <a href="javascript:;">Help</a>
                        </li>
                        <li><a href="/admin/logout"><i class="fa fa-sign-out pull-right"></i> Log Out </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

</div>
