FROM alpine:3.19

# `shadow` package contains `useradd` that supports password as command line argument
RUN apk update && \
    apk add --no-cache openssh shadow

# User:     test
# Password: password
RUN useradd -m -p $(cryptpw password) test

# Host keys are needed for sshd to run
COPY ssh_host_rsa_key ssh_host_rsa_key.pub /etc/ssh/
RUN chmod 600 /etc/ssh/ssh_host_rsa_key && \
    chmod 644 /etc/ssh/ssh_host_rsa_key.pub

ENTRYPOINT ["/usr/sbin/sshd", "-D", "-e"]