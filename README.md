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
* Add searching.
    * Things like genre, rating, actor, studio, producer, runtime, budget, box, resolution, year, rating
* Fix general layout inconsistencies on the data
    * make it not wrap the text on closed elements
    * fix the layout of the names
    * handle year 0000 and rating -1 better
* More icon work
    * Add MPAA icons instead of txt
    * Put an icon for the highest rez next to the title
* Make a cron based update script to update the ratings weekly
    * Maybe also background the first pull of data rather than have the page just sit 'loading'
* Improve security
* Add a graphical edit for each movie (to spot-fix bugs)
  
  
The Log of Change
-----------------
* Nov 03 2012 - v0.1.0
    * Uploaded first code, mostly based off of the origional myth which had no DB support.