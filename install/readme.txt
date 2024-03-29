==============================
UPGRADE QNM  to v1.0
==============================

To upgrade from version 1beta to 1.0, you can proceed with a normal installation (see here after).

  Remarque #1
  It's recommended to backup your config.php file in the /bin/ directory
  (in case you don't remember the connection parameters of your database)
  
  Remarque #2
  If your board allows user photo (avatar) or document upload,
  it's recommended to NOT delete the /avatar/ and /upload/ directories.
  Other files and folders can be deleted before installing the new release.

==============================
INSTALLATION of QNM  v1.0
==============================

BEFORE starting the installation procedure, make sure you know:
- The type of database you will use (MySQL, SQLserver, PostgreSQL, SQLite, Firebird, Oracle or DB2).
- Your database host (the name of your database server, often "localhost")
- The name of your database (where the QNM can install the tables).
- The user name for this database (having the right to create table).
- The user password for this database.


1. Upload the application on your web server
--------------------------------------------
Just send (ftp) all the files and folders on your webserver (for example in a folder /qnm/).
If you are making an upgrade, do NOT overwrite the /avatar/ nor /upload/ directories.


2. Configure the permissions
----------------------------
This step is very important !
Without this configuration, the installation programme will not work and the database will not be configured.

Change the permission of the file /bin/config.php to make it writable (chmod 777).
Change the permission of the directories /avatar/ and /upload/ (and subdirectories) to make them writable (chmod 777).


3. Start the installation
-------------------------
From your web browser, start the installation script: install/install.php
(i.e. Type the url http://www.yourwebsite.com/qnm/install/install.php)
This script will ask you the database connection information and create the necessary table in it.


4. Clean up
-----------
When previous steps are completed, you can delete the /install/ folder on your website and set the permission for /bin/config.php to readonly.


VERSION HISTORY
===============
