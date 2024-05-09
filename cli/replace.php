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
 * This script allows you to replace text fields of any user.
 *
 * @package    tool_proper
 * @copyright  iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

$longparams = [
    'help' => false,
    'id' => '',
    'all' => '',
];

$shortparams = [
    'h' => 'help',
    'i' => 'id',
    'q' => 'all',
];

[$options, $unrecognized] = cli_get_params($longparams, $shortparams);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
    exit();
}

if ($options['help']) {
    $help =
        "Replace text field of one/all user(s) with proper text.

Options:
-h, --help                    Print out this help
-i, --id=id                   Specify user by id
-a, --all                     Do all users

Example:
\$sudo -u www-data /usr/bin/php admin/tool/proper/cli/replace.php --id=33
\$sudo -u www-data /usr/bin/php admin/tool/proper/cli/replace.php --all
";

    echo $help;
    exit();
}
if ($options['all']) {
    $ids = $DB->get_fieldset_select('user', 'id', 'confirmed = 1 AND deleted = 0', []);
    foreach (\tool_proper\replace::implemented() as $field) {
        $enabled = $DB->get_field('config', 'value', ['name' => 'proper_' . $field]);
        if ($enabled > 0) {
            foreach ($ids as $id) {
                \tool_proper\replace::doreplace($field, $id, $enabled);
            }
        }
    }
    exit();
}

if ($options['id']) {
    foreach (\tool_proper\replace::implemented() as $field) {
        $enabled = $DB->get_field('config', 'value', ['name' => 'proper_' . $field]);
        if ($enabled > 0) {
            \tool_proper\replace::doreplace($field, (int)$options['id'], $enabled);
        }
    }
    exit();
}
exit();
