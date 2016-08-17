<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/30/16
 * Time: 3:51 PM
 */

class M_Admin extends CI_Model
{
    function loginAdmin($admin_name, $admin_pass) {
        $query = $this->db->query('select * from tblAdmin where admin_name = \''.$admin_name.'\' and admin_pass = HEX(AES_ENCRYPT(\''.$admin_pass.'\', \''.DB_USER_PASSWORD.'\'))');

        return $query->row();
    }

    function updateAdminPassword($admin_id, $admin_old_pass, $admin_new_pass) {
        $query = $this->db->query('select * from tblAdmin where admin_id = '.$admin_id.' and admin_pass = HEX(AES_ENCRYPT(\''.$admin_old_pass.'\', \''.DB_USER_PASSWORD.'\'))');
        if($query->num_rows() > 0) {
            $result = $this->db->query('update tblAdmin set admin_pass = HEX(AES_ENCRYPT(\''.$admin_new_pass.'\', \''.DB_USER_PASSWORD.'\')) where admin_id = '.$admin_id);

            return $result;
        } else {
            return false;
        }
    }
}