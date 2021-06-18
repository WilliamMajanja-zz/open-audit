<?php
#
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

/**
* @category  Helper
* @package   Open-AudIT
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_4.1.2
* @link      http://www.open-audit.org
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!function_exists('integrations_execute')) {
    function integrations_execute($integration)
    {
        error_reporting(E_ALL);
        $CI = & get_instance();

        // Get our devices
        $url = $integration->attributes->attributes->url;

        // Create temp file to store cookies
        $ckfile = tempnam("/tmp", "CURLCOOKIE");

        $form_fields = array(
            'username' => $integration->attributes->attributes->username,
            'password' => $integration->attributes->attributes->password,
        );

        // Post login form and follow redirects
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url . '/admin/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $form_fields);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $output = curl_exec($ch);
        if (strpos($output, 'HTTP/1.1 403 Forbidden') !== false) {
            // bad credentials
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not logon to NMIS, check Username and Password.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        if (strpos($output, 'redirect_url=') !== false) {
            // Likely a bad URL
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not logon to NMIS, check URL.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        if (strpos($output, 'Set-Cookie') !== false) {
            // Success
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', 'Logged on to NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }

        // Get the external devices list
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Accepts all CAs
        curl_setopt($ch, CURLOPT_URL, $url . '/admin/api/v2/nodes.json');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $output = curl_exec($ch);
        if (!is_string($output) || !strlen($output)) {
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not retrieve devices from NMIS.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        $external_devices = json_decode($output);
        if (empty($external_devices)) {
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'No devices returned from NMIS.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        } else {
            $count = count($external_devices);
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', '$count devices returned from NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }

        // Restrict the device select if required
        if ($integration->attributes->select_external_type === 'none') {
            unset($external_devices);
            $external_devices = array();
        }
        if ($integration->attributes->select_external_type === 'attribute') {
            foreach ($external_devices as $key => $value) {
                $value = array_reduce(explode('.', $integration->attributes->select_external_attribute), function ($previous, $current) { return isset($previous->$current) && !empty($previous->$current)? $previous->$current: null; }, $value);
                if ((string)$value !== (string)$integration->attributes->select_external_value) {
                    unset($external_devices[$key]);
                }
            }
            $count = count($external_devices);
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', '$count devices filtered from NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }


        // Take the external data and make an internal structure
        $external_formatted_devices = array();

        foreach ($external_devices as $device) {
            $newdevice = new stdClass();            
            foreach ($integration->attributes->fields as $field) {
                if (empty($field->internal_field_name)) {
                    $temp = explode('.', $field->external_field_name);
                    $field->internal_field_name = 'fields.nmis_' . $temp[count($temp)-1];
                }

                if (!empty($field->internal_field_name)) {
                    $int = explode('.', $field->internal_field_name);
                    if (empty($newdevice->{$int[0]})) {
                        $newdevice->{$int[0]} = new stdClass();
                    }
                    if (empty($newdevice->{$int[0]}->{$int[1]})) {

                        $newdevice->{$int[0]}->{$int[1]} = array_reduce(explode('.', $field->external_field_name), function ($previous, $current) { return isset($previous->$current) && !empty($previous->$current)? $previous->$current: null; }, $device);

                        if (is_null($newdevice->{$int[0]}->{$int[1]})) {
                            unset($newdevice->{$int[0]}->{$int[1]});
                        }
                    }
                }
            }
            $external_formatted_devices[] = $newdevice;
        }

        # Ensure we have an IP in system.ip
        foreach ($external_formatted_devices as $device) {
            if (isset($device->system->ip) and !empty($device->system->ip)) {
                if (!filter_var($device->system->ip, FILTER_VALIDATE_IP)) {
                    if (strpos('.', $device->system->ip) !== false) {
                        $device->system->dns_fqdn = $device->system->ip;
                    } else {
                        $device->system->dns_hostname = $device->system->ip;
                    }
                    $device->system->ip = gethostbyname($device->system->ip);
                }
                $fqdn = gethostbyaddr($device->system->ip);
                if (empty($device->system->dns_fqdn) and strpos($fqdn, '.') !== false) {
                    $device->system->dns_fqdn = $fqdn;
                }
            }
        }


        // check each retrieved device and see if we already have it
        $CI->load->model('m_devices');
        $CI->load->model('m_device');
        $CI->load->model('m_edit_log');
        $CI->load->model('m_rules');
        $CI->load->helper('audit');


        $log = new stdClass();
        $log->type = 'system';
        $log->severity = 5;
        $log->user = 'system';
        $log->collection = 'integrations';
        $log->action = 'create device';
        $log->status = '';
        $log->summary = '';
        $log->detail = '';

        foreach ($external_formatted_devices as $device) {
            $device->system->last_seen_by = 'integrations';
            $parameters = new stdClass();
            $parameters->details = $device->system;
            $parameters->log = $log;
            $id = $CI->m_device->match($parameters);
            if (!empty($id)) {
                // We matched an existing device
                $message = 'Device match found, ID: ' . $id . ' for ' . $device->system->name;
                $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', ?, 'success')";
                $data = array($integration->id, microtime(true), $message);
                $query = $CI->db->query($sql, $data);
                $device->system->id = $id;
                // Should we update it?
                if ($integration->attributes->update_local_from_external === 'y') {
                    $temp_device = new stdClass();
                    $temp_device->id = $device->system->id;
                    $temp_device->last_seen_by = 'integrations';
                    foreach ($integration->attributes->fields as $field) {
                        if ($field->priority === 'external' and strpos($field->internal_field_name, 'system.') !== false) {
                            // a regular field in Open-AudIT that we should update
                            $system_field = str_replace('system.', '', $field->internal_field_name);
                            if (!empty($device->system->{$system_field})) { # TODO - better than not empty
                                $temp_device->{$system_field} = $device->system->{$system_field};
                            }
                        }
                    }
                    $message = 'Updating device ID: ' . $id . ' for ' . $device->system->name;
                    $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', ?, 'success')";
                    $data = array($integration->id, microtime(true), $message);
                    $query = $CI->db->query($sql, $data);

                    // $message = json_encode($temp_device);
                    // $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', ?, '')";
                    // $data = array($integration->id, microtime(true), $message);
                    // $query = $CI->db->query($sql, $data);

                    $CI->m_device->update($temp_device);
                }
            } else {
                // No existing device
                $message = 'No device match found for ' . $device->system->name;
                $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', ?, 'success')";
                $data = array($integration->id, microtime(true), $message);
                $query = $CI->db->query($sql, $data);
                if ($integration->attributes->create_local_from_external === 'y') {
                    $device->system->id = $CI->m_device->insert($device->system);
                    if (!empty($device->system->id)) {
                        $message = 'Device Created locally ID: ' . $device->system->id . ', ' . $device->system->name;
                        $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', ?, 'success')";
                        $data = array($integration->id, microtime(true), $message);
                        $query = $CI->db->query($sql, $data);
                    } else {
                        $message = 'Could not create device ' . $device->system->name;
                        $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', ?, 'success')";
                        $data = array($integration->id, microtime(true), $message);
                        $query = $CI->db->query($sql, $data);
                    }
                }
            }

            if ($integration->attributes->create_local_from_external === 'y' or $integration->attributes->update_local_from_external === 'y') {
                # Rules
                $parameters = new stdClass();
                $parameters->id = intval($device->system->id);
                $parameters->discovery_id = '';
                $parameters->action = 'update';
                $CI->m_rules->execute($parameters);

                $sql = "SELECT * FROM fields";
                $query = $CI->db->query($sql);
                $all_fields = $query->result();

                $sql = "SELECT * FROM field WHERE system_id = ?";
                $data = array($device->system->id);
                $query = $CI->db->query($sql, $data);
                $device_fields = $query->result();

                # Custom fields
                foreach ($integration->attributes->fields as $field) {
                    if ($field->priority === 'external' and (strpos($field->internal_field_name, 'fields.') !== false or $field->internal_field_name === '')) {
                        // a custom field in Open-AudIT that we should update
                        $field_name = str_replace('fields.', '', $field->internal_field_name);
                        if (empty($custom_field_name)) {
                            $external_field = explode('.', $field->external_field_name);
                            $field_name = $integration->attributes->type . '_' . $external_field[count($external_field)-1];
                        }

                        $id = 0;
                        foreach ($all_fields as $temp_field) {
                            if ($field_name === $temp_field->name) {
                                $id = $temp_field->id;
                            }
                        }

                        if (!$id) {
                            // Throw an error as we should always have a field already created
                        }

                        $device_field_id = 0;
                        $value = '';
                        foreach ($device_fields as $device_field) {
                            if ($id === $device_field->fields_id) {
                                $device_field_id = $device_field->id;
                                $value = $device_field->value;
                            }
                        }

                        if (!$device_field_id) {
                            // Insert a new field
                            $sql = "INSERT INTO field VALUES (null, ?, ?, NOW(), ?)";
                            $data = array($device->system->id, $id, $device->fields->{$field_name});
                            $query = $CI->db->query($sql, $data);
                            // Insert an edit log
                            $sql = "INSERT INTO edit_log (user_id, system_id, details, source, weight, db_table, db_column, timestamp, value, previous_value) VALUES (0, ?, 'Field data was created', 'integrations', 1000, 'field', ?, NOW(), ?, ?)";
                            $data = array($device->system->id, $field_name, $device->fields->{$field_name}, $value);
                            $CI->db->query($sql, $data);

                        } else {
                            // We already have the field associated to the device, check if the value has changed befofe updating
                            if ((string)$value !== (string)$device->fields->{$field_name}) {
                                // It IS different - update it
                                $sql = "UPDATE field SET value = ? WHERE id = ?";
                                $data = array($device->fields->{$field_name}, $device_field_id);
                                $query = $CI->db->query($sql, $data);
                                // Insert an edit log
                                $sql = "INSERT INTO edit_log (user_id, system_id, details, source, weight, db_table, db_column, timestamp, value, previous_value) VALUES (0, ?, 'Field data was updated', 'integrations', 1000, 'field', ?, NOW(), ?, ?)";
                                $data = array($device->system->id, $field_name, $device->fields->{$field_name}, $value);
                                $CI->db->query($sql, $data);

                            }
                        }
                    }
                }
            }



        }
        
        $sql = "SELECT * FROM integrations_log WHERE integrations_id = ?";
        $data = array($integration->attributes->id);
        $query = $CI->db->query($sql, $data);
        $result = $query->result();
        #echo "<pre>\n" . json_encode($result, JSON_PRETTY_PRINT);

        // $table = "<table><thead><tr><th>id</th><th>IntID</th><th>timestamp</th><th>microtime</th><th>severity</th><th>message</th><th>result</th></tr></thead><tbody>";
        // foreach ($result as $row) {
        //     $table .= "<tr><td>" . $row->id . "</td><td>" . $row->integrations_id . "</td><td>" . $row->timestamp . "</td><td>" . $row->microtime . "</td><td>" . $row->severity_text . "</td><td>" . $row->message . "</td><td>" . $row->result . "</td><td></tr>";
        // }

        // echo $table;
        // echo "<pre>";
        // #echo json_encode($external_formatted_devices);
        // echo "</pre>";



#        print_r($external_formatted_devices);
        exit;








    }



    function integrations_pre($integration)
    {
        return true;
    }





    function integrations_collection($integration)
    {
        error_reporting(E_ALL);
        $CI = & get_instance();

        // Get our devices
        $url = $integration->attributes->attributes->url;

        // Create temp file to store cookies
        $ckfile = tempnam("/tmp", "CURLCOOKIE");

        $form_fields = array(
            'username' => $integration->attributes->attributes->username,
            'password' => $integration->attributes->attributes->password,
        );

        // Post login form and follow redirects
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url . '/admin/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $form_fields);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $output = curl_exec($ch);
        if (strpos($output, 'HTTP/1.1 403 Forbidden') !== false) {
            // bad credentials
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not logon to NMIS, check Username and Password.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        if (strpos($output, 'redirect_url=') !== false) {
            // Likely a bad URL
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not logon to NMIS, check URL.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        if (strpos($output, 'Set-Cookie') !== false) {
            // Success
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', 'Logged on to NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }

        // Get the external devices list
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Accepts all CAs
        curl_setopt($ch, CURLOPT_URL, $url . '/admin/api/v2/nodes.json');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $output = curl_exec($ch);
        if (!is_string($output) || !strlen($output)) {
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'Could not retrieve devices from NMIS.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        }
        $external_devices = json_decode($output);
        if (empty($external_devices)) {
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'error', 'No devices returned from NMIS.', 'fail')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
            return false;
        } else {
            $count = count($external_devices);
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', '$count devices returned from NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }

        // Restrict the device select if required
        if ($integration->attributes->select_external_type === 'none') {
            unset($external_devices);
            $external_devices = array();
        }
        if ($integration->attributes->select_external_type === 'attribute') {
            foreach ($external_devices as $key => $value) {
                $value = array_reduce(explode('.', $integration->attributes->select_external_attribute), function ($previous, $current) { return isset($previous->$current) && !empty($previous->$current)? $previous->$current: null; }, $value);
                if ((string)$value !== (string)$integration->attributes->select_external_value) {
                    unset($external_devices[$key]);
                }
            }
            $count = count($external_devices);
            $sql = "INSERT INTO integrations_log VALUES (null, ?, null, ?, 'debug', '$count devices filtered from NMIS.', 'success')";
            $data = array($integration->id, microtime(true));
            $query = $CI->db->query($sql, $data);
        }

        return $external_devices;
    }









}