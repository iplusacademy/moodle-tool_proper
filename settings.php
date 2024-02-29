<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tool proper settings.
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $s = get_strings(['upper', 'lower', 'proper', 'handled', 'pluginname'], 'tool_proper');
    $temp = new admin_settingpage('toolproper', $s->pluginname);
    $options = [0 => get_string('disabled', 'admin'), 1 => $s->proper, 2 => $s->lower, 3 => $s->upper];
    $temp->add(new admin_setting_heading('proper_handled', $s->handled, '', ''));
    $arr = \tool_proper\replace::implemented();
    foreach ($arr as $value) {
        if ($value == 'email') {
            $limited = [0 => get_string('disabled', 'admin'), 2 => $s->lower];
            $temp->add(new admin_setting_configselect('proper_' . $value, get_string($value), '', 0, $limited));
        } else {
            $temp->add(new admin_setting_configselect('proper_' . $value, get_string($value), '', 0, $options));
        }
    }
    $ADMIN->add('accounts', $temp);
}
