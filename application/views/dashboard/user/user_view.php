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
<link href="<?= asset_base_url() ?>/css/calendar/fullcalendar.min.css" rel='stylesheet' />
<link href="<?= asset_base_url() ?>/css/calendar/fullcalendar.print.css" rel='stylesheet' media='print' />

<style>
    #script-warning {
        display: none;
        background: #eee;
        border-bottom: 1px solid #ddd;
        padding: 0 10px;
        line-height: 40px;
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        color: red;
    }

    #loading {
        display: none;
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .fc-event {
        cursor: pointer;
    }
</style>
<script src="<?= asset_base_url() ?>/js/nprogress.js"></script>

</head>


<body class="nav-md">


<!-- Event Modal -->
<div class="modal fade" id="eventModal" role="dialog" style="z-index: 999999;">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <div class="actions" style="float: right;padding-top: 10px;">
                    <a href="#" class="close-down" data-dismiss="modal"
                       aria-hidden="true"><i
                            class="fa fa-times"></i></a>
                </div>
                <h3 class="content-header">Event Details</h3>
            </div>

            <div class="modal-body">
                <form action="#" class="form-horizontal row-border" >
                    <div class="porlets-content">
                        <div class="form-group">
                            <div class="col-sm-4 col-md-offset-4"><img class="img-circle img-responsive" id="creator_avatar" width="100%"></div>
                            <div class="col-sm-8 col-md-offset-2"><h4 id="creator" align="center"></h4></div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Title</label>
                            <div class="col-sm-8">
                                <input type="text" name="title" id="title" class="form-control" readonly="">
                            </div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Starts</label>
                            <div class="col-sm-8">
                                <input type="text" name="starts" id="starts" class="form-control" readonly="">
                            </div>

                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Ends</label>
                            <div class="col-sm-8">
                                <input type="text" name="ends" id="ends" class="form-control" readonly="">
                            </div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Repeat</label>
                            <div class="col-sm-8">
                                <input type="text" name="repeat" id="repeat" class="form-control" readonly="">
                            </div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Repeat ends</label>
                            <div class="col-sm-8">
                                <input type="text" name="repeat_end" id="repeat_end" class="form-control" readonly="">
                            </div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Passengers</label>
                            <div class="col-sm-8">
                                <input type="text" name="passengers" id="passengers" class="form-control" readonly="">
                            </div>
                        </div>
                        <!--/form-group-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Alert time</label>
                            <div class="col-sm-8">
                                <input type="text" name="alert_time" id="alert_time" class="form-control" readonly="">
                            </div>
                        </div>
                    </div>
                    <!--/porlets-content-->
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


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
                            <?=$user->user_first_name." ".$user->user_last_name." Events"?>
                        </h3>
                    </div>
                    <div class="title_right">
                        <div class="col-md-2 col-sm-2 col-xs-12 form-group pull-right">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" onclick="javascript:history.back();"><span class="fa fa-arrow-left" aria-hidden="true"></span> Back </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="row">

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id='script-warning'> Something went wrong!</div>
                                        <div id='loading'>loading...</div>
                                        <div id='calendar'></div>
                                    </div>
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

<!-- Moment -->
<script src="<?= asset_base_url() ?>/js/moment/moment.min.js"></script>
<!-- Calendar -->
<script src="<?= asset_base_url() ?>/js/calendar/fullcalendar.min.js"></script>

<!-- pace -->
<script src="<?= asset_base_url() ?>/js/pace/pace.min.js"></script>


<script>

    var user_id = "<?=$user->user_id?>";
    var event_repeat_types = [
        'No repeat',
        'Everyday',
        'Every Weekday',
        'Weekly',
        'Every Other Week',
        'Every Month',
        'Custom',
    ];

    $(document).ready(function() {

        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaDay'
            },
            editable: false,
            eventLimit: true, // allow "more" link when too many events
            allDayDefault: false,
            events: {
                url: '/dashboard/getUserEvents?user_id='+user_id,
                error: function(e) {
                    console.log(e);
                    $('#script-warning').show();
                }
            },
            eventClick: function (event) {

                console.log(event);
                var avatar_url = '/assets/images/placeholder_avatar.png';
                if(event.event_creator_avatar_url) {
                    avatar_url = '/assets/avatar/' + event.event_creator_avatar_url;
                }

                var event_start = '';
                if(event.start) {
                    event_start = moment(event.start._i).format('MMM D, YYYY h:mm a');
                }

                var event_end = '';
                if(event.end) {
                    event_end = moment(event.end._i).format('MMM D, YYYY h:mm a');
                }

                var repeat_end = '';
                if(event.event_repeat_end_at) {
                    repeat_end = moment(event.event_repeat_end_at).format('MMM D, YYYY h:mm a');
                }

                var repeat_type = event_repeat_types[event.event_repeat_type];

                $('#creator').text(event.event_creator_name);
                $('#creator_avatar').attr('src', avatar_url);
                $('#title').val(event.title);
                $('#starts').val(event_start);
                $('#ends').val(event_end);
                $('#repeat').val(repeat_type);
                $('#repeat_end').val(repeat_end);
                $('#passengers').val(event.event_passengers);
                $('#alert_time').val(event.event_alert_time);
                $('#eventModal').modal('show');
            },
            loading: function(bool) {
                $('#loading').toggle(bool);
            }
        });

    });

</script>

</body>

</html>