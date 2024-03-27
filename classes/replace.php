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
 * Replace
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_proper;

use core_text;
use core_user;

/**
 * Replace
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 */
class replace {
    /**
     * Do all work
     * @return bool
     */
    public static function doall(): bool {
        global $DB;
        $ids = $DB->get_fieldset_select('user', 'id', 'confirmed = 1 AND deleted = 0', []);
        $return = true;
        foreach ($ids as $id) {
            foreach (self::implemented() as $field) {
                $return = $return && self::dowork($field, $id);
            }
        }
        return $return;
    }

    /**
     * Do one
     * @param int $id
     * @return bool
     */
    public static function doone(int $id): bool {
        $return = true;
        foreach (self::implemented() as $field) {
            $return = $return && self::dowork($field, $id);
        }
        return $return;
    }

    /**
     * Do work
     * @param string $field
     * @param int $id
     * @return bool
     */
    private static function dowork(string $field, int $id): bool {
        global $DB;
        $enabled = (int)$DB->get_field('config', 'value', ['name' => 'proper_' . $field]);
        if ($enabled == 0 || $id == 1) {
            return true;
        }
        return self::doreplace($field, $id, $enabled);
    }

    /**
     * Do replace
     * @param string $field
     * @param int $id
     * @param int $enabled
     * @return bool
     */
    public static function doreplace(string $field, int $id, int $enabled): bool {
        global $DB;
        $value = $DB->get_field('user', $field, ['id' => $id]);
        $newvalue = $value;
        switch ($enabled) {
            case 2:
                $newvalue = core_text::strtolower($value);
                break;
            case 3:
                $newvalue = core_text::strtoupper($value);
                break;
            default:
                $newvalue = core_text::strtolower($value);
                $newvalue = core_text::strtotitle($newvalue);
                break;
        }
        if ($value !== $newvalue) {
            return $DB->set_field('user', $field, trim($newvalue), ['id' => $id]);
        }
        return true;
    }

    /**
     * Implemented fields
     * @return array
     */
    public static function implemented(): array {
        $names = \core_user\fields::get_name_fields(true);
        return array_merge($names, ['email', 'city', 'idnumber', 'institution', 'department', 'address']);
    }
}
