<?php
/**
 * vshop - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
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
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class vShop_Forms {
    public $vshop = null;

    public function get($type)
    {
        switch ($type) {

        case 'new_dept':
        case 'edit_dept':
            if (empty($this->vshop->dept)) {
                $this->vshop->loadDept();
            }
            $this->editDept();
            break;

        case 'list_depts':
            $this->vshop->panel->setCurrentTab('list_depts');
            $this->listDepts();
            break;

        case 'new_item':
            $this->selectDept();
            break;
            
        case 'edit_item':
            if (empty($this->vshop->item)) {
                $this->vshop->loadItem();
            }
            $this->editItem();
            break;

        case 'list_items':
            $this->vshop->panel->setCurrentTab('list_items');
            $this->listItems();
            break;

        case 'new_tax':
        case 'edit_tax':
            if (empty($this->vshop->tax)) {
                $this->vshop->loadTax();
            }
            $this->editTax();
            break;

        case 'taxes':
            $this->vshop->panel->setCurrentTab('taxes');
            $this->listTaxes();
            break;

        case 'settings':
            $this->vshop->panel->setCurrentTab('settings');
            $this->editSettings();
            break;

        case 'edit_order':
            if (empty($this->vshop->order)) {
                $this->vshop->loadOrder();
            }
            $this->editOrder();
            break;

        case 'set_status':
            if (empty($this->vshop->order)) {
                $this->vshop->loadOrder();
            }
//print_r($this->vshop->order); exit;
            $this->setStatus();
            break;

        case 'orders':
            $this->vshop->panel->setCurrentTab('orders');
            $this->listOrders(1);
            break;

        case 'incompleted':
            $this->vshop->panel->setCurrentTab('incompleted');
            $this->listOrders(0);
            break;

        case 'cancelled':
            $this->vshop->panel->setCurrentTab('cancelled');
            $this->listOrders(1,1);
            break;

        case 'reports':
            $this->vshop->panel->setCurrentTab('reports');
            $this->reportsMenu();
            break;

        case 'info':
            $this->vshop->panel->setCurrentTab('info');
            $this->showInfo();
            break;

        }

    }


    function settingsPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=vshop&aop=menu';

        if (Current_User::allow('vshop', 'settings', null, null, true)){
            $tags['settings'] = array('title'=>dgettext('vshop', 'Settings'), 'link'=>$link);
            $tags['taxes'] = array('title'=>dgettext('rolodex', 'Taxes'), 'link'=>$link);
        }

        $panel = new PHPWS_Panel('vshop-settings-panel');
        $panel->quickSetTabs($tags);
        $panel->setModule('vshop');
        return $panel;
    }


    function ordersPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=vshop&aop=menu';

        if (Current_User::allow('vshop', 'edit_orders')){
            $tags['orders'] = array('title'=>dgettext('vshop', 'Orders'), 'link'=>$link);
            $tags['reports'] = array('title'=>dgettext('rolodex', 'Reports'), 'link'=>$link);
            $tags['incompleted'] = array('title'=>dgettext('vshop', 'Abandoned Orders'), 'link'=>$link);
            $tags['cancelled'] = array('title'=>dgettext('vshop', 'Cancelled Orders'), 'link'=>$link);
        }

        $panel = new PHPWS_Panel('vshop-orders-panel');
        $panel->quickSetTabs($tags);
        $panel->setModule('vshop');
        return $panel;
    }


    public function listDepts()
    {
        if (Current_User::allow('vshop', 'edit_items') && isset($_REQUEST['uop'])) {
            $link[] = PHPWS_Text::secureLink(dgettext('vshop', 'Add new department'), 'vshop', array('aop'=>'new_dept'));
            MiniAdmin::add('vshop', $link);
        }

        $ptags['TITLE_HEADER'] = dgettext('vshop', 'Title');
        $ptags['ITEMS_HEADER'] = dgettext('vshop', 'Items');

        PHPWS_Core::initModClass('vshop', 'vShop_Dept.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vshop_depts', 'vShop_Dept');
        $pager->setModule('vshop');

        /* I am not using the next line in this mod, I just leave it
         * as a reminder of addWhere()
        if (!Current_User::isUnrestricted('vshop')) {
            $pager->addWhere('active', 1);
        }
        */

        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_depts.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'new_dept';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vshop', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('vshop', 'Settings'), 'vshop', $vars),  PHPWS_Text::secureLink(dgettext('vshop', 'New Department'), 'vshop', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');
        $pager->cacheQueries();

        $this->vshop->content = $pager->get();
        $this->vshop->title = sprintf(dgettext('vshop', '%s Departments'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
    }


    public function listItems()
    {
        $ptags['TITLE_HEADER'] = dgettext('vshop', 'Name');
        $ptags['PRICE_HEADER'] = dgettext('vshop', 'Price');
//        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            $ptags['STOCK_HEADER'] = dgettext('vshop', 'Stock');
//        }
        $ptags['DEPT_HEADER'] = dgettext('vshop', 'Department');

        PHPWS_Core::initModClass('vshop', 'vShop_Item.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vshop_items', 'vShop_Item');
        $pager->setModule('vshop');
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_items.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'menu';
            $vars2['tab']  = 'new_item';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vshop', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('vshop', 'Settings'), 'vshop', $vars),  PHPWS_Text::secureLink(dgettext('vshop', 'New Item'), 'vshop', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');
        $pager->cacheQueries();

        $this->vshop->content = $pager->get();
        $this->vshop->title = sprintf(dgettext('vshop', '%s Items'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
    }


    public function listTaxes()
    {
        if (Current_User::allow('vshop', 'settings') && isset($_REQUEST['uop'])) {
            $link[] = PHPWS_Text::secureLink(dgettext('vshop', 'Add new tax'), 'vshop', array('aop'=>'new_tax'));
            MiniAdmin::add('vshop', $link);
        }

        $ptags['TITLE_HEADER'] = dgettext('vshop', 'Title');
        $ptags['RATE_HEADER'] = dgettext('vshop', 'Rate');

        PHPWS_Core::initModClass('vshop', 'vShop_Tax.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vshop_taxes', 'vShop_Tax');
        $pager->setModule('vshop');

        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_taxes.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'new_tax';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vshop', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('vshop', 'Settings'), 'vshop', $vars),  PHPWS_Text::secureLink(dgettext('vshop', 'New Tax'), 'vshop', $vars2));
        }
        if (Current_User::allow('vshop', 'settings', null, null, true)) {
            $vars['aop']  = 'new_tax';
            $label = Icon::show('add', dgettext('rolodex', 'Add Tax'));
            $ptags['ADD_LINK'] = PHPWS_Text::secureLink($label . ' ' . dgettext('vshop', 'Add Tax'), 'vshop', $vars);
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->cacheQueries();

        $this->vshop->content = $pager->get();
        $this->vshop->title = sprintf(dgettext('vshop', '%s Taxes'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
    }


    public function listOrders($completed=1,$cancelled=0)
    {
        $ptags['ORDER_HEADER'] = dgettext('vshop', 'Order');
        $ptags['TOTAL_HEADER'] = dgettext('vshop', 'Total');
        $ptags['ORDERED_HEADER'] = dgettext('vshop', 'Ordered');
        $ptags['UPDATED_HEADER'] = dgettext('vshop', 'Modified');
        $ptags['STATUS_HEADER'] = dgettext('vshop', 'Status');

        PHPWS_Core::initModClass('vshop', 'vShop_Order.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('vshop_orders', 'vShop_Order');
        $pager->setModule('vshop');
        $pager->addWhere('completed', $completed);
        $pager->addWhere('cancelled', $cancelled);
        $pager->setOrder('id', 'desc', true);
        $pager->setTemplate('list_orders.tpl');
        $pager->addRowTags('rowTag');
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('id', 'first_name', 'last_name');
        $pager->cacheQueries();

        $this->vshop->content = $pager->get();
        $this->vshop->title = sprintf(dgettext('vshop', '%s Orders'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
    }


    public function editDept()
    {
        $form = new PHPWS_Form('vshop_dept');
        $dept = & $this->vshop->dept;

        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'post_dept');
        if ($dept->id) {
            $form->addHidden('id', $dept->id);
            $form->addSubmit(dgettext('vshop', 'Update'));
            $this->vshop->title = sprintf(dgettext('vshop', 'Update %s department'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
        } else {
            $form->addSubmit(dgettext('vshop', 'Create'));
            $this->vshop->title = sprintf(dgettext('vshop', 'Create %s department'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
        }

        $form->addText('title', $dept->getTitle());
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('vshop', 'Title'));

        $form->addTextArea('description', $dept->getDescription());
//        $form->useEditor('description', true, true, 0, 0, 'fckeditor');
        $form->useEditor('description', true, true, 0, 0);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setRequired('description');
        $form->setLabel('description', dgettext('vshop', 'Description'));

        if (PHPWS_Settings::get('vshop', 'enable_files')) {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('file_id', $dept->file_id);
            $manager->imageOnly();
            $manager->maxImageWidth(PHPWS_Settings::get('vshop', 'max_width'));
            $manager->maxImageHeight(PHPWS_Settings::get('vshop', 'max_height'));
            if ($manager) {
                $form->addTplTag('FILE_MANAGER', $manager->get());
            }
        }

        $tpl = $form->getTemplate();

        $tpl['DETAILS_LABEL'] = dgettext('vshop', 'Details');


        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'edit_dept.tpl');
    }


    public function editItem()
    {
        $form = new PHPWS_Form;
        $item = & $this->vshop->item;
        $dept = & $this->vshop->dept;

        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'post_item');

        if ($item->id) {
            $this->vshop->title = sprintf(dgettext('vshop', 'Update %s item'), $dept->title);
            $form->addHidden('item_id', $item->id);
            $form->addSubmit(dgettext('vshop', 'Update'));
        } else {
            $this->vshop->title = sprintf(dgettext('vshop', 'Add item to %s'), $dept->title);
            $form->addSubmit(dgettext('vshop', 'Add'));
        }

        PHPWS_Core::initModClass('vshop', 'vShop_Dept.php');
        $db = new PHPWS_DB('vshop_depts');
        $db->addColumn('id');
        $db->addColumn('title');
        $result = $db->getObjects('vShop_Dept');
        if ($result) {
            foreach ($result as $dept) {
                $choices[$dept->id] = $dept->title;
            }
            $form->addSelect('dept_id', $choices);
            $form->setLabel('dept_id', dgettext('vshop', 'Department'));
            $form->setMatch('dept_id', $item->dept_id);
        }

        $form->addText('title', $item->title);
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('vshop', 'Title'));

        $form->addTextArea('description', $item->description);
        $form->useEditor('description', true, true, 0, 0);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setRequired('description');
        $form->setLabel('description', dgettext('vshop', 'Description'));

        if (PHPWS_Settings::get('vshop', 'enable_files')) {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('file_id', $item->file_id);
//            $manager->imageOnly();
            $manager->maxImageWidth(PHPWS_Settings::get('vshop', 'max_width'));
            $manager->maxImageHeight(PHPWS_Settings::get('vshop', 'max_height'));
            if ($manager) {
                $form->addTplTag('FILE_MANAGER', $manager->get());
            }
        }

        $form->addText('price', $item->price);
        $form->setSize('price', 10);
        $form->setMaxSize('price', 20);
        $form->setRequired('price');
        $form->setLabel('price', dgettext('vshop', 'Price'));

        $form->addCheckbox('taxable', 1);
        $form->setMatch('taxable', $item->taxable);
        $form->setLabel('taxable', dgettext('vshop', 'Taxable'));

        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            $form->addText('stock', $item->stock);
            $form->setSize('stock', 5);
            $form->setMaxSize('stock', 5);
    //        $form->setRequired('stock');
            $form->setLabel('stock', dgettext('vshop', 'Qty in stock'));
        }

        $form->addText('weight', $item->weight);
        $form->setSize('weight', 5);
        $form->setMaxSize('weight', 5);
//        $form->setRequired('weight');
        $form->setLabel('weight', dgettext('vshop', 'Weight'));

        if (PHPWS_Settings::get('vshop', 'shipping_calculation') == 2) {
            $form->addText('shipping', $item->shipping);
            $form->setSize('shipping', 5);
            $form->setMaxSize('shipping', 5);
    //        $form->setRequired('shipping');
            $form->setLabel('shipping', dgettext('vshop', 'Shipping fee'));
        }

        $tpl = $form->getTemplate();
        $tpl['INFO_LABEL'] = dgettext('vshop', 'Item details');

        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'edit_item.tpl');
    }


    public function editTax()
    {
        $form = new PHPWS_Form;
        $tax = & $this->vshop->tax;
        require PHPWS_SOURCE_DIR . 'mod/vshop/inc/zones.php';

        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'post_tax');
        if ($tax->id) {
            $this->vshop->title = dgettext('vshop', 'Update tax');
            $form->addHidden('tax_id', $tax->id);
            $form->addSubmit(dgettext('vshop', 'Update'));
        } else {
            $this->vshop->title = dgettext('vshop', 'Add new tax');
            $form->addSubmit(dgettext('vshop', 'Add'));
        }

        $form->addText('title', $tax->title);
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('vshop', 'Title'));

        $form->addMultiple('zones', $all_zones);
        $form->setRequired('zones');
        $form->setLabel('zones', dgettext('vshop', 'Zone(s)'));
        $form->setMatch('zones', $tax->zones);

        $form->addText('rate', $tax->rate);
        $form->setSize('rate', 5);
        $form->setMaxSize('rate', 5);
        $form->setRequired('rate');
        $form->setLabel('rate', dgettext('vshop', '% Rate'));

        $tpl = $form->getTemplate();
        $tpl['INFO_LABEL'] = dgettext('vshop', 'Tax details');

        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'edit_tax.tpl');
    }


    public function editOrder()
    {

        $tpl['CART_TITLE'] = dgettext('vshop', 'Cart contents');
        $tpl['CART'] = $this->orderDetails();
        $tpl['CUSTOMER_TITLE'] = dgettext('vshop', 'Billing information');
        $tpl['CUSTOMER'] = $this->billingApp();

        $this->vshop->title = dgettext('vshop', 'Checkout');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'checkout.tpl');
    }


    public function setStatus($order)
    {

        $form = new PHPWS_Form('set_status');
        require PHPWS_SOURCE_DIR . 'mod/vshop/inc/statuses.php';

        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'update_status');
        $form->addHidden('order_id', $order->id);

        $form->addSelect('status', $statuses);
        $form->setMatch('status', $order->status);
        $form->setLabel('status', dgettext('vshop', 'Order status'));

        $form->addCheckbox('notice', 1);
        $form->setMatch('notice', 1);
        $form->setLabel('notice', dgettext('vshop', 'Send notification?'));

        $form->addSubmit(dgettext('vshop', 'Update'));
        
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = sprintf(dgettext('vshop', 'Order %s Status'), $order->id);
        $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="window.close();" />', dgettext('vshop', 'Cancel'));
        $content = PHPWS_Template::process($tpl, 'vshop', 'set_status.tpl');

        return $content;
    }


    public function editSettings()
    {

        require PHPWS_SOURCE_DIR . 'mod/vshop/inc/payment_methods.php';
        $form = new PHPWS_Form('vshop_settings');
        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('enable_sidebox', 1);
        $form->setMatch('enable_sidebox', PHPWS_Settings::get('vshop', 'enable_sidebox'));
        $form->setLabel('enable_sidebox', sprintf(dgettext('vshop', 'Enable %s sidebox'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title'))));

        $form->addCheckbox('sidebox_homeonly', 1);
        $form->setMatch('sidebox_homeonly', PHPWS_Settings::get('vshop', 'sidebox_homeonly'));
        $form->setLabel('sidebox_homeonly', dgettext('vshop', 'Show sidebox on home page only'));

        $form->addTextField('mod_title', PHPWS_Settings::get('vshop', 'mod_title'));
        $form->setLabel('mod_title', dgettext('vshop', 'Module title'));
        $form->setSize('mod_title', 30);

        $form->addTextArea('sidebox_text', PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'sidebox_text')));
        $form->setRows('sidebox_text', '4');
        $form->setCols('sidebox_text', '40');
        $form->setLabel('sidebox_text', dgettext('vshop', 'Sidebox text'));

        $form->addCheckbox('enable_files', 1);
        $form->setMatch('enable_files', PHPWS_Settings::get('vshop', 'enable_files'));
        $form->setLabel('enable_files', dgettext('vshop', 'Enable images and files on department and item records'));

        $form->addTextField('max_width', PHPWS_Settings::get('vshop', 'max_width'));
        $form->setLabel('max_width', dgettext('vshop', 'Maximum image width (50-600)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', PHPWS_Settings::get('vshop', 'max_height'));
        $form->setLabel('max_height', dgettext('vshop', 'Maximum image height (50-600)'));
        $form->setSize('max_height', 4,4);

        $form->addCheckbox('use_inventory', 1);
        $form->setMatch('use_inventory', PHPWS_Settings::get('vshop', 'use_inventory'));
        $form->setLabel('use_inventory', dgettext('vshop', 'Use inventory control'));

        $form->addCheckbox('use_breadcrumb', 1);
        $form->setMatch('use_breadcrumb', PHPWS_Settings::get('vshop', 'use_breadcrumb'));
        $form->setLabel('use_breadcrumb', dgettext('vshop', 'Use breadcrumb navigation'));

        $form->addTextArea('checkout_inst', PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'checkout_inst')));
        $form->setRows('checkout_inst', '4');
        $form->setCols('checkout_inst', '40');
        $form->setLabel('checkout_inst', dgettext('vshop', 'Checkout instructions'));

        $form->addTextField('admin_email', PHPWS_Settings::get('vshop', 'admin_email'));
        $form->setRequired('admin_email');
        $form->setLabel('admin_email', dgettext('vshop', 'Shop admin e-mail'));
        $form->setSize('admin_email', 30);

        $form->addTextArea('order_message', PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'order_message')));
        $form->setRows('order_message', '4');
        $form->setCols('order_message', '40');
        $form->setLabel('order_message', dgettext('vshop', 'Thanks message to customer'));

        $form->addTextField('weight_unit', PHPWS_Settings::get('vshop', 'weight_unit'));
        $form->setLabel('weight_unit', dgettext('vshop', 'Unit of weight'));
        $form->setSize('weight_unit', 5);

        $form->addTextField('currency', PHPWS_Settings::get('vshop', 'currency'));
        $form->setRequired('currency');
        $form->setLabel('currency', dgettext('vshop', 'Currency code (ISO 3 letter code, eg. USD, CAD)'));
        $form->setSize('currency', 5);

        $form->addTextField('currency_symbol', PHPWS_Settings::get('vshop', 'currency_symbol'));
        $form->setLabel('currency_symbol', dgettext('vshop', 'Currency symbol'));
        $form->setSize('currency_symbol', 5);

        $choices = array(1=>dgettext('vshop', 'Leading'), 2=>dgettext('vshop', 'Trailing'));
        $form->addSelect('curr_symbol_pos', $choices);
        $form->setMatch('curr_symbol_pos', PHPWS_Settings::get('vshop', 'curr_symbol_pos'));
        $form->setLabel('curr_symbol_pos', dgettext('vshop', 'Currency marker position'));

        $form->addCheckbox('display_currency', 1);
        $form->setMatch('display_currency', PHPWS_Settings::get('vshop', 'display_currency'));
        $form->setLabel('display_currency', dgettext('vshop', 'Display currency code'));

        $choices = array(1=>dgettext('vshop', 'Free shipping'), 2=>dgettext('vshop', 'Flat rate per item'), 3=>dgettext('vshop', 'Flat rate per order'), 4=>dgettext('vshop', '% rate * weight'), 5=>dgettext('vshop', '% rate * total'));
        $form->addSelect('shipping_calculation', $choices);
        $form->setLabel('shipping_calculation', dgettext('vShop', 'Shipping calculation method'));
        $form->setMatch('shipping_calculation', PHPWS_Settings::get('vshop', 'shipping_calculation'));

        $form->addTextField('shipping_flat', PHPWS_Settings::get('vshop', 'shipping_flat'));
        $form->setLabel('shipping_flat', dgettext('vshop', '.00 Shipping flat rate (if using flat rate per order)'));
        $form->setSize('shipping_flat', 4);

        $form->addTextField('shipping_percent', PHPWS_Settings::get('vshop', 'shipping_percent'));
        $form->setLabel('shipping_percent', dgettext('vshop', '% Shipping rate (if using a percentage rate)'));
        $form->setSize('shipping_percent', 3);

        $form->addTextField('shipping_minimum', number_format(PHPWS_Settings::get('vshop', 'shipping_minimum'), 2, '.', ','));
        $form->setLabel('shipping_minimum', dgettext('vshop', 'Minimum shipping charge of x.xx (0 to disable)'));
        $form->setSize('shipping_minimum', 6);

        $form->addTextField('shipping_maximum', number_format(PHPWS_Settings::get('vshop', 'shipping_maximum'), 2, '.', ','));
        $form->setLabel('shipping_maximum', dgettext('vshop', 'Free shipping if total is over xx.xx (0 to disable)'));
        $form->setSize('shipping_maximum', 6);

        $form->addMultiple('pay_methods', $pay_methods);
        $form->setRequired('pay_methods');
        $form->setLabel('pay_methods', dgettext('vshop', 'Payment Method(s)'));
        $form->setMatch('pay_methods', unserialize(PHPWS_Settings::get('vshop', 'payment_methods')));

        $form->addCheckbox('secure_checkout', 1);
        $form->setMatch('secure_checkout', PHPWS_Settings::get('vshop', 'secure_checkout'));
        $form->setLabel('secure_checkout', dgettext('vshop', 'Use SSL checkout'));

        $form->addTextField('secure_url', PHPWS_Settings::get('vshop', 'secure_url'));
        $form->setLabel('secure_url', dgettext('vshop', 'Secure URL (eg. https://secure.mydomain.com/)'));
        $form->setSize('secure_url', 40);

        $form->addSubmit('save', dgettext('vshop', 'Save settings'));
        
        $tpl = $form->getTemplate();
        $tpl['GENERAL_GROUP_LABEL'] = dgettext('vshop', 'Shop Behavior');
        $tpl['CURRENCY_GROUP_LABEL'] = dgettext('vshop', 'Currency and Weight');
        $tpl['TEXT_GROUP_LABEL'] = dgettext('vshop', 'Texts and Messages');
        $tpl['SHIPPING_GROUP_LABEL'] = dgettext('vshop', 'Shipping Charges');
        $tpl['PAY_METHODS_NOTE_TITLE'] = dgettext('vshop', 'Important Note on Payment Methods:');
        $tpl['PAY_METHODS_NOTE'] = dgettext('vshop', 'You must check each payment method you wish to use for configuration. For instance, the Paypal method requires an email address. Please refer to mod/vshop/class/pay_mods/*.php for each method you wish to offer.');

        $this->vshop->title = dgettext('vshop', 'Settings');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'edit_settings.tpl');
    }


    public function reportsMenu()
    {
        

        $tpl['TITLE'] = dgettext('vshop', 'Reports');
        $tpl['INFO'] = 'coming soon...';

        $this->vshop->title = dgettext('vshop', 'Reports');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'reports.tpl');
    }


    public function showInfo()
    {
        
        $filename = 'mod/vshop/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('vshop', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('vshop', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('vshop', 'If you would like to help out with the ongoing development of vShop, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=vShop%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->vshop->title = dgettext('vshop', 'Read me');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'info.tpl');
    }


    public function selectDept()
    {

        $form = new PHPWS_Form('vshop_depts');
        $form->addHidden('module', 'vshop');
        $form->addHidden('aop', 'edit_item');

        PHPWS_Core::initModClass('vshop', 'vShop_Dept.php');
        $db = new PHPWS_DB('vshop_depts');
        $db->addColumn('id');
        $db->addColumn('title');
        $result = $db->getObjects('vShop_Dept');

        if ($result) {
            foreach ($result as $dept) {
                $choices[$dept->id] = $dept->title;
            }
            $form->addSelect('dept_id', $choices);
            $form->setLabel('dept_id', dgettext('vshop', 'Available departments'));
            $form->addSubmit('save', dgettext('vshop', 'Continue'));
        } else {
            $form->addTplTag('NO_DEPTS_NOTE', sprintf(dgettext('vshop', 'Sorry, there are no departments available. You will have to create a %s first.'), PHPWS_Text::secureLink(dgettext('vshop', 'New Department'), 'vshop', array('aop'=>'new_dept'))));
        }
        
        $tpl = $form->getTemplate();
        $tpl['DEPT_ID_GROUP_LABEL'] = dgettext('vshop', 'Select department');

        $this->vshop->title = dgettext('vshop', 'New item step one');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'select_dept.tpl');
    }


    public function checkout()
    {

        $tpl['INSTRUCTION'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'checkout_inst'));
        $tpl['CART_TITLE'] = dgettext('vshop', 'Cart contents');
        $tpl['CART'] = $this->cartApp();
        $tpl['NOTE'] = dgettext('vshop', 'Any applicable taxes and shipping/handling fees will be calculated and displayed when you click Continue below.');
        $tpl['CUSTOMER_TITLE'] = dgettext('vshop', 'Billing information');
        $tpl['CUSTOMER'] = $this->billingApp();

        $this->vshop->title = dgettext('vshop', 'Checkout');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'checkout.tpl');
    }


    public function cartApp()
    {
        $form = new PHPWS_Form('vshop_cart');
        $form->addHidden('module', 'vshop');
        $form->addHidden('uop', 'update_cart');
        $form->addSubmit(dgettext('vshop', 'Update quantities'));

        $tpl = $form->getTemplate();

        PHPWS_Core::initModClass('vshop', 'vShop_Cart.php');
        $cart = vShop_Cart::CreateInstance();
        $cart_data = $cart->GetCart();
        if (!empty($cart_data)) {
            $tpl['TITLE'] = sprintf(dgettext('vshop', '%s Cart'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
            $tpl['LABEL'] = dgettext('vshop', 'Cart contents');
            $tpl['NAME_LABEL'] = dgettext('vshop', 'Name');
            $tpl['QTY_LABEL'] = dgettext('vshop', 'Qty');
            $tpl['PRICE_LABEL'] = dgettext('vshop', 'Price');
            $tpl['SUBTOTAL_LABEL'] = dgettext('vshop', 'Sub-total');
            $total_items = 0.00;
            foreach ($cart_data as $id=>$val) {
                $qty = $cart_data[$id]['count'];
                PHPWS_Core::initModClass('vshop', 'vShop_Item.php');
                $item = new vShop_Item($id);
                $subtotal = $item->price * $qty;
                $total_items = $total_items + $subtotal;

                $tpl['items'][] = array(
                                    'ID'        => $id, 
                                    'QTY'       => '<input type="text" name="qtys[' . $id . ']" id="qtys[' . $id . ']" size="3" maxsize="5" value="' . $qty . '" />', 
                                    'NAME'      => $item->viewLink(), 
                                    'PRICE'     => number_format($item->price, 2, '.', ','),
                                    'SUBTOTAL'  => number_format($subtotal, 2, '.', ',')
                                 );
            }
            $tpl['TOTAL_LABEL'] = dgettext('vshop', 'Total');
//            $tpl['TOTAL'] = number_format($total_items, 2, '.', ',');
            if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
                $tpl['TOTAL'] = PHPWS_Settings::get('vshop', 'currency_symbol') . number_format($total_items, 2, '.', ',');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $tpl['TOTAL'] .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            } else {
                $tpl['TOTAL'] = number_format($total_items, 2, '.', ',') . PHPWS_Settings::get('vshop', 'currency_symbol');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $tpl['TOTAL'] .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            }
            return PHPWS_Template::process($tpl, 'vshop', 'checkout_cart.tpl');
        }
    }


    public function billingApp($admin=false)
    {
        $form = new PHPWS_Form('vshop_billing');
        $order = & $this->vshop->order;
        require PHPWS_SOURCE_DIR . 'mod/vshop/inc/zones.php';
        require PHPWS_SOURCE_DIR . 'mod/vshop/inc/payment_methods.php';

        if ($order->id) {
            require PHPWS_SOURCE_DIR . 'mod/vshop/inc/statuses.php';
            $this->vshop->title = sprintf(dgettext('vshop', 'Update order %s'), $order->id);
            $form->addHidden('order_id', $order->id);
            $form->addSubmit(dgettext('vshop', 'Update'));
            $form->addHidden('aop', 'post_order');
            $form->addSelect('status', $statuses);
            $form->setMatch('status', $order->status);
            $form->setLabel('status', dgettext('vshop', 'Order status'));
        } else {
            $this->vshop->title = dgettext('vshop', 'Submit order');
            $form->addSubmit(dgettext('vshop', 'Continue to payment'));
            $form->addHidden('uop', 'post_order');
        }

        $form->addHidden('module', 'vshop');

        $form->addText('first_name', $order->first_name);
        $form->setRequired('first_name');
        $form->setSize('first_name', 20);
        $form->setLabel('first_name', dgettext('vshop', 'First name'));

        $form->addText('last_name', $order->last_name);
        $form->setRequired('last_name');
        $form->setSize('last_name', 30);
        $form->setLabel('last_name', dgettext('vshop', 'Last name'));

        $form->addTextArea('comments', $order->comments);
        $form->setRows('comments', '6');
        $form->setCols('comments', '40');
        $form->setLabel('comments', dgettext('vshop', 'Comments'));

        $form->addText('email', $order->email);
        $form->setRequired('email');
        $form->setSize('email', 30);
        $form->setLabel('email', dgettext('vshop', 'E-mail'));

        $form->addText('phone', $order->phone);
        $form->setSize('phone', 15);
        $form->setLabel('phone', dgettext('vshop', 'Phone'));

        $form->addText('address_1', $order->address_1);
        $form->setRequired('address_1');
        $form->setSize('address_1', 50);
        $form->setLabel('address_1', dgettext('vshop', 'Address 1'));

        $form->addText('address_2', $order->address_2);
        $form->setSize('address_2', 50);
        $form->setLabel('address_2', dgettext('vshop', 'Address 2'));

        $form->addText('city', $order->city);
        $form->setRequired('city');
        $form->setSize('city', 30);
        $form->setLabel('city', dgettext('vshop', 'City'));

        $form->addSelect('state', $all_zones);
        $form->setRequired('state');
        $form->setLabel('state', dgettext('vshop', 'Province/State'));
        $form->setMatch('state', $order->state);

        $form->addText('country', $order->country);
        $form->setRequired('country');
        $form->setSize('country', 30);
        $form->setLabel('country', dgettext('vshop', 'Country'));

        $form->addText('postal_code', $order->postal_code);
        $form->setRequired('postal_code');
        $form->setSize('postal_code', 10);
        $form->setLabel('postal_code', dgettext('vshop', 'Postal/Zip code'));


        $allowed_methods = unserialize(PHPWS_Settings::get('vshop', 'payment_methods'));
        foreach ($allowed_methods as $method) {
            $choices[$method] = $pay_methods[$method];
        }
        $form->addSelect('pay_method', $choices);
        $form->setRequired('pay_method');
        $form->setLabel('pay_method', dgettext('vshop', 'Payment Method'));
        $form->setMatch('pay_method', $order->pay_method);



        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'vshop', 'checkout_billing.tpl');
    }


    public function payment()
    {

        $tpl['ORDER_TITLE'] = dgettext('vshop', 'Order details');
        $tpl['ORDER'] = $this->orderDetails();
        $tpl['PAYMENT_TITLE'] = dgettext('vshop', 'Payment information');
        $tpl['PAYMENT'] = $this->paymentApp();

        $this->vshop->title = dgettext('vshop', 'Payment');
        $this->vshop->content = PHPWS_Template::process($tpl, 'vshop', 'payment.tpl');
    }


    public function orderDetails($formatted=true)
    {

        $order = & $this->vshop->order;
        $order_data = $order->order_array;

        $tpl['TITLE'] = sprintf(dgettext('vshop', '%s Purchase'), PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')));
        $tpl['LABEL'] = dgettext('vshop', 'Order details');
        $tpl['ID_LABEL'] = dgettext('vshop', 'ID');
        $tpl['NAME_LABEL'] = dgettext('vshop', 'Name');
        $tpl['QTY_LABEL'] = dgettext('vshop', 'Qty');
        $tpl['PRICE_LABEL'] = dgettext('vshop', 'Unit Price');
        $tpl['SUBTOTAL_LABEL'] = dgettext('vshop', 'Sub-total');

        foreach ($order_data['items'] as $item) {
            $tpl['items'][] = array(
                                'ID'        => $item['id'], 
                                'QTY'       => $item['qty'], 
                                'NAME'      => $item['name'], 
                                'PRICE'     => number_format($item['price'], 2, '.', ','),
                                'SUBTOTAL'  => number_format($item['subtotal'], 2, '.', ',')
                             );
        }

        $tpl['TOTAL_LABEL'] = dgettext('vshop', 'Product Total');
        $tpl['TOTAL'] = number_format($order_data['total_items'], 2, '.', ',');

        if (isset($order_data['taxes'])) {
            foreach ($order_data['taxes'] as $tax) {
                $tpl['taxes'][] = array (
                                'TAX_LABEL' => $tax['name'], 
                                'TAX'       => number_format($tax['tax_total'], 2, '.', ',') 
                            );
            }
        }

        if ($order_data['total_shipping']) {
            $tpl['SHIPPING_LABEL'] = dgettext('vshop', 'Shipping/Handling');
            $tpl['SHIPPING'] = number_format($order_data['total_shipping'], 2, '.', ',');
        }

        $tpl['FINAL_LABEL'] = dgettext('vshop', 'Total Due');
//        $tpl['FINAL'] = number_format($order_data['total_grand'], 2, '.', ',');
        if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
            $tpl['FINAL'] = PHPWS_Settings::get('vshop', 'currency_symbol') . number_format($order_data['total_grand'], 2, '.', ',');
            if (PHPWS_Settings::get('vshop', 'display_currency')) {
                $tpl['FINAL'] .= ' ' . PHPWS_Settings::get('vshop', 'currency');
            }
        } else {
            $tpl['FINAL'] = number_format($order_data['total_grand'], 2, '.', ',') . PHPWS_Settings::get('vshop', 'currency_symbol');
            if (PHPWS_Settings::get('vshop', 'display_currency')) {
                $tpl['FINAL'] .= ' ' . PHPWS_Settings::get('vshop', 'currency');
            }
        }
        
        if ($formatted) {
            return PHPWS_Template::process($tpl, 'vshop', 'order_details.tpl');
        } else {
            return PHPWS_Template::process($tpl, 'vshop', 'order_details_plain.tpl');
        }
    }


    public function paymentApp()
    {

        /* I should error check here to make sure the class is there */
        
        $payclass = $this->vshop->order->pay_method;
        PHPWS_Core::initModClass('vshop', 'pay_mods/' . $payclass . '.php');
        $payment = new $payclass($this->vshop->order->id);

        $this->vshop->title = dgettext('vshop', 'Confirm order');
        $tpl = $payment->form($this->vshop->order);

        return PHPWS_Template::process($tpl, 'vshop', 'pay_mods/' . $payclass . '.tpl');
    }




}

?>