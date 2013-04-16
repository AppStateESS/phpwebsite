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
        $this->addHiddenVariable('table');
    }

    public function getTable()
    {
        if (empty($this->table)) {
            throw new \Exception(t('Table not set in Resource object "%s"',
                    get_class($this)));
        }
        return $this->table;
    }

    /**
     * Receives the result of a form post.
     * @return object Response object
     */
    public function post()
    {
        $response = Response::singleton();
        $vars = $this->getVars();
        foreach ($vars as $var) {
            if ($var instanceof Variable) {
                try {
                    $var->post();
                } catch (Error $e) {
                    $response->addProblem($var->getVarName(), $e->getMessage());
                    $response->setStatus('failure');
                }
            }
        }
        return $response;
    }

    public function setId($id)
    {
        $this->id->set($id);
    }

    public function getId()
    {
        return $this->id->get();
    }

    public function isSaved()
    {
        return !$this->id->isEmpty();
    }

    public function permitUser($permission_name, \User\User $user = null)
    {
        if (is_null($user)) {
            $user = \User\Current::get();
        }

        return \User\Permission::permit($permission_name, $this, $user);
    }

    public function permitRole($permission_name, \User\Role $role)
    {
        return \User\Permission::permit($permission_name, $this, $role);
    }

    /**
     * Saves the current resource object using the ResourceFactory class.
     * @return object
     */
    public function save()
    {
        return ResourceFactory::saveResource($this);
    }

}

?>