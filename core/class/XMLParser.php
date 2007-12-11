<?php
  /**
   * Copied from php.net
   * http://us2.php.net/manual/en/ref.xml.php
   *
   * Written  by  raphael at schwarzschmid dot de
   * Modified by  james at clickmedia dot com
   *              php dot notes at stoecklin dot net
   *              Felix dot Riesterer at gmx dot net
   *
   * The first part of the code pulls the data from the xml file.
   *
   * The second part orders the data into an associative array
   *
   * @author raphael at schwarzschmid dot de
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */ 


class XMLParser {
    var $filename = NULL;
    var $xml      = NULL;
    var $data     = NULL;
    var $error    = NULL;
    var $mapped   = NULL;
  
    function XMLParser($xml_file)
    {
        $this->filename = $xml_file;
        $this->xml = xml_parser_create();
        xml_set_object($this->xml, $this);
        xml_set_element_handler($this->xml, 'startHandler', 'endHandler');
        xml_set_character_data_handler($this->xml, 'dataHandler');
        $result = $this->parse($xml_file);
        if (PEAR::isError($result)) {
            $this->error = $result;
        }
    }
  
    function parse($xml_file)
    {
        $file_contents = @file($xml_file);

        if (empty($file_contents)) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'XMLParser:parse', $xml_file);
        }

        foreach ($file_contents as $data) {
            $parse = xml_parse($this->xml, $data);
            if (!$parse) {
                die(sprintf("XML error: %s at line %d",
                            xml_error_string(xml_get_error_code($this->xml)),
                            xml_get_current_line_number($this->xml)));
                xml_parser_free($this->xml);
            }
        }

        return true;
    }
  
    function startHandler($parser, $name, $attributes)
    {
        $data['name'] = $name;
        if ($attributes) { $data['attributes'] = $attributes; }
        $this->data[] = $data;
    }

    function dataHandler($parser, $data) {
        //Trims everything except for spaces
        if($data = trim($data, "\t\n\r\0\x0B")) {
            $test = str_replace(' ', '', $data);
            if (empty($test)) {
                $data = null;
            }
            $index = count($this->data) - 1;
            if(isset($this->data[$index]['content'])) {
                $this->data[$index]['content'] .= $data;
            } else {
                $this->data[$index]['content'] = $data;
            }
        }
    }

    function endHandler($parser, $name)
    {
        if (count($this->data) > 1) {
            $data = array_pop($this->data);
            $index = count($this->data) - 1;
            $this->data[$index]['child'][] = $data;
        }
    }

    function format()
    {
        return $this->subformat($this->data[0]);
    }


    function subformat($foo, $hold=null)
    {
        if (isset($foo['child'])) {
            $content = array();
            foreach ($foo['child'] as $bar) {
                $result = $this->subformat($bar);
                if (isset($bar['child'])) {
                    list($key,$value) = each($result);
                    if (count($value) > 1) {
                        $content[$key][] = $value;
                    } else {
                        $content[$key] = $value;
                    }
                } else {
                    $content = $content + $result;
                }
            }

            return array($foo['name']=>$content);
        } else {
            return array($foo['name']=>$foo['content']);
        }
    }

}
?>