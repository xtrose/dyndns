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
Se hai più sottodomini per altri siti web o reindirizzamenti che non sono cambiati
