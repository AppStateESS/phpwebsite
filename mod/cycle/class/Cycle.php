<?php

/*
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
PHPWS_Core::initModClass('cycle', 'Cycle_Slot.php');

class Cycle {

    private $content = null;
    private $error;

    public function get()
    {
        if (isset($_GET['aop'])) {
            $command = $_GET['aop'];
        } else {
            $command = 'main';
        }

        switch ($command) {
            case 'main':
                $this->main();
                break;

            case 'form':
                $slot = new Cycle_Slot($_GET['sid']);
                echo $this->slotForm($slot);
                exit();
                break;
        }

        Layout::add($this->content);
    }

    public function post()
    {
        if (isset($_POST['aop'])) {
            $command = $_POST['aop'];
        } else {
            $command = 'main';
        }

        switch ($command) {
            case 'post_slot':
                if (isset($_POST['delete'])) {
                    $this->deleteSlot();
                } else {
                    $this->postSlot();
                }
                break;
        }
    }

    private function deleteSlot()
    {
        $slot = new Cycle_Slot($_POST['slot_order']);
        unlink($slot->thumbnail_path);
        unlink($slot->background_path);
        $slot->delete();
        PHPWS_Core::goBack();
    }

    private function postSlot()
    {
        $slot = new Cycle_Slot($_POST['slot_order']);
        try {
            $slot->post();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->main($slot);
        }
        if (!empty($slot->errors)) {
            $this->error = implode('<br />', $slot->errors);
            $this->main($slot);
        } else {
            $result = $slot->save();
            if (PEAR::isError($result)) {
                $this->error = $result->getMessage();
                $this->main($slot);
            } else {
                PHPWS_Core::goBack();
            }
        }
    }

    private function main($default_slot=null)
    {
        $cycle_thumb_width = PHPWS_Settings::get('cycle', 'tn_width');
        $cycle_thumb_height = PHPWS_Settings::get('cycle', 'tn_height');

        $cycle_picture_width = PHPWS_Settings::get('cycle', 'bg_width');
        $cycle_picture_height = PHPWS_Settings::get('cycle', 'bg_height');
        Layout::addStyle('cycle');
        javascriptMod('cycle', 'admin');
        javascript('required_input');

        $result = $this->getSlots();
        for ($count = 1; $count < 5; $count++) {
            if (isset($result[$count])) {
                $slot = $result[$count];
                $thumb['thumb'] = sprintf('<li><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img width="%s" height="%s" src="%s" /></a></li>', $cycle_thumb_width, $cycle_thumb_height, $count, $cycle_thumb_width, $cycle_thumb_height, $slot->thumbnail_path);
            } else {
                $thumb['thumb'] = sprintf('<li style="text-align : center; border : 1px solid black"><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img class="new-thumb" src="%s" /></a></li>', $cycle_thumb_width, $cycle_thumb_height, $count, PHPWS_SOURCE_HTTP . 'mod/cycle/img/new_thumb.png');
            }
            $tpl['thumbnails'][] = $thumb;
        }

        if (empty($default_slot)) {
            $default_slot = new Cycle_Slot(1);
        }

        $tpl['form'] = $this->slotForm($default_slot);
        if ($this->error) {
            $tpl['error'] = $this->error;
        }
        $tpl['width'] = $cycle_thumb_width + 3;
        Layout::add(PHPWS_Template::process($tpl, 'cycle', 'admin.tpl'));
    }

    private function slotForm($slot)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'cycle');
        $form->addHidden('aop', 'post_slot');
        $form->addHidden('slot_order', $slot->slot_order);

        $form->addFile('background_image');
        $form->setLabel('background_image', 'Main image');
        if (empty($slot->background_path)) {
            $form->setRequired('background_image');
        }

        $form->addFile('thumbnail_image');
        $form->setLabel('thumbnail_image', 'Thumbnail');

        $form->addText('thumbnail_text', $slot->thumbnail_text);
        $form->setLabel('thumbnail_text', 'Thumbnail title');
        $form->setRequired('thumbnail_text');


        $form->addTextarea('feature_text', $slot->feature_text);
        $form->useEditor('feature_text');
        $form->setLabel('feature_text', 'Feature text');

        $form->addText('feature_x', $slot->feature_x);
        $form->setLabel('feature_x', 'X position');
        $form->setSize('feature_x', 3, 3);

        $form->addText('feature_y', $slot->feature_y);
        $form->setLabel('feature_y', 'Y position');
        $form->setSize('feature_y', 3, 3);

        $form->addText('f_width', $slot->f_width);
        $form->setLabel('f_width', 'Width');
        $form->setSize('f_width', 3, 3);

        $form->addText('f_height', $slot->f_height);
        $form->setLabel('f_height', 'Height');
        $form->setSize('f_height', 3, 3);

        $form->addText('destination_url', $slot->destination_url);
        $form->setLabel('destination_url', 'Destination url');
        //$form->setRequired('destination_url');
        $form->setSize('destination_url', 30);

        $form->addText('bg_width', PHPWS_Settings::get('cycle', 'bg_width'));
        $form->setLabel('bg_width', 'BG width');
        $form->setSize('bg_width', 4);

        $form->addText('bg_height', PHPWS_Settings::get('cycle', 'bg_height'));
        $form->setLabel('bg_height', 'BG height');
        $form->setSize('bg_height', 4);

        $form->addText('tn_width', PHPWS_Settings::get('cycle', 'tn_width'));
        $form->setLabel('tn_width', 'TN width');
        $form->setSize('tn_width', 4);

        $form->addText('tn_height', PHPWS_Settings::get('cycle', 'tn_height'));
        $form->setLabel('tn_height', 'TN height');
        $form->setSize('tn_height', 4);


        $form->addCheck('show_thumbnails', 1);
        $form->setLabel('show_thumbnails', 'Show thumbnails');
        $form->setMatch('show_thumbnails', PHPWS_Settings::get('cycle', 'show_thumbnails'));

        $result = PHPWS_Settings::get('cycle', 'show_thumbnails');

        if (!$slot->isNew()) {
            $form->addSubmit('add_new', 'Update slot ' . $slot->slot_order);
        } else {
            $form->addSubmit('add_new', 'Add new slot ' . $slot->slot_order);
        }

        $cycle_thumb_width = PHPWS_Settings::get('cycle', 'tn_width');
        $cycle_thumb_height = PHPWS_Settings::get('cycle', 'tn_height');

        $cycle_picture_width = PHPWS_Settings::get('cycle', 'bg_width');
        $cycle_picture_height = PHPWS_Settings::get('cycle', 'bg_height');


        $tpl = $form->getTemplate();
        $tpl['thumb_dimensions'] = 'Thumbnail dimensions : ' . $cycle_thumb_width . 'x' . $cycle_thumb_height;
        $tpl['pic_dimensions'] = 'Background dimensions : ' . $cycle_picture_width . 'x' . $cycle_picture_height;
        $tpl['SORT'] = $slot->slot_order;
        $tpl['thumbnail_path'] = $slot->thumbnail_path;
        return PHPWS_Template::process($tpl, 'cycle', 'slot_form.tpl');
    }

    public static function getSlots()
    {
        PHPWS_Core::initModClass('cycle', 'Cycle_Slot.php');

        $db = new PHPWS_DB('cycle_slots');
        $db->addOrder('slot_order');
        $db->setIndexBy('slot_order');
        return $db->getObjects('Cycle_Slot');
    }

    public static function Display()
    {
        $result = self::getSlots();
        if (empty($result)) {
            return null;
        }

        $cycle_thumb_width = PHPWS_Settings::get('cycle', 'tn_width');
        $cycle_thumb_height = PHPWS_Settings::get('cycle', 'tn_height');

        $cycle_picture_width = PHPWS_Settings::get('cycle', 'bg_width');
        $cycle_picture_height = PHPWS_Settings::get('cycle', 'bg_height');

        $bg_tile = PHPWS_SOURCE_HTTP . 'mod/cycle/img/75-percent.png';
        Layout::addStyle('cycle');
        $count = 0;
        foreach ($result as $slot) {
            $fullpic = $thumb = null;
            $fullpic['pic_width'] = $cycle_picture_width;
            $fullpic['pic_height'] = $cycle_picture_height;
            $fullpic['id'] = $slot->slot_order;
            $urls[] = <<<EOF
url[{$slot->slot_order}] = '{$slot->destination_url}';
EOF;
            $count++;
            $fullpic['image'] = $slot->background_path;

            if (PHPWS_Settings::get('cycle', 'show_thumbnails')) {
                $thumb['thumb'] = sprintf('<li><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img width="%s" height="%s" src="%s" /></a></li>',
                        $cycle_thumb_width, $cycle_thumb_height, $count, $cycle_thumb_width, $cycle_thumb_height, $slot->thumbnail_path);
                $tpl['thumbnails'][] = $thumb;
            }

            if (!empty($slot->feature_text)) {
                $fullpic['story'] = <<<EOF
<div class="cycle-story" style="top : {$slot->feature_y}px; left : {$slot->feature_x}px; width : {$slot->f_width}px; height : {$slot->f_height}px; background-image : url({$bg_tile})">{$slot->feature_text}</div>
EOF;
            }
            $tpl['fullpic'][] = $fullpic;
        }
        $js['urls'] = implode("\n", $urls);

        javascriptMod('cycle', 'cycle', $js);

        return PHPWS_Template::process($tpl, 'cycle', 'cycle_box.tpl');
    }

}

?>
