<?php

/* Default limit to use */
define("PHPWS_PAGER_LIMIT", 10);

/* Default for the section break point */
define("PHPWS_PAGER_BREAK", 20);

/* Default for returning an array */
define("PHPWS_PAGER_ARRAY", FALSE);

/* Default padding when breaking up sections */
define("PHPWS_PAGER_PAD", 3);

/**
 * The PHPWS_Pager class handles paging of data arrays.
 *
 * This class was implemented to eventually replace the function in Array.php
 * called paginateDataArray.  To many parameters where being added so it needed
 * its own class.
 *
 * For an example see the file pager_example.php in the docs/developer directory
 * Just put the file in your core/ directory and execute it directly
 *
 * @version $Id$
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 */
class PHPWS_Pager {

    /**
     * The data for PHPWS_Pager to paginate
     *
     * @var    array
     * @access private
     */
    var $_data = array();

    /**
     * The link back to the function listing the data
     *
     * @var    string
     * @access private
     */
    var $_linkBack = NULL;

    /**
     * The limits that can be placed on the list of items
     *
     * @var    integer
     * @access private
     */
    var $_limits = array(5, 10, 25, 50);

    /**
     * The decoration to use on the current section
     *
     * @var    array
     * @access private
     */
    var $_decoration = array("<b>[ ", " ]</b>");

    /**
     * The style class to use for the links that are generated
     *
     * @var    string
     * @access private
     */
    var $_class = NULL;

    /**
     * The number of sections there must be to break up the section links\
     *
     * @var    integer
     * @access private
     */
    var $_break = PHPWS_PAGER_BREAK;

    /**
     * Flag whether or not to return an array or a string of content
     *
     * @var    boolean
     * @access private
     */
    var $_array = PHPWS_PAGER_ARRAY;

    /**
     * The current total number of rows
     *
     * @var    integer
     * @access private
     */
    var $_numrows = NULL;

    /**
     * The current number of items being displayed
     *
     * @var    integer
     * @access private
     */
    var $_itemCount = 0;

    var $_anchor = NULL;

    /**
     * Public variable list
     */
    var $limit = PHPWS_PAGER_LIMIT;
    var $start = 0;
    var $section = 1;
    var $returnData = NULL;

    function setData($data) {
        if(is_array($data)) {
            if($this->_data != $data) {
                $this->start = 0;
                $this->section = 1;
            }
            $this->_data = $data;
        } else {
            return FALSE;
        }
    }

    function setLinkBack($linkBack) {
        if(is_string($linkBack)) {
            $this->_linkBack = $linkBack;
        } else {
            return FALSE;
        }
    }

    function setLimits($limits) {
        if(is_array($limits)) {
            $this->_limits = $limits;
        } else {
            return FALSE;
        }
    }

    function setDecoration($decoration) {
        if(is_array($decoration)) {
            $this->_decoration = $decoration;
        } else {
            return FALSE;
        }
    }

    function setClass($class) {
        if(is_string($class)) {
            $this->_class = $class;
        } else {
            return FALSE;
        }
    }

    function setBreak($break) {
        if(is_integer($break)) {
            $this->_break = $break;
        } else {
            return FALSE;
        }
    }

    function makeArray($flag) {
        if(is_bool($flag)) {
            $this->_array = $flag;
        } else {
            return FALSE;
        }
    }

    function setAnchor($anchor) {
        $this->_anchor = $anchor;
    }

    function pageData($catchRequest=TRUE) {
        unset($this->returnData);
        if($catchRequest) {
            if(isset($_REQUEST['PAGER_limit'])) {
                $this->limit = $_REQUEST['PAGER_limit'];
            }

            if(isset($_REQUEST['PAGER_start'])) {
                $this->start = $_REQUEST['PAGER_start'];
            }

            if(isset($_REQUEST['PAGER_section'])) {
                $this->section = $_REQUEST['PAGER_section'];
            }
        }

        reset($this->_data);
        if(is_array($this->_data)){
            $this->_numrows = sizeof($this->_data);
            $this->_itemCount = 0;
            $dataKeys = array_keys($this->_data);
            $itemsString = "";
            $itemsArray = array();
            $pad = PHPWS_PAGER_PAD;

            reset($dataKeys);
            for($x = 0; $x < $this->start; $x++){
                next($dataKeys);
            }
            while((list($dataKey, $dataValue) = each($dataKeys)) && (($this->_itemCount < $this->limit) && (($this->start + $this->_itemCount) < $this->_numrows ))){
                if($this->_array) {
                    $itemsArray[] = $this->_data[$dataKeys[$dataKey]];
                } else {
                    $itemsString .= $this->_data[$dataKeys[$dataKey]] . "\n";
                }

                $this->_itemCount++;
            }

            if($this->_array) {
                $this->returnData = $itemsArray;
            } else {
                $this->returnData = $itemsString;
            }
        } else {
            exit ("The data array was not set.");
        }
    }

    function getNumRows() {
        return $this->_numrows;
    }

    function getData() {
        return $this->returnData;
    }

    function getBackLink($back = "&#60;&#60;") {
        if($this->_limits[0] > $this->_numrows) {
            return null;
        }

        if($this->start == 0){
            $backLink = $back . "\n";
        } else {
            $backLink = "<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->start - $this->limit) . "&amp;PAGER_section=" . ($this->section - 1) . $this->_anchor . "\"";
            if(!is_null($this->_class)) {
                $backLink .= " class=\"" . $this->_class . "\"";
            }
            $backLink .= ">" . $back . "</a>\n";
        }

        return $backLink;
    }

    function getForwardLink($forward = "&#62;&#62;") {
        if($this->_limits[0] > $this->_numrows) {
            return null;
        }

        if(($this->start + $this->limit) >= $this->_numrows){
            $forwardLink = $forward . "\n";
        } else {
            $forwardLink = "<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->start + $this->limit) . "&amp;PAGER_section=" . ($this->section + 1) . $this->_anchor . "\"";
            if(!is_null($this->_class)) {
                $forwardLink .= " class=\"" . $this->_class . "\"";
            }
            $forwardLink .= ">" . $forward . "</a>\n";
        }

        return $forwardLink;
    }

    function getSectionLinks() {
        if($this->_limits[0] > $this->_numrows) {
            return null;
        }

        $numSections = ceil($this->_numrows / $this->limit);
        $sectionLinks = NULL;
        $pad = PHPWS_PAGER_PAD;
        if($numSections <= $this->_break){
            for($x = 1; $x <= $numSections; $x++){
                if($x == $this->section){
                    $sectionLinks .= "&#160;" . $this->_decoration[0] . $x . $this->_decoration[1] . "&#160;\n";
                } else {
                    $sectionLinks .= "&#160;<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->limit * ($x - 1)) . "&amp;PAGER_section=" . $x . $this->_anchor . "\"";
                    if(!is_null($this->_class)) {
                        $sectionLinks .= " class=\"" . $this->_class . "\"";
                    }
                     
                    $sectionLinks .= ">" . $x . "</a>&#160;\n";
                }
            }
        } else if($numSections > $this->_break){
            for($x = 1; $x <= $numSections; $x++){
                if($x == $this->section){
                    $sectionLinks .= "&#160;" . $this->_decoration[0] . $x . $this->_decoration[1] . "&#160;\n";
                } else if($x == 1 || $x == 2){
                    $sectionLinks .= "&#160;<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->limit * ($x - 1)) . "&amp;PAGER_section=" . $x . $this->_anchor . "\"";
                    if(!is_null($this->_class)) {
                        $sectionLinks .= " class=\"" . $this->_class . "\"";
                    }
                    $sectionLinks .= ">" . $x . "</a>&#160;\n";
                } else if(($x == $numSections) || ($x == ($numSections - 1))){
                    $sectionLinks .= "&#160;<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->limit * ($x - 1)) . "&amp;PAGER_section=" . $x . $this->_anchor . "\"";
                    if(!is_null($this->_class)) {
                        $sectionLinks .= " class=\"" . $this->_class . "\"";
                    }
                    $sectionLinks .= ">" . $x . "</a>&#160;\n";
                } else if(($this->section == ($x - $pad)) || ($this->section == ($x + $pad))){
                    $sectionLinks .= "&#160;<b>. . .</b>&#160;";
                } else if(($this->section > ($x - $pad)) && ($this->section < ($x + $pad))){
                    $sectionLinks .= "&#160;<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->limit . "&amp;PAGER_start=" . ($this->limit * ($x - 1)) . "&amp;PAGER_section=" . $x . $this->_anchor . "\"";
                    if(!is_null($this->_class)) {
                        $sectionLinks .= " class=\"" . $this->_class . "\"";
                    }
                    $sectionLinks .= ">" . $x . "</a>&#160;\n";
                }
            }
        } else {
            $sectionLinks .= "&#160;&#160;\n";
        }

        return $sectionLinks;
    }

    function getSectionInfo() {
        if(($this->start + $this->limit) >= $this->_numrows){
            $sectionInfo = ($this->start + 1) . " - " . ($this->start + $this->_itemCount) . " of " . $this->_numrows . "\n";
        } else {
            $sectionInfo = ($this->start + 1) . " - " . ($this->start + $this->limit) . " of " . $this->_numrows . "\n";
        }

        return $sectionInfo;
    }

    function getLimitLinks($addPipes=FALSE) {
        $count = 0;
        $limitLinks = array();
        for($x = 0; $x < sizeof($this->_limits); $x++) {
            if(($this->_limits[$x] < $this->_numrows) || (isset($this->_limits[$x - 1]) && ($this->_limits[$x - 1] < $this->_numrows))) {
                if($this->_limits[$x] != $this->limit) {
                    $limitLinks[$count] = "<a href=\"" . $this->_linkBack . "&amp;PAGER_limit=" . $this->_limits[$x] . "&amp;PAGER_start=0&amp;PAGER_section=1" . $this->_anchor . "\"";
                    if(!is_null($this->_class)) {
                        $limitLinks[$count] .= " class=\"" . $this->_class . "\"";
                    }
                    $limitLinks[$count] .= ">" . $this->_limits[$x] . "</a>";
                } else {
                    $limitLinks[$count] = $this->_limits[$x];
                }
                $count ++;
            }
        }

        if(sizeof($limitLinks) > 0) {
            if($addPipes) {
                return implode("&#160;|&#160;", $limitLinks);
            } else {
                return implode("&#160;", $limitLinks);
            }
        }
    }

    function cleanUp() {
        unset($this->returnData);
    }
} // END CLASS PHPWS_Pager

?>