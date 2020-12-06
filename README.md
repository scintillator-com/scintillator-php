# scintillator-php

Host Build:

ref: https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-20-04

#Pre-reqs
sudo apt install php-cli unzip

#Install composer globally
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

#Create a helper
sudo touch /usr/local/bin/php-composer.sh
echo '#!/bin/bash' | sudo tee /usr/local/bin/php-composer.sh
echo "$(which php) $(which composer)" | sudo tee -a /usr/local/bin/php-composer.sh
sudo chmod 0755 /usr/local/bin/php-composer.sh

sudo apt install php-mongodb
php-composer.sh install

#Docker build



