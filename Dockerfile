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

# # Install Ansible, Powershell, InvokeAtomic
COPY ./engine ./engine/
RUN chmod +x /var/www/html/module/engine/install_ansible_server.sh && \
     sh /var/www/html/module/engine/install_ansible_server.sh

# Copy the PHP project files into the container
COPY . .

# Expose port 80 for HTTP traffic
EXPOSE 80

# Copy available_tests.txt to /data when container starts
ENTRYPOINT ["/bin/bash", "-c", "cp /var/www/html/module/engine/available_tests.txt /data && \
    mkdir -p /root/.ssh && \
    echo $WEB_PUBLIC_KEY > /root/.ssh/id_ed25519.pub && \
    chmod 644 /root/.ssh/id_ed25519.pub && \
    echo $WEB_PRIVATE_KEY | sed 's/%/\\n/g' > /root/.ssh/id_ed25519 && \
    chmod 600 /root/.ssh/id_ed25519 && \
    touch /data-known_hosts/known_hosts && \
    ln -fs /data-known_hosts/known_hosts /root/.ssh/known_hosts && \
    php -S 0.0.0.0:80 -t /var/www/html/module"]
#CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/module"]