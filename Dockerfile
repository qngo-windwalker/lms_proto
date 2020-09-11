# QN: from Drupal official Dockerfile: https://github.com/docker-library/drupal/blob/4e4d23ee86e88a0e6e88ec28837402ad1c8453fa/9.0/apache-buster/Dockerfile
# https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ec2-lamp-amazon-linux-2.html
FROM drupal:9.0.4

# EXPOSE 80
# EXPOSE 443
# EXPOSE 3306

# # QN: snippets from Gizra/drupal-lamp https://github.com/Gizra/drupal-lamp/blob/master/Dockerfile
# RUN apt-get install -y vim
RUN apt-get update -y && apt-get install -y \
	vim  \
	net-tools

# Setup SSH
# RUN apt-get update && apt-get install -y openssh-server
# RUN mkdir /var/run/sshd
# RUN echo 'root:asdf123' | chpasswd
# RUN sed -i 's/#*PermitRootLogin prohibit-password/PermitRootLogin yes/g' /etc/ssh/sshd_config

# SSH login fix. Otherwise user is kicked off after login
# RUN sed -i 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' /etc/pam.d/sshd

# ENV NOTVISIBLE "in users profile"
# RUN echo "export VISIBLE=now" >> /etc/profile

# EXPOSE 22
# CMD ["/usr/sbin/sshd", "-D"]

# EXPOSE 80
# EXPOSE 443
# EXPOSE 3306
# EXPOSE 11211
# EXPOSE 27017

# Drupal base image put app file in /opt/drupal
ADD . /opt/drupal

RUN set -ex; \
    \
    cp /opt/drupal/web/sites/default/default.settings.php /opt/drupal/web/sites/default/settings.php; \
    mkdir -p /opt/drupal/web/sites/default/files; \
    chmod 777 /opt/drupal/web/sites/default/files; 
    # chown wodby:wodby web/sites/default/settings.php web/sites/default/files; \
    # su-exec wodby composer require drush/drush; \
    # @todo install console, currently in conflict with D9 https://github.com/hechoendrupal/drupal-console/issues/4220
    #su-exec wodby composer require --dev drupal/console:@stable; \
