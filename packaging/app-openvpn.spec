
Name: app-openvpn
Epoch: 1
Version: 2.1.0
Release: 1%{dist}
Summary: OpenVPN
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-accounts
Requires: app-certificate-manager
Requires: app-groups
Requires: app-users
Requires: app-network
Requires: app-user-certificates

%description
The OpenVPN app provides secure remote access to this system and your local network.

%package core
Summary: OpenVPN - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-certificate-manager-core
Requires: app-events-core
Requires: app-network-core >= 1:1.6.0
Requires: app-openvpn-plugin-core
Requires: openvpn >= 2.3.2

%description core
The OpenVPN app provides secure remote access to this system and your local network.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/openvpn
cp -r * %{buildroot}/usr/clearos/apps/openvpn/

install -d -m 0755 %{buildroot}/etc/clearos/openvpn.d
install -d -m 0755 %{buildroot}/etc/openvpn/ssl
install -d -m 0755 %{buildroot}/var/clearos/openvpn
install -d -m 0755 %{buildroot}/var/clearos/openvpn/backup
install -d -m 0755 %{buildroot}/var/lib/openvpn
install -D -m 0644 packaging/authorize %{buildroot}/etc/clearos/openvpn.d/authorize
install -D -m 0644 packaging/clients-tcp.conf %{buildroot}/etc/openvpn/clients-tcp.conf
install -D -m 0644 packaging/clients.conf %{buildroot}/etc/openvpn/clients.conf
install -D -m 0755 packaging/network-configuration-event %{buildroot}/var/clearos/events/network_configuration/openvpn
install -D -m 0755 packaging/network-peerdns-event %{buildroot}/var/clearos/events/network_peerdns/openvpn
install -D -m 0644 packaging/openvpn.conf %{buildroot}/etc/clearos/openvpn.conf
install -D -m 0644 packaging/openvpn.php %{buildroot}/var/clearos/base/daemon/openvpn.php
install -D -m 0755 packaging/samba-configuration-event %{buildroot}/var/clearos/events/samba_configuration/openvpn

%post
logger -p local6.notice -t installer 'app-openvpn - installing'

%post core
logger -p local6.notice -t installer 'app-openvpn-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/openvpn/deploy/install ] && /usr/clearos/apps/openvpn/deploy/install
fi

[ -x /usr/clearos/apps/openvpn/deploy/upgrade ] && /usr/clearos/apps/openvpn/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-openvpn - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-openvpn-core - uninstalling'
    [ -x /usr/clearos/apps/openvpn/deploy/uninstall ] && /usr/clearos/apps/openvpn/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/openvpn/controllers
/usr/clearos/apps/openvpn/htdocs
/usr/clearos/apps/openvpn/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/openvpn/packaging
%dir /usr/clearos/apps/openvpn
%dir /etc/clearos/openvpn.d
%dir /etc/openvpn/ssl
%dir /var/clearos/openvpn
%dir /var/clearos/openvpn/backup
%dir /var/lib/openvpn
/usr/clearos/apps/openvpn/deploy
/usr/clearos/apps/openvpn/language
/usr/clearos/apps/openvpn/libraries
%config(noreplace) /etc/clearos/openvpn.d/authorize
%config(noreplace) /etc/openvpn/clients-tcp.conf
%config(noreplace) /etc/openvpn/clients.conf
/var/clearos/events/network_configuration/openvpn
/var/clearos/events/network_peerdns/openvpn
%config(noreplace) /etc/clearos/openvpn.conf
/var/clearos/base/daemon/openvpn.php
/var/clearos/events/samba_configuration/openvpn
