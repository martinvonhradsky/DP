FROM centos:centos7

# Install necessary packages
RUN yum -y update && \
    yum -y install openssh-server sudo python3 && \
    yum clean all

# User:     test
# Password: password
RUN useradd -m test && \
    echo 'test:password' | chpasswd
    
# Add test user to sudoers
RUN echo '%test ALL=(ALL) ALL' >> /etc/sudoers

# Patched systemctl for docker, see: https://github.com/gdraheim/docker-systemctl-replacement
COPY systemctl.py /usr/bin/systemctl

# Copy SSH host keys
COPY ssh_host_ed25519_key ssh_host_ed25519_key.pub \
     ssh_host_ecdsa_key ssh_host_ecdsa_key.pub \
     ssh_host_rsa_key ssh_host_rsa_key.pub /etc/ssh/
RUN chmod 600 /etc/ssh/ssh_host_ed25519_key /etc/ssh/ssh_host_ecdsa_key /etc/ssh/ssh_host_rsa_key && \
    chmod 644 /etc/ssh/ssh_host_ed25519_key.pub /etc/ssh/ssh_host_ecdsa_key.pub /etc/ssh/ssh_host_rsa_key.pub

# Start SSH service
RUN systemctl restart sshd

CMD ["/usr/bin/systemctl"]