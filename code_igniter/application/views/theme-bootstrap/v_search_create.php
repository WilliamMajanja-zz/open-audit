<?php
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
* @category  View
* @package   Open-AudIT
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   GIT: Open-AudIT_4.3.0
* @link      http://www.open-audit.org
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
        <span class="text-left"><?php echo __('Search'); ?></span>
        <span class="pull-right" style="padding-right:20px;"><?php echo $this->response->meta->total . ' results'; ?></span>
        </h3>
    </div>
    <div class="panel-body">
<?php
if (!empty($this->response->data)) { ?>
    <form action="devices?action=update" method="post" id="bulk_edit" name="bulk_edit">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <?php
            if (!empty($this->response->data[0]->attributes)) {
                $properties = get_object_vars($this->response->data[0]->attributes);
            } else {
                $properties = array();
            }
            foreach ($properties as $key => $value) {
                if (strpos($key, '.') !== false) {
                    $key = substr($key, strpos($key, '.')+1);
                }
                if (strrpos($key, 'ip_padded') === strlen($key)-9 or $key == 'ip.ip_padded') {
                    continue;
                }
                if ($key == 'system.id' or $key == 'id') {
                    $key = 'ID';
                }
                $key = str_replace('_', ' ', $key);
                $key = str_replace('os ', 'OS ', $key);
                $key = str_replace(' id', ' ID', $key);
                $key = str_replace(' ip', ' IP', $key);
                $key = ucwords($key);
                if ($key == 'Ip') {
                    $key = 'IP';
                }
                if (stripos($key, 'icon') !== false) {
                    echo "            <th class=\"text-center\">" . __($key) . "</th>\n";
                } else {
                    echo "            <th>" . __($key) . "</th>\n";
                }
            }
            if ($this->m_users->get_user_permission('', 'devices', 'u')) {
                foreach ($properties as $key => $value) {
                    if ($key == 'system.id') {
                        echo "            <th class=\"text-center\">\n";
                        echo "              <button type=\"button\" class=\"btn btn-primary btn-xs bulk_edit_button\">" . __('Bulk Edit') . "</button>&nbsp;\n";
                        echo "              <input type=\"checkbox\" name=\"select_all\" id=\"select_all\" onClick=\"select_all_click()\"/>\n";
                        echo "            </th>\n";
                    }
                }
            }
            ?>
          </tr>
        </thead>
        <tbody>
    <?php

    # for every returned row
    foreach ($this->response->data as $item) {
        echo "          <tr>\n";

        # for every attribute in our returned row
        foreach ($properties as $property => $value) {
            # remove any extra spacing
            $property = trim($property);

            if (!empty($item->attributes->{$property})) {
                if ($property == 'system.id') {
                    echo '<td><a style="min-width:38px;" href="' . $item->links->self . '" role="button" class="btn btn-sm btn-success">' . $item->id . '</a></td>';
                } elseif (strrpos($property, 'ip_padded') === strlen($property)-9 or $property == 'ip.ip_padded') {
                    continue;
                } elseif ((strrpos($property, 'ip') === strlen($property)-2)) {
                    if (!empty($item->attributes->{$property . '_padded'})) {
                        echo "            <td><span style='display:none;'>" . str_replace('.', '', $item->attributes->{$property . '_padded'}) . "</span>" . $item->attributes->$property . "</td>\n";
                    } else {
                        echo "            <td>" . $item->attributes->$property . "</td>\n";
                    }
                } elseif (strrpos($property, 'icon') === strlen($property)-4) {
                    echo "            <td style=\"text-align: center;\"><img src=\"" . str_replace("index.php", "", site_url()) . "device_images/" . strtolower(str_replace(" ", "_", $item->attributes->$property)) . ".svg\" style='border-width:0; width:24px; height:24px' title=\"" . $item->attributes->$property . "\" alt=\"" . $item->attributes->$property . "\"/></td>\n";
                } else {
                    if (strlen($item->attributes->$property) > 30) {
                        echo "            <td class=\"wrap\">" . substr($item->attributes->$property, 0, 30) . "...</span></td>\n";
                    } else {
                        echo "            <td>" . $item->attributes->$property . "</td>\n";
                    }
                }
            } else {
                if ($property != 'ip.ip_padded') {
                    echo "            <td></td>\n";
                }
            }
        }
        if ($this->m_users->get_user_permission('', 'devices', 'u')) {
            if (!empty($item->attributes->{'system.id'})) {
                echo "            <td style=\"text-align: center;\"><input type='checkbox' id='ids[" . $item->attributes->{'system.id'} . "]' value='" . $item->attributes->{'system.id'} . "' name='ids[" . $item->attributes->{'system.id'} . "]' /></td>\n";
            }
        }
        echo "          </tr>\n";
    }
    ?>
        </tbody>
      </table>
    </form>
    <?php
}
    ?>
  </div>
</div>