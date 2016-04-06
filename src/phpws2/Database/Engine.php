<?php

namespace phpws2\Database;

/**
 * Description of Engine
 *
 * @author matt
 */
abstract class Engine {

    protected $db;

    abstract public function addPrimaryIndexId(\phpws2\Database\Table $table);
    abstract public function getDBType();
    abstract public function getDelimiter();

    public function __construct(DB $db)
    {
        $this->db = $db;
    }
}
