cd tags
qpdf --qdf $1 x-$1
sed -i 's/zzzPsgiolePfrp/              /g' /var/www/html/tags/x-$1
lpr x-$1

