<?php
if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
}
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

/*
* @category  Helper
* @package   Open-AudIT
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   3.1.2
* @link      http://www.open-audit.org
 */

# Vendor Ceragon

$get_oid_details = function ($ip, $credentials, $oid) {
    $details = new stdClass();
    $details->manufacturer = 'Ceragon';
    $details->type = 'wireless link';

    if (strpos($oid, '1.3.6.1.4.1.2281.1.20') !== false) {
        $details->model = 'FibeAir IP-20'; }

    if (strpos($oid, '1.3.6.1.4.1.2281.1.20.1.3') !== false) {
        $details->model = 'FibeAir IP-20G/GX'; }

    if ($oid == '1.3.6.1.4.1.2281.1.20.1.1') { $details->model = 'FibeAir IP-20N 1RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.1.2') { $details->model = 'FibeAir IP-20N 1RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.1.3') { $details->model = 'FibeAir IP-20LH 1RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.1.4') { $details->model = 'FibeAir IP-20 Evolution LH 1RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.2') { $details->model = 'FibeAir IP-20N 2RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.2.2') { $details->model = 'FibeAir IP-20A 2RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.2.3') { $details->model = 'FibeAir IP-20LH 2RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.2.4') { $details->model = 'FibeAir IP-20 Evolution LH 2RU'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.3.1') { $details->model = 'FibeAir IP-20G'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.1.3.2') { $details->model = 'FibeAir IP-20GX'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.2.2') { $details->model = 'FibeAir IP-20C'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.2.2.2') { $details->model = 'FibeAir IP-20S'; $details->type = 'wireless link'; }
    if ($oid == '1.3.6.1.4.1.2281.1.20.2.2.3') { $details->model = 'FibeAir IP-20E'; $details->type = 'wireless link'; }
    return($details);
};