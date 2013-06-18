<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class AcceptMediaRange
{
    protected $type;
    protected $subtype;
    protected $priority;
    protected $params;

    const WILDCARD  = '*';
    const TYPESEP   = '/';
    const PARAMSEP  = ',';
    const RANGESEP  = ';';

    public function __construct($contentType, $priority, $params)
    {
        list($this->type, $this->subtype) = explode(self::TYPESEP, $contentType);
        $this->priority    = $priority;
        $this->params      = $params;
    }

    public function matches($contentType)
    {
        list($type, $subtype) = explode(self::TYPESEP, $contentType);

        return
            $this->type == self::WILDCARD || (     // True if */*, or
            $this->type == $type && (               // If match on type:
                $this->subtype == self::WILDCARD || // True if $type/*, or
                $this->subtype == $subtype));       // True if match on $subtype
                                                    // False Otherwise
    }

    public function getContentType()
    {
        return $this->type . self::TYPESEP . $this->subtype;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubType()
    {
        return $this->subtype;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getParams()
    {
        return $this->params;
    }
}

?>
