%define name phpwebsite
%define install_dir /var/www/phpwebsite

Summary:   phpWebSite Content Management System
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
The phpWebSite Content Management System

%prep
%setup -n phpwebsite

%post
/usr/bin/curl -L -k http://127.0.0.1/apc/clear

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
