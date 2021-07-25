xtrose Media Studio
Autore: Moses Rivera
Web: https://xtrose.com
Mail: media.studio@xtrose.com



Con XTrose DynDNS ottieni una soluzione DynDns semplice e gratuita senza provider esterni e costi nascosti.
Il server web online richiesto può creare un numero infinito di sottodomini per gli IP dinamici.
Il metodo utilizzato è di facile attuazione.
Per i server client, i server che hanno un IP dinamico, viene impostato un semplice cron job con cron, che invia una semplice query curl al server web a intervalli regolari.
Il server web, che riceve la richiesta curl con uno script PHP, verifica se l'IP del server client è cambiato e crea nuovi file di configurazione di Apache.
Un altro cron job sul server web controlla se ci sono nuovi file di configurazione di Apache, li copia nella directory di configurazione di Apache e riavvia l'applicazione Apache.
Con pochi script e una semplice configurazione, puoi creare rapidamente il tuo server DynDNS.



Necessario:
- Server client - A casa (Linux)
- Server web - In linea (Linux)

Richiesto (server client):
- sudo diritti
- cron

Richiesto (server web):
- sudo diritti
- cron
- letencrypt
- apache2 (server web)
- PHP
- Dominio pubblico




Clona o scarica i file
Scarica o clona i file del repository GIT sul tuo computer:
$ git clone "https://github.com/xtrose/dyndns.git"



Crea un sottodominio per il tuo server web
Per prima cosa hai bisogno di un sottodominio sul tuo server web, che in seguito conterrà lo script PHP per elaborare le richieste del client.
Se non hai ancora familiarità con Apache, puoi utilizzare i file dalla directory File e adattarli di conseguenza.
Si prega di copiare i file per questo in un'altra directory, poiché questi saranno richiesti in seguito invariati.

Sostituisci i campi tra parentesi quadre nei file come segue:
- [SERVERADMIN] -> Indirizzo e-mail dell'amministratore del server.
- [SOTTODOMINIO] -> Sottodominio che vengono attivati ​​per il server client.
- [DOMINIO] -> Dominio pubblico sotto il quale è possibile raggiungere il server web.

Rimuovi le seguenti righe dal file apache-le-ssl.conf e sostituiscile:
I file sono progettati per un proxy inverso a un IP.
Per lo script del server Web, il file deve essere adattato in modo che possa essere richiamato in una directory di documenti.
Sostituisci [MY_PATH] con un percorso reale a cui può accedere il tuo server Apache.

Rimuovere:
SSLProxy Engine attivato
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Inserire:
DocumentRoot /var/www/[MY_PATH]/

Rinominare i 2 file come segue:
apache.conf -> [MY_SUBDOMAIN] .conf
apache-le-ssl.conf -> [MY_SUBDOMAIN] -le-ssl.conf

Copia i due file che hai creato nella directory di configurazione di apache sul tuo server web:
Assicurati di avere i diritti di root.
/etc/apache2/sites-enabled/

Crea la directory dei documenti sul tuo server web come l'hai configurata nel file apache-le-ssl.conf e assegna i diritti all'utente apache:
$ sudo chown www-data: www-data /var/www/[MY_PATH]/

Riavvia l'applicazione apache2:
$ sudo service apache2 restart

Crea un certificato Letsencrypt per il sottodominio appena creato:
Sostituisci i campi tra parentesi quadre con i dati del sottodominio e del dominio nel comando.
$ certbot certonly -d [SOTTODOMINIO]. [DOMINIO] -d www. [SOTTODOMINIO]. [DOMINIO] --apache --renew-by-default

Apri il file copiato [MY_SUBDOMAIN] -le-ssl.conf nella tua directory Apache e rimuovi # dalle seguenti righe:
Si prega di notare che sono necessari i diritti di root per questo.
Prendere nota del percorso del certificatoletsencrypt e adattarlo al percorso del certificato creato.
# Includi /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc.
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN]

Riavvia l'applicazione apache2:
$ sudo service apache2 restart

Ora hai creato un sottodominio per il tuo server che dovrebbe essere accessibile dall'esterno.
Tutte le richieste al sottodominio verranno eseguite dall'applicazione nella directory dei documenti configurata.
Tutte le richieste http vengono reindirizzate a https.
Si prega di notare che il certificatoletsencrypt deve essere rinnovato ogni 3 mesi.
Puoi automatizzarlo in un secondo momento usando cron.




Configura i file xtrose DynDNS
Apri il file index.php dal repository GIT scaricato e modifica le voci in "// Config". come segue:
MY_SECRET -> Password sicura
MY_DOMAIN.COM -> Dominio pubblico del tuo server web (example.com)
MY_EMAIL_ADDRESS -> Indirizzo email dell'amministratore del server (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Inserisci tutti i sottodomini per i quali non può essere costruito alcun inoltro DynDNS, inclusi quelli che gestiscono lo script.
Se disponi di ulteriori sottodomini per altri siti Web o reindirizzamenti che non devono essere modificati, aggiungili tutti qui.

Apri il file bash / server.sh e modifica le voci come segue:
/PATH/TO/UPDATE/DIRECTORY/ -> Il percorso della directory di aggiornamento successivo deve essere inserito nella directory del documento creato (/var/www/[MY_SUBDOMAIN]/update)

Apri il file bash/client.sh e modifica le voci come segue:
DYNDNS.MY_DOMAIN.COM -> Il sottodominio e il dominio generati devono essere inseriti qui.
MY_SECRET -> Sostituiscilo con la stessa password sicura che hai inserito nel file index.php.
MY_SUBDOMAIN -> Il nuovo sottodominio sotto il quale è possibile accedere al tuo IP dinamico. (home.esempio.com)



Copia tutti i file dal repository GIT nella directory dei documenti creata sul tuo server web:
Quindi elimina il file bash/client.sh sul tuo server web. Questo verrà utilizzato in seguito per i server client.
Se non è disponibile, crea i dati delle due directory e aggiorna nella directory dei documenti sul tuo server web.

Quindi assegna i diritti di tutti i file nella directory dei documenti sul tuo server web all'utente apache:
$ sudo chown -R www-data: www-data /var/www/[MY_SUBDOMAIN]/



Crea un lavoro cron con cron sul tuo server web
Per fare ciò, il file nella directory del documento bash/server.sh deve essere reso eseguibile:
$ sudo chmod + x /var/www/[MY_SUBDOMAIN]/bash/server.sh

Quando richiami il crontabile per la prima volta, devi selezionare il tuo editor di testo preferito:
$ sudo crontable -e

Aggiungi la seguente riga di seguito e salva il file:
* * * * * /bin/bash "/var/www/[MY_SUBDOMAIN]/bash/server.sh"

Quindi ricaricare il crontabile:
$ sudo service cron reload

Ora hai creato un cron job che controlla ogni minuto se è stato creato un aggiornamento per un IP dinamico:
Non appena lo script index.php ha creato un nuovo file apache nella directory Update, questo viene copiato nella directory Apache e il server web viene riavviato.



Configurazione del server client con l'IP dinamico che deve essere raggiunto tramite un sottodominio
Per fare ciò, copia il file bash/client.sh in qualsiasi posizione sul server client e rendi eseguibile il file:
$ chmod + x /PATH_TO_FILE/client.sh

Apri il crontable sul tuo server Cleint e crea un cron job che esegua il file ogni minuto:

Quando richiami il crontabile per la prima volta, devi selezionare il tuo editor di testo preferito.
$ crontabile -e

Aggiungi la seguente riga di seguito e salva il file:
* * * * * /bin/bash "/PATH_TO_FILE/client.sh"

Quindi ricaricare il crontabile:
$ servizio cron ricarica

Ora hai creato un cron job che chiama il file index.php sul tuo server web ogni minuto.
Il server web controlla se l'IP del server client è cambiato e se c'è un cambiamento crea nuovi file Apache e riavvia il server web.



Crea server client aggiuntivi con IP dinamici
Il file bash/client.sh può essere utilizzato con qualsiasi numero di server client con IP dinamici:
Cambia MY_SUBDOMAIN nel file con il sottodominio sotto il quale deve essere raggiunto.
Copia il file sul tuo nuovo server client e imposta un nuovo cron job.



Divertiti
