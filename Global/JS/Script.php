<?php

namespace JS;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Script {

    /**
     * Array of other scripts that are required prior to this script. They will
     * always be listed before this script.
     * @var array
     */
    private $dependencies;

    /**
     * Path to the script
     * @var string
     */
    private $path;

    /**
     * If true, the script is already compressed
     * @var boolean
     */
    private $compressed;

    /**
     * An array of css files that will be included after this script.
     * @var array
     */
    private $include_css;

    /**
     * md5 hash of address.
     * @var string
     */
    private $hash;

    public function setDependencies($dependencies)
    {
        if (is_array($dependencies)) {
            foreach ($dependencies as $d) {
                $this->dependencies[] = $d;
            }
        } else {
            $this->dependencies[] = $dependencies;
        }
    }

    public function setPath($path)
    {
        $this->path = $path;
        $this->buildHash();
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function setCompressed($compressed)
    {
        $this->compressed = (bool) $compressed;
    }

    public function setIncludeCSS($include)
    {
        if (is_array($include)) {
            foreach ($include as $d) {
                $this->include_css[] = $d;
            }
        } else {
            $this->include_css[] = $include;
        }
    }

    public function getIncludeCSS()
    {
        return $this->include_css;
    }

    public function getIncludeCSSTags()
    {
        if (empty($this->include_css)) {
            return null;
        }

        foreach ($this->include_css as $i) {
            $link = $this->prefixAddress($i);
            $stack[] = '<link rel="stylesheet" type="text/css" href="' . $link . '" />';
        }
        return implode("\n", $stack);
    }

    public function prefixAddress($url)
    {
        return PHPWS_SOURCE_HTTP . $url;
    }

    public function prefixDirectory($dir)
    {
        return PHPWS_SOURCE_DIR . $dir;
    }

    /**
     * Returns script path as a web address
     * @return string
     */
    public function getAddress()
    {
        return $this->prefixAddress($this->path);
    }

    /**
     * Returns script path as a file directory.
     * @return string
     */
    public function getDirectory()
    {
        return $this->prefixDirectory($this->path);
    }

    public function buildHash()
    {
        $this->hash = md5($this->path);
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Returns true if script file exists
     * @return boolean
     */
    public function isFile()
    {
        return is_file($this->getDirectory());
    }

    public function getAddressAsScriptTag()
    {
        return '<script type="text/javascript" src="' . $this->getAddress() . '"></script>';
    }

}

?>
