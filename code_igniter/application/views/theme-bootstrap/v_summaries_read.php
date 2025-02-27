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
$item = $this->response->data[0];
?>
<form class="form-horizontal" id="form_update" method="post" action="<?php echo $this->response->links->self; ?>">
    <div class="panel panel-default">
        <?php include('include_read_panel_header.php'); ?>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group">
                        <label for="id" class="col-sm-3 control-label"><?php echo __('ID'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="id" name="id" value="<?php echo $item->attributes->id; ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label"><?php echo __('Name'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $item->attributes->name ?>" disabled>
                            <?php if (!empty($edit)) { ?>
                            <span class="input-group-btn">
                                <button id="edit_name" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="name"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="org_id" class="col-sm-3 control-label"><?php echo __('Organisation'); ?></label>
                        <div class="col-sm-8 input-group">
                            <select class="form-control" id="org_id" name="org_id" disabled>
                                <?php
                                foreach ($this->response->included as $org) {
                                    if ($org->type == 'orgs') { ?>
                                        <option value="<?php echo $org->id; ?>"<?php if ($item->attributes->org_id == $org->id) { echo " selected"; } ?>><?php echo $org->attributes->name; ?></option>
                                <?php
                                    }
                                } ?>
                                </select>
                                <?php if (!empty($edit)) { ?>
                                <span class="input-group-btn">
                                    <button id="edit_org_id" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="org_id"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                                </span>
                                <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="menu_category" class="col-sm-3 control-label"><?php echo __('Menu Category'); ?></label>
                        <div class="col-sm-8 input-group">
                            <select class="form-control" id="menu_category" name="menu_category" disabled>
                                <?php
                                foreach ($this->response->included as $attribute) {
                                    if ($attribute->type == 'attributes' and $attribute->attributes->resource == 'queries' and $attribute->attributes->type == 'menu_category') {
                                        $selected = '';
                                        if ($attribute->attributes->name == $item->attributes->menu_category) {
                                            $selected = ' selected';
                                        }
                                        $label = $attribute->attributes->name;
                                        if (empty($label)) {
                                            $label = ' ';
                                        }
                                        echo "                                <option $selected label=\"$label\" value=\"" . $attribute->attributes->name . "\">" . $attribute->attributes->value . "</option>\n";
                                    }
                                }
                                ?>
                            </select>
                            <?php if (!empty($edit)) { ?>
                            <span class="input-group-btn">
                                <button id="edit_menu_category" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="menu_category"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="table" class="col-sm-3 control-label"><?php echo __('Table'); ?></label>
                        <div class="col-sm-8 input-group">
                            <select class="form-control" id="table" name="table" disabled>
                            <?php
                            foreach ($this->response->included as $table) {
                                if ($table->type == 'table') { ?>
                                    <option value="<?php echo $table->attributes->name; ?>"<?php if ($item->attributes->table === $table->attributes->name) { echo ' selected'; } ?>><?php echo $table->attributes->name; ?></option>
                                <?php
                                }
                            } ?></select>
                            <?php if (!empty($edit)) { ?>
                            <span class="input-group-btn">
                                <button id="edit_table" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="table"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="column" class="col-sm-3 control-label"><?php echo __('Column'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="column" name="column" value="<?php echo $item->attributes->column; ?>" disabled>
                            <?php if (!empty($edit)) { ?>
                            <span class="input-group-btn">
                                <button id="edit_column" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="column"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="column" class="col-sm-3 control-label"><?php echo __('Extra Columns'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="extra_columns" title="extra_columns" name="extra_columns" value="<?php echo $item->attributes->extra_columns; ?>" disabled>
                            <?php if (!empty($edit)) { ?>
                            <span class="input-group-btn">
                                <button id="edit_extra_columns" data-action="edit" class="btn btn-default edit_button" type="button" data-attribute="extra_columns"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>
                            </span>
                            <?php } ?>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="edited_by" class="col-sm-3 control-label"><?php echo __('Edited By'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="edited_by" name="edited_by" value="<?php echo $item->attributes->edited_by; ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edited_date" class="col-sm-3 control-label"><?php echo __('Edited Date'); ?></label>
                        <div class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="edited_date" name="edited_date" value="<?php echo $item->attributes->edited_date; ?>" disabled>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="col-md-8 col-md-offset-2">
                        <?php if ( ! empty($this->response->dictionary->about)) {
                            echo "<h4 class=\"text-center\">About</h4><br />";
                            echo html_entity_decode($this->response->dictionary->about);
                        } ?>
                        <?php if ( ! empty($this->response->dictionary->notes)) {
                            echo "<h4 class=\"text-center\">Notes</h4><br />";
                            echo html_entity_decode($this->response->dictionary->notes);
                        } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>
