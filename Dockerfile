#!/usr/bin/env bash

FROM debian:11

# Update the system and install necessary dependencies
RUN apt-get update && \
    apt-get install -y php php-mysql php-gd php-xml php-mbstring && \
    apt-get install -y net-tools openssh-server openssh-client && \
    apt-get clean && \
    apt-get install -y php-pgsql && \
    rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www/html/module

# Copy the PHP project files into the container
COPY . .

# # Install Ansible, Powershell, InvokeAtomic
 RUN chmod +x /var/www/html/module/engine/install_ansible_server.sh && \
     sh /var/www/html/module/engine/install_ansible_server.sh 

# Expose port 80 for HTTP traffic
EXPOSE 80



# Copy available_tests.txt to /data when container starts
ENTRYPOINT ["/bin/bash", "-c", "cp /var/www/html/module/engine/available_tests.txt /data && php -S 0.0.0.0:80 -t /var/www/html/module"]
#CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/module"]