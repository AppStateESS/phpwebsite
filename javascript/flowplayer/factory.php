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

// smallest dimension allowed for videos
define('FP_MIN_DIMENSION', 100);

class javascript_flowplayer extends Javascript {

    private $width = 640;

    private $height = 480;

    private $video_path = 'scooter.flv';

    private $id = 'wtf';

    private $sample = 'edward.jpg';

    protected $use_jquery = false;

    /**
     * An array of "common clip" options
     * @var array
     */
    private $clip = null;

    /**
     * "player" settings for the object
     * @var array
     */
    private $player = null;

    /**
     * Playlist options
     * This will be an array of clip arrays
     *
     * $playlist = array( array('url'=>'image.jpg', 'scaling'=>'orig'),
     *                    array('url'=>'movie_file.flv', 'autoplay'=>false)
     *                  );
     * @var array
     */
    private $playlist = null;

    private $resize_to_image = false;

    /**
     * Sets "clip" option for the flowplayer
     * @link http://flowplayer.org/documentation/configuration/clips.html
     * @param array $clip
     */
    public function setClipOptions(array $clip)
    {
        $this->clip = $clip;
    }

    /**
     * Sets "player" option for the flowplayer
     * @link http://flowplayer.org/documentation/configuration/player.html
     * @param array $clip
     */
    public function setPlayerOptions(array $player)
    {
        $this->player = $player;
    }

    public function setWidth($width)
    {
        if ($width < FP_MIN_DIMENSION) {
            return;
        }
        $this->width = (int)$width;
    }

    public function setHeight($height)
    {
        if ($height < FP_MIN_DIMENSION) {
            return;
        }
        $this->height = (int)$height;
    }

    public function setDimensions($width, $height)
    {
        $this->setWidth($width);
        $this->setHeight($height);
    }

    public function setSample($sample, $resize=false)
    {
        $this->sample = $sample;
        $this->resize_to_image = (bool)$resize;
    }

    public function loadDemo() {

    }

    public function prepare()
    {
        $this->addInclude('flowplayer.js');
        $this->setHeadScript('<link rel="stylesheet" type="text/css" href="javascript/flowplayer/style.css">');
        $this->loadBody();
    }

    private function getClip()
    {
        if (empty($this->clip)) {
            return '"' . $this->video_path . '"';
        } else {
            if (!isset($this->clip['url']) && empty($this->playlist)) {
                $this->clip['url'] = $this->video_path;
            }
            $clip = '{clip: {' . Javascript::displayParams($this->clip) . '} }';
            return $clip;
        }
    }

    private function loadBody()
    {
        $flowplayer = PHPWS_SOURCE_HTTP . 'javascript/flowplayer/flowplayer.swf';

        if ($this->sample) {
            $img_dim = getimagesize($this->sample);
            if ($this->resize_to_image) {
                $this->setDimensions($img_dim[0], $img_dim[1]);
            }
            $sample = <<<EOF
<img src="$this->sample" $img_dim[3] />
EOF;
        } else {
            $sample = null;
        }

        $clip = $this->getClip();

        $body = <<<EOF
        <div style="width : $this->width; height : $this->height" id="$this->id">$sample</div>
<script language="javascript">flowplayer("$this->id", "$flowplayer", $clip);</script>
EOF;
        $this->setBodyScript($body);
    }

}

?>