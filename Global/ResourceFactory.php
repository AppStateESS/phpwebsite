<?php

/**
 * Description of ResourceFactory
 *
 * @author matt
 */
class ResourceFactory {

    /**
     * Loads a Resource from the database according to table_name.
     * If table_name is not entered, Resource is checked for a table name
     * If resource is not found in table, the resource will just be as it was passed.
     * @param \Resource $resource
     * @param string $table_name
     * @throws \Exception
     * @return void
     */
    public static function loadByID(\Resource $resource, $id=null, $table_name = null)
    {
        if (empty($table_name)) {
            $table_name = $resource->getTable();
        }

        if (empty($id)) {
            $id = self::pullId($resource);
        }

        $db = \Database::newDB();
        $table = $db->addTable($table_name);
        $table->addFieldConditional('id', (int)$id);
        $result = $db->selectOneRow();
        if (!empty($result)) {
            $resource->setVars($result);
        }
    }

    /**
     * Saves a resource in the database.
     *
     * @param \Resource $resource
     * @param string $table_name
     */
    public static function saveResource(\Resource $resource, $table_name = null)
    {
        if (empty($table_name)) {
            $table_name = $resource->getTable();
        }

        $id = $resource->getId();
        $db = \Database::newDB();
        $tbl = $db->addTable($table_name);
        $vars = $resource->getVars();

        $tbl->addValueArray($vars);
        if (empty($id)) {
            $tbl->insert();
            $last_id = (int) $tbl->getLastId();
            $resource->setId($last_id);
        } else {
            $db->addConditional($tbl->getFieldConditional('id', $id));
            $db->update();
        }
        return $resource;
    }

    /**
     * Attempts to extract the required id from a resource, throwing an exception if id
     * is null.
     * @param \Resource $resource
     * @return integer
     * @throws \Exception
     */
    private static function pullId(\Resource $resource)
    {
        $id = $resource->getId();
        if (empty($id)) {
            throw new \Exception(t('Id not set in Resource "%s"', get_class($resource)));
        }
        return $id;
    }

    public static function deleteResource(\Resource $resource, $table_name = null)
    {
        if (empty($table_name)) {
            $table_name = $resource->getTable();
        }
        $db = \Database::newDB();
        $tbl = $db->addTable($table_name);
        $db->addConditional($tbl->getFieldConditional('id', self::pullId($resource) ));
        $db->delete();
    }

}

?>
