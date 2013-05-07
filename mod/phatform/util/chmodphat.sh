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

if [ $# -ne 0 ]; then
    echo 1>&2 Usage: chmodphat.sh
    exit 127
else
    chmod 775 `find ../ -type d`
    chmod 664 `find ../ -type f`
    chmod 555 *.sh

    chmod 777 ../archive/
    chmod 777 ../export/

    echo Phatform permissions are set!
fi

exit 0
