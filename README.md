The Myth
========
###PHP interface for movie browsing based upon a standardized naming convention
  
This page was designed to utilize a particular naming sequence, it will probably not be useful to anyone but Cocide, RKuykendall, and GonMD.  
Data is gathered by finding the IMDB number and resolution from the filename then searching Rotten Tomatoes and TMDb with API versions 1.0 and 2.1 respectively.  
Anyone may use this under the GPLv3 license.  
  
  
Installation Process
---------------------
Simply put a copy of The Myth on any apache/PHP/MySQL server and browse to it.  
During the install process the directory must be writable by apache, once the conf.php is created you can/should make the directory read-only.  
On the first load you will be prompted for MySQL info, API numbers from both [RT](http://developer.rottentomatoes.com/) and [TMDb](http://api.themoviedb.org/2.1/), along with a password you will later use to add/edit movies from the list.  
Browsing to admin.php will let you upload new file lists. They must be in a .txt or .ls format. The easiest way to generate these lists is by piping ls to a file.  
  
  
Things that Need to Eventually Happen
-------------------------------------
* Add advanced search (perhaps)
* Improve security
* Add a graphical edit for each movie (to spot-fix bugs)
* Fix sorting of unknown quality movies (currently they mix with SD)
* Make the form look better.  
  
  
The Log of Change
-----------------
* Nov 10 2012 - v0.2.0
    * Removed " 3D" from the titles
    * Changed the year to not be based off of year in theaters (RT updates that to most recent)
        * NOTE: This will require a DB upgrade, run install to upgrade.
        * NOTE: It is a good idea to just flush the info_rt table, it will get better year info.
    * Changed how the IMDB numbers are compared with TMDb and RT
        * NOTE: This will require a DB upgrade, run install to upgrade.
    * Added searching.
    * The regex video quality now matches nearly perfectly for 1080, 720, DvD, R5/6 (no Cam/Screener/TS support)
    * Added a cron job to be ran nightly, it will update up to 150 movies which are at least 15 days old starting with the oldest.
        * NOTE: You will need to add a wget (or similar) for cron.php to be ran nightly.
    * Made the clean URLs able to direct to / instead of /index.php
* Nov 04 2012 - v0.1.1
    * Added icons for quality and rating on the collapsed view
    * Removed the tagline from the collapsed view
    * Fixed several CSS problems
* Nov 03 2012 - v0.1.0
    * Uploaded first code, mostly based off of the original myth which had no DB support.