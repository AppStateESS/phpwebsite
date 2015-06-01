<?php

/**
 *  Abstract class forming the basis of content objects
 * @todo See Database/Object
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Resource extends Data {

    /**
     * Primary key of Resource
     * @var integer
     */
    protected $id;

    /**
     * Name of table associated with this resource
     * @var string
     */
    protected $table;

    /**
     * Plugs in default Variable objects
     */
    public function __construct()
    {
        $this->id = new \Variable\Integer(0, 'id');
        $this->id->setInputType('hidden');
        $this->addHiddenVariable('table');
    }

    /**
     * Returns name of table set to Resource
     * @return string
     * @throws \Exception Table variable was null
     */
    public function getTable()
    {
        if (empty($this->table)) {
            throw new \Exception(t('Table not set in Resource object "%s"',
                    get_class($this)));
        }
        return $this->table;
    }

    public function post(\Request $request)
    {
        $post_vars = $request->getRequestVars();
        $this->setVars($post_vars);
    }

    public function setId($id)
    {
        $this->id->set($id);
    }

    public function getId()
    {
        return $this->id->get();
    }

    /**
     * Returns true if this resource has been saved (i.e. has a positive id)
     * and false otherwise.
     * @return boolean
     */
    
    public function isSaved()
    {
        return !$this->id->isEmpty();
    }

    /*
     * @todo reapply when user permissions rewritten
      public function permitUser($permission_name, \User\User $user = null)
      {
      if (is_null($user)) {
      $user = \User\Current::get();
      }

      return \User\Permission::permit($permission_name, $this, $user);
      }
     */

    /*
     * @todo reapply when user permissions rewritten
      public function permitRole($permission_name, \User\Role $role)
      {
      return \User\Permission::permit($permission_name, $this, $role);
      }
     */

    /**
     * Saves the current resource object using the ResourceFactory class.
     * @return object
     */
    public function save()
    {
        return ResourceFactory::saveResource($this);
    }

    /**
     * Returns an associative array of Datatypes based on the Variable parameter
     * objects in the current object
     * @param \Database\Table $table
     * @return Array
     */
    public function getVariablesAsDatatypes(\Database\Table $table)
    {
        $vars = $this->getVars();
        foreach ($vars as $variable) {
            if ($variable instanceof \Variable) {
                $dts[$variable->getVarname()] = $variable->loadDatatype($table);
            }
        }
        return empty($dts) ? null : $dts;
    }

    /**
     * Creates a new table based on the resource object. Returns table object
     * if successful
     * @param \Database\DB $db
     * @return \Database\Table
     */
    public function createTable(\Database\DB $db)
    {
        $resource_table = $db->buildTable($this->getTable());
        $datatypes = $this->getVariablesAsDatatypes($resource_table);
        if (!$datatypes) {
            throw new \Exception('Resource did not return any datatypes');
        }
        $resource_table->addPrimaryIndexId();
        $resource_table->create();
        return $resource_table;
    }

}

?>