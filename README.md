# vt-votation

## About
A plugin for Wordpress collecting the number of submissions from different forms. Used for a votation where the voters can vote for several options and have to submit an email address.

## Functionality

* Compiles results from votation and show on a page in the admin interface.
* Accepts only one vote per form per email address.
* Makes it possible to only allow one vote per form per IP address.
* Allows blocking of individual IP addresses.

## Get started
* Install Forminator
* Create forms in Forminator. Each form should have a field for email.
* Install this plugin by placing it in the plugins directory in your Wordpress.
* Activate the plugin. 
* Go to the settings page of this plugin. 
* Mark the forms which should be included in the votation and save.
* Add the forms to some page.
* Now you will be able to see the results of the votation on the results page: `/wp-admin/admin.php?page=render_votation_results`
* You can change the settings here: `/wp-admin/admin.php?page=render_votation_settings`

