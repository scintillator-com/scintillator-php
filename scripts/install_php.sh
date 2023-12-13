#!/bin/bash

# Infrastructure
sudo yum update -y
sudo amazon-linux-extras enable nginx1 php7.4
sudo yum clean metadata
sudo yum install -y gcc git nginx php php-devel php-fpm php-pear php-sodium


## Configure NGINX
if [ -f /etc/nginx/default.d/php.conf ]; then
  echo "NGINX has already been updated for PHP..."
else
	sudo sh -c 'cat <<EOF >/etc/nginx/default.d/php.conf
	location /api/1.0/ {
			try_files \$uri /api/1.0/index.php?\$args;
	}

	location ~ \.(css|csv|doc|gif|ico|jpg|jpeg|js|png|xls|xlsx)$ {
			try_files \$uri =404;
	}

	location ~ \.php$ {
			include fastcgi.conf;

			# With php-fpm (or other unix sockets):
			fastcgi_pass php-fpm;
			fastcgi_index index.php;

			# With php-cgi (or other tcp sockets):
			#fastcgi_pass 127.0.0.1:9000;
	}
	EOF'
fi


# NGINX: Enable
echo "Enable nginx.service..."
sudo systemctl enable nginx.service


# PHP: Configuration
## Enable environment ($_ENV) variables
sudo sed -i 's/^variables_order = "GPCS"$/variables_order = "EGPCS"/' /etc/php.ini


# PECL: MongoDB
if [ -f /etc/php.d/20-mongodb.ini ]; then
  echo "PECL: MongoDB has already been installed..."
else
  sudo pecl channel-update pecl.php.net
  sudo pecl install mongodb

  sudo sh -c 'cat <<EOF >/etc/php.d/20-mongodb.ini
  ; Enable mongodb extension module
  extension=mongodb.so
  EOF'
fi


# PHP-FPM: Configuration
## Update www.conf
echo "Updating /etc/php-fpm.d/www.conf..."
sudo sed -i 's/^.*clear_env =.*/clear_env = no/' /etc/php-fpm.d/www.conf
sudo sed -i 's/^user =.*/user = nginx/'          /etc/php-fpm.d/www.conf
sudo sed -i 's/^group =.*/group = nginx/'        /etc/php-fpm.d/www.conf

echo "Securing /var/log/php-fpm/..."
sudo chown -R nginx:nginx /var/log/php-fpm/
sudo chmod -R ug=rwX /var/log/php-fpm/
sudo chmod -R o=-rwx /var/log/php-fpm/

# PHP-FPM: Enable
echo "Enable php-fpm.service..."
sudo systemctl enable php-fpm.service

for dir in html lib scripts vendor; do
  echo "Securing /usr/share/nginx/${dir}/"
  sudo chown -R nginx:nginx "/usr/share/nginx/${dir}/"
  sudo chmod -R ug=rX "/usr/share/nginx/${dir}/"
  sudo chmod -R o=-rwx "/usr/share/nginx/${dir}/"
done
