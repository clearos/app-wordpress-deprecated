
Name: app-wordpress
Epoch: 1
Version: 1.0.0
Release: 1%{dist}
Summary: WordPress
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-webapp
Requires: app-system-database-core >= 1:1.5.30

%description
Joomla is an award-winning content management system (CMS), which enables you to build web sites and powerful online applications.

%package core
Summary: WordPress - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-webapp-core
Requires: webapp-wordpress

%description core
Joomla is an award-winning content management system (CMS), which enables you to build web sites and powerful online applications.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/wordpress
cp -r * %{buildroot}/usr/clearos/apps/wordpress/

install -d -m 0755 %{buildroot}/var/clearos/wordpress
install -d -m 0755 %{buildroot}/var/clearos/wordpress/archive
install -d -m 0755 %{buildroot}/var/clearos/wordpress/backup
install -d -m 0755 %{buildroot}/var/clearos/wordpress/webroot
install -D -m 0644 packaging/webapp-wordpress-flexshare.conf %{buildroot}/etc/clearos/flexshare.d/webapp-wordpress.conf
install -D -m 0644 packaging/webapp-wordpress-httpd.conf %{buildroot}/etc/httpd/conf.d/webapp-wordpress.conf

%post
logger -p local6.notice -t installer 'app-wordpress - installing'

%post core
logger -p local6.notice -t installer 'app-wordpress-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/wordpress/deploy/install ] && /usr/clearos/apps/wordpress/deploy/install
fi

[ -x /usr/clearos/apps/wordpress/deploy/upgrade ] && /usr/clearos/apps/wordpress/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-wordpress - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-wordpress-core - uninstalling'
    [ -x /usr/clearos/apps/wordpress/deploy/uninstall ] && /usr/clearos/apps/wordpress/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/wordpress/controllers
/usr/clearos/apps/wordpress/htdocs
/usr/clearos/apps/wordpress/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/wordpress/packaging
%dir /usr/clearos/apps/wordpress
%dir /var/clearos/wordpress
%dir /var/clearos/wordpress/archive
%dir /var/clearos/wordpress/backup
%dir /var/clearos/wordpress/webroot
/usr/clearos/apps/wordpress/deploy
/usr/clearos/apps/wordpress/language
/usr/clearos/apps/wordpress/libraries
%config(noreplace) /etc/clearos/flexshare.d/webapp-wordpress.conf
%config(noreplace) /etc/httpd/conf.d/webapp-wordpress.conf
