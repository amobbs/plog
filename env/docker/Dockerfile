FROM ubuntu:12.04
MAINTAINER "4mation - Harlan" <harlan.wilton@4mation.com.au>

# Update system files and install required packages
RUN set -ex; \
	\
	apt-get update; \
	apt-get install -y \
	    apache2 \
        php5 \
        php5-cli \
        libapache2-mod-php5 \
        php5-gd \
        php5-ldap \
        php5-mysql \
        php-soap \
        php5-pgsql \
#        Mongo requirements
        php-pear php5-dev pkg-config make \
	    curl \
	    php5-curl \
	    git \
	    zip \
	    vim \
	    sudo \
	    wget \
	    nmap \
	; \
	rm -rf /var/lib/apt/lists/*;


RUN pecl install mongo
RUN echo "extension=mongo.so" >> /etc/php5/conf.d/mongo.ini


# turn on apache modules
RUN a2enmod rewrite expires headers

# Configure Apache & PHP
COPY conf/php.ini /usr/local/etc/php/
COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY conf/vhost.conf /etc/apache2/sites-enabled/000-default.conf

RUN echo "cd /home/preslog" >> /root/.bashrc

# setup our folder
RUN mkdir /home/preslog/
RUN mkdir /home/preslog/logs/

RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
RUN echo "deb [ arch=amd64 ] http://repo.mongodb.org/apt/ubuntu precise/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
RUN apt-get update
RUN apt-get install -y mongodb-org-shell

# Setup our web root
RUN chown -R www-data:www-data /home/preslog/

# This makes sure everyhting we did is within this folder
VOLUME /home/preslog/

# Check services are working
RUN service apache2 restart

# Expore our ports
EXPOSE 80 443

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]