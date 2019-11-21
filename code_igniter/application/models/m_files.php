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
* @package   Files
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_3.3.0
* @link      http://www.open-audit.org
*/

/**
* Base Model Files
*
* @access   public
* @category Model
* @package  Files
* @author   Mark Unwin <marku@opmantek.com>
* @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @link     http://www.open-audit.org
 */
class M_files extends MY_Model
{
    /**
    * Constructor
    *
    * @access public
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * [create description]
     * @return [type] [description]
     */
    public function create()
    {
        $CI = & get_instance();
        // check to see if we already have a file with the same name
        $sql = 'SELECT COUNT(id) AS count FROM `files` WHERE `path` = ?';
        $data = array($CI->response->meta->received_data->attributes->path);
        $result = $this->run_sql($sql, $data);
        if (intval($result[0]->count) !== 0) {
            log_error('ERR-0010', 'm_files::create_file');
            return false;
        }
        $sql = 'INSERT INTO `files` VALUES (NULL, ?, ?, ?, ?, NOW())';
        if (empty($CI->response->meta->received_data->attributes->org_id)) {
            $CI->response->meta->received_data->attributes->org_id = 1;
        }
        $data = array($CI->response->meta->received_data->attributes->org_id, $CI->response->meta->received_data->attributes->path, $CI->response->meta->received_data->attributes->description, $CI->user->full_name);
        $id = intval($this->run_sql($sql, $data));
        return ($id);
    }

    /**
     *
     * @param  int $id The ID of the requested item
     * @return array The array of requested items
     */
    public function read(int $id = 0)
    {
        $sql = 'SELECT files.*, orgs.id AS `orgs.id`, orgs.name AS `orgs.name` FROM `files` LEFT JOIN `orgs` ON (files.org_id = orgs.id) WHERE id = ?';
        $data = array($id);
        $result = $this->run_sql($sql, $data);
        $result = $this->format_data($result, 'files');
        return ($result);
    }

    /**
     * [update description]
     * @return [type] [description]
     */
    public function update()
    {
        $CI = & get_instance();
        $sql = '';
        $fields = ' path description ';
        foreach ($CI->response->meta->received_data->attributes as $key => $value) {
            if (strpos($fields, ' '.$key.' ') !== false) {
                if ($sql === '') {
                    $sql = "SET `{$key}` = '{$value}'";
                } else {
                    $sql .= ", `{$key}` = '{$value}'";
                }
            }
        }
        $sql = "UPDATE `files` {$sql}, `edited_by` = '{$CI->user->full_name}', `edited_date` = NOW() WHERE id = " . intval($CI->response->meta->id);
        $this->run_sql($sql, array());
        return;
    }

    /**
     *
     * @param  int $id The ID of the requested item
     * @return bool True = success, False = fail
     */
    public function delete(int $id = 0)
    {
        $sql = 'DELETE FROM `attributes` WHERE `id` = ?';
        $data = array($id);
        $test = $this->run_sql($sql, $data);
        if ( ! empty($test)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param  int $user_id  The ID of the requesting user, no $response->meta->filter used and no $response->data populated
     * @param  int $response A flag to tell us if we need to use $response->meta->filter and populate $response->data
     * @return bool True = success, False = fail
     */
    public function collection(int $user_id = null, int $response = null)
    {
        $CI = & get_instance();
        if ( ! empty($user_id)) {
            $org_list = $CI->m_orgs->get_user_all($user_id);
            $sql = 'SELECT * FROM files WHERE org_id IN (' . implode(',', $org_list) . ')';
            $result = $this->run_sql($sql, array());
            $result = $this->format_data($result, 'files');
            return $result;
        }
        if ( ! empty($response)) {
            $total = $this->collection($CI->user->id);
            $CI->response->meta->total = count($total);
            $sql = 'SELECT ' . $CI->response->meta->internal->properties . ', orgs.id AS `orgs.id`, orgs.name AS `orgs.name` FROM files LEFT JOIN orgs ON (files.org_id = orgs.id) ' . 
                    $CI->response->meta->internal->filter . ' ' . 
                    $CI->response->meta->internal->groupby . ' ' . 
                    $CI->response->meta->internal->sort . ' ' . 
                    $CI->response->meta->internal->limit;
            $result = $this->run_sql($sql, array());
            $CI->response->data = $this->format_data($result, 'files');
            $CI->response->meta->filtered = count($CI->response->data);
        }
    }
}
// End of file m_files.php
// Location: ./models/m_files.php
