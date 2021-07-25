xtrose Media Studio
Autor: Moses Rivera
Web: https://xtrose.com
Correo: media.studio@xtrose.com



Con xtrose DynDNS, obtiene una solución DynDns simple y gratuita sin proveedores externos ni costos ocultos.
El servidor web en línea requerido puede crear un número infinito de subdominios para IP dinámicas.
El método utilizado es fácil de implementar.
Para los servidores cliente, los servidores que tienen una IP dinámica, se configura un trabajo cron simple con cron, que envía una consulta curl simple al servidor web a intervalos regulares.
El servidor web, que recibe la solicitud curl con un script PHP, verifica si la IP del servidor cliente ha cambiado y crea nuevos archivos de configuración de Apache.
Otro trabajo cron en el servidor web verifica si hay nuevos archivos de configuración de Apache, los copia en el directorio de configuración de Apache y reinicia la aplicación Apache.
Con solo unos pocos scripts y una configuración simple, puede crear rápidamente su propio servidor DynDNS.



Necesario:
- Cliente Servidor - En casa (Linux)
- Servidor web - En línea (Linux)

Requerido (cliente servidor):
- derechos de sudo
- cron

Requerido (servidor web):
- derechos de sudo
- cron
- letsencrypt
- apache2 (servidor web)
- PHP
- Dominio publico




Clona o descarga los archivos
Descargue o clone los archivos del repositorio GIT en su computadora:
$ git clon "https://github.com/xtrose/dyndns.git"



Crea un subdominio para tu servidor web
Primero necesita un subdominio en su servidor web, que luego contiene el script PHP para procesar las solicitudes del cliente.
Si aún no está familiarizado con Apache, puede usar los archivos del directorio Archivos y adaptarlos en consecuencia.
Copie los archivos para esto en otro directorio, ya que serán necesarios más adelante sin cambios.

Reemplace los campos entre corchetes en los archivos de la siguiente manera:
- [SERVERADMIN] -> Dirección de correo electrónico del administrador del servidor.
- [SUBDOMAIN] -> Subdominio que se activa para el servidor cliente.
- [DOMINIO] -> Dominio público bajo el cual se puede acceder al servidor web.

Elimine las siguientes líneas del archivo apache-le-ssl.conf y reemplácelas:
Los archivos están diseñados para un proxy inverso a una IP.
Para el script del servidor web, el archivo debe adaptarse para que pueda ser llamado en un directorio de documentos.
Reemplace [MY_PATH] con una ruta real a la que pueda acceder su servidor Apache.

Eliminar:
SSLProxyEngine activado
ProxyPass / http://[IP]/
ProxyPassReverse / http://[IP]/

Insertar:
DocumentRoot /var/www/[MY_PATH]/

Cambie el nombre de los 2 archivos de la siguiente manera:
apache.conf -> [MI_SUBDOMINIO] .conf
apache-le-ssl.conf -> [MI_SUBDOMINIO] -le-ssl.conf

Copie los dos archivos que creó en el directorio de configuración de apache en su servidor web:
Asegúrese de tener derechos de root.
/etc/apache2/sites-enabled/

Cree el directorio de documentos en su servidor web como lo configuró en el archivo apache-le-ssl.conf y asigne los derechos al usuario de apache:
$ sudo chown www-data: www-data /var/www/[MY_PATH]/

Reinicie la aplicación apache2:
$ sudo service apache2 reiniciar

Cree un certificado Letsencrypt para el subdominio recién creado:
Reemplace los campos entre corchetes con su subdominio y datos de dominio en el comando.
$ certbot certonly -d [SUBDOMAIN]. [DOMAIN] -d www. [SUBDOMAIN]. [DOMAIN] --apache --renew-by-default

Abra el archivo copiado [MY_SUBDOMAIN] -le-ssl.conf en su directorio de Apache y elimine # de las siguientes líneas:
Tenga en cuenta que necesita derechos de root para esto.
Anote la ruta del certificado letsencrypt y adáptela a la ruta del certificado creado.
# Incluir /etc/letsencrypt/options-ssl-apache.conf
# SSLCertificateFile /etc.
# SSLCertificateKeyFile /etc/letsencrypt/live/[SUBDOMAIN]

Reinicie la aplicación apache2:
$ sudo service apache2 reiniciar

Ahora ha creado un subdominio para su servidor que debería ser accesible desde el exterior.
Todas las solicitudes al subdominio serán realizadas por la aplicación en el directorio de documentos configurado.
Todas las solicitudes http se redirigen a https.
Tenga en cuenta que el certificado de letsencrypt debe renovarse cada 3 meses.
Puede automatizar esto en un momento posterior utilizando cron.




Configurar los archivos xtrose DynDNS
Abra el archivo index.php del repositorio GIT descargado y cambie las entradas en "// Config." como sigue:
MY_SECRET -> Contraseña segura
MY_DOMAIN.COM -> Dominio público de su servidor web (example.com)
MY_EMAIL_ADDRESS -> Dirección de correo electrónico del administrador del servidor (mail@example.com)
BLACKLISTED_SUBDOMAIN -> Ingrese todos los subdominios para los que no se puede construir ningún reenvío DynDNS, incluidos aquellos que administran el script.
Si tiene más subdominios para otros sitios web o redireccionamientos que no se deben cambiar, agréguelos todos aquí.

Abra el archivo bash / server.sh y cambie las entradas de la siguiente manera:
/PATH/TO/UPDATE/DIRECTORY/ -> La ruta al directorio de actualización posterior debe insertarse en el directorio de documentos creado (/var/www/[MY_SUBDOMAIN]/update)

Abra el archivo bash / client.sh y cambie las entradas de la siguiente manera:
DYNDNS.MY_DOMAIN.COM -> El subdominio y el dominio generados deben ingresarse aquí.
MY_SECRET -> Reemplace esto con la misma contraseña segura que ingresó en el archivo index.php.
MY_SUBDOMAIN -> El nuevo subdominio bajo el cual se puede acceder a su IP dinámica. (home.ejemplo.com)



Copie todos los archivos del repositorio GIT en el directorio de documentos creado en su servidor web:
Luego elimine el archivo bash/client.sh en su servidor web. Esto se utilizará más adelante para los servidores cliente.
Si no está disponible, cree los datos de los dos directorios y actualícelos en el directorio de documentos de su servidor web.

Luego asigne los derechos de todos los archivos en su directorio de documentos en su servidor web al usuario de apache:
$ sudo chown -R www-data:www-data /var/www/[MY_SUBDOMAIN]/



Cree un trabajo cron con cron en su servidor web
Para hacer esto, el archivo en su directorio de documentos bash/server.sh debe ser ejecutable:
$ sudo chmod + x /var/www/[MY_SUBDOMAIN]/bash/server.sh

Cuando abre la tabla cron por primera vez, debe seleccionar su editor de texto favorito:
$ sudo crontable -e

Agregue la siguiente línea a continuación y guarde el archivo:
* * * * * /bin/bash "/var/www/[MY_SUBDOMAIN]/bash/server.sh"

Luego recargue el crontable:
$ sudo service cron recarga

Ahora ha creado un trabajo cron que verifica cada minuto si se ha creado una actualización para una IP dinámica:
Tan pronto como el script index.php ha creado un nuevo archivo apache en el directorio de actualización, este se copia en el directorio de Apache y se reinicia el servidor web.



Configurar el servidor del cliente con la IP dinámica que se debe alcanzar a través de un subdominio
Para hacer esto, copie el archivo bash/client.sh a cualquier ubicación en el servidor del cliente y haga que el archivo sea ejecutable:
$ chmod + x /PATH_TO_FILE/client.sh

Abra el crontable en su servidor Cleint y cree un trabajo cron que ejecute el archivo cada minuto:

Cuando acceda al crontable por primera vez, debe seleccionar su editor de texto favorito.
$ crontable -e

Agregue la siguiente línea a continuación y guarde el archivo:
* * * * * /bin/bash "/PATH_TO_FILE/client.sh"

Luego recargue el crontable:
$ service cron recarga

Ahora ha creado un trabajo cron que llama al archivo index.php en su servidor web cada minuto.
El servidor web comprueba si la IP del servidor cliente ha cambiado y, si hay algún cambio, crea nuevos archivos Apache y reinicia el servidor web.



Cree servidores cliente adicionales con IP dinámicas
El archivo bash/client.sh se puede utilizar con cualquier número de servidores cliente con IP dinámicas:
Cambie MY_SUBDOMAIN en el archivo por el subdominio bajo el cual se debe acceder.
Copie el archivo a su nuevo servidor cliente y configure un nuevo trabajo cron.



Que te diviertas
