
# AWS
 - Launch Instance:
 - `mi-0be2609ba883822ec` (64-bit x86)
 - `t2.micro`



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


### Setup MITMProxy
***Note:*** mitmproxy:  dev version is commit a42d071995e70e39010d91233d768f25b73a7f95
#ref: https://github.com/mitmproxy/mitmproxy/blob/master/CONTRIBUTING.md
```
cd ~/sites/
git clone https://github.com/mitmproxy/mitmproxy.git
cd mitmproxy/
git checkout tags/v6.0.0
```


echo "-e .[dev]" > requirements.txt
echo "dnspython==2.0.0" >> requirements.txt
echo "pymongo==3.11.2"  >> requirements.txt

### MITMProxy Launch
```
cd ~/sites/mitmproxy/
./dev.sh
. venv/bin/activate
ref: https://stackoverflow.com/questions/52068746/mitmproxy-client-connection-killed-by-block-global
mitmdump --set block_global=false -s scintillator/scintillator.py &

pip install ~/scintillator-0.2.0.tar.gz
pip freeze | grep scintillator | xargs pip uninstall -y

```




#### Setup PHP: Composer
---
**Install**
```
mkdir ~/composer/
cd ~/composer/
curl -sS https://getcomposer.org/installer -o composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```


**Create a helper**
```
sudo touch /usr/local/bin/php-composer.sh
echo '#!/bin/bash' | sudo tee /usr/local/bin/php-composer.sh
echo "$(which php) $(which composer) \$@" | sudo tee -a /usr/local/bin/php-composer.sh
sudo chmod 0755 /usr/local/bin/php-composer.sh
```






## Hardening
```
chmod -R u=rX /etc/nginx/
chmod -R go=-rwx /etc/nginx/

chmod 0400 /etc/php.ini
chmod -R u=rX /etc/php.d/
chmod -R go=-rwx /etc/php.d/

chmod 0400 /etc/php-fpm.conf
chmod -R u=rX /etc/php-fpm.d/
chmod -R go=-rwx /etc/php-fpm.d/

chmod -R u=rX /etc/php-zts.d/
chmod -R go=-rwx /etc/php-zts.d/
```




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





Whitelisted AWS public IP within Atlas
savant
Q5z!TB2$92MaSzgZ
#TODO:  SSL?



openssl req -new \
  -newkey rsa:2048 \
	-nodes \
	-out proxy_scintillator_com.csr \
	-keyout proxy_scintillator_com.key \
	-subj "CN=proxy.scintillator.com"



