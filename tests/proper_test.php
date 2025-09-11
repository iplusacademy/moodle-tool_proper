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
 * @copyright  iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_proper;

use advanced_testcase;
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider};

/**
 * The tool_proper test class.
 *
 * @package    tool_proper
 * @copyright  iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(privacy\provider::class)]
#[CoversClass(observer::class)]
#[CoversClass(user_created::class)]
#[CoversClass(replace::class)]
final class proper_test extends advanced_testcase {
    /**
     * Test returning metadata.
     */
    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('tool_proper');
        $reason = privacy\provider::get_reason($collection);
        $this->assertEquals($reason, 'privacy:metadata');
    }

    /**
     * Test the observer.
     */
    public function test_observer(): void {
        $class = new \ReflectionClass(new observer());
        $this->assertCount(1, $class->getMethods());
        $this->assertCount(0, $class->getProperties());
    }

    /**
     * Test the usercreated class.
     */
    public function test_user_created_task(): void {
        global $CFG, $USER;
        $this->resetaftertest();
        $class = new user_created();
        $class->set_custom_data(['userid' => $CFG->siteguest]);
        $this->assertTrue($class->execute());
        $this->setGuestUser();
        $class->set_custom_data(['userid' => $USER->id]);
        $this->assertTrue($class->execute());
        $this->setAdminUser();
        $class->set_custom_data(['userid' => 9999]);
        $this->assertTrue($class->execute());
        $generator = $this->getDataGenerator();
        $userid = $generator->create_user(['firstname' => 'aAaAaA'])->id;
        $class->set_custom_data(['userid' => $userid]);
        $this->assertTrue($class->execute());
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'aAaAaA');
        set_config('firstname', 1, 'tool_proper');
        $class->set_custom_data(['userid' => $userid]);
        $this->assertTrue($class->execute());
        get_config('tool_proper', 'proper_firstname');
        \phpunit_util::run_all_adhoc_tasks();
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'Aaaaaa');
    }

    /**
     * Test the observer.
     */
    public function test_observer_events(): void {
        $this->resetaftertest();
        $generator = $this->getDataGenerator();
        $sink = $this->redirectEvents();
        $this->assertCount(0, $sink->get_events());
        $user = $generator->create_user();
        $this->assert_count(0);
        \core\event\user_created::create_from_userid($user->id)->trigger();
        $events = $sink->get_events();
        $eventdata = end($events);
        $this->assertEquals('\core\event\user_created', $eventdata->eventname);
        $this->assert_count(0);
        \phpunit_util::run_all_adhoc_tasks();
        $observer = new observer();
        $event = \core\event\user_created::create_from_userid($user->id);
        $observer::usercreated($event);
        $event->trigger();
        $events = $sink->get_events();
        $eventdata = end($events);
        $this->assertEquals('\core\event\user_created', $eventdata->eventname);
        $this->assert_count(1);
        \phpunit_util::run_all_adhoc_tasks();
        $sink->close();
    }

    /**
     * Test the observer2.
     */
    public function test_observer_nosink(): void {
        $this->resetaftertest();
        $generator = $this->getDataGenerator();
        set_config('firstname', 1, 'tool_proper');
        set_config('lastname', 1, 'tool_proper');
        set_config('email', 2, 'tool_proper');
        $this->assert_count(0);
        $userid = $generator->create_user(['firstname' => 'AAAAA AAAA', 'lastname' => 'BBB BBB'])->id;
        $this->waitForSecond();
        $this->assert_count(1);
        \phpunit_util::run_all_adhoc_tasks();
        $user = \core_user::get_user($userid);
        $this->assertEquals($user->firstname, 'Aaaaa Aaaa');
        $this->assertEquals($user->lastname, 'Bbb Bbb');
    }

    /**
     * Test replace.
     */
    public function test_replace(): void {
        $this->resetaftertest();
        $gen = $this->getDataGenerator();
        $user1 = $gen->create_user(['firstname' => 'aAaAaA']);
        $user2 = $gen->create_user(['lastname' => 'BbBbBb ']);
        $user3 = $gen->create_user(['city' => 'cCCCCC']);
        $this->assertNull(\tool_proper\replace::doone(0));
        $this->assertNull(\tool_proper\replace::doone(1));
        $this->assertNull(\tool_proper\replace::doone(2));
        $this->assertNull(\tool_proper\replace::doone($user1->id));
        $user = \core_user::get_user($user1->id);
        $this->assertEquals($user->firstname, 'aAaAaA');
        set_config('firstname', 1, 'tool_proper');
        \tool_proper\replace::doone($user1->id);
        $user = \core_user::get_user($user1->id);
        $this->assertEquals($user->firstname, 'Aaaaaa');
        \tool_proper\replace::doall();
        $user = \core_user::get_user($user2->id);
        $this->assertEquals($user->lastname, 'BbBbBb ');
        set_config('lastname', 2, 'tool_proper');
        set_config('city', '3', 'tool_proper');
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
     */
    #[DataProvider('replace_provider')]
    public function test_dataprov(string $before, string $after1, string $after2, string $after3): void {
        $this->resetaftertest();
        $arr = \tool_proper\replace::implemented();
        $this->assertEquals(
            $arr,
            [
                'firstname',
                'lastname',
                'firstnamephonetic',
                'lastnamephonetic',
                'middlename',
                'alternatename',
                'email',
                'city',
                'idnumber',
                'institution',
                'department',
                'address',
            ]
        );
        $gen = $this->getDataGenerator();
        foreach ($arr as $value) {
            $userid = $gen->create_user([$value => $before])->id;
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $before);
            set_config($value, '1', 'tool_proper');
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after1);
            set_config($value, 1, 'tool_proper');
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after1);
            set_config($value, 2, 'tool_proper');
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after2);
            set_config($value, 3, 'tool_proper');
            \tool_proper\replace::doone($userid);
            $newuser = \core_user::get_user($userid);
            $this->assertEquals($newuser->{$value}, $after3);
        }
    }

    /**
     * Data provider replace.
     *
     * @return Generator
     */
    public static function replace_provider(): \Generator {
        yield 'Basic' => ['AAAAAA', 'Aaaaaa', 'aaaaaa', 'AAAAAA'];
        yield 'Spelling' => ['JaNE', 'Jane', 'jane', 'JANE'];
        yield 'Spaces' => ['Jane doe', 'Jane Doe', 'jane doe', 'JANE DOE'];
        yield 'Lowers' => ['jane doe', 'Jane Doe', 'jane doe', 'JANE DOE'];
        yield 'Uppers' => ['JANE DOE', 'Jane Doe', 'jane doe', 'JANE DOE'];
        yield 'Correct' => ['Jane Doe', 'Jane Doe', 'jane doe', 'JANE DOE'];
    }

    /**
     * Test the observer3.
     */
    public function test_observer_field_deleted(): void {
        global $DB;
        $this->resetaftertest();
        $dbman = $DB->get_manager();
        $generator = $this->getDataGenerator();
        set_config('address', 1, 'tool_proper');
        $userid = $generator->create_user(['address' => 'DDDD'])->id;
        $table = new \xmldb_table('user');
        $field = new \xmldb_field('address');
        $dbman->drop_field($table, $field);
        $class = new user_created();
        $class->set_custom_data(['userid' => $userid]);
        $this->expectException(\dml_read_exception::class);
        $class->execute();
        $this->assertDebuggingCalledCount(1);
    }

    /**
     * Assert count of adhoc tasks.
     *
     * @param int $amount
     */
    private function assert_count(int $amount): void {
        global $DB;
        $this->assertCount($amount, $DB->get_records('task_adhoc', ['component' => 'tool_proper']));
    }
}
