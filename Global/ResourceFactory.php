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

        if (empty($table_name)) {
            throw new Exception(t('Table name not submitted nor found in the Resource object'));
        }

        if (empty($id)) {
            $id = self::pullId($resource);
        }

        if (empty($id)) {
            throw new Exception(t('Id not submitted nor found in the Resource object'));
        }

        $db = \Database::newDB();
        $table = $db->addTable($table_name);
        $table->addWhere('id', $id);
        $db->loadSelectStatement();
        $row = $db->fetchOneRow();
        if (empty($row)) {
            throw new \Exception(t('Row not found'));
        }
        $resource->setVars($row);
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
            $tbl->addWhere('id', $id);
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

    public function removeFromDB(\Resource $resource, $table_name = null)
    {
        if (empty($table_name)) {
            $table_name = $resource->getTable();
        }
        $db = \Database::newDB();
        $tbl = $db->addTable($table_name);
        $tbl->addWhere('id', $this->pullId($resource));
        $db->delete();
    }

}

?>
