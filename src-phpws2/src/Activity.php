<?php

namespace phpws2;


/**
 * Logs activity occuring in system
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
class Activity extends \phpws2\Resource {

    protected $id;
    protected $class_name;
    protected $resource_id;
    protected $action;
    protected $datestamp;
    protected $user_id;
    protected $ip_address;
    protected $table = 'Activity';

    public function __construct()
    {
        $this->id = new Variable\IntegerVar(0, 'id');
        $this->class_name = new Variable\Attribute(null, 'class_name');
        $this->resource_id = new Variable\IntegerVar(null, 'resource_id');
        $this->action = new Variable\StringVar(null, 'action');
        $this->datestamp = new Variable\Datetime(null, 'datestamp');
        $this->user_id = new Variable\IntegerVar(null, 'user_id');
        $this->ip_address = new Variable\Ip(null, 'ip_address');
        $this->datestamp->stamp();
        parent::__construct();
    }

    /**
     * Stamps the current datestamp and ip_address.
     */
    public static function stampResource(Resource $resource, $action)
    {
        $activity = new Activity;
        $activity->class_name = $resource->getNamespace();
        $activity->resource_id = $resource->getId();
        $activity->action = $action;

        if (User\Current::isLoggedIn()) {
            $activity->user_id = \User\Current::getUserId();
        }
        try {
            $activity->ip_address = \Canopy\Server::getUserIp();
        } catch (Exception $e) {
            $activity->ip_address = '0.0.0.0';
            \phpws2\Error::log($e);
        }
        \phpws2\ResourceFactory::saveResource($activity);
    }

}
