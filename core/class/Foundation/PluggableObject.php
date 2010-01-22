<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class PluggableObject {
    public function plug(array $vars)
    {
        $reflect = new ReflectionObject($this);
        foreach($vars as $key => $val) {
            try {
                $prop = $reflect->getProperty($key);
                $prop->setValue($this,$val);
            } catch (ReflectionException $e) {
                $setter = "set$key";
                if($reflect->hasMethod($setter))
                    $this->$setter($val);
            }
        }
    }
}

?>
