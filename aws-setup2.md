# Git2s
## Pre-setup
To deploy private repo, generate PAT with full `repo` permissions
ref: https://stackoverflow.com/questions/42148841/github-clone-with-oauth-access-token


# AWS
 - Launch Instance:
 - `mi-0be2609ba883822ec` (64-bit x86)
 - `t2.micro`

---

# EC2 instance
## Install NGINX, PHP, Python
---
```
sudo yum update
sudo amazon-linux-extras enable nginx1 php7.4 python3.8
sudo yum clean metadata
sudo yum install -y gcc git nginx php php-devel php-fpm php-pear python38
sudo ln -s /usr/bin/python3.8 /usr/bin/python3
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


### Setup MITMProxy
***Note:*** mitmproxy:  dev version is commit a42d071995e70e39010d91233d768f25b73a7f95
```
cd ~/sites/
git clone https://github.com/mitmproxy/mitmproxy.git
cd mitmproxy/
git checkout tags/v6.0.0
```


-c checksum
-n dry run

### MITMProxy Deployment
```
scp ~/sites/mitm-addon/requirements.txt herodev@54.163.103.92:~/sites/mitmproxy/
ssh herodev@54.163.103.92 "mkdir -p ~/sites/mitmproxy/scintillator/logs"
rsync -mptvz --del --dirs ~/sites/mitm-addon/*.py herodev@54.163.103.92:~/sites/mitmproxy/scintillator/
scp ~/sites/mitm-addon/requirements.txt herodev@54.163.103.92:~/sites/mitmproxy/

#update /etc/environment with RULES_FILE=/home/herodev/sites/mitmproxy/scintillator/rules.json
scp ~/sites/mitm-addon/data/rules.json herodev@54.163.103.92:~/sites/mitmproxy/scintillator/
```

### MITMProxy Launch
```
cd ~/sites/mitmproxy/
./dev.sh
. venv/bin/activate
mitmdump --set block_global=false -s scintillator/scintillator.py &
```


### Setup PHP
---
**Configuration**

Enable environment ($_ENV) variables
```
sed -i.bak 's/^variables_order = "GPCS"$/variables_order = "EGPCS"/' /etc/php.ini
```

**PECL and MongoDB**

Install PECL dependencies
```
sudo pecl channel-update pecl.php.net
sudo pecl install mongodb

sudo sh -c 'cat <<EOF >/etc/php.d/20-mongodb.ini
; Enable mongodb extension module
extension=mongodb.so
EOF'
```


#### Setup PHP: Composer
---
**Install**
```
mkdir ~/composer
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


### Setup PHP-FPM
---
**Configuration**

Update www.conf
```
sed -i 's/^.*clear_env =.*/clear_env = no/' /etc/php-fpm.d/www.conf
sed -i 's/^user =.*/user = nginx/'        /etc/php-fpm.d/www.conf
sed -i 's/^group =.*/group = nginx/'      /etc/php-fpm.d/www.conf
chown -R nginx:nginx /var/log/php-fpm
chmod -R ug=rwX /var/log/php-fpm
chmod -R o=-rwx /var/log/php-fpm

chown -R nginx:nginx /usr/share/nginx/html
chmod -R ug=rX /usr/share/nginx/html
chmod -R o=-rwx /usr/share/nginx/html

```

Create/update /etc/systemd/system/php-fpm.service.d/override.conf
```
[Service]
Environment="MONGO_DB=scintillator"
Environment="MONGO_URI=mongodb://192.168.1.31:27017/scintillator"
Environment="SESSION_LIMITS=[3600,86400,300,300]"
```

**Service**
```
#install
sudo systemctl start php-fpm.service
sudo systemctl enable php-fpm.service

#after systemd update
systemctl daemon-reload
systemctl restart php-fpm.service
```


## Configure NGINX
---
**Configuration**
```
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
```

**Service**
```
sudo systemctl start nginx.service
sudo systemctl enable nginx.service
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




```
deploy-apps-template.sh
18:echo "rsync -rzhL --delete --exclude 'logs' --exclude 'uploads' ${SourceHost}:${SourceRoot}/* ${DestinationRoot}/"
19:rsync -rzhL --delete --exclude 'logs' --exclude 'uploads' ${SourceHost}


diff-sync.sh
38:rsync -rvnc --exclude '*.bak' --exclude '.git' --exclude '.gitignore' --exclude '*.pyc' "${lpath}" "${rhost}:${rpath}" > "${output}"

git-sync.sh
102:rsync -rvnc --exclude '*.bak' --exclude '.gitignore' --exclude '*.pyc' "${lpath}" "${rhost}:${rpath}" > "${output}"




pip install ~/scintillator-0.2.0.tar.gz
pip freeze | grep scintillator | xargs pip uninstall -y

```