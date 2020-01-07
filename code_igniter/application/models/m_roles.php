<?php
/**
#  Copyright 2003-2015 Opmantek Limited (www.opmantek.com)
#
#  ALL CODE MODIFICATIONS MUST BE SENT TO CODE@OPMANTEK.COM
#
#  This file is part of Open-AudIT.
#
#  Open-AudIT is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as published
#  by the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  Open-AudIT is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
#
#  You should have received a copy of the GNU Affero General Public License
#  along with Open-AudIT (most likely in a file named LICENSE).
#  If not, see <http://www.gnu.org/licenses/>
#
#  For further information on Open-AudIT or for a license other than AGPL please see
#  www.opmantek.com or email contact@opmantek.com
#
# *****************************************************************************
*
* PHP version 5.3.3
* 
* @category  Model
* @package   Roles
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_3.3.0
* @link      http://www.open-audit.org
*/

/**
* Base Model Roles
*
* @access   public
* @category Model
* @package  Roles
* @author   Mark Unwin <marku@opmantek.com>
* @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @link     http://www.open-audit.org
 */
class M_roles extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->log = new stdClass();
        $this->log->status = 'reading data';
        $this->log->type = 'system';
    }

    public function create()
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'creating data';
        stdlog($this->log);
        $CI = & get_instance();
        if (empty($CI->response->meta->received_data->attributes->name)) {
            return false;
        } else {
            $name = $CI->response->meta->received_data->attributes->name;
        }
        $sql = "SELECT COUNT(*) AS `count` FROM roles WHERE roles.name = ?";
        $data = array("$name");
        $result = $this->run_sql($sql, $data);
        if (intval($result[0]->count) != 0) {
            log_error('ERR-0010', 'm_roles::create');
            return false;
        }

        if (empty($CI->response->meta->received_data->attributes->description)) {
            $description = '';
        } else {
            $description = $CI->response->meta->received_data->attributes->description;
        }

        $permissions = new stdClass();
        if (empty($CI->response->meta->received_data->attributes->permissions)) {
            $permissions = '';
        } else {
            foreach ($CI->response->meta->received_data->attributes->permissions as $endpoint => $object) {
                $permissions->{$endpoint} = '';
                foreach ($object as $key => $value) {
                    $permissions->{$endpoint} .= $key;
                }
            }
            $permissions = json_encode($permissions);
        }
        $ad_group = 'open-audit_roles_' . strtolower(str_replace(' ', '_', $CI->response->meta->received_data->attributes->name));

        if (empty($CI->user->name)) {
            $user = '';
        } else {
            $user = $CI->user->name;
        }
        $sql = "INSERT INTO `roles` VALUES (NULL, ?, ?, ?, ?, ?, NOW())";
        $data = array("$name", "$description", $permissions, $ad_group, $user);
        $id = intval($this->run_sql($sql, $data));
        return ($id);
    }

    public function read($id = '')
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        if ($id == '') {
            $CI = & get_instance();
            $id = intval($CI->response->meta->id);
        } else {
            $id = intval($id);
        }
        $sql = "SELECT * FROM roles WHERE id = ?";
        $data = array($id);
        $result = $this->run_sql($sql, $data);
        if (!empty($result[0]->permissions)) {
            $result[0]->permissions = json_decode($result[0]->permissions);
        }
        $result = $this->format_data($result, 'roles');
        return ($result);
    }

    public function update()
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'updating data';
        stdlog($this->log);
        $CI = & get_instance();
        $sql = '';
        $fields = ' description name permissions ';
        foreach ($CI->response->meta->received_data->attributes as $key => $value) {
            if (strpos($fields, ' '.$key.' ') !== false) {
                if ($sql == '') {
                    $sql = "SET `" . $key . "` = '" . $value . "'";
                } else {
                    $sql .= ", `" . $key . "` = '" . $value . "'";
                }
            }
        }
        $sql = "UPDATE `roles` " . $sql . " WHERE id = " . intval($CI->response->meta->id);
        $this->run_sql($sql, array());
        return;
    }

    public function delete($id = '')
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'deleting data';
        stdlog($this->log);
        if ($id == '') {
            $CI = & get_instance();
            $id = intval($CI->response->meta->id);
        } else {
            $id = intval($id);
        }
        if ($id != 0) {
            // do NOT allow deleting the default roles
            $sql = "SELECT name FROM roles WHERE id = ?";
            $data = array($id);
            $result = $this->run_sql($sql, $data);
            if ($result[0]->name == 'admin' or $result[0]->name == 'org_admin' or $result[0]->name == 'reporter' or $result[0]->name == 'user') {
                log_error('ERR-0013', 'm_roles::delete');
                return false;
            }
            // attempt to delete the item
            $sql = "DELETE FROM `roles` WHERE id = ?";
            $data = array($id);
            $this->run_sql($sql, $data);
            return true;
        } else {
            log_error('ERR-0013', 'm_roles::delete');
            return false;
        }
    }

    public function read_sub_resource($id = '')
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        if ($id == '') {
            $CI = & get_instance();
            $id = intval($CI->response->meta->id);
        } else {
            $id = intval($id);
        }
        $sql = "SELECT name FROM `roles` WHERE id = ?";
        $data = array($id);
        $result = $this->run_sql($sql, $data);
        if (empty($result[0]->name)) {
            return false;
        } else {
            $name = $result[0]->name;
        }

        $sql = "select users.id, users.full_name, users.roles, users.org_id, orgs.name AS `org_name` FROM users LEFT JOIN orgs ON (users.org_id = orgs.id) WHERE users.roles LIKE '%\"" . $name . "\"%'";
        $result = $this->run_sql($sql, array());
        $result = $this->format_data($result, 'users');
        return $result;
    }

    # pass this a collection and permission and it will update the role->permissions if that collection exists
    # and insert it if it doesn't exist
    public function update_permissions($rolename, $collection, $permissions)
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        if (empty($rolename) or empty($collection)) {
            return false;
        }
        if (empty($permissions)) {
            $permissions = '';
        }
        if (!empty($rolename)) {
            $sql = "SELECT * FROM `roles` WHERE `name` = ?";
            $data = array($rolename);
            $result = $this->run_sql($sql, $data);
        }
        if (!empty($result)) {
            foreach ($result as $role) {
                $role_permissions = @json_decode($role->permissions);
                $role_permissions->{$collection} = $permissions;
                $role->permissions = json_encode($role_permissions);
                $sql = "UPDATE `roles` SET `permissions` = ? WHERE `id` = ?";
                $data = array($role->permissions, $role->id);
                $result = $this->run_sql($sql, $data);
            }
        }
        return true;
    }

    public function collection($user_id = null, $response = null)
    {
        $CI = & get_instance();
        if ( ! empty($user_id)) {
            $sql = 'SELECT * FROM roles';
            $result = $this->run_sql($sql, array());
            $result = $this->format_data($result, 'roles');
            return $result;
        }
        if ( ! empty($response)) {
            $total = $this->collection($CI->user->id);
            $CI->response->meta->total = count($total);
            $sql = "SELECT " . $CI->response->meta->internal->properties . " FROM roles " . 
                    $CI->response->meta->internal->groupby . " " . 
                    $CI->response->meta->internal->sort . " " . 
                    $CI->response->meta->internal->limit;
            $result = $this->run_sql($sql, array());
            $CI->response->data = $this->format_data($result, 'roles');
            $CI->response->meta->filtered = count($CI->response->data);
        }
    }
}
