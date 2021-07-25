xtrose Media Studio
Auteur : Moïse Rivera
Web : https://xtrose.com
Courriel : media.studio@xtrose.com



Avec xtrose DynDNS, vous obtenez une solution DynDns simple et gratuite sans fournisseurs externes et sans frais cachés.
Le serveur Web en ligne requis peut créer un nombre infini de sous-domaines pour les adresses IP dynamiques.
La méthode utilisée est simple à mettre en œuvre.
Pour les serveurs clients, les serveurs qui ont une IP dynamique, une simple tâche cron est configurée avec cron, qui envoie une simple requête curl au serveur Web à intervalles réguliers.
Le serveur Web, qui reçoit la requête curl avec un script PHP, vérifie si l'IP du serveur client a changé et crée de nouveaux fichiers de configuration Apache.
Une autre tâche cron sur le serveur Web vérifie s'il existe de nouveaux fichiers de configuration Apache, les copie dans le répertoire de configuration Apache et redémarre l'application Apache.
Avec seulement quelques scripts et une configuration simple, vous pouvez rapidement créer votre propre serveur DynDNS.



Nécessaire:
- Serveur Client - À Domicile (Linux)
- Serveur Web - En ligne (Linux)

Obligatoire (client serveur) :
- droits sudo
- cron

Obligatoire (serveur Web) :
- droits sudo
- cron
- permet de crypter
- apache2 (serveur web)
- PHP
- Domaine public




Cloner ou télécharger les fichiers
Téléchargez ou clonez les fichiers du référentiel GIT sur votre ordinateur :
$ git clone "https://github.com/xtrose/dyndns.git"



Créez un sous-domaine pour votre serveur Web
Vous avez d'abord besoin d'un sous-domaine sur votre serveur Web, qui contiendra plus tard le script PHP pour traiter les demandes des clients.
Si vous n'êtes pas encore familiarisé avec Apache, vous pouvez utiliser les fichiers du répertoire Files et les adapter en conséquence.
Veuillez copier les fichiers correspondants dans un autre répertoire, car ils seront nécessaires ultérieurement sans modification.

Remplacez les champs entre crochets dans les fichiers comme suit :
- [SERVERADMIN] -> Adresse e-mail de l'administrateur du serveur.
- [SOUS-DOMAINE] -> Sous-domaine qui sont déclenchés pour le serveur client.
- [DOMAINE] -> Domaine public sous lequel le serveur Web est accessible.

Supprimez les lignes suivantes du fichier apache-le-ssl.conf et remplacez-les :
Les fichiers sont conçus pour un proxy inverse vers une adresse IP.
Pour le script du serveur Web, le fichier doit être adapté pour pouvoir être appelé dans un répertoire de documents.
Remplacez [MY_PATH] par un vrai chemin accessible par votre serveur Apache.

Supprimer:
SSLProxyEngine activé
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Insérer:
DocumentRoot /var/www/[MON_CHEMIN]/

Renommez les 2 fichiers comme suit :
apache.conf -> [MON_SOUS-DOMAINE] .conf
apache-le-ssl.conf -> [MON_SOUS-DOMAINE] -le-ssl.conf

Copiez les deux fichiers que vous avez créés dans le répertoire de configuration d'Apache sur votre serveur Web :
Assurez-vous que vous disposez des droits root.
/etc/apache2/sites-enabled/

Créez le répertoire document sur votre serveur web tel que vous l'avez configuré dans le fichier apache-le-ssl.conf et attribuez les droits à l'utilisateur apache :
$ sudo chown www-data: www-data /var/www/[MY_PATH]/

Redémarrez l'application apache2 :
$ sudo service apache2 redémarrer

Créez un certificat Letsencrypt pour le sous-domaine nouvellement créé :
Remplacez les champs entre crochets par vos données de sous-domaine et de domaine dans la commande.
$ certbot certonly -d [SOUS-DOMAINE]. [DOMAINE] -d www. [SOUS-DOMAINE]. [DOMAINE] --apache --renew-by-default

Ouvrez le fichier copié [MY_SUBDOMAIN] -le-ssl.conf dans votre répertoire Apache et supprimez # des lignes suivantes :
Veuillez noter que vous avez besoin des droits root pour cela.
Notez le chemin d'accès au certificat letsencrypt et adaptez-le au chemin d'accès au certificat créé.
# Inclure /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc.
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN]

Redémarrez l'application apache2 :
$ sudo service apache2 redémarrer

Vous avez maintenant créé un sous-domaine pour votre serveur qui devrait être accessible de l'extérieur.
Toutes les requêtes au sous-domaine seront effectuées par l'application dans le répertoire de documents configuré.
Toutes les requêtes http sont redirigées vers https.
Veuillez noter que le certificat letsencrypt doit être renouvelé tous les 3 mois.
Vous pouvez automatiser cela ultérieurement à l'aide de cron.




Configurer les fichiers xTrose DynDNS
Ouvrez le fichier index.php à partir du référentiel GIT téléchargé et modifiez les entrées sous "// Config." comme suit:
MY_SECRET -> Mot de passe sécurisé
MON_DOMAINE.COM -> Domaine public de votre serveur Web (exemple.com)
MY_EMAIL_ADDRESS -> Adresse e-mail de l'administrateur du serveur (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Saisissez tous les sous-domaines pour lesquels aucun transfert DynDNS ne peut être construit, y compris ceux qui gèrent le script.
Si vous avez plus de sous-domaines pour d'autres sites Web ou des redirections qui n'ont pas changé
