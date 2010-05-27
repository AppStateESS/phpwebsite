<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class javascript_flowplayer extends Javascript {

    private $width = 640;

    private $height = 480;

    private $video_path = 'scooter.flv';

    private $id = 'wtf';

    private $sample = 'edward.jpg';

    protected $use_jquery = false;

    private $player = null;


    public function __construct()
    {
        $this->player = new Flowplayer;
    }

    public function loadDemo() {

    }

    public function prepare()
    {
        $this->addInclude('flowplayer.js');
        $this->setHeadScript('<link rel="stylesheet" type="text/css" href="javascript/flowplayer/style.css">');
        $this->loadBody();
    }

    private function loadBody()
    {
        $flowplayer = PHPWS_SOURCE_HTTP . 'javascript/flowplayer/flowplayer.swf';
        $body = <<<EOF
<a href="$this->video_path" style="display:block;width:{$this->width}px;height:{$this->height}px;" id="$this->id"></a>
<script language="javascript">flowplayer("$this->id", "$flowplayer");</script>
EOF;
        $this->setBodyScript($body);
    }

}

class Flowplayer extends Data {
    private $clip = null;
    private $playlist = null;

    public function __construct() {
        $this->clip = new FP_Clip;
        $this->playlist = new FP_Playlist;
    }
}

class FP_Clip {
}

class FP_Playlist {

}

?>