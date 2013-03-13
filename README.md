This repository is the code for [Babelium's][] Moodle plugins.

[Babelium's]: http://babeliumproject.com

Here you will find the latest versions of the plugins. Sometimes it takes a while for the plugins to be added
to the official Moodle plugin repository so if you want to test the latest features this is the way to go.

The plugin is currently available in 3 languages: English, Spanish and Basque.

Cloning the repository
----------------------
To run the development version of Babelium first clone the git repository.

	$ git clone git://github.com/babeliumproject/babeliumproject.git babeliumproject

Now the entire project should be in the `babeliumproject/` directory.

Installing the Mooodle 1.9 plugin
---------------------------------
Copy the `common` and `moodle19` folders to Moodle's home directory.
	
	$ cd babeliumproject/moodle
	$ cp common <moodle_directory>
	$ cp moodle19 <moodle_directory>

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium


Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site
registration data.

	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Enabling the plugin
After copying the files and filling in the `babelium_config.php` file login with an admin account to your Moodle
site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take.
If this is not the case, browse to the following URL to force the plugin installation page to appear:

	http://<moodle_domain>/admin

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity... -> Assignments -> Babelium activity

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Installing the Mooodle 2.0-2.2 plugin
-------------------------------------
Copy the `common` and `moodle20-22` folders to Moodle's home directory.
	
	$ cd babeliumproject/moodle
	$ cp common <moodle_directory>/
	$ cp moodle20-22 <moodle_directory>/

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium


Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site
registration data.

	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Enabling the plugin
After copying the files and filling in the `babelium_config.php` file login with an admin account to your Moodle
site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take.
If this is not the case, browse to the following URL to force the plugin installation page to appear:

	http://<moodle_domain>/admin

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Installing the Moodle 2.3+ plugin
---------------------------------

**NOTE:** Also works for Moodle 2.4.

From Moodle 2.3 onwards, `assignment_type` plugins are deprecated and replaced by `assign_submission` or
`assign_feedback` plugins.

Copy the files from the `moodle23` to Moodle's home directory. 

	$ cd babeliumproject/moodle
	$ cp moodle23 <moodle_directory>/

If you copied the folders in the right place you should see some files here:

	$ ls <moodle_directory>/mod/assign/submission/babelium

###Enabling the plugin
Log in with an admin account to your Moodle site. Moodle should automatically detect that a new plugin
is being added and prompt you for actions to take. If this is not the case, browse to the following URL 
to force the plugin installation page to appear:

	http://<moodle_domain>/admin

After successfully installing the plugin you will see a settings page where you should input the key-pair
provided in your moodle site registration step.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity or resource -> Assignments

In the form that is displayed you can see Babelium alongside File and Text submissions. This means you can have
man kinds of submissions for the same assignment.

###Upgrading from previous versions
If you have the 1.9 or 2.0-2.2 version of Babelium's Moodle plugin installed you can upgrade all your
assignment data to the new architecture to avoid the deprecated modules. Simply install Babelium's Moodle
2.3 plugin, follow the configuration steps and when you are done, use the built-in migration wizard. Check
Moodle's documentation if you don't know how to proceed.

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Enabling Moodle support in the Babelium server
--------------------------------------------
If you are using your own Babelium server and want to enable Moodle instances to access the exercises stored there, you have
to take additional steps, such as placing some API files and compiling a special version of the video player.

* Copy the Moodle API files and the Moodle site registration script:

	$ cd babeliumproject/moodle/patches
	$ cp api &lt;babelium_home&gt;/
	$ cp css &lt;babelium_home&gt;/
	$ cp moodleapi.php &lt;babelium_directory&gt;/

* Copy the video merging script to the Babelium script folder:

	$ cd babeliumproject/moodle/patches
	$ cp script/* &lt;babelium_script_directory&gt;/

* Copy the placeholder video to display while the videos are still not merged:

	$ cd babeliumproject/moodle/patches/media
	$ cp placeholder_merge.flv &lt;red5_home&gt;/webapps/vod/streams

* Apply the provided SQL patch to enable Moodle site registration

	$ mysql -u &lt;babeliumdbuser&gt; -p
	> use &lt;babeliumdbname&gt;;
	> source babeliumproject/moodle/patches/sql/moodle_patch.sql;

* Fill the data of `&lt;babelium_home&gt;/api/services/utils/Config.php` (or copy from `&lt;babelium_home&gt;/services/utils/Config.php`)
and check if the paths in `&lt;babelium_home&gt;/api/services/utils/VideoProcessor.php` are right.

* Copy the standalone video player to the Babelium home directory.

	$ cd babeliumproject/flex/standalone/player/dist
	$ cp babeliumPlayer.* &lt;babelium_directory&gt;/

**NOTE:** If you need help compiling the standalone player read the next point.

Following these steps you should be able to register Moodle instances in your Babelium server.

###Compile the standalone video player
Babelium needs a special version of the video player to support embeding in Moodle and other systems. These are the steps you need to take
to compile the standalone player from the source code.

* Download and unpack Flex SDK 4.6

	$ wget http://download.macromedia.com/pub/flex/sdk/flex_sdk_4.6.zip
	$ unzip flex_sdk_4.6.zip

* Make a locale for Basque language (because it is not included by default):

	$ cd &lt;flex_home&gt;/bin
	$ ./copylocale en_US eu_ES

* Fill the build.properties file for the standalone player (assuming you already clone the git repository):

	$ cd babeliumproject/flex/embeddable/player
	$ cp build.properties.template build.properties
	$ vi build.properties

  Specify the home folder of Flex SDK FLEX_HOME and leave the rest of the fields unchanged (we don't need them for this purpose).

	$ ant

  The compiled files are placed in the `dist` folder.

* Copy the standalone video player to the Babelium home directory.

	$ cd babeliumproject/flex/standalone/player/dist
	$ cp babeliumPlayer.* &lt;babelium_directory&gt;/

###Troubleshooting
If you need help configuring the server or find any bug please don't hesitate to contact us at support@babeliumproject.com

