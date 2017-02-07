<?php

namespace phpws2;

/**
 *  Abstract class forming the basis of content objects
 * @todo See Database/Object
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Resource extends \Data
{

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
     *
     * @var array
     */
    protected $no_save;

    /**
     * Plugs in default Variable objects
     */
    public function __construct()
    {
        $this->id = new \phpws2\Variable\Integer(0, 'id');
        $this->id->setInputType('hidden');
        $this->no_save = array();
        $this->addHiddenVariable('table');
        $this->addHiddenVariable('no_save');
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

    public function doNotSave($var)
    {
        if (is_array($var)) {
            $this->no_save = array_merge($this->no_save, $var);
        } else {
            $this->no_save[] = $var;
        }
    }

    public function getSaveVars($return_null = false)
    {
        return parent::getVars($return_null, $this->no_save);
    }

    /**
     * Tries to load the current resource with the variables from a POST. Note
     * that if a variable in the POST is present but not in the Resource, an error
     * will be thrown. To avoid this, added the variable to the ignore array.
     * The ignore array can also be used if you do not want something from the
     * post to be saved.
     * If a variable in the Resource is ignored, the current resource value
     * stays.
     * @param \Request $request
     * @param  array $ignore Array of variables to ignore
     * @throws \Exception
     * @throws \phpws2\Exception\WrongType
     */
    public function loadPostByType(\Request $request, array $ignore = null)
    {
        $variable_names = $this->getVariableNames();
        if (empty($variable_names)) {
            throw new \Exception('Resource missing variables');
        }
        unset($variable_names[array_search('table', $variable_names)]);
        unset($variable_names[array_search('no_save', $variable_names)]);
        unset($variable_names[array_search('parent', $variable_names)]);

        if (!empty($ignore) && is_array($ignore)) {
            foreach ($ignore as $ignore_name) {
                unset($variable_names[array_search($ignore_name, $variable_names)]);
            }
        }

        foreach ($variable_names as $name) {
            $var = $this->$name;
            switch (1) {
                case is_subclass_of($var, 'phpws2\Variable\CanopyString') || is_a($var,
                        'phpws2\Variable\CanopyString'):
                    $result = $request->pullPostString($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Arr') || is_a($var,
                        'phpws2\Variable\Arr'):
                    $result = $request->pullPostString($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Bool') || is_a($var,
                        'phpws2\Variable\Bool'):
                    $result = $request->pullPostBoolean($name);
                    $success = $result !== null;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Integer') || is_a($var,
                        'phpws2\Variable\Integer'):
                    $result = $request->pullPostInteger($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Float') || is_a($var,
                        'phpws2\Variable\Float'):
                    $result = $request->pullPostFloat($name);
                    $success = $result !== false;
                    break;

                default:
                    throw new \Exception('Unknown Variable type');
            }

            if ($success) {
                $var->set($result);
            } else {
                throw new \phpws2\Exception\WrongType($name, $var);
            }
        }
    }

    /**
     * Duplicate of loadPostByType but using PUT method
     * @param \Request $request
     * @param array $ignore
     * @throws \Exception
     * @throws \phpws2\Exception\WrongType
     */
    public function loadPutByType(\Request $request, array $ignore = null)
    {
        $variable_names = $this->getVariableNames();
        if (empty($variable_names)) {
            throw new \Exception('Resource missing variables');
        }
        unset($variable_names[array_search('table', $variable_names)]);
        unset($variable_names[array_search('no_save', $variable_names)]);
        unset($variable_names[array_search('parent', $variable_names)]);

        if (!empty($ignore) && is_array($ignore)) {
            foreach ($ignore as $ignore_name) {
                unset($variable_names[array_search($ignore_name, $variable_names)]);
            }
        }

        foreach ($variable_names as $name) {
            $var = $this->$name;
            switch (1) {
                case is_subclass_of($var, 'phpws2\Variable\CanopyString') || is_a($var,
                        'phpws2\Variable\CanopyString'):
                    $result = $request->pullPutString($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Arr') || is_a($var,
                        'phpws2\Variable\Arr'):
                    $result = $request->pullPutString($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Bool') || is_a($var,
                        'phpws2\Variable\Bool'):
                    $result = $request->pullPutBoolean($name);
                    $success = $result !== null;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Integer') || is_a($var,
                        'phpws2\Variable\Integer'):
                    $result = $request->pullPutInteger($name);
                    $success = $result !== false;
                    break;

                case is_subclass_of($var, 'phpws2\Variable\Float') || is_a($var,
                        'phpws2\Variable\Float'):
                    $result = $request->pullPutFloat($name);
                    $success = $result !== false;
                    break;

                default:
                    throw new \Exception('Unknown Variable type');
            }

            if ($success) {
                $var->set($result);
            } else {
                throw new \phpws2\Exception\WrongType($name, $var);
            }
        }
    }

    public function setId($id)
    {
        $this->id->set($id);
    }

    public function getId()
    {
        return $this->id->get();
    }

    public function getVariableNames()
    {
        return array_keys(get_object_vars($this));
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
     * @param \phpws2\Database\Table $table
     * @return Array
     */
    public function getVariablesAsDatatypes(\phpws2\Database\Table $table)
    {
        $vars = $this->getVars();
        foreach ($vars as $variable) {
            if ($variable instanceof \phpws2\Variable) {
                if ($variable->getIsTableColumn()) {
                    $dts[$variable->getVarname()] = $variable->loadDatatype($table);
                }
            }
        }
        return empty($dts) ? null : $dts;
    }

    /**
     * Creates a new table based on the resource object. Returns table object
     * if successful
     * @param \phpws2\Database\DB $db
     * @return \phpws2\Database\Table
     */
    public function createTable(\phpws2\Database\DB $db)
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

    /**
     * Returns the values of the Variables as part of a resource.
     * @param boolean $return_null If true, return variables with NULL values
     * @param string|array Variables to ignore/not return
     * @return array
     */
    public function getVariablesAsValue($return_null = null, $hide = null)
    {
        $vars = $this->getVars($return_null, $hide);
        foreach ($vars as $v) {
            $values[$v->getVarname()] = $v->get();
        }
        return $values;
    }

}
