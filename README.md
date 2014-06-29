uibStudyInfo
============

Uses open data from the University of Bergen (UiB). Imports, stores in DB and provides API (JSON).

		
What is it?
-----------
This is a package of PHP-scripts used to:

- Import open data from the University of Bergen (UiB) and 
  store it in a database.
- Give an API to fetch data, provide full-text search etc.
  Can be used to feed data to a web-app.

It was created for a student project, but can easily be reused for 
other applications using the same data sets.
  
Latest version
--------------

Is found on the GitHub-repository
[https://github.com/livarb/uibStudyInfo](https://github.com/livarb/uibStudyInfo)
  
Documentation
-------------
  
See code comments or ask the author.
  
Installation
------------
  
1. Copy files to a webserver with PHP-capabilities and a MySQL-database.
2. Set up connect.php (see sample-connect.php)
3. Run scripts (named "fetch...") in web browser to populate database
4. Download Slim framework and place in subfolder "Slim"
5. Make sure .htaccess-file is updloaded for the Slim-framework to be able to
	provide you with an API.
  
Dependencies
------------
  
"Slim PHP micro framework" for API

http://www.slimframework.com/
  
About the open data from UiB
----------------------------
See UiB's web pages about open data:

http://data.uib.no/

And provisional documentation of the datasets used:

http://hackathon.b.uib.no/data-og-idear/uibdata/
  
Licensing
---------
  
See separate file with license.
  
Future improvements
-------------------
  
Some points to improve upon later:

- Translate all code and comments to english
- Create script to run import every day (i.e. cron-job).
	Current script clears database table for each import.
- Give links to guides used.
- Document what each file does and all routes in the API.
  
Contact
-------
  
Contact author at livarbe at gmail.com

For questions regarding UiBs open data, contact UiB at data@uib.no

