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
* @package   Baselines
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_4.3.0
* @link      http://www.open-audit.org
*/

/**
* Base Model BaselinesPolicies
*
* @access   public
* @category Model
* @package  Baselines
* @author   Mark Unwin <marku@opmantek.com>
* @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @link     http://www.open-audit.org
 */
class M_baselines_policies extends MY_Model
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
     * Create an individual item in the database
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function create($data = null)
    {
        $this->load->helper('software_version');
        if ( ! empty($data->table) && $data->table === 'software') {
            $data->name = $data->tests->name->value . ' ' . $data->tests->version->operator . ' ' . $data->tests->version->value;
            $tests = array();
            $entry = new stdClass();
            $entry->column = 'name';
            $entry->operator = '=';
            $entry->value = $data->tests->name->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'version';
            $entry->operator = $data->tests->version->operator;
            $entry->value = $data->tests->version->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'version_padded';
            $entry->operator = $data->tests->version->operator;
            $entry->value = version_padded($data->tests->version->value);
            $tests[] = $entry;

            unset($data->tests);
            $data->tests = json_encode($tests);
        }

        if ( ! empty($data->table) && $data->table === 'netstat') {
            $data->name = $data->tests->program->value . ' on ' . $data->tests->port->value . ' using ' . $data->tests->protocol->value;
            $tests = array();
            $entry = new stdClass();
            $entry->column = 'protocol';
            $entry->operator = '=';
            $entry->value = $data->tests->protocol->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'program';
            $entry->operator = '=';
            $entry->value = $data->tests->program->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'port';
            $entry->operator = '=';
            $entry->value = $data->tests->port->value;
            $tests[] = $entry;

            unset($data->tests);
            $data->tests = json_encode($tests);
        }

        if ( ! empty($data->table) && $data->table === 'user') {
            $data->name = $data->tests->name->value;
            $tests = array();
            $entry = new stdClass();
            $entry->column = 'name';
            $entry->operator = '=';
            $entry->value = $data->tests->name->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'status';
            $entry->operator = '=';
            $entry->value = $data->tests->status->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'type';
            $entry->operator = '=';
            $entry->value = $data->tests->type->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'password_expires';
            $entry->operator = '=';
            $entry->value = $data->tests->password_expires->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'password_changeable';
            $entry->operator = '=';
            $entry->value = $data->tests->password_changeable->value;
            $tests[] = $entry;

            $entry = new stdClass();
            $entry->column = 'password_required';
            $entry->operator = '=';
            $entry->value = $data->tests->password_required->value;
            $tests[] = $entry;

            unset($data->tests);
            $data->tests = json_encode($tests);
        }
        if ($id = $this->insert_collection('baselines_policies', $data)) {
            return intval($id);
        } else {
            return false;
        }
    }

    /**
     * Read an individual item from the database, by ID
     *
     * @param  int $id The ID of the requested item
     * @return array The array of requested items
     */
    public function read($id = 0)
    {
        $sql = 'SELECT baselines_policies.*, baselines.id AS `baselines.id`, baselines.name AS `baselines.name` FROM `baselines_policies` LEFT JOIN `baselines` ON (baselines_policies.baseline_id = baselines.id) WHERE baselines_policies.id = ?';
        $data = array($id);
        $result = $this->run_sql($sql, $data);
        $result[0]->tests = @json_decode($result[0]->tests);
        $result = $this->format_data($result, 'baselines_policies');
        return ($result);
    }

    /**
     * Delete an individual item from the database, by ID
     *
     * @param  int $id The ID of the requested item
     * @return bool True = success, False = fail
     */
    public function delete($id = 0)
    {
        $data = array(intval($id));
        $sql = 'DELETE FROM `baselines_policies` WHERE `id` = ?';
        $test = $this->run_sql($sql, $data);
        if ( ! empty($test)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Read the collection from the database
     *
     * @param  int $user_id  The ID of the requesting user, no $response->meta->filter used and no $response->data populated
     * @param  int $response A flag to tell us if we need to use $response->meta->filter and populate $response->data
     * @return bool True = success, False = fail
     */
    public function collection($user_id = null, $response = null)
    {
        $CI = & get_instance();
        if ( ! empty($user_id)) {
            $org_list = array_unique(array_merge($CI->user->orgs, $CI->m_orgs->get_user_descendants($user_id)));
            $sql = 'SELECT baselines_policies.*, baselines.id AS `baselines.id`, baselines.name AS `baselines.name`, orgs.id AS `orgs.id`, orgs.name AS `orgs.name` FROM baselines_policies LEFT JOIN baselines ON (baselines_policies.baseline_id = baselines.id) LEFT JOIN orgs ON (baselines.org_id = orgs.id) WHERE orgs.id IN (' . implode(',', $org_list) . ')';
            $result = $this->run_sql($sql, array());
            $result = $this->format_data($result, 'baselines_policies');
            return $result;
        }
        if ( ! empty($response)) {
            $total = $this->collection($CI->user->id);
            $CI->response->meta->total = count($total);
            $sql = 'SELECT ' . $CI->response->meta->internal->properties . ', baselines.id AS `baselines.id`, baselines.name AS `baselines.name`, orgs.id AS `orgs.id`, orgs.name AS `orgs.name` FROM baselines_policies LEFT JOIN baselines ON (baselines_policies.baseline_id = baselines.id) LEFT JOIN orgs ON (baselines.org_id = orgs.id) ' . 
                    $CI->response->meta->internal->filter . ' ' . 
                    $CI->response->meta->internal->groupby . ' ' . 
                    $CI->response->meta->internal->sort . ' ' . 
                    $CI->response->meta->internal->limit;
            $result = $this->run_sql($sql, array());
            if ( ! empty($result) && is_array($result)) {
                for ($i=0; $i < count($result); $i++) {
                    $result[$i]->tests = json_decode($result[$i]->tests);
                }
            }
            $CI->response->data = $this->format_data($result, 'baselines_policies');
            $CI->response->meta->filtered = count($CI->response->data);
        }
    }

    /**
     * [dictionary description]
     * @return [type] [description]
     */
    public function dictionary()
    {
        $CI = & get_instance();
        $collection = 'baselines_policies';
        $CI->temp_dictionary->link = str_replace('$collection', 'baselines policies', $CI->temp_dictionary->link);
        $this->load->helper('collections');

        $dictionary = new stdClass();
        $dictionary->table = $collection;
        $dictionary->about = '';
        $dictionary->marketing = '';
        $dictionary->notes = '';
        $dictionary->columns = new stdClass();
        $dictionary->attributes = new stdClass();
        $dictionary->attributes->fields = $this->db->list_fields($collection);
        $dictionary->attributes->create = mandatory_fields($collection);
        $dictionary->attributes->update = update_fields($collection);
        $dictionary->sentence = 'sentence';
        $dictionary->about = '<p>The About</p>';
        $dictionary->marketing = '<p>Some Marketing</p>';
        $dictionary->product = 'enterprise';
        $dictionary->notes = '<p>More Notes</p>';

        $dictionary->columns->id = $CI->temp_dictionary->id;
        $dictionary->columns->baseline_id = '';
        $dictionary->columns->name = $CI->temp_dictionary->name;
        $dictionary->columns->priority = 'The importance of this baseline (not used yet).';
        $dictionary->columns->notes = 'Any additional notes you care to make.';
        $dictionary->columns->documentation = 'Any additional documentation you need.';
        $dictionary->columns->table = 'The table to compare in the database.';
        $dictionary->columns->tests = '';
        $dictionary->columns->edited_by = $CI->temp_dictionary->edited_by;
        $dictionary->columns->edited_date = $CI->temp_dictionary->edited_date;
        return $dictionary;
    }
}
// End of file m_baselines_policies.php
// Location: ./models/m_baselines_policies.php
