#!/bin/bash
chown -R apache:apache /var/www/html/config /var/www/html/files /var/www/html/images /var/www/html/logs
exec /usr/sbin/httpd -D FOREGROUND
