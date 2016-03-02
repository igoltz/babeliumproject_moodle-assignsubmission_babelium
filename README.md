[Babelium]: http://babeliumproject.com

#Babelium Assign Submission for Moodle
Babelium is an open source video platform aimed at second language learning. Language learners are able to record their voice using a browser and the cuepoint-constrained videos that are freely available at our main website.

These instructions describe how to install the Babelium Assign Submission for Moodle. This plugin adds a new assign submission to your Moodle platform, allowing your students to do different kinds of speaking exercises using their microphone and submit their work to be reviewed by their teacher.

> **NOTE:** Assign submission plugins were introduced in Moodle 2.3. If you a running an older Moodle version you should use the [Babelium Assignment plugin](https://github.com/babeliumproject/moodle-assignment_babelium) instead.


**Table of contents**
- [Obtaining the source](#obtaining-the-source)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
	- [Enable the plugin](#enable-the-plugin)
	- [Make sure everything works](#make-sure-everything-works)
	- [Upgrading old assignment data](#upgrading-old-assignment-data)
- [Troubleshooting & support](#troubleshooting--support)
	- [Support](#support)
	- [Babelium Error 403. Wrong authorization credentials](#babelium-error-403-wrong-authorization-credentials)
	- [Babelium Error 400. Malformed request error](#babelium-error-400-malformed-request-error)
	- [Babelium Error 500. Internal server error](#babelium-error-500-internal-server-error)
	- [Moodle server is behind a firewall](#moodle-server-is-behind-a-firewall)
	- [Uninstall the plugin](#uninstall-the-plugin)

##Obtaining the source
To run the latest version of the assignment plugin, clone the git repository.

	$ git clone git://github.com/babeliumproject/moodle-assignsubmission_babelium.git moodle-assignsubmission_babelium

Now the entire project should be in the `moodle-assignsubmission_babelium/` directory.

##Prerequisites

* Moodle 2.4+ (build 2012120300.05)
* Babelium server (running on a separate server)
* Internet Explorer 8, Firefox 13, Chrome 20
* Flash Player 11.1+
* Broadband connection (1Mbps/512Kbps)

We provide you a demo Babelium server at [babeliumproject.com](http://babeliumproject.com) for testing this plugin. To use this server you need to:

1. [Sign up](http://babeliumproject.com/#/signup) for a free account
2. [Request a set of API keys](http://babeliumproject.com/moodleapi.php) for your Moodle server's domain (you will also need the account name you created in the previous step)

##Installation
If you are installing the plugin from the source code, you should first create a folder to store the plugin's files inside your Moodle instance.
	
```sh
$ mkdir <moodle_home>/mod/assign/submission/babelium
$ cd moodle-assignsubmission_babelium
$ cp -r * <moodle_home>/mod/assign/submission/babelium
```

###Enable the plugin
After copying the files, log in with an admin account into your Moodle site. Moodle should automatically detect that a new plugin is being added and it will prompt you for actions to take. If this is not the case, browse to following URL to force the plugin installation page to appear:

    http://<moodle_domain>/admin

A settings page will be displayed after the installation is done. You should fill that page with the data you got when you requested the API keys for your Moodle site. You can later change these settings by visiting `Site Administration → Plugins → Activity Modules → Assignment → Submission plugins → Babelium submissions` using an admin account.


###Make sure everything works
Now the installation and configuration are over you should check if everything works properly. To that aim, choose a random course and add a new sample Assignment:

	Add an activity or resource → Assignments
	
In the form that is displayed you should see **Babelium** alongside **File** and **Text** in the submission options.

If everything is right, you shouldn't see any errors and you should be able to pick an exercise from the Babelium exercise list. After adding the new assignment you should also check if you can view and hear the exercise without issues and make a test recording to make sure you can hear what you recorded with your microphone. If you have problems, please check the troubleshooting and support section.

###Upgrading old assignment data
If you upgraded your Moodle instance to the 2.3 version (or more recent) and had the Babelium Assignment plugin installed, you might have some assignment data that needs to be upgraded to avoid being deprecated in future versions. To upgrade these data, you only need to install the assign submission plugin, then use the built-in assignment migration wizard. Check Moodle's documentation if you don't know how to proceed.


##Troubleshooting & support

These are some common errors you might find and how to go about them.

###Support
If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com describing your problem. Please provide the following data in your e-mail so that we can give you a better answer:
* A copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder)
* Version of your browser. You can usually find it in the Help or About areas (e.g. __Mozilla Firefox 19.0.2__)
* Version of Flash Player Plugin. You can find it in the `about:plugins.` section of your browser (e.g. __Shockwave Flash 11.6 r602 PPAPI (out-of-process)__)
* Moodle version. You can find it in `<moodle_home>/version.php` (e.g. `$release  = '2.2.7+ (Build: 20130222)'`)
* The Babelium assignment plugin version. You can find it in `<moodle_home>/mod/assign/submission/babelium/version.php` (e.g. `$plugin->release = '0.9.6 (Build: 2012090600)'`);
* The Babelium username you used to request your set of API keys


###Babelium Error 403. Wrong authorization credentials
* Check the lengths of the key-set you were given. The access key should be 20 characters long and the private access key should be 40 characters long. If the lengths are wrong please contact us to get a new set of keys.

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are supported but you should be within a +/- 15 minute boundary relative to the actual time.

* If your key-set and server time are correct, perhaps you have a problem with the domain of your Moodle site. Please contact us so that we can analyze the problem.

* If your server is behind a load balancer or reverse proxy that uses a different IP address from the one defined in the DNS records for your domain/subdomain you will need to contact us stating the actual request IP (when registering for an API key the IP address is retrieved from the DNS record of the specified domain).

###Babelium Error 400. Malformed request error
* If you are running your own Babelium server, take a look at the log file of `ZendRestJson.php` (by default it is placed in `/tmp/moodle.log`) to see if you have a permission issue in your Babelium file system.

* If you are using [babeliumproject.com](http://babeliumproject.com) please contact us stating your problem, we might have some kind of issue in our own server.

###Babelium Error 500. Internal server error
* Very unlikely to occur. Could happen when the Babelium server uses an old version of PHP (&lt; PHP 5.0)

###Moodle server is behind a firewall
Babelium uses cURL to retrieve data from its API. If your Babelium instance is hosted in a different server than Moodle's and the Moodle server is behind a proxy/firewall you'll need to configure Moodle's proxy settings to have access to the data (the Babelium plugin will inherit these settings). To change these settings go to:

	Administration → Site administration → Server → HTTP
	
Fill in the data for your web proxy and remember to add the domain of your Babelium instance to the `Proxy bypass hosts` field.


###Uninstall the plugin
This is useful when the plugin isn't correctly installed. With these steps you can completely remove the Babelium plugin, in order to do a fresh installation.

Delete all the plugin files and folders (they should be at this location):

```sh
rm -r <moodle_home>/mod/assign/submission/babelium
```

Check if your Moodle database has a table called `mdl_assignsubmission_babelium`, and drop it.

```sql
DROP TABLE mdl_assignsubmission_babelium;
```

Also, check if the `mdl_config_plugin` table contains any values that have to do with babelium and delete them:

```sql
DELETE FROM mdl_config_plugin WHERE plugin='assignsubmission_babelium';
```

Let's also clean any trace left in the `mdl_upgrade_log`:

```sql
DELETE FROM mdl_upgrade_log WHERE plugin='assignsubmission_babelium';
```

With that, we should have removed all the traces of the failed plugin install. Please refer to the previous sections to do a fresh install of the plugin.
