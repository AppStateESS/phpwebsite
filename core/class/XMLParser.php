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
    public $filename     = null;
    public $xml          = null;
    public $data         = null;
    public $error        = null;
    public $mapped       = null;
    /**
     * If content_only is true, attribute values will be ignored and only
     * the 'content' tags will be paired to the tag name.
     * If false, the tag name will contain an array with the content and
     * attributes.
     * @var boolean
     */
    public $content_only = false;

    public function __construct($xml_file, $die_on_error=true)
    {
        $this->filename = $xml_file;
        $this->xml = xml_parser_create();
        xml_parser_set_option($this->xml, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_set_object($this->xml, $this);
        xml_set_element_handler($this->xml, 'startHandler', 'endHandler');
        xml_set_character_data_handler($this->xml, 'dataHandler');

        $result = $this->parse($xml_file, $die_on_error);

        if (PEAR::isError($result)) {
            $this->error = $result;
        }
    }

    public function parse($xml_file, $die_on_error=true)
    {
        $file_contents = @file($xml_file);

        if (empty($file_contents)) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'XMLParser:parse', $xml_file);
        }

        foreach ($file_contents as $data) {
            $parse = xml_parse($this->xml, $data);

            if (!$parse) {
                if ($die_on_error) {
                    die(sprintf("XML error: %s at line %d",
                                xml_error_string(xml_get_error_code($this->xml)),
                                xml_get_current_line_number($this->xml)));
                    xml_parser_free($this->xml);
                } else {
                    return PHPWS_Error::get(PHPWS_WRONG_TYPE, 'core', 'XMLParset:parse', $xml_file);
                }
            }
        }

        return true;
    }

    public function startHandler($parser, $name, $attributes)
    {
        $data['name'] = $name;
        if ($attributes) { $data['attributes'] = $attributes; }
        $this->data[] = $data;
    }

    /**
     * Sets the value of content_only. See variable description.
     */
    public function setContentOnly($only=true)
    {
        $this->content_only = (bool)$only;
    }

    public function dataHandler($parser, $data) {
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

    public function endHandler($parser, $name)
    {
        if (count($this->data) > 1) {
            $data = array_pop($this->data);
            $index = count($this->data) - 1;
            $this->data[$index]['child'][] = $data;
        }
    }

    public function format()
    {
        return $this->subformat($this->data[0]);
    }


    public function subformat($foo, $hold=null)
    {
        if (isset($foo['child'])) {
            $content = array();
            $used_keys = array();
            foreach ($foo['child'] as $bar) {
                $result = $this->subformat($bar);

                if (!is_array($result)) {
                    continue;
                }

                foreach ($result as $key=>$value);

                if (isset($bar['child'])) {
                    if (count($value) > 1) {
                        $content[$key][] = $value;
                    } else {
                        $content[$key] = $value;
                    }
                } else {
                    if (isset($content[$key])) {
                        $temp = $content[$key];
                        $content[$key] = array();
                        if (is_array($temp)) {
                            foreach ($temp as $tmp_key=>$tmp_value);
                            if (isset($value[$tmp_key])) {
                                $content[$key][] = $temp;
                                $content[$key][] = $value;
                            } else {
                                $temp[] = $value;
                                $content[$key] = $temp;
                            }
                        } else {
                            $content[$key][] = $temp;
                            $content[$key][] = $value;
                        }
                    } else {
                        $content = array_merge($content, $result);
                    }
                }
                $used_keys[] = $key;
            }

            return array($foo['name']=>$content);
        } elseif ($this->content_only) {
            return array($foo['name']=>$foo['content']);
        } else {
            if (isset($foo['attributes'])) {
                $row['ATTRIBUTES'] = $foo['attributes'];
            }

            if (isset($foo['content'])) {
                $row['CONTENT'] = $foo['content'];
            }

            if (isset($row)) {
                return array($foo['name']=>$row);
            }
        }
    }

}
?>