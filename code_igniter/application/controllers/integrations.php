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
* @category  Controller
* @package   Integrations
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_4.3.0
* @link      http://www.open-audit.org
*/

/**
* Base Object Integrations
*
* @access   public
* @category Controller
* @package  Integrations
* @author   Mark Unwin <marku@opmantek.com>
* @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @link     http://www.open-audit.org
 */
class Integrations extends MY_Controller
{
    /**
    * Constructor
    *
    * @access    public
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_integrations');
        // inputRead();
        $this->response = response_create();
        $this->output->url = $this->config->config['oa_web_index'];
    }

    /**
    * Index that is unused
    *
    * @access public
    * @return NULL
    */
    public function index()
    {
    }

    /**
    * Our remap function to override the inbuilt controller->method functionality
    *
    * @access public
    * @return NULL
    */
    public function _remap()
    {
        $this->{$this->response->meta->action}();
    }

    /**
    * Process the supplied data and create a new object
    *
    * @access public
    * @return NULL
    */
    public function create()
    {
        $this->response->meta->id = $this->{'m_'.$this->response->meta->collection}->create($this->response->meta->received_data->attributes);
        $this->response->data = $this->{'m_'.$this->response->meta->collection}->read($this->response->meta->id);
        $this->response->meta->format = 'json';
        output($this->response);
    }

    /**
    * Read a single object
    *
    * @access public
    * @return NULL
    */
    public function read()
    {
        $this->response->data = $this->{'m_'.$this->response->meta->collection}->read($this->response->meta->id);
        if (! empty($this->response->data) && is_array($this->response->data)) {
            $this->response->meta->total = 1;
            $this->response->meta->filtered = 1;
            $this->load->model('m_orgs');
            $this->response->dictionary = $this->{'m_'.$this->response->meta->collection}->dictionary();
            if ($this->response->meta->format === 'screen') {
                $this->response->included = array_merge($this->response->included, $this->m_orgs->collection($this->user->id));
            } else {
                $this->response->included = array_merge($this->response->included, $this->m_orgs->read($this->response->data[0]->attributes->org_id));
            }
            $this->response->included = array_merge($this->response->included, $this->m_integrations->read_sub_resource($this->response->meta->id));
        } else {
            log_error('ERR-0002', $this->response->meta->collection . ':read');
            $this->session->set_flashdata('error', 'No object could be retrieved when ' . $this->response->meta->collection . ' called m_' . $this->response->meta->collection . '->read.');
            if ($this->response->meta->format !== 'json') {
                redirect($this->response->meta->collection);
            }
        }
        output($this->response);
    }

    /**
    * Process the supplied data and update an existing object
    *
    * @access public
    * @return NULL
    */
    public function update()
    {
        include 'include_update.php';
    }

    /**
    * Delete an existing object
    *
    * @access public
    * @return NULL
    */
    public function delete()
    {
        include 'include_delete.php';
    }

    /**
     * [execute description]
     * @return [type] [description]
     */
    public function execute()
    {
        $this->response->meta->format = 'json';
        $this->m_integrations->queue($this->response->meta->id);
        $this->load->model('m_queue');
        $this->m_queue->start();
        sleep(2);
        if ($this->response->meta->format === 'json') {
            $this->response->meta->format = 'json';
            $this->response->data = $this->m_integrations->read($this->response->meta->id);
            $this->response->included = array_merge($this->response->included, $this->m_orgs->read($this->response->data[0]->attributes->org_id));
            $this->response->included = array_merge($this->response->included, $this->m_integrations->read_sub_resource($this->response->meta->id));
            output($this->response);
        } else {
            redirect('integrations/'.$this->response->meta->id);
        }
    }

    /**
    * Execute this integration
    *
    * @access public
    * @return NULL
    */
    public function execute_now()
    {
        $this->{'m_'.$this->response->meta->collection}->execute($this->response->meta->id);
        $sql = "SELECT * FROM integrations_log WHERE integrations_id = " . intval($this->response->meta->id);
        $query = $this->db->query($sql);
        $result = $query->result();
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }

    /**
    * Collection of objects
    *
    * @access public
    * @return NULL
    */
    public function collection()
    {
        $this->{'m_'.$this->response->meta->collection}->collection(0, 1);
        output($this->response);
    }

    /**
    * Supply a HTML form for the user to create an object
    *
    * @access public
    * @return NULL
    */
    public function create_form()
    {
        $this->response->dictionary = $this->m_integrations->dictionary();
        $this->load->model('m_orgs');
        $this->response->included = array_merge($this->response->included, $this->m_orgs->collection($this->user->id));
        $this->load->model('m_queries');
        $this->response->included = array_merge($this->response->included, $this->m_queries->collection($this->user->id));
        $this->load->model('m_groups');
        $this->response->included = array_merge($this->response->included, $this->m_groups->collection($this->user->id));

        $this->response->defaults = new stdClass();
        $this->response->defaults->name = 'NMIS Integration';
        $count = $this->m_integrations->count();
        if (!empty($count)) {
            $this->response->defaults->name = 'NMIS Integration ' . ($count + 1);
        }
        output($this->response);
    }

    /**
     * Delete a field
     * @return [type] [description]
     */
    public function sub_resource_delete()
    {
        $subresource = new stdClass();
        foreach ($this->response->meta->query_parameters as $parameter) {
            if ($parameter->name === 'internal_field_name') {
                $subresource->internal_field_name = $parameter->value;
            }
            if ($parameter->name === 'external_field_name') {
                $subresource->external_field_name = $parameter->value;
            }
        }
        $this->m_integrations->sub_resource_delete($this->response->meta->id, $subresource);
        $this->response->meta->format = 'json';
        output($this->response);
    }

    /**
    * The requested table will have optimize run upon it and it's autoincrement reset to 1
    *
    * @access public
    * @return NULL
    */
    public function reset()
    {
        include 'include_reset.php';
    }

}
// End of file integrations.php
// Location: ./controllers/integrations.php
