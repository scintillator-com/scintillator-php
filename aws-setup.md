# Git2s
## Pre-setup
To deploy private repo, generate PAT with full `repo` permissions
ref: https://stackoverflow.com/questions/42148841/github-clone-with-oauth-access-token


# AWS
 - Launch Instance:
 - `mi-0be2609ba883822ec` (64-bit x86)
 - `t2.micro`


# EC2 instance

### Install NGINX
`sudo amazon-linux-extras install nginx1`

### Install PHP
```
sudo amazon-linux-extras install php7.4
sudo yum install gcc php-devel
sudo yum install php-pear
sudo pecl channel-update pecl.php.net
sudo pecl install mongodb
```


```
running: make INSTALL_ROOT="/var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0" install
Installing shared extensions:     /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr/lib64/php/modules/
running: find "/var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0" | xargs ls -dils
  191080    0 drwxr-xr-x 3 root root      17 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0
 4606341    0 drwxr-xr-x 3 root root      19 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr
 8486482    0 drwxr-xr-x 3 root root      17 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr/lib64
14402729    0 drwxr-xr-x 3 root root      21 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr/lib64/php
16838387    0 drwxr-xr-x 2 root root      24 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr/lib64/php/modules
16838388 6080 -rwxr-xr-x 1 root root 6222128 Jan 27 02:23 /var/tmp/pear-build-rootxxo2Yc/install-mongodb-1.9.0/usr/lib64/php/modules/mongodb.so

Build process completed successfully
Installing '/usr/lib64/php/modules/mongodb.so'
install ok: channel://pecl.php.net/mongodb-1.9.0
configuration option "php_ini" is not set to php.ini location
You should add "extension=mongodb.so" to php.ini

#
# /etc/php.d/20-mongodb.ini
#
; Enable mongodb extension module
extension=mongodb.so
```


```
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
```



#### Install Composer
```
mkdir ~/composer
cd ~/composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

#Create a helper
sudo touch /usr/local/bin/php-composer.sh
echo '#!/bin/bash' | sudo tee /usr/local/bin/php-composer.sh
echo "$(which php) $(which composer) \$@" | sudo tee -a /usr/local/bin/php-composer.sh
sudo chmod 0755 /usr/local/bin/php-composer.sh
```


#### Install API Layer
```
mkdir ~/sites
cd ~/sites
git clone https://github.com/scintillator-com/scintillator-php.git
cd scintillator-php
git checkout develop
/usr/local/bin/php-composer.sh install
cd
sudo ln -s ~/sites/scintillator-php/lib     /usr/share/nginx/lib
sudo ln -s ~/sites/scintillator-php/pub/api /usr/share/nginx/html/api
```


#### Update NGINX

```
#
# /etc/nginx/default.d/php.conf
#
location /api/1.0/ {
    try_files $uri /api/1.0/index.php?$args;
}

location ~ \.(css|csv|doc|gif|ico|jpg|jpeg|js|png|xls|xlsx)$ {
    try_files $uri =404;
}
```

```
sudo systemctl start nginx
sudo systemctl enable nginx
```



### Install Python
`sudo amazon-linux-extras install python3.8`
`sudo ln -s /usr/bin/python3.8 /usr/bin/python3`




### Install MongoDB
ref: https://docs.mongodb.com/manual/tutorial/install-mongodb-on-amazon/
```
#
# /etc/yum.repos.d/mongodb-org-4.4.repo
#
[mongodb-org-4.4]
name=MongoDB Repository
baseurl=https://repo.mongodb.org/yum/amazon/2/mongodb-org/4.4/x86_64/
gpgcheck=1
enabled=1
gpgkey=https://www.mongodb.org/static/pgp/server-4.4.asc

sudo yum install -y mongodb-org
sudo systemctl start mongod
sudo systemctl enable mongod
```


### Create Local User
```
sudo useradd -m -U herodev
sudo usermod -a -G wheel herodev
sudo mkdir /home/herodev/.ssh
sudo vim /home/herodev/.ssh/authorized_keys
sudo chown -R herodev:herodev /home/herodev/.ssh
sudo chmod -R go=-rww /home/herodev/.ssh
sudo chmod -R u=rwX /home/herodev/.ssh
```

### Instal Git Projects
```
sudo yum install git


mkdir ~/sites
cd ~/sites
git clone https://github.com/scintillator-com/mitm-addon.git
cd mitm-addon
git checkout develop
#TODO: git checkout tags/0.1.0
mkdir logs


#CEE: mitmproxy:  dev version is commit a42d071995e70e39010d91233d768f25b73a7f95
cd ~/sites
git clone https://github.com/mitmproxy/mitmproxy.git
cd mitmproxy
git checkout tags/v6.0.0


ln -s ~/sites/mitm-addon ~/sites/mitmproxy/scintillator
echo "-e .[dev]" > requirements.txt
echo "dnspython==2.0.0" >> requirements.txt
echo "pymongo==3.11.2"  >> requirements.txt


#ref: https://github.com/mitmproxy/mitmproxy/blob/master/CONTRIBUTING.md
./dev.sh
. venv/bin/activate


ref: https://stackoverflow.com/questions/52068746/mitmproxy-client-connection-killed-by-block-global
mitmdump --set block_global=false -s scintillator/scintillator.py
```


