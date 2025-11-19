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
 * @copyright  iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $p = 'tool_proper';
    $s = get_strings(['upper', 'lower', 'proper', 'handled', 'pluginname'], $p);
    $disabled = get_string('disabled', 'admin');
    $options = [0 => $disabled, 1 => $s->proper, 2 => $s->lower, 3 => $s->upper];
    $limited = [0 => $disabled, 2 => $s->lower];

    $temp = new admin_settingpage('toolproper', $s->pluginname);
    $temp->add(new admin_setting_heading('proper_handled', $s->handled, '', ''));
    $arr = \tool_proper\replace::implemented();
    foreach ($arr as $value) {
        $sel = $p . '/' . $value;
        $opts = ($value == 'email') ? $limited : $options;
        if (str_starts_with((string) $value, \core_user\fields::PROFILE_FIELD_PREFIX)) {
            $str = \core_user\fields::get_display_name($value);
        } else {
            $str = get_string($value);
        }

        $temp->add(new admin_setting_configselect($sel, $str, '', 0, $opts));
    }

    $ADMIN->add('accounts', $temp);
}
