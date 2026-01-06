#!/bin/bash
set -e

# Configure Apache to serve from /workspace
cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /workspace
    
    <Directory /workspace>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Enable Apache modules
a2enmod rewrite

# Restart Apache
service apache2 restart