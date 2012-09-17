#!/bin/bash

TARSUFFIX=".tar.gz"
DESTDIR="dist"
TAR="tar -cz"

shopt -s dotglob

if [ $(basename $(pwd)) = 'util' ]; then
    cd ..
fi

if [ ! -d mod ]; then
    echo "This does not appear to be a full phpWebSite checkout because"
    echo "there is no mod/ directory."
    echo
    echo "Please run this script from the root of your phpWebSite"
    echo "checkout, like this:"
    echo " $ ./util/$0"
    exit 1
fi

if [ ! -d "$DESTDIR" ]; then
    echo "Creating $DESTDIR"
    mkdir -p $DESTDIR
fi

function boost_version {
    if [ ! -f "$1" ]; then
        echo "$1 is not a file."
        return 1
    fi

    CONF="$1"

    cat "$CONF" | egrep -i '\$version *=' | sed -e 's/^\$version *= *'"'"'//' -e "s/';$//"
}

PHPWS_VERSION=`boost_version core/conf/version.php`
NAME="phpwebsite-$PHPWS_VERSION"

echo "Building $DESTDIR/$NAME$TARSUFFIX"

$TAR --transform="s,^,$NAME/,S" -f "$DESTDIR/$NAME$TARSUFFIX" --exclude=".svn" --exclude="$DESTDIR" *

CORE_VERSION=`boost_version core/boost/boost.php`
NAME="base-$CORE_VERSION"

echo "Building $DESTDIR/$NAME$TARSUFFIX"

$TAR --transform="s,^,$NAME/,S" -f "$DESTDIR/$NAME$TARSUFFIX" --exclude=".svn" --exclude="$DESTDIR" --exclude="mod/**" *

for MODULE in $(ls mod/); do
    MOD_VERSION=`boost_version mod/$MODULE/boost/boost.php`
    echo "Building $DESTDIR/$MODULE-$MOD_VERSION$TARSUFFIX"
    $TAR -f "$DESTDIR/$MODULE-$MOD_VERSION$TARSUFFIX" --exclude=".svn" "mod/$MODULE/"
done
