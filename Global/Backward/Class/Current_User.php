<?php

/**
 * Description of Current_User
 *
 * @author matt
 */
class Current_User {

    public static function allow($module, $subpermission = null, $item_id = 0, $itemname = null, $unrestricted_only = false)
    {
        if (\User\Current::isSuperUser()) {
            return true;
        }
        $module_object = \ModuleManager::pullModule($module);

        if (empty($subpermission)) {
            $subpermission = 'general';
        }

        if ($item_id) {
            $resource = $module_object->pullResource($item_id, $class_name);
            \User\Current::permitResource($subpermission, $module_object, $resource);
        } else {
            \User\Current::permit($subpermission);
        }
    }

    public static function authorized($module, $subpermission = null, $item_id = 0, $itemname = null, $unrestricted_only = false)
    {
        // @todo remove
        return true;
    }

    public static function isUnrestricted($module)
    {
        // @todo remove
        return self::allow($module);
    }

    public static function isRestricted($module)
    {
        $module_object = \ModuleManager::pullModule($module);
        return \User\Current::permit('restricted', $module_object);
    }

    public static function isDeity()
    {
        return \User\Current::isSuperUser();
    }

    public static function getAuthKey()
    {
        return \User\Current::getAuthKey();
    }

    /**
     * Returned a link that would popup a window with permission settings for
     * the current page.
     *
     * @param type $key_id
     * @param type $label
     * @param type $mode
     */
    public static function popupPermission($key_id, $label = null, $mode = null)
    {
        return '<input type="button" name="permission" value="popupPermission needs work">';
    }

}

?>
