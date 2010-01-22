<?php

/**
 * Module Configuration.  To use the phpWebSite Framework, override this
 * in your module, make it a singleton, and provide it to Framework classes.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class ModuleConfig
{
    public abstract function getName();
    public abstract function getVersion();
}

?>
