%define name phpwebsite
%define install_dir /var/www/html/hub

Summary:   Housing Management System
Name:      %{name}
Version:   %{version}
Release:   %{release}
License:   GPL
Group:     Development/PHP
URL:       http://phpwebsite.appstate.edu
Source:   %{name}-%{version}-%{release}.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0
BuildArch: noarch

%description
The Housing Management System

%prep
%setup -n phpwebsite

%post
/sbin/service httpd restart

%install
mkdir -p $RPM_BUILD_ROOT%{install_dir}
cp -r * .htaccess $RPM_BUILD_ROOT%{install_dir}

%clean
rm -rf "$RPM_BUILD_ROOT%{install_dir}"

%files
%defattr(-,apache,apache)
%{install_dir}

%changelog
* Fri May  3 2012 Jeff Tickle <jtickle@tux.appstate.edu>
- Initial RPM for phpWebSite
