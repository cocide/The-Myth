The Myth
========
###PHP interface for movie browsing based upon a standardized naming convention
  
This page was designed to utilize a particular naming sequence, it will probably not be useful to anyone but Cocide, RKuykendall, and GonMD.  
Anyone may use this under the GPLv3 license.
  
  
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
* Update the 3+ year old JS
* Clean URLs
  
  
The Log of Change
-----------------
* Nov 03 2012 - v0.1.0
    * Uploaded first code, mostly based off of the origional myth which had no DB support.