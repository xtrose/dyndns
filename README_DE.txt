Mit xtrose DynDNS bekommen Sie eine einfach, kostenlose DynDns Lösung ohne fremde Anbieter und versteckte Kosten.
Der Benötigte Online Webserver kann unendlich viele Subdomains für dynamische IP's erstellen.
Die angewandte Methode ist einfach umzusetzen.
Für die Client Server, die Server welche eine dynamische IP haben, wird ein einfache Cronjob mit cron eingerichtet, der in regelmäßigen Abständen eine einfache Curl Abfrage zum Webserver sendet.
Der Webserver, welcher die Curl Anfrage mit einen PHP Script empfängt, prüft ob sich die IP des Client Servers geändert hat und baut neue Apache config Dateien.
Ein weiterer Cronjob, auf dem Webserver, prüft ob neue Apache config Dateien vorhanden sind, kopiert diese in Apache Config Verzeichnis und Startet die Apache Anwendung neu.
Mit wenigen Skripten und einer einfachen Einrichtung erstellen Sie in Kürze einen eigenen DynDNS Server.



Benötigt:
- Client Server - Zuhause (Linux) 
- Webserver - Online (Linux)

Benötigt (Client Server):
- sudo Rechte
- cron

Benötigt (Webserver):
- sudo Rechte
- cron
- letsencrypt
- apache2 (Webserver)
- PHP
- Öffentliche Domain




Klonen oder Herunterladen der Dateien.
Laden Sie die Dateien des GIT Repository auf Ihren Computer herunter oder Klonen Sie es.
$ git clone "https://github.com/xtrose/dyndns.git"



Erstellen einer Subdomain für Ihren Webserver.
Als erstes benötigen Sie auf Ihrem Webserver eine Subdomain, welche später das PHP Script enthält um die Client Anfragen zu verarbeiten.
Wenn Sie sich bislang noch nicht mit Apache auskennen, dann können Sie die Dateien aus dem Verzeichnis Files verwenden und entsprechend anpassen.
Bitte kopieren Sie die Dateien hierfür in ein anderes Verzeichnis, da diese später unverändert benötigt werden.

Ersetzen die die Felder in Eckigen Klammern, in den Dateien, wie folgt.
- [SERVERADMIN] -> Email Adresse des Sever Administrators.
- [SUBDOMAIN] -> Subdomain welche für die Client Server getriggert werden.
- [DOMAIN] -> Öffentliche Domain unter welchen der Webserver erreichbar ist.

Entfernen Sie folgende Zeilen aus der Datei apache-le-ssl.conf und ersetzen diese.
Die Dateien sind für einen Reverse Proxy zu einer IP ausgelegt.
Für das Webserver Script muss die Datei angepasst werden, dass dies in einem Dokumenten Verzeichnis aufgerufen werden kann.
Ersetzen Sie [MY_PATH] durch einen realen Pfad, der von Ihrem Apache Server aufgerufen werden kann.

Enternen:
SSLProxyEngine On
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Einfügen:
DocumentRoot /var/www/[MY_PATH]/

Benennen Sie die 2 Dateien wie folgt um.
apache.conf -> [MY_SUBDOMAIN].conf
apache-le-ssl.conf -> [MY_SUBDOMAIN]-le-ssl.conf

Kopieren Sie die zwei erstellen Dateien in das apache config Verzeichnis auf Ihren Webserver.
Beachten Sie, dass Sie dabei über root Rechte verfügen.
/etc/apache2/sites-enabled/

Erstellen Sie das Dokumenten Verzeichnis auf Ihrem Webserver, so wie Sie es in der Datei apache-le-ssl.conf konfiguriert haben und vergeben Sie die Rechte an den apache Benutzer.
$ sudo chown www-data:www-data /var/www/[MY_PATH]/

Starten Sie die apache2 Anwendung neu.
$ sudo service apache2 restart

Letsencrypt Zertifikat für die neu erstellte Subdomain erstellen.
Tauschen Sie in dem Befehl die Felder in den eckigen Klammern mit Ihren Subdomain und Domain Daten aus.
$ certbot certonly -d [SUBDOMAIN].[DOMAIN] -d www.[SUBDOMAIN].[DOMAIN] --apache --renew-by-default

Öffnen Sie die kopierte Datei [MY_SUBDOMAIN]-le-ssl.conf in Ihrem Apache Verzeichnis und entfernen Sie # von folgenden Zeilen.
Bitte beachten Sie, dass Sie hierfür root Rechte benötigen.
Beachten Sie den Pfad zum letsencrypt Zertifikat und passen Sie es an den Pfad zum erstellen Zertifikat an.
# Include /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/fullchain.pem
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/privkey.pem

Starten Sie die apache2 Anwendung neu.
$ sudo service apache2 restart

Sie haben nun eine Subdomain für Ihren Server erstellt die von außen erreichbar sein sollte.
Alle Anfragen zu der Subdomain werden die Anwendung im konfigurierten Dokumenten Verzeichnis ausführen.
Dabei werden alle http Anfragen zu https umgeleitet.
Bitte beachten Sie, dass das letsencrypt Zertifikat alle 3 Monate erneuert werden muss.
Dies können Sie zu einem späteren Zeitpunkt über cron automatisieren.




Konfigurieren der xtrose DynDNS Dateien
Öffnen Sie die Datei index.php aus dem heruntergeladenen GIT Repository und ändern Sie die Einträge unter "// Config." wie folgt.
MY_SECRET -> Sicheres Passwort
MY_DOMAIN.COM -> Öffentliche Domain ihres Webservers (example.com)
MY_EMAIL_ADDRESS -> E-Mail Adresse des Server Administrators (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Tragen Sie hier alle Subdomains ein für die keine DynDNS Weiterleitung gebaut werden darf, inklusive der die das Skript verwaltet.
Wenn Sie weitere Subdomains haben, für andere Webseiten oder Weiterleitungen, die nicht verändert werden dürfen, dann fügen Sie diese alle hier hinzu.

Öffnen Sie die Datei bash/server.sh und ändern Sie die Einträge wie folgt.
/PATH/TO/UPDATE/DIRECTORY/ -> Hier muss der Pfad zum späteren Update Verzeichnis in Ihren erstellten Dokumenten Verzeichnis eingefügt werden (/var/www/[MY_SUBDOMAIN]/update)

Öffnen Sie die Datei bash/client.sh und ändern Sie die Einträge wie folgt.
DYNDNS.MY_DOMAIN.COM -> Hier muss die erzeugte Subdomain und Domain eingetragen werden.
MY_SECRET -> Ersetzen Sie dies durch das selbe sichere Passwort, dass Sie in der Datei index.php eingetragen haben.
MY_SUBDOMAIN -> Die neue Subdomain unter der Ihre dynamische IP aufgerufen werden kann. (home.example.com)



Kopieren Sie alle Dateien aus dem GIT Repository in das erstellte Dokumenten Verzeichnis auf Ihren Webserver.
Löschen Sie anschließend die Datei bash/client.sh auf Ihrem Webserver. Diese wird später für den Client Servern verwendet.
Falls nicht vorhanden, dann erstellen Sie die zwei Verzeichnisse data und update im Dokumenten Verzeichnis auf Ihrem Webserver.

Vergeben Sie anschließend die Rechte aller Dateien in Ihrem Dokumenten Verzeichnis auf Ihrem Webserver dem apache Benutzer.
$ sudo chown -R www-data:www-data /var/www/[MY_SUBDOMAIN]/



Erstellen Sie eine Cronjob mit cron auf Ihrem Webserver.
Dafür muss die Datei in Ihrem Dokumenten Verzeichnis bash/server.sh ausführbar gemacht werden.
$ sudo chmod +x /var/www/[MY_SUBDOMAIN]/bash/server.sh

Beim ersten Aufruf der crontable müssen Sie Ihrem favorisierten Texteditor auswählen.
$ sudo crontable -e

Fügen Sie unten folgende Zeile ein uns speichern Sie die Datei.
* * * * * /bin/bash "/var/www/[MY_SUBDOMAIN]/bash/server.sh"

Laden Sie anschließend die crontable neu.
$ sudo service cron reload

Sie haben nun einen Cronjob angelegt, der jede Minute prüft ob ein Update für eine dynamische IP angelegt wurde.
Sobald das index.php Skript eine neue apache Datei im Verzeichnis Update angelegt hat, wird dieses in das Apache Verzeichnis kopiert und der Webserver neu gestartet.



Einrichten des client Servers, mit der dynamischen IP, der über eine Subdomain erreicht werden soll.
Kopieren Sie die Datei bash/client.sh dazu an eine beliebige Stelle auf den client Server und machen Sie die Datei ausführbar.
$ chmod +x /PATH_TO_FILE/client.sh

Öffnen Sie die Crontable auf Ihrem Cleint Server und erstellen Sie einen Cronjob der die Datei jede Minute ausführt.

Beim ersten Aufruf der crontable müssen Sie Ihrem favorisierten Texteditor auswählen.
$ crontable -e

Fügen Sie unten folgende Zeile ein uns speichern Sie die Datei.
* * * * * /bin/bash "/PATH_TO_FILE/client.sh"

Laden Sie anschließend die crontable neu.
$ service cron reload

Sie haben nun einen Cronjob angelegt, der jede Minute die index.php Datei auf Ihrem Webserver aufruft.
Der Webserver prüft ob sich die IP des Client Servers verändert hat und erstellt bei einer Änderung neue Apache Dateien und Startet den Webserver neu.



Weitere Client Server mit dynamischen IP‘s erstellen.
Die Datei bash/client.sh kann bei beliebig vielen Client Servern, mit dynamischen IP‘s, verwendet werden.
Ändern Sie jeweils MY_SUBDOMAIN in der Datei in die Subdomain unter der er erreicht werden soll.
Kopieren Sie die Datei auf Ihren neuen Client Server und richten einen neuen Cronjob ein.



Viel Spaß
