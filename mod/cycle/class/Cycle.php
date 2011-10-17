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
    }

    private function postSlot()
    {
        $slot = new Cycle_Slot($_POST['slot_order']);
        try {
            $slot->post();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->main();
        }
        if (!empty($slot->errors)) {
            $this->error = implode('<br />', $slot->errors);
            $this->main();
        } else {
            $result = $slot->save();
            if (PEAR::isError($result)) {
                test($result);

                $this->error = $result->getMessage();
                $this->main();
            } else {
                PHPWS_Core::goBack();
            }
        }
    }

    private function main()
    {
        Layout::addStyle('cycle');
        javascriptMod('cycle', 'admin');
        javascript('required_input');

        $result = $this->getSlots();
        for ($count = 1; $count < 5; $count++) {
            if (isset($result[$count])) {
                $slot = $result[$count];
                $thumb['thumb'] = sprintf('<li><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img src="%s" /></a></li>', cycle_thumb_width, cycle_thumb_height, $count, $slot->thumbnail_path);
            } else {
                $thumb['thumb'] = sprintf('<li style="text-align : center; border : 1px solid black"><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img style="margin-top : 12px" src="%s" /></a></li>', cycle_thumb_width, cycle_thumb_height, $count, PHPWS_SOURCE_HTTP . 'mod/cycle/img/new_thumb.png');
            }
            $tpl['thumbnails'][] = $thumb;
        }

        $slot = new Cycle_Slot(1);

        $tpl['form'] = $this->slotForm($slot);
        if ($this->error) {
            $tpl['error'] = $this->error;
        }

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
        $form->setRequired('destination_url');
        $form->setSize('destination_url', 30);

        if (!$slot->isNew()) {
            $form->addSubmit('add_new', 'Update slot ' . $slot->slot_order);
            $form->addSubmit('delete', 'Delete slot ' . $slot->slot_order);
        } else {
            $form->addSubmit('add_new', 'Add new slot ' . $slot->slot_order);
        }


        $tpl = $form->getTemplate();
        $tpl['thumb_dimensions'] = 'Thumbnail dimensions : ' . cycle_thumb_width . 'x' . cycle_thumb_height;
        $tpl['pic_dimensions'] = 'Background dimensions : ' . cycle_picture_width . 'x' . cycle_picture_height;
        $tpl['TITLE'] = '#' . $slot->slot_order;
        $tpl['thumbnail_path'] = $slot->thumbnail_path;
        return PHPWS_Template::process($tpl, 'cycle', 'slot_form.tpl');
    }

    public function getSlots()
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


        $bg_tile = PHPWS_SOURCE_HTTP . 'mod/cycle/img/50-percent.png';
        Layout::addStyle('cycle');
        $count = 0;
        foreach ($result as $slot) {
            $fullpic = $thumb = null;
            $fullpic['pic_width'] = cycle_picture_width;
            $fullpic['pic_height'] = cycle_picture_height;
            $fullpic['id'] = $slot->slot_order;
            $urls[] = <<<EOF
url[{$slot->slot_order}] = '{$slot->destination_url}';
EOF;
            $count++;
            $thumb['thumb'] = sprintf('<li><a style="width : %spx; height : %spx" class="thumb-nav" href="#" id="goto%s"><img src="%s" /></a></li>', cycle_thumb_width, cycle_thumb_height, $count, $slot->thumbnail_path);
            $fullpic['image'] = $slot->background_path;
            if (!empty($slot->feature_text)) {
                $fullpic['story'] = <<<EOF
<div class="cycle-story" style="top : {$slot->feature_y}px; left : {$slot->feature_x}px; width : {$slot->f_width}px; height : {$slot->f_height}px; background-image : url({$bg_tile})">{$slot->feature_text}</div>
EOF;
            }
            $tpl['fullpic'][] = $fullpic;
            $tpl['thumbnails'][] = $thumb;
        }
        $js['urls'] = implode("\n", $urls);

        javascriptMod('cycle', 'cycle', $js);

        return PHPWS_Template::process($tpl, 'cycle', 'cycle_box.tpl');
    }

}

?>
