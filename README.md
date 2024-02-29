## Moodle proper ##

The proper tool rewrites user names and user fields so a site can have more or less a policy how to write names.
When using self registration, some users are using capitals, some do not know the existence of the shift button.
With this plugin, after a new user registration, the enabled fields are automatically changed into lowercase, UPPERCASE, Propercase, or simply ignored.

## Warnings ##

 - The proper admin tool does write immedately in the datatabase. Take a backup before using this plugin.
 - The proper function is not perfect, it has now idea about naming pecularities.

## Command line tool ##

From the command line, it is possible to fix:

 - one user: php admin/tool/proper/cli/replace.php --id=22
 - all users: php admin/tool/proper/cli/replace.php --all

## Admin tools ##

Check the global documentation about [admin tools](https://docs.moodle.org/403/en/Admin_tools)

## Installation: ##

 1. Unpack the zip file into the admin/tool/ directory. A new directory will be created called proper.
 2. Go to Site administration > Notifications to complete the plugin installation.
 3. You will be able to configure the behaviour for each text field.

## Requirements ##

This plugin requires Moodle 4.2+

## Theme support ##

This plugin is developed and tested on Moodle Core's Boost theme and Boost child themes, including Moodle Core's Classic theme.

## Plugin repositories ##

This plugin will be published and regularly updated on [Github](https://github.com/iplusacademy/moodle-tool_proper)

## Bug and problem reports / Support requests##

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.
Please report bugs and problems on [Github](https://github.com/iplusacademy/moodle-tool_proper/issues)
We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.

## Feature proposals ##
Please issue feature proposals on [Github](https://github.com/iplsuacademy/moodle-tool_proper/issues)
Please create pull requests on [Github](https://github.com/iplusacademy/moodle-tool_proper/pulls)
We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature proposals and not as feature requests.

## Todo ##

 - implement exceptions

## Status ##

[![Build Status](https://github.com/iplusacademy/moodle-tool_proper/workflows/Tests/badge.svg)](https://github.com/iplusacademy/moodle-tool_proper/actions)

## Copyright ##

iplusacademy.org
