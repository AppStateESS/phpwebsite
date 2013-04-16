<?php

/**
 * Logs activity occuring in system
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
class Activity extends \Resource {

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
        $this->id = new Variable\Integer(0, 'id');
        $this->class_name = new Variable\Attribute(null, 'class_name');
        $this->resource_id = new Variable\Integer(null, 'resource_id');
        $this->action = new Variable\String(null, 'action');
        $this->datestamp = new Variable\Datetime(null, 'datestamp');
        $this->user_id = new Variable\Integer(null, 'user_id');
        $this->ip_address = new Variable\Ip(null, 'ip_address');
        $this->datestamp->stamp();
        parent::__construct();
    }

    /**
     * Stamps the current datestamp and ip_address.
     */
    public static function stampResource(\Resource $resource, $action)
    {
        $activity = new Activity;
        $activity->class_name = $resource->getNamespace();
        $activity->resource_id = $resource->getId();
        $activity->action = $action;

        if (\User\Current::isLoggedIn()) {
            $activity->user_id = \User\Current::getUserId();
        }
        try {
            $activity->ip_address = \Server::getUserIp();
        } catch (Exception $e) {
            $activity->ip_address = '0.0.0.0';
            \Error::log($e);
        }
        \ResourceFactory::saveResource($activity);
    }

}

?>
