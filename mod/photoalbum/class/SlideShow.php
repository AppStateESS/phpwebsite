<?php
/**
 * This class will show javascript slideshow for a photoalbum.
 *
 * @version $Id$
 * @author  Darren Greene <dg49379@NOSPAM.tux.appstate.edu>
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */
class SlideShow {

    function play($photos) {
        $largest_height = 0;
        $filenames = '';
        $names = '';
        $widths = '';
        $heights = '';
        $blurbs = '';
        $jsTags=array();

        for($i = 0; $i < count($photos); $i++) {
            $photo = new PHPWS_Photo($photos[$i]);

            $filenames .= "'./images/photoalbum/";
            $filenames .= $photo->_album . '/';
            $filenames .= str_replace("'", "\'", $photo->_name) . "', ";

            $label = str_replace('"', '\"', $photo->getLabel());
            $label = str_replace('&#39;', '\'', $label);
            $names  .= "'" . str_replace("'", "\'", $label) . "', ";

            $blurb = str_replace('"', '\"', $photo->_blurb);
            $blurb = str_replace('&#39;', '\'', $blurb);
            $blurbs .= "'" . str_replace("'", "\'", $blurb) . "', ";

            if ((($photo->_width >= PHOTOALBUM_MAX_WIDTH ||
            $photo->_height >= PHOTOALBUM_MAX_HEIGHT))) {

                $ratio = $photo->_width / $photo->_height;
                if ($ratio >= 1) {
                    $widths  .= PHOTOALBUM_MAX_WIDTH . ', ';
                    $heights .= PHOTOALBUM_MAX_WIDTH / $ratio . ', ';

                    if((PHOTOALBUM_MAX_HEIGHT / $ratio) > $largest_height)
                    $largest_height = PHOTOALBUM_MAX_HEIGHT / $ratio;
                } else {
                    $widths  .= PHOTOALBUM_MAX_HEIGHT * $ratio . ', ';
                    $heights .= PHOTOALBUM_MAX_HEIGHT . ', ';

                    if(PHOTOALBUM_MAX_HEIGHT > $largest_height)
                    $largest_height = PHOTOALBUM_MAX_HEIGHT;
                }
            } else {
                if(isset($photo->_width))
                $widths .= $photo->_width . ', ';
                else
                $widths .= '0, ';

                if(isset($photo->_height))
                $heights .= $photo->_height . ', ';
                else
                $heights .= '0, ';

                if($photo->_height > $largest_height)
                $largest_height = $photo->_height;
            }
        }

        $tags['LARGEST_IMHEIGHT'] = $largest_height + 100;
        $jsTags['IMAGES'] = substr($filenames, 0, -2);
        $jsTags['IMAGE_NAMES'] = substr($names, 0, -2);
        $jsTags['IMAGE_BLURBS'] = substr($blurbs, 0, -2);
        $jsTags['IMAGE_HEIGHTS'] = substr($heights, 0, -2);
        $jsTags['IMAGE_WIDTHS'] = substr($widths, 0, -2);
        $jsTags['PAUSE_TEXT'] = dgettext('photoalbum', 'Stop Show');
        $jsTags['PLAY_TEXT'] = dgettext('photoalbum', 'Start Show');
        $tags['QUIT_SLIDESHOW'] = sprintf('<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=view&amp;PHPWS_Album_id=%s">%s</a>',
        $_SESSION['PHPWS_AlbumManager']->album->_id,
        dgettext('photoalbum', 'Back to Album'));

        $speedOptions = array('2000'  =>dgettext('photoalbum', 'Two Seconds'),
                              '3000'  =>dgettext('photoalbum', 'Three Seconds'),
                              '5000'  =>dgettext('photoalbum', 'Five Seconds'),
                              '7000'  =>dgettext('photoalbum', 'Seven Seconds'),
                              '10000' =>dgettext('photoalbum', 'Ten Seconds'),
                              '30000' =>dgettext('photoalbum', 'Thirty Seconds'),
                              '60000' =>dgettext('photoalbum', 'One Minute'),
                              '120000'=>dgettext('photoalbum', 'Two Minutes'));

        $ieFilters = array('blendTrans(duration=1)' => dgettext('photoalbum', 'Fade'),
                           'revealTrans(duration=1, transition=0)' => dgettext('photoalbum', 'Box In'),
                           'revealTrans( transition=1, duration=1)' => dgettext('photoalbum', 'Box Out'),
                           'progid:DXImageTransform.Microsoft.Pixelate(duration=3)' => dgettext('photoalbum', 'Pixellate'),
                           'revealTrans(duration=1, transition=2)' => dgettext('photoalbum', 'Circle In'),
                           'revealTrans(duration=1, transition=3)' => dgettext('photoalbum', 'Circle Out'),
                           'revealTrans(duration=1, transition=10)' => dgettext('photoalbum', 'Horizontal Checkerboard'),
                           'revealTrans(duration=1, transition=11)' => dgettext('photoalbum', 'Vertical Checkerboard'),
                           'revealTrans(duration=1, transition=12)' => dgettext('photoalbum', 'Dissolve'),
                           'revealTrans(duration=1, transition=4)' => dgettext('photoalbum', 'Wipe Up'),
                           'progid:DXImageTransform.Microsoft.gradientWipe(duration=1)' => dgettext('photoalbum', 'Gradient Wipe'),
                           'progid:DXImageTransform.Microsoft.Spiral(duration=3, GridSizeX=205, GridSizeY=205)' => dgettext('photoalbum', 'Spiral'),
                           'progid:DXImageTransform.Microsoft.Wheel((duration=3, spokes=10)' => dgettext('photoalbum', 'Wheel'),
                           'progid:DXImageTransform.Microsoft.RadialWipe(duration=3)' => dgettext('photoalbum', 'Radial Wipe'),
                           'progid:DXImageTransform.Microsoft.Iris((duration=3)' => dgettext('photoalbum', 'Iris'),
                           'revealTrans(duration=3, transition=20)' => dgettext('photoalbum', 'Strips'),
                           'revealTrans(duration=3, transition=14)' => dgettext('photoalbum', 'Barn'));

        $form = new PHPWS_Form;
        $form->addSelect('adjustSpeedField', $speedOptions);
        $form->setMatch('adjustSpeedField', 5000);
        $form->setExtra('adjustSpeedField', 'onchange="adjustSpeed()"');

        $form->addSelect('ieFilterField', $ieFilters);
        $form->setMatch('ieFilterField', 'blendTrans(duration=1)');
        $form->setExtra('ieFilterField', 'onchange="changeFilter()"');

        $jsTags["IE_FILTER_FIELD"] = str_replace("\n", "", $form->get('ieFilterField'));

        $tags['STOP_TEXT'] = dgettext('photoalbum', 'Stop show');
        $tags['ADJUST_SPEED_TEXT_FIELD'] = $form->get('adjustSpeedField');

        $tags["ADJUST_SPEED_LABEL"] =
        dgettext('photoalbum', "Set Speed: &nbsp;");

        $jsTags["IE_FILTER_LABEL"] =
        dgettext('photoalbum', "Transition Effect: &nbsp;");

        $tags["LOOP_LABEL"] = dgettext('photoalbum', "Loop:  ");
        $jsTags["LOADING_NEXT_TXT"] = dgettext('photoalbum', "Loading Next Image...");
        $jsTags["LOADING_TXT"] = dgettext('photoalbum', "Loading Image...");

        $tags["LOW_TECH_LINK"] = dgettext('photoalbum', "Not working, try " .
                                   "the ");
        $jsTags["PRE_FILLER"] = "'" . PHPWS_SOURCE_HTTP . "mod/photoalbum/img/pre_filler.gif'";

        $linkText = dgettext('photoalbum', "low tech");

        $get_var["PHPWS_Album_op"] = "slideShow";
        $get_var["SS_mode"] = "nojsmode";

        $tags["LOW_TECH_LINK"] .= PHPWS_Text::moduleLink($linkText, 'photoalbum', $get_var);
        $tags["LOW_TECH_LINK"] .= dgettext('photoalbum', ' mode.');

        $jsContent = PHPWS_Template::processTemplate($jsTags, 'photoalbum', 'slideshow/js.tpl');

        Layout::addJSHeader($jsContent);

        if(count($photos) == 0) {
            $tags['DEFAULT_TITLE'] = dgettext('photoalbum', 'Album Contains No Photos');
        }
        else {
            $tags['IMAGE'] = ' ';
        }

        return PHPWS_Template::processTemplate($tags, 'photoalbum', 'slideshow/slideshow.tpl');
    }

}

?>