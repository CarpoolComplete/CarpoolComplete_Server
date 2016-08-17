CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `viewDriver` AS
    SELECT
        `invitation`.`invitation_event_id` AS `event_id`,
        `invitation`.`invitation_id` AS `driver_invitation_id`,
        `invitation`.`invitation_driver_family_id` AS `driver_family_id`,
        `invitation`.`invitation_driver_user_id` AS `driver_user_id`,
        `invitation`.`invitation_driver_first_name` AS `driver_first_name`,
        `invitation`.`invitation_driver_last_name` AS `driver_last_name`,
        `invitation`.`invitation_driver_email` AS `driver_email`,
        `invitation`.`invitation_driver_phone` AS `driver_phone`,
        (SELECT
             `user`.`user_avatar_url`
         FROM
             `tblUser` `user`
         WHERE
             (`user`.`user_id` = `invitation`.`invitation_driver_user_id`)) AS `driver_avatar_url`,
        `invitation`.`invitation_status` AS `driver_status`
    FROM
        `tblInvitation` `invitation`


CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `viewEvent` AS
    SELECT
        `event`.`event_id` AS `event_id`,
        `event`.`event_user_id` AS `event_user_id`,
        `user`.`user_family_id` AS `event_family_id`,
        `user`.`user_first_name` AS `event_creator_first_name`,
        `user`.`user_last_name` AS `event_creator_last_name`,
        `user`.`user_email` AS `event_creator_email`,
        `user`.`user_phone` AS `event_creator_phone`,
        `user`.`user_avatar_url` AS `event_creator_avatar_url`,
        `event`.`event_title` AS `event_title`,
        `event`.`event_start_at` AS `event_start_at`,
        `event`.`event_end_at` AS `event_end_at`,
        `event`.`event_repeat_end_at` AS `event_repeat_end_at`,
        `event`.`event_repeat_no_end` AS `event_repeat_no_end`,
        `event`.`event_alert_time` AS `event_alert_time`,
        `event`.`event_repeat_type` AS `event_repeat_type`,
        `event`.`event_custom_repeat_dates` AS `event_custom_repeat_dates`,
        `event`.`event_deleted_dates` AS `event_deleted_dates`,
        `event`.`event_created_at` AS `event_created_at`
    FROM
        (`tblEvent` `event`
            JOIN `tblUser` `user` ON ((`event`.`event_user_id` = `user`.`user_id`)))

CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `viewInvitation` AS
    SELECT
        `invitation`.`invitation_id` AS `invitation_id`,
        `invitation`.`invitation_event_id` AS `invitation_event_id`,
        `event`.`event_user_id` AS `invitation_event_user_id`,
        `event`.`event_title` AS `invitation_event_title`,
        `invitation`.`invitation_driver_user_id` AS `invitation_driver_user_id`,
        `invitation`.`invitation_driver_first_name` AS `invitation_driver_first_name`,
        `invitation`.`invitation_driver_last_name` AS `invitation_driver_last_name`,
        `invitation`.`invitation_driver_email` AS `invitation_driver_email`,
        `invitation`.`invitation_driver_phone` AS `invitation_driver_phone`,
        `invitation`.`invitation_status` AS `invitation_status`,
        `invitation`.`invitation_created_at` AS `invitation_created_at`
    FROM
        (`tblInvitation` `invitation`
            JOIN `tblEvent` `event` ON ((`invitation`.`invitation_event_id` = `event`.`event_id`)))


CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `viewUser` AS
    SELECT
        `user`.`user_id` AS `user_id`,
        `user`.`user_family_id` AS `user_family_id`,
        `user`.`user_first_name` AS `user_first_name`,
        `user`.`user_last_name` AS `user_last_name`,
        `user`.`user_email` AS `user_email`,
        `user`.`user_phone` AS `user_phone`,
        `user`.`user_pass` AS `user_pass`,
        `user`.`user_avatar_url` AS `user_avatar_url`,
        (SELECT
             COUNT(0)
         FROM
             `tblEvent`
         WHERE
             ((`tblEvent`.`event_family_id` = `user`.`user_family_id`)
              OR `tblEvent`.`event_id` IN (SELECT
                                               `tblInvitation`.`invitation_event_id`
                                           FROM
                                               `tblInvitation`
                                           WHERE
                                               ((`tblInvitation`.`invitation_driver_user_id` = `user`.`user_id`)
                                                AND (`tblInvitation`.`invitation_status` = 1))))) AS `user_event_count`,
        `user`.`user_time_zone` AS `user_time_zone`,
        `user`.`user_device_type` AS `user_device_type`,
        `user`.`user_device_token` AS `user_device_token`,
        (SELECT
             COUNT(0)
         FROM
             `tblInvitation`
         WHERE
             ((`tblInvitation`.`invitation_driver_user_id` = `user`.`user_id`)
              AND (`tblInvitation`.`invitation_status` = 0))) AS `user_noti_badges`,
        `user`.`user_created_at` AS `user_created_at`
    FROM
        `tblUser` `user`