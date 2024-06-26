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
 * Event observer.
 *
 * @package    tool_proper
 * @copyright  iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_proper;

/**
 * Event observer.
 *
 * @package   tool_proper
 * @copyright iplusacademy (www.iplusacademy.org)
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * New user created.
     *
     * @param \core\event\user_created $user user object
     */
    public static function usercreated(\core\event\user_created $user) {
        if (!empty($user)) {
            $adhock = new \tool_proper\user_created();
            $adhock->set_custom_data(['userid' => $user->objectid]);
            \core\task\manager::queue_adhoc_task($adhock);
        }
    }
}
