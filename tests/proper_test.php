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
 * File containing tests for tool_proper
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_proper;

use advanced_testcase;

/**
 * The tool_proper test class.
 *
 * @package    tool_proper
 * @copyright  2024 iplusacademy
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class proper_test extends advanced_testcase {

    /**
     * Test returning metadata.
     * @covers \tool_proper\privacy\provider
     */
    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('tool_proper');
        $reason = privacy\provider::get_reason($collection);
        $this->assertEquals($reason, 'privacy:metadata');
    }

    /**
     * Test the observer.
     * @covers \tool_proper\observer
     */
    public function test_observer(): void {
        $class = new \ReflectionClass(new observer());
        $this->assertCount(1, $class->getMethods());
        $this->assertCount(0, $class->getProperties());
    }

    /**
     * Test the usercreated class.
     * @covers \tool_proper\user_created
     */
    public function test_user_created_task(): void {
        $this->resetaftertest();
        $class = new user_created();
        $class->set_custom_data(['userid' => 9999]);
        $class->execute();
        $generator = $this->getDataGenerator();
        $userid = $generator->create_user(['firstname' => 'aAaAaA'])->id;
        $class->set_custom_data(['userid' => $userid]);
        $class->execute();
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'aAaAaA');
        set_config('proper_firstname', 1);
        $class->set_custom_data(['userid' => $userid]);
        $class->execute();
        get_config('tool_proper', 'proper_firstname');
        \phpunit_util::run_all_adhoc_tasks();
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'Aaaaaa');
    }

    /**
     * Test the observer.
     * @covers \tool_proper\observer
     * @covers \tool_proper\user_created
     */
    public function test_observer_events(): void {
        global $DB;
        $this->resetaftertest();
        $generator = $this->getDataGenerator();
        $sink = $this->redirectEvents();
        $this->assertCount(0, $sink->get_events());
        $user = $generator->create_user();
        $this->assertCount(0, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
        \core\event\user_created::create_from_userid($user->id)->trigger();
        $events = $sink->get_events();
        $eventdata = end($events);
        $this->assertEquals('\core\event\user_created', $eventdata->eventname);
        $this->assertCount(0, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
        \phpunit_util::run_all_adhoc_tasks();
        $observer = new observer();
        $event = \core\event\user_created::create_from_userid($user->id);
        $observer::usercreated($event);
        $event->trigger();
        $events = $sink->get_events();
        $eventdata = end($events);
        $this->assertEquals('\core\event\user_created', $eventdata->eventname);
        $this->assertCount(1, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
        \phpunit_util::run_all_adhoc_tasks();
        $sink->close();
    }

    /**
     * Test the observer2.
     * @covers \tool_proper\observer
     * @covers \tool_proper\user_created
     */
    public function test_observer_nosink(): void {
        global $DB;
        $this->resetaftertest();
        $generator = $this->getDataGenerator();
        set_config('proper_firstname', 1);
        set_config('proper_lastname', 1);
        set_config('proper_email', 2);
        $this->assertCount(0, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
        $userid = $generator->create_user(['firstname' => 'AAAAA AAAA', 'lastname' => 'BBB BBB'])->id;
        $this->waitForSecond();
        $this->assertCount(1, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
        \phpunit_util::run_all_adhoc_tasks();
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'Aaaaa Aaaa');
        $this->assertEquals($user->lastname, 'Bbb Bbb');
    }

    /**
     * Test replace.
     * @covers \tool_proper\replace
     */
    public function test_replace(): void {
        $this->resetaftertest();
        $gen = $this->getDataGenerator();
        $user1 = $gen->create_user(['firstname' => 'aAaAaA']);
        $user2 = $gen->create_user(['lastname' => 'BbBbBb ']);
        $user3 = $gen->create_user(['city' => 'cCCCCC']);
        \tool_proper\replace::doone($user1->id);
        $user = \core_user::get_user($user1->id);
        $this->assertEquals($user->firstname, 'aAaAaA');
        set_config('proper_firstname', 1);
        \tool_proper\replace::doone($user1->id);
        $user = \core_user::get_user($user1->id);
        $this->assertEquals($user->firstname, 'Aaaaaa');
        \tool_proper\replace::doall();
        $user = \core_user::get_user($user2->id);
        $this->assertEquals($user->lastname, 'BbBbBb ');
        set_config('proper_lastname', 2);
        set_config('proper_city', 3);
        \tool_proper\replace::doall();
        $user = \core_user::get_user($user2->id);
        $this->assertEquals($user->lastname, 'bbbbbb');
        $user = \core_user::get_user($user3->id);
        $this->assertEquals($user->city, 'CCCCCC');
    }

    /**
     * Test dataprovider.
     * @dataProvider replace_provider
     * @param string $before
     * @param string $after1
     * @param string $after2
     * @param string $after3
     * @covers \tool_proper\replace
     */
    public function test_dataprov(string $before, string $after1, string $after2, string $after3): void {
        $this->resetaftertest();
        $arr = \tool_proper\replace::implemented();
        $gen = $this->getDataGenerator();
        foreach ($arr as $value) {
            $userid = $gen->create_user([$value => $before])->id;
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $before);
            set_config('proper_' . $value, 1);
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after1);
            set_config('proper_' . $value, 2);
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after2);
            set_config('proper_' . $value, 3);
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after3);
        }
    }

    /**
     * Data provider for {@see self::test_foobar()}.
     *
     * @return array List of data sets
     */
    public static function replace_provider(): array {
        return [
            'Basic' => ['AAAAAA', 'Aaaaaa', 'aaaaaa', 'AAAAAA'],
            'Spelling' => ['JaNE', 'Jane', 'jane', 'JANE'],
            'Spaces' => ['Jane doe', 'Jane Doe', 'jane doe', 'JANE DOE'],
            'Lowers' => ['jane doe', 'Jane Doe', 'jane doe', 'JANE DOE'],
            'Uppers' => ['JANE DOE', 'Jane Doe', 'jane doe', 'JANE DOE'],
            'Correct' => ['Jane Doe', 'Jane Doe', 'jane doe', 'JANE DOE'],
        ];
    }
}
