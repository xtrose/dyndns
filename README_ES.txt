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
Si tiene más subdominios para otros sitios web o redireccionamientos que no han cambiado
