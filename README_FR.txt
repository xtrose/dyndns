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
- Droits sudo
- cron

Obligatoire (serveur Web) :
- Droits sudo
- cron
- letsencrypt
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
- [SUBDOMAINE] -> Sous-domaine qui sont déclenchés pour le serveur client.
- [DOMAIN] -> Domaine public sous lequel le serveur Web est accessible.

Supprimez les lignes suivantes du fichier apache-le-ssl.conf et remplacez-les :
Les fichiers sont conçus pour un proxy inverse vers une adresse IP.
Pour le script du serveur Web, le fichier doit être adapté pour pouvoir être appelé dans un répertoire de documents.
Remplacez [MY_PATH] par un vrai chemin accessible par votre serveur Apache.

Supprimer:
SSLProxyEngine activé
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Insérer:
DocumentRoot /var/www/[MY_PATH]/

Renommez les 2 fichiers comme suit :
apache.conf -> [MY_SUBDOMAIN].conf
apache-le-ssl.conf -> [MY_SUBDOMAIN]-le-ssl.conf

Copiez les deux fichiers que vous avez créés dans le répertoire de configuration d'Apache sur votre serveur Web :
Assurez-vous que vous disposez des droits root.
/etc/apache2/sites-enabled/

Créez le répertoire document sur votre serveur web tel que vous l'avez configuré dans le fichier apache-le-ssl.conf et attribuez les droits à l'utilisateur apache :
$ sudo chown www-data: www-data /var/www/[MY_PATH]/

Redémarrez l'application apache2 :
$ sudo service apache2 redémarrer

Créez un certificat Letsencrypt pour le sous-domaine nouvellement créé :
Remplacez les champs entre crochets par vos données de sous-domaine et de domaine dans la commande.
$ certbot certonly -d [SUBDOMAIN].[DOMAIN] -d www.[SUBDOMAIN].[DOMAIN] --apache --renew-by-default

Ouvrez le fichier copié [MY_SUBDOMAIN] -le-ssl.conf dans votre répertoire Apache et supprimez # des lignes suivantes :
Veuillez noter que vous avez besoin des droits root pour cela.
Notez le chemin d'accès au certificat letsencrypt et adaptez-le au chemin d'accès au certificat créé.
# Include /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/fullchain.pem
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN].[DOMAIN]/privatekey.pem

Redémarrez l'application apache2 :
$ sudo service apache2 restart

Vous avez maintenant créé un sous-domaine pour votre serveur qui devrait être accessible de l'extérieur.
Toutes les requêtes au sous-domaine seront effectuées par l'application dans le répertoire de documents configuré.
Toutes les requêtes http sont redirigées vers https.
Veuillez noter que le certificat letsencrypt doit être renouvelé tous les 3 mois.
Vous pouvez automatiser cela ultérieurement à l'aide de cron.




Configurer les fichiers xtrose DynDNS
Ouvrez le fichier index.php à partir du référentiel GIT téléchargé et modifiez les entrées sous "// Config." comme suit:
MY_SECRET -> Mot de passe sécurisé
MON_DOMAINE.COM -> Domaine public de votre serveur Web (exemple.com)
MY_EMAIL_ADDRESS -> Adresse e-mail de l'administrateur du serveur (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Saisissez tous les sous-domaines pour lesquels aucun transfert DynDNS ne peut être construit, y compris ceux qui gèrent le script.
Si vous avez d'autres sous-domaines pour d'autres sites Web ou des redirections qui ne doivent pas être modifiés, ajoutez-les tous ici.

Ouvrez le fichier bash / server.sh et modifiez les entrées comme suit :
/PATH/TO/UPDATE/DIRECTORY/ -> Le chemin d'accès au répertoire de mise à jour ultérieure doit être inséré dans votre répertoire de document créé (/var/www/[MY_SUBDOMAIN]/update)

Ouvrez le fichier bash/client.sh et modifiez les entrées comme suit :
DYNDNS.MY_DOMAIN.COM -> Le sous-domaine et le domaine générés doivent être saisis ici.
MY_SECRET -> Remplacez-le par le même mot de passe sécurisé que vous avez entré dans le fichier index.php.
MY_SUBDOMAIN -> Le nouveau sous-domaine sous lequel votre IP dynamique est accessible. (home.exemple.com)



Copiez tous les fichiers du référentiel GIT dans le répertoire de documents créé sur votre serveur Web :
Supprimez ensuite le fichier bash/client.sh sur votre serveur web. Cela sera utilisé plus tard pour les serveurs clients.
S'il n'est pas disponible, créez les deux répertoires data et mettez-les à jour dans le répertoire de documents sur votre serveur Web.

Attribuez ensuite les droits de tous les fichiers de votre répertoire de documents sur votre serveur Web à l'utilisateur Apache :
$ sudo chown -R www-data:www-data /var/www/[MY_SUBDOMAIN]/



Créez une tâche cron avec cron sur votre serveur Web
Pour ce faire, le fichier dans votre répertoire de documents bash/server.sh doit être rendu exécutable :
$ sudo chmod + x /var/www/[MY_SUBDOMAI]/bash/server.sh

Lorsque vous appelez le crontable pour la première fois, vous devez sélectionner votre éditeur de texte préféré :
$ sudo crontable -e

Ajoutez la ligne suivante ci-dessous et enregistrez le fichier :
* * * * * /bin/bash "/var/www/[MY_SUBDOMAIN]/bash/server.sh"

Rechargez ensuite le crontable :
$ sudo service cron reload

Vous avez maintenant créé une tâche cron qui vérifie chaque minute si une mise à jour a été créée pour une IP dynamique.
Dès que le script index.php a créé un nouveau fichier apache dans le répertoire Update, celui-ci est copié dans le répertoire Apache et le serveur web est redémarré.



Configuration du serveur client avec l'IP dynamique à atteindre via un subdomain
Pour ce faire, copiez le fichier bash/client.sh à n'importe quel emplacement sur le serveur client et rendez le fichier exécutable :
$ chmod + x /PATH_TO_FILE/client.sh

Ouvrez le crontable sur votre serveur Cleint et créez une tâche cron qui exécute le fichier toutes les minutes :

Lorsque vous appelez le crontable pour la première fois, vous devez sélectionner votre éditeur de texte préféré.
$ crontable -e

Ajoutez la ligne suivante ci-dessous et enregistrez le fichier :
* * * * * /bin/bash "/PATH_TO_FILE/client.sh"

Rechargez ensuite le crontable :
$ service cron reload

Vous avez maintenant créé une tâche cron qui appelle le fichier index.php sur votre serveur Web toutes les minutes.
Le serveur Web vérifie si l'adresse IP du serveur client a changé et s'il y a un changement, il crée de nouveaux fichiers Apache et redémarre le serveur Web.



Créer des serveurs clients supplémentaires avec des adresses IP dynamiques
Le fichier bash/client.sh peut être utilisé avec n'importe quel nombre de serveurs clients avec des IP dynamiques :
Remplacez MY_SUBDOMAIN dans le fichier par le sous-domaine sous lequel il doit être atteint.
Copiez le fichier sur votre nouveau serveur client et configurez une nouvelle tâche cron.



S'amuser
