<?php

/**
 * Main Controller Class for the FancyPants module
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initCoreClass('Foundation.php');

PHPWS_Core::initModClass('fancypants', 'FancypantsCommand.php');

class FancypantsConfig extends ModuleConfig
{
    public function getName() { return 'fancypants'; }
    public function getVersion() { return '0.0.1'; }
}

class FancypantsController extends ModuleController
{
    private static $INSTANCE;

    public function main()
    {
        $cmd = $this->loadCommand($this->context->get('action'));
        $cmd->execute($this->context);
        Layout::add('testing');
    }

    public function miniAdmin(Key $key)
    {
        // TODO: Add items to MiniAdmin
    }

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            self::$INSTANCE = new FancypantsController(new FancypantsConfig());
        }

        return self::$INSTANCE;
    }
}

?>
