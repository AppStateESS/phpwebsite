<?php

namespace Http;

/**
 * A php Object implementation of the HTTP Accept header, which indicates the 
 * Content-types that the client is capable of accepting.
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Accept
{
    /**
     * An array containing individual AcceptMediaRange objects in order of 
     * priority.
     *
     * @var AcceptMediaRange
     */
    protected $mediaRanges;

    /**
     * Creates a new Accept object from the value portion of an HTTP Accept 
     * header string.
     *
     * @param $acceptStr string RFC 2616 sec 14.1 Accept string.
     */
    public function __construct($acceptStr)
    {
        $this->mediaRanges = array();
        foreach(explode(',', $acceptStr) as $mediaRange) {
            $acceptParams = explode(';', $mediaRange);
            $contentType = array_shift($acceptParams);
            $priority = -1.0;
            $params = array();

            foreach($acceptParams as $param) {
                list($key, $val) = explode('=', $param);
                if($key == 'q') {
                    $priority = $val;
                }

                $params[$key] = $val;
            }

            if(!array_key_exists('q', $params)) {
                // Wildcard type gets lowest nonzero priority by definition
                if(substr($contentType, 0, 1) == '*') $priority = 0.0001;

                // Wildcard subtype gets second-lowest priority by definition
                else if(substr($contentType, -1) == '*') $priority = 0.001;

                // Everything else gets highest priority and stable order
                else $priority = 1;
            }

            $this->addMediaRange(new AcceptMediaRange($contentType, $priority, $params));
        }
    }

    /**
     * Inserts an AcceptMediaRange into the right place based on priority.
     *
     * @param $newMR AcceptMediaRange A media-range
     */
    public function addMediaRange(AcceptMediaRange $newMR)
    {
        $mr = array();

        $inserted = false;
        foreach($this->mediaRanges as $oldMR) {
            if(!$inserted && $oldMR->getPriority() < $newMR->getPriority()) {
                $mr[] = $newMR;
            }
            $mr[] = $oldMR;
        }

        if(!$inserted) $mr[] = $newMR;

        $this->mediaRanges = $mr;
    }

    /**
     * Gets an iterator over the array of AcceptMediaRanges. This should be used 
     * to determine which View object to use to render the client's view.
     *
     * @return ArrayIterator Iterator over AcceptMediaRanges
     */
    public function getIterator()
    {
        return new AcceptIterator($this->mediaRanges);
    }
}

?>
