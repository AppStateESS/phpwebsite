<?php
/**
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class LikeboxView {

    private $settings;

    public function __construct(LikeboxSettings $settings)
    {
        $this->settings = $settings;
    }

    public function view()
    {
        $tpl = array();

        /*
        foreach($allSettings as $key=>$value){
            if($value == 1){
                $tpl[$key] = ""; // dummy template
            }
        }
        */
        
        if ($this->settings->get('enabled') === true) {
            return;
        }
        
        $tpl['fb_url'] = $this->settings->get('fb_url');
        $tpl['width']  = $this->settings->get('width');
        $tpl['height'] = $this->settings->get('height');
        
        if($this->settings->get('show_header') == 1) {
            $tpl['show_header'] = 'true';
        } else {
            $tpl['show_header'] = 'false';
        }
        
        if ($this->settings->get('show_border') == 1) {
            $tpl['show_border'] = 'true';
        } else {
            $tpl['show_border'] = 'false';
        }
        
        if ($this->settings->get('show_stream') == 1) {
            $tpl['show_stream'] = 'true';
        } else {
            $tpl['show_stream'] = 'false';
        }
        
        if ($this->settings->get('show_faces') == 1) {
            $tpl['show_faces'] = 'true';
        } else {
            $tpl['show_faces'] = 'false';
        }
        
        $content = PHPWS_Template::process($tpl, 'likebox', 'likebox.tpl');

        Layout::add($content, 'likebox', 'DEFAULT');
    }
}
?>
