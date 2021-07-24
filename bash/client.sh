#!/bin/sh

# Config.
host="DYNDNS.MY_DOMAIN.COM"
secret="MY_SECRET"
subdomain="MY_SUBDOMAIN"

# Call server.
url="${host}""?secret=""${secret}""&subdomain=""${subdomain}"
curl "${url}"

exit
