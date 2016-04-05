<?php

namespace Tag;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Link extends \Tag {

    protected $href;
    protected $target;
    protected $rel;

    public function __construct($label, $href = null)
    {
        parent::__construct('a', $label);
        $this->addIgnoreVariables('rel');
        if ($href) {
            $this->setHref($href);
        }
    }

    public function setHref($href)
    {
        if (preg_match('/^mailto:/i', $href)) {
            $this->href = new \Variable\Email($href);
        } elseif ($href == '#' || strtolower($href) == 'javascript:void(0)') {
            $this->voidHref();
            $this->href = 'javascript:void(0)';
        } else {
            $this->href = new \Variable\Url($href);
        }
    }

    public function getHref()
    {
        return (string) $this->href;
    }

    public function __toString()
    {
        if (empty($this->href) && !empty($this->events)) {
            $this->voidHref();
        }
        return parent::__toString();
    }

    public function voidHref()
    {
        $this->href = 'javascript:void(0)';
    }

    public function setTarget($target)
    {
        $target_lc = strtolower($target);
        switch ($target_lc) {
            case 'index':
            case 'blank':
                $this->target = '_blank';
                break;

            case '_blank':
            case '_parent':
            case '_self':
            case '_top':
                $this->target = $target_lc;
                break;

            default:
                $this->target = $target;
        }
    }

    public function addRel($rel)
    {
        static $rel_list = array('alternate', 'author', 'bookmark', 'help',
    'license', 'next', 'nofollow', 'noreferrer', 'prefetch', 'prev', 'search',
    'tag');
        $rel = strtolower($rel);
        if (!in_array($rel, $rel_list)) {
            throw new \Exception(t('Unknown rel type'));
        }
        $this->rel[] = $rel;
    }

    public function removeRel($rel)
    {
        $rel = strtolower($rel);
        $key = array_search($rel, $this->rel);
        if ($key !== false) {
            unset($this->rel[$key]);
        }
    }

    protected function buildTag()
    {
        $data = parent::buildTag();
        if (!empty($this->rel)) {
            $data[] = 'rel="' . implode(' ', $this->rel) . '"';
        }
        return $data;
    }

}

?>
