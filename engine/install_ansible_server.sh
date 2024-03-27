#!/usr/bin/env bash

ansible --version

# Install ansible if not installed  (exit status != 0)
if [ $? -ne 0 ]; then
     apt-get update
     apt-get install -y software-properties-common
     apt-add-repository --yes --update ppa:ansible/ansible
     apt-get install -y ansible
     apt-get install -y sshpass
    ansible --version
    if [ $? -ne 0 ]; then
       echo "Unable to install Ansible"
       exit 1
    fi
fi

# Remove repository -> to enable apt-get update 
apt-add-repository --remove -y ppa:ansible/ansible


# Execute Ansible script to:
#    install powershell, InvokeAtomic
#    create configuration files for Ansible 
ansible-playbook /var/www/html/module/engine/pastebins/server_setup_noVM.yaml

