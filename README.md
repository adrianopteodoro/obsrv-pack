# Biohazard Outbreak File #1 and #2 Private Server

All original credit goes to [obsrv.org](https://www.obsrv.org/) team, the following content is a simplification from guide taken from [gh0stline GitLab](https://gitlab.com/gh0stl1ne)
Official thread about this on [obsrv.org forum](http://www.obsrv.org/viewtopic.php?f=10&t=5251)

## Requirements

- Linux based system (This guide uses Debian)
- Custom build of Apache HTTP Server
- Custon build of OpenSSl with weak cipher (Apache Build Prereq)
- DNAS Emulation uses old protocol HTTP/1.0
- Install VIM for file edit or use nano if you prefer (Debian: `apt install vim`)

## Setting up weak ciphers

### Compiling OpenSSL

- Download OpenSSL `cd && wget https://www.openssl.org/source/openssl-1.0.2q.tar.gz`
- Extract to you home folder `tar xzvf openssl-1.0.2g.tar.gz`
- Access extracted folder `cd openssl-1.0.2g`
- Setup make using config `./config --prefix=/opt/openssl-1.0.2 --openssldir=/etc/ssl shared enable-weak-ssl-ciphers enable-ssl3 enable-ssl3-method enable-ssl2 -Wl,-rpath=/opt/openssl-1.0.2/lib`
- Execute make to build `make`
- Install on system `sudo make install` or just `make install`
- Test if have the needed cipher with `/opt/openssl-1.0.2/bin/openssl ciphers -V 'ALL' | grep 0x13`
    Expected result:
    ```text
    0xC0,0x13 - ECDHE-RSA-AES128-SHA    SSLv3 Kx=ECDH     Au=RSA  Enc=AES(128)  Mac=SHA1
    0x00,0x13 - EDH-DSS-DES-CBC3-SHA    SSLv3 Kx=DH       Au=DSS  Enc=3DES(168) Mac=SHA1
    ```

### Compiling Apache 2

- Need to setup the OpenSSL lib to be used with Apache, so edit the ld file with this command `vi /etc/ld.so.conf.d/$(uname -m)-linux-gnu.conf` or `nano /etc/ld.so.conf.d/$(uname -m)-linux-gnu.conf` and add the following lines at the end and after execute the `ldconfig`
    ```conf
    # custom OpenSSL
    /opt/openssl-1.0.2/lib
    ```
- Use apt to install some prereqs `apt update && apt install libpcre3 libpcre3-dev libexpat1 libexpat1-dev libxml2 libxml2-dev libxslt1-dev libxslt1`
- Download Apache 2 `cd && wget http://mirror.nbtelecom.com.br/apache//httpd/httpd-2.4.43.tar.gz`
- Download Apache Apr `wget http://ftp.unicamp.br/pub/apache//apr/apr-1.7.0.tar.gz`
- Download Apache apr-util `wget http://ftp.unicamp.br/pub/apache//apr/apr-util-1.6.1.tar.gz`
- Extract it `tar xzvf httpd-2.4.43.tar.gz`
- Access srclib folder with `cd ~/httpd-2.4.43/srclib/`
- Extract Apr with `tar xzvf ~/apr-1.7.0.tar.gz`
- Extract Apr-util with `tar xzvf ~/apr-util-1.6.1.tar.gz`
- Make symlink of extracted Apr with `ln -s apr-1.7.0 apr`
- Make symlink of extracted Apr-util with `ln -s apr-util-1.6.1 apr-util`
- Go back one folder with `cd ..`
- Execute make configure script with `./configure --prefix=/opt/apache --with-included-apr --with-ssl=/opt/openssl-1.0.2 --enable-ssl`
- Build with `make` and after just install with `make install`
- Execute `vi /opt/apache/bin/envvars` and add the line with OpenSSL lib line with the following:
    ```bash
    # Licensed to the Apache Software Foundation (ASF) under one or more
    # contributor license agreements.  See the NOTICE file distributed with
    # this work for additional information regarding copyright ownership.
    # The ASF licenses this file to You under the Apache License, Version 2.0
    # (the "License"); you may not use this file except in compliance with
    # the License.  You may obtain a copy of the License at
    #
    #     http://www.apache.org/licenses/LICENSE-2.0
    #
    # Unless required by applicable law or agreed to in writing, software
    # distributed under the License is distributed on an "AS IS" BASIS,
    # WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    # See the License for the specific language governing permissions and
    # limitations under the License.
    #
    #
    # envvars-std - default environment variables for apachectl
    #
    # This file is generated from envvars-std.in
    #
    if test "x$LD_LIBRARY_PATH" != "x" ; then
    LD_LIBRARY_PATH="/opt/apache/lib:$LD_LIBRARY_PATH"
    else
    LD_LIBRARY_PATH="/opt/apache/lib"
    fi
    # Add the below line between "fi" and "export LD_LIBRARY_PATH"
    LD_LIBRARY_PATH="/opt/openssl-1.0.2/lib:$LD_LIBRARY_PATH"
    export LD_LIBRARY_PATH
    #
    ```
- Create a systemd service for apache server with `vi /etc/systemd/system/apache.service` and add the following:
    ```ini
    [Unit]
    Description=Apache Server for Outbreak

    [Service]
    Type=forking
    EnvironmentFile=/opt/apache/bin/envvars
    PIDFile=/opt/apache/logs/httpd.pid
    ExecStart=/opt/apache/bin/apachectl -k start
    ExecReload=/opt/apache/bin/apachectl graceful
    ExecStop=/opt/apache/bin/apachectl -k stop
    KillSignal=SIGCONT
    PrivateTmp=true

    [Install]
    WantedBy=multi-user.target
    ```
- Enable the new created service with `systemctl enable apache.service`
- And start it with `systemctl start apache`
- Check in you browser with `http://127.0.0.1` (replace with your IP)
- If show `Its works!` it means that all fine for now

## Setting up DNS

- Install dnsmasq and dnsutils with `apt update && apt install dnsmasq dnsutils`
- Execute `mv /etc/dnsmasq.conf /etc/dnsmasq.conf.backup`
- Execeute `vi /etc/dnsmasq.conf` and add following:
    ```conf
    # Replace XXX.XXX.XXX.XXX with your machine IP Address
    address=/gate1.jp.dnas.playstation.org/XXX.XXX.XXX.XXX
    address=/www01.kddi-mmbb.jp/XXX.XXX.XXX.XXX
    server=8.8.8.8
    server=1.1.1.1
    interface=eth0
    listen-address=::1,127.0.0.1,XXX.XXX.XXX.XXX
    ```
- Restart dnsmasq with `systemctl restart dnsmasq`

## Cloning this Repo

- Clones this repo with `git clone https://github.com/adrianopteodoro/obsrv-pack.git`
- And enter on the repo folder with `cd obsrv-pack`

## Installing and setting up DNAS

- On repo folder execute this `mv DNAS/etc/dnas /etc/dnas` to move the DNAS etc folder
- Execute `chown -R 0:0 /etc/dnas` to set permissions
- Execute `mkdir /var/www` to create the www folder
- Execute `mv DNAS/www/dnas /var/www/dnas` to move www DNAS folder
- Execute `chown -R www-data:www-data /var/www/dnas` to set permissions
- Now we need to setup Apache to execute PHP scripts, install with `apt update && apt install php7.0-fpm`
- Edit apache config file with `vi /opt/apache/conf/httpd.conf`
- Search and uncomment the following lines (look for each one because its not in sequence):
    ```conf
    #LoadModule proxy_module modules/mod_proxy.so
    #LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so
    #LoadModule ssl_module modules/mod_ssl.so
    #LoadModule rewrite_module modules/mod_rewrite.so
    ```
- Replace this:
    ```conf
    <IfModule unixd_module>
    #
    # If you wish httpd to run as a different user or group, you must run
    # httpd as root initially and it will switch.
    #
    # User/Group: The name (or #number) of the user/group to run httpd as.
    # It is usually good practice to create a dedicated user and group for
    # running httpd, as with most system services.
    #
    User daemon
    Group daemon

    </IfModule>
    ```
    With this:
    ```conf
    <IfModule unixd_module>
    #
    # If you wish httpd to run as a different user or group, you must run
    # httpd as root initially and it will switch.
    #
    # User/Group: The name (or #number) of the user/group to run httpd as.
    # It is usually good practice to create a dedicated user and group for
    # running httpd, as with most system services.
    #
    User www-data
    Group www-data

    </IfModule>
    ```
- Add the following to the end on file:
    ```conf
    <IfModule ssl_module>
        # Replace XXX.XXX.XXX.XXX with your machine IP Address
        Listen XXX.XXX.XXX.XXX:443
        SSLEngine on
        # nail it to the securest cipher PS2 understands DHE-RSA-DES-CBC3-SHA
        # check this with openssl
        SSLCipherSuite DHE:!DSS:!AES:!SEED:!CAMELLIA!TLSv1.2
        SSLCertificateFile /etc/dnas/cert-jp.pem
        SSLCertificateKeyFile /etc/dnas/cert-jp-key.pem
        SSLCertificateChainFile /etc/dnas/ca-cert.pem
        ServerName gate1.jp.dnas.playstation.org
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/dnas
        <Directory />
            Options FollowSymLinks
            AllowOverride None
        </Directory>
        <Directory "/var/www/dnas">
            Options -Indexes
            Require all granted
        </Directory>
        # rewrite some URLs
        RewriteEngine on
        RewriteRule ^(/.*)/v2\.5_i-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/i-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/v2\.5_d-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/v2\.5_others$ $1/others.php [PT]
        RewriteRule ^(/.*)/others$ $1/others.php [PT]
        # send this to php-fpm socket (needs write access!)
        <FilesMatch "\.php$">
            SetHandler "proxy:unix:/var/run/php/php7.0-fpm.sock|fcgi://127.0.0.1"
        </FilesMatch>
        ErrorLog /opt/apache/logs/dnas_error.log
        # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
        LogLevel warn
        CustomLog /opt/apache/logs/dnas_access.log combined
        <FilesMatch "\.(cgi|shtml|phtml|php)$">
            SSLOptions +StdEnvVars
        </FilesMatch>
        <Directory /usr/lib/cgi-bin>
            SSLOptions +StdEnvVars
        </Directory>
        # we need to downgrade protocol for the DNAS browser
        BrowserMatch "open sesame asdfjkl" \
        nokeepalive ssl-unclean-shutdown \
        downgrade-1.0 force-response-1.0
    </IfModule>
    ```
- Restart apache with `systemctl restart apache`
- Test with `wget --no-check-certificate -O - https://gate1.jp.dnas.playstation.org/gai-gw/v2.5_i-connect` on other machine that its DNS setting is set to use from your server

## Installing Outbreak server

- Install prereqs with `apt update && apt install mariadb-server php7.0-mysql openjdk-8-jre-headless openjdk-8-jre openjdk-8-jdk-headless  openjdk-8-jdk`
- Execute `mysql -u root` an use teh following query to create user (change the password):
    ```sql
    CREATE USER 'bioserver'@'%' IDENTIFIED BY 'xxxSECUREPASSWORDxxx';
    exit
    ```

### Setup Outbreak File 1

- On repo folder enter into `FILE1` folder with `cd FILE1`
- Execute `mkdir /var/www/bhof1` to create `bhof1` folder
- Copy content from `www` with `cp www/* /var/www/bhof1`
- Set permissions with `chown -R www-data:www-data /var/www/bhof1`
- Create symlink for DNAS folder `ln -s /var/www/bhof1 /var/www/dnas/00000002`
- Edit with `vi /var/www/bhof1/db_cred.php` the `$pass` param with `bioserver` mysql user password
- Restore database with `mysql -u root < database/bioserver.sql`
- Edit `config.properties` and edit the following:
    ```config
    # Configuration for the server

    # IP address for gameserver (your machine IP)
    gs_ip=XXX.XXX.XXX.XXX

    # credentials for the database
    db_user=bioserver
    db_password=xxxSECUREPASSWORDxxx
    ```
- Execute `chmod +x observice.sh` to make script to be a executable
- Build server with `./observice.sh build`
- Start with `./observice.sh start`

### Setup Outbreak File 2

- On repo folder enter into `FILE2` folder with `cd FILE2`
- Execute `mkdir /var/www/bhof2` to create `bhof2` folder
- Copy content from `www` with `cp www/* /var/www/bhof2`
- Set permissions with `chown -R www-data:www-data /var/www/bhof2`
- Create symlink for DNAS folder `ln -s /var/www/bhof2 /var/www/dnas/00000010`
- Edit with `vi /var/www/bhof2/db_cred.php` the `$pass` param with `bioserver` mysql user password
- Restore database with `mysql -u root < database/bioserver.sql`
- Edit `config.properties` and edit the following:
    ```config
    # Configuration for the server

    # IP address for gameserver (your machine IP)
    gs_ip=XXX.XXX.XXX.XXX

    # credentials for the database
    db_user=bioserver
    db_password=xxxSECUREPASSWORDxxx
    ```
- Execute `chmod +x observice.sh` to make script to be a executable
- Build server with `./observice.sh build`
- Start with `./observice.sh start`