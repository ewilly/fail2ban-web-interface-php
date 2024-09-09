# Fail2Ban Web Interface PHP (f2bwiphp)

## Overview

Monitoring fail2ban and manually ban / release IP's

### Documentation

- Get your fail2ban and jails running, if not already done
- Unpack archive and put contents to your webspace
- Protect this script, use at least .htaccess auth or ip allow/deny rules
- Allow use of `exec()` php function for this script if restricted
- Run this script to check if socket access is ok (you probably will need to set the socket r/w for your webserver user)
- Run the script with your browser

### Credits

Alexander Mirvis (https://github.com/LynxGeekNYC/fail2ban-web-interface)
