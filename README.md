SiteCat 8
=========

SiteCat is a [Drupal 8](https://www.drupal.org/8) distribution, that seeks to 
bundle the most common config to allow faster web site development.

Originally based on the [Drupal-project](https://github.com/drupal-composer/drupal-project) GitHub project,
SiteCat has been reworked to bring the vendor folder back inside the root folder,
to preserve compatibility with the shared hosting.


Changes since version 2.1
-------------------------
SiteCat version 2.2 is based on Drupal 8.6.x that has had some important changes
to layout and media handling. That made it necessary to start a new branch and
rework some of the core functioanlity. At the same time, the long overdue shift
to Bootstrap 4 has also been made.
1. Base theme bas been switched from Bootstrap 3 to Barrio (Bootstrap 4).
2. Main theme has been renamed bSub to SiteCat.
3. Panelizer has been superseded by the core Layout Builder.
4. Files have been superseded by the core media entities.


Modules Selection
-----------------
SiteCat 8 comes with the set of most commonly used modules, that provide such 
functionality as:
* Clean paths handling
* Advanced content handling
* Layout and site building tools and enhancements
* Media management
* Look & Feel enhancements
* Content editing
* Security
* Other modules and themes


Config Setup
------------
SiteCat 8 has some initial configuration presets out of the box, that seeks to 
allow the developer to skip the commonly repeated configuration steps. These
settings include the following configuration:
* Roles and permissions
* Content editing settings
* URLs handling settings
* Most common page layout settings
* Initial configuration of additional modules
* Many other settings

Those settings have been applied, that are most common.


Base theme
----------
Base theme is a bootsrap subtheme, called BSub8. It has a few most common 
enhancements, like some initial SASS and Bootsrap libraries added into it, but 
it remains clean and does not make choices that the developer would have to undo.


Content
-------
There is no content on the site except for the front page, containing this text.


Changes Note
------------
IMPORTANT: This SiteCat 8 is different from other distros in that it's not 
aiming to provide upgrades, but to only start sites. You can update the site as
usual, rather that having to wait till the distro update comes out. At the same 
time, SiteCat does not keep compatibility with it's previous versions. Every new 
update of the SiteCat may add or remove themes, modules, and configuration, 
expecting you to use it create sites, not to update them.


Developer Note
--------------
IMPORTANT: This site has some developer presets enabled. Don't forget to disable
them when either putting the site on production or sharing with a coworker:
* Edit the root .gitignore to disallow versioning of settings.php
* Comment or delete the line that does the verbose error output in settings.php.
* Enable caching. Check that your twig debugging is disabled.
