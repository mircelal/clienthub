#!/bin/bash
# SQL dosyasını MySQL'e import etmek için script
# Kullanım: ./import_payments.sh [dbuser] [dbname] [dbpass]

DBUSER=${1:-nextcloud_user}
DBNAME=${2:-nextcloud}
DBPASS=${3}

if [ -z "$DBPASS" ]; then
    echo "MySQL şifresini girin:"
    mysql -u "$DBUSER" -p "$DBNAME" < restore_payments_table.sql
else
    mysql -u "$DBUSER" -p"$DBPASS" "$DBNAME" < restore_payments_table.sql
fi

echo "Tablo başarıyla import edildi!"

