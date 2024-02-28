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
 * User created task
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_proper;

use core_user;

/**
 * User created task
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_created extends \core\task\adhoc_task {

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        $data = $this->get_custom_data();
        if ($newuser = core_user::get_user($data->userid)) {
            return replace::doone($newuser->id);
        }
        return true;
    }
}
