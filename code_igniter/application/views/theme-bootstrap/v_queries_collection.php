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
        <?php include('include_collection_panel_header.php'); ?>
    </div>
    <div class="panel-body">
        <?php include('include_collection_panel_body_links.php'); ?>
        <?php if (!empty($this->response->data)) { ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                <th style="text-align:center;"><?php echo __('Execute')?></th>
                <th style="text-align:center;"><?php echo __('Details')?></th>
                <th><?php echo __('Name')?></th>
                <th><?php echo __('Description')?></th>
                <?php if ($this->m_users->get_user_permission('', 'queries', 'd')) { ?>
                <th style="text-align:center;"><?php echo __('Delete')?></th>
                <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->response->data as $item): ?>
                <?php if (strlen($item->attributes->description) > 30) { $item->attributes->description = substr($item->attributes->description, 0, 27) . '...'; } ?>
                <tr>
                    <td class="text-center"><a class="btn btn-sm btn-success" href="<?php echo $item->links->self; ?>/execute"><span class="glyphicon glyphicon-play" aria-hidden="true"></span></a></td>
                    <td class="text-center"><a class="btn btn-sm btn-primary" href="<?php echo $item->links->self; ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></td>
                    <?php refine('queries.name', $item->attributes->name); ?>
                    <?php refine('queries.description', $item->attributes->description); ?>
                    <?php if ($this->m_users->get_user_permission('', 'queries', 'd')) { ?>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete_link" data-id="<?php echo $item->id; ?>" data-name="<?php echo $item->attributes->name ?>" aria-label="Left Align" ><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td>
                    <?php } ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php } ?>
  </div>
</div>