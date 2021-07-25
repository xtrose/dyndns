xtrose Media Studio
Author: Moses Rivera
Web: https://xtrose.com
Mail: media.studio@xtrose.com



With xtrose DynDNS you get a simple, free DynDns solution without external providers and hidden costs.
The required online web server can create an infinite number of subdomains for dynamic IP's.
The method used is easy to implement.
For the client servers, the servers that have a dynamic IP, a simple cron job is set up with cron, which sends a simple curl query to the web server at regular intervals.
The web server, which receives the curl request with a PHP script, checks whether the IP of the client server has changed and builds new apache config files.
Another cron job on the web server checks whether there are new apache config files, copies them to the apache config directory and restarts the apache application.
With just a few scripts and a simple setup, you can quickly create your own DynDNS server.



Needed:
- Client Server - At Home (Linux)
- Web server - Online (Linux)

Required (client server):
- sudo rights
- cron

Required (web server):
- sudo rights
- cron
- letsencrypt
- apache2 (web server)
- PHP
- Public domain




Clone or download the files
Download or clone the GIT repository files to your computer.
$ git clone "https://github.com/xtrose/dyndns.git"



Create a subdomain for your web server
First you need a subdomain on your web server, which later contains the PHP script to process the client requests.
If you are not yet familiar with apache, you can use the files from the Files directory and adapt them accordingly.
Please copy the files for this into another directory, as these will be required later unchanged.

Replace the fields in square brackets in the files as follows:
- [SERVERADMIN] -> Email address of the server administrator.
- [SUBDOMAIN] -> Subdomain which are triggered for the client server.
- [DOMAIN] -> Public domain under which the web server can be reached.

Remove the following lines from the apache-le-ssl.conf file and replace them:
The files are designed for a reverse proxy to an IP.
For the web server script, the file must be adapted so that it can be called up in a document directory.
Replace [MY_PATH] with a real path that can be accessed by your apache server.

Remove:
SSLProxyEngine On
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Insert:
DocumentRoot /var/www/[MY_PATH]/

Rename the 2 files as follows:
apache.conf -> [MY_SUBDOMAIN] .conf
apache-le-ssl.conf -> [MY_SUBDOMAIN] -le-ssl.conf

Copy the two files you created into the apache config directory on your web server:
Make sure that you have root rights.
/etc/apache2/sites-enabled/

Create the document directory on your web server as you configured it in the apache-le-ssl.conf file and assign the rights to the apache user:
$ sudo chown www-data: www-data /var/www/[MY_PATH]/

Restart the apache2 application:
$ sudo service apache2 restart

Create a Letsencrypt certificate for the newly created subdomain:
Replace the fields in the square brackets with your subdomain and domain data in the command.
$ certbot certonly -d [SUBDOMAIN].[DOMAIN] -d www.[SUBDOMAIN].[DOMAIN] --apache --renew-by-default

Open the copied file [MY_SUBDOMAIN] -le-ssl.conf in your apache directory and remove # from the following lines:
Please note that you need root rights for this.
Note the path to the letsencrypt certificate and adapt it to the path to the created certificate.
# Include /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/fullchain.pem
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/privatekey.pem

Restart the apache2 application:
$ sudo service apache2 restart

You have now created a subdomain for your server that should be accessible from outside.
All requests to the subdomain will be carried out by the application in the configured document directory.
All http requests are redirected to https.
Please note that the letsencrypt certificate must be renewed every 3 months.
You can automate this at a later point in time using cron.



Configure the xtrose DynDNS files
Open the index.php file from the downloaded GIT repository and change the entries under "// Config." as follows:
MY_SECRET -> Secure password
MY_DOMAIN.COM -> Public domain of your web server (example.com)
MY_EMAIL_ADDRESS -> Email address of the server administrator (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Enter all subdomains for which no DynDNS forwarding may be built, including those that manage the script.
If you have further subdomains for other websites or redirects that must not be changed, then add them all here.

Open the file bash/server.sh and change the entries as follows:
/PATH/TO/UPDATE/DIRECTORY/ -> The path to the later update directory must be inserted into your created document directory (/var/www/[MY_SUBDOMAIN]/update)

Open the file bash/client.sh and change the entries as follows:
DYNDNS.MY_DOMAIN.COM -> The generated subdomain and domain must be entered here.
MY_SECRET -> Replace this with the same secure password that you entered in the index.php file.
MY_SUBDOMAIN -> The new subdomain under which your dynamic IP can be accessed. (home.example.com)



Copy all files from the GIT repository into the created document directory on your web server:
Then delete the file bash/client.sh on your web server. This will be used later for the client servers.
If not available, then create the two directories data and update in the document directory on your web server.

Then assign the rights of all files in your document directory on your web server to the apache user:
$ sudo chown -R www-data:www-data /var/www/[MY_SUBDOMAIN]/



Create a cron job with cron on your web server
To do this, the file in your document directory bash/server.sh must be made executable:
$ sudo chmod + x /var/www/[MY_SUBDOMAIN]/bash/server.sh

When you call up the crontable for the first time, you have to select your favorite text editor:
$ sudo crontable -e

Add the following line below and save the file:
* * * * * /bin/bash "/var/www/[MY_SUBDOMAIN]/bash/server.sh"

Then reload the crontable:
$ sudo service cron reload

You have now created a cron job that checks every minute whether an update has been created for a dynamic IP.
As soon as the index.php script has created a new apache file in the Update directory, this is copied into the apache directory and the web server is restarted.



Setting up the client server with the dynamic IP that is to be reached via a subdomain
To do this, copy the file bash/client.sh to any location on the client server and make the file executable:
$ chmod + x /PATH_TO_FILE/client.sh

Open the crontable on your Cleint server and create a cron job that executes the file every minute.

When you call up the crontable for the first time, you have to select your favorite text editor:
$ crontable -e

Add the following line below and save the file:
* * * * * /bin/bash "/PATH_TO_FILE/client.sh"

Then reload the crontable:
$ service cron reload

You have now created a cron job that calls the index.php file on your web server every minute:
The web server checks whether the IP of the client server has changed and if there is a change it creates new apache files and restarts the web server.



Create additional client servers with dynamic IPs
The file bash/client.sh can be used with any number of client servers with dynamic IPs.
Change MY_SUBDOMAIN in the file to the subdomain under which it is to be reached.
Copy the file to your new client server and set up a new cron job.



Have fun
