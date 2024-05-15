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

COPY . /var/www/html/module

# Install Ansible, Powershell, InvokeAtomic
RUN chmod +x /var/www/html/module/engine/install_ansible_server.sh && \
     sh /var/www/html/module/engine/install_ansible_server.sh

ENTRYPOINT ["/var/www/html/module/app/entrypoint.sh"]
