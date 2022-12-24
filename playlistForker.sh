# Pakete installieren
sudo apt-get -y install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo add-apt-repository ppa:ondrej/apache2
sudo apt-get -qq update
sudo apt-get -qq install apache2 php8.2 php8.2-mongodb php8.2-curl libapache2-mod-php8.2 certbot python3-certbot-apache

# Firewall
sudo ufw allow 'OpenSSH'
sudo ufw allow 'Apache Full'
sudo ufw --force enable

# MongoDB installieren
cd ~
wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/4.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-4.4.list
sudo apt-get -qq update
sudo apt-get -qq install mongodb-org
sudo systemctl start mongod
sudo systemctl enable mongod.service

# Setup php
sudo sed -i 's/;extension=curl/extension=curl/' /etc/php/8.2/apache2/php.ini
sudo sh -c 'echo "extension=mongodb.so" >> /etc/php/8.2/apache2/php.ini'

# Setup apache
sudo a2enmod -q ssl
sudo a2enmod -q rewrite
sudo mkdir /var/www/spotify/
sudo rm -rf /var/www/html
sudo rm -rf /etc/apache2/sites-available/*

sudo sh -c 'cat > /etc/apache2/sites-available/spotify.conf << EOF
<VirtualHost *:80>
ServerAdmin webmaster@localhost
DocumentRoot /var/www/spotify/public
ServerName spotify.daselias.io

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF'

sudo a2ensite -q spotify.conf
sudo a2dissite -q 000-default.conf
sudo service apache2 restart

# add admin user
adduser -home /var/www/spotify admin
usermod -aG sudo admin
chown admin:admin /var/www/spotify 

# SSL
sudo certbot --apache

sudo service apache2 restart
