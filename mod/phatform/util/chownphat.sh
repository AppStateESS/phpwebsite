#!/bin/sh

#
# chmodphat.sh
#
# Author: Jeremy Agee <jagee@NOSPAM.tux.appstate.edu>
# Author: Adam Morton <adam@NOSPAM.tux.appstate.edu>
#
# Version: 0.1
#
# SEE: phatform/docs/INSTALL.txt for details
#

if [ $# -ne 1 ]; then
    echo 1>&2 Usage: chownphat.sh username.group
    exit 127
else
    chown $1 ../archive/
    chown $1 ../export/

    chmod 775 `find ../ -type d`
    chmod 664 `find ../ -type f`
    chmod 555 *.sh

    echo Phatform permissions are set!
fi

exit 0
