<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 10:15 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<?php $this->load->view('master/head') ?>

</head>

<body style="background:#F7F7F7;">

<div class="">
    <a class="hiddenanchor" id="toregister"></a>
    <a class="hiddenanchor" id="tologin"></a>

    <div id="wrapper">
        <div id="login" class="animate form">
            <section class="login_content">
                <form action = "/admin/login" method="post">
                    <h1>Login Form</h1>
                    <?php
                        if(validation_errors() != '') {
                            echo('<i class="fa fa-exclamation-triangle"></i> Invalid user account.');
                        }
                    ?>

                    <div>
                        <input type="text" class="form-control" placeholder="Username" required="required" name="username"/>
                    </div>
                    <div>
                        <input type="password" class="form-control" placeholder="Password" required="required" name="password"/>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-default submit">Log in</button>
                    </div>
                    <div class="clearfix"></div>
                    <div class="separator">
                        <div>
                            <p>©2016 Carpool Complete, Inc. All Rights Reserved.</p>
                        </div>
                    </div>
                </form>
                <!-- form -->
            </section>
            <!-- content -->
        </div>
    </div>
</div>

</body>

</html>
