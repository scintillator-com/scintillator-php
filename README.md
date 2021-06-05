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


# APIs

## APIs

| Verb  | Path                                        | Description                                                          | Request     | Return      |
|-------|---------------------------------------------|----------------------------------------------------------------------|-------------|-------------|
|POST   |/api/1.0/login                               |List/search `File`s (and branches)                                    | AuthRequest | SessionInfo |
|POST   |/api/1.0/user                                |Create user                                                           | UserRequest |             |
|POST   |/api/1.0/org                                 |After user verification, create your organization                     |             |             |
|-----  |-----                                        |-----                                                                 |-----        |-----        |
|GET    |/api/1.0/generators                          |                                                                      |             |             |
|GET    |/api/1.0/history                             |                                                                      |             |             |
|GET    |/api/1.0/moment                              |                                                                      |             |             |
|PATCH  |/api/1.0/moment                              |                                                                      |             |             |
|DELETE |/api/1.0/moment                              |                                                                      |             |             |
|GET    |/api/1.0/project                             |                                                                      |             |             |
|PATCH  |/api/1.0/project                             |                                                                      |             |             |
|POST   |/api/1.0/snippets                            |                                                                      |             |             |
|PUT    |/api/1.0/snippets                            |                                                                      |             |             |
|DELETE |/api/1.0/snippets                            |                                                                      |             |             |


### AuthRequest

```json
{
    "username": "user@website.com",
    "password": "SecretPassword!"
}
```

### SessionInfo

```json
{
    "expires": "2021-12-31T01:20:36Z",
    "token": "0a052a182d0aef7e0a1eccaa9838ab4697aff51a413aa05c9e",
    "type": "bearer"
}
```

### UserRequest

```json
{
    "email": "user@website.com",
    "first_name": "First", 
    "last_name": "Last",
    "password": "SecretPassword!"
}
```