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

Core\Core::requireInc('vshop', 'errordefines.php');
Core\Core::requireConfig('vshop');

class vShop {
    public $forms      = null;
    public $panel      = null;
    public $title      = null;
    public $message    = null;
    public $content    = null;
    public $dept       = null;
    public $item       = null;
    public $tax        = null;
    public $order      = null;


    public function adminMenu()
    {
        if (!Current_User::allow('vshop')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        /* This switch determines if sub panels needs creating */
        switch($_REQUEST['aop']) {
            case 'post_settings':
            case 'view_tax':
            case 'new_tax':
            case 'edit_tax':
            case 'post_tax':
            case 'delete_tax':
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $settingsPanel = vShop_Forms::settingsPanel();
                $settingsPanel->enableSecure();
                break;
            case 'sales_report':
            case 'inventory_report':
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $ordersPanel = vShop_Forms::ordersPanel();
                $ordersPanel->enableSecure();
                break;
            case 'menu':
                if (isset($_GET['tab'])) {
                    Core\Core::initModClass('vshop', 'vShop_Forms.php');
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'taxes') {
                        $settingsPanel = vShop_Forms::settingsPanel();
                        $settingsPanel->enableSecure();
                    } elseif ($_GET['tab'] == 'orders' || $_GET['tab'] == 'reports' || $_GET['tab'] == 'incompleted' || $_GET['tab'] == 'cancelled') {
                        Core\Core::initModClass('vshop', 'vShop_Forms.php');
                        $ordersPanel = vShop_Forms::ordersPanel();
                        $ordersPanel->enableSecure();
                    }
                }
    
        }

        /* This switch dumps the content in */
        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list_depts');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'new_dept':
            case 'edit_dept':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_dept');
                break;
    
            case 'post_dept':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                if ($this->postDept()) {
                    if (Core\Error::logIfError($this->dept->save())) {
                        $this->forwardMessage(dgettext('vshop', 'Error occurred when saving department.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('vshop', 'Department saved successfully.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu');
                    }
                } else {
                    $this->loadForm('edit_dept');
                }
                break;
    
            case 'delete_dept':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                $this->loadDept();
                $this->dept->delete();
                $this->message = dgettext('vshop', 'Department deleted.');
                $this->loadForm('list');
                break;
                

            case 'edit_item':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_item');
                break;
    
            case 'post_item':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                if ($this->postItem()) {
                    if (Core\Error::logIfError($this->item->save())) {
                        $this->forwardMessage(dgettext('vshop', 'Error occurred when saving item.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('vshop', 'Item saved successfully.'));
// old                        Core\Core::reroute('index.php?module=vshop&dept='.$this->item->dept_id);
                        Core\Core::reroute('index.php?module=vshop&aop=menu&tab=list_items&dept='.$this->item->dept_id); // new from wendall
                    }
                } else {
                    $this->loadForm('edit_item');
                }
                break;
    
            case 'delete_item':
                if (!Current_User::authorized('vshop', 'edit_items')) {
                    Current_User::disallow();
                }
                $this->loadItem();
                $this->item->delete();
                $this->message = dgettext('vshop', 'Item deleted.');
                $this->loadForm('list');
                break;


            case 'view_tax':
                $settingsPanel->setCurrentTab('taxes');
                $this->loadTax();
                $this->title = $this->tax->getTitle(true);
                $this->content = $this->tax->view();
                break;

            case 'new_tax':
            case 'edit_tax':
                $settingsPanel->setCurrentTab('taxes');
                if (!Current_User::authorized('vshop', 'settings')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_tax');
                break;
    
            case 'post_tax':
                $settingsPanel->setCurrentTab('taxes');
                if (!Current_User::authorized('vshop', 'settings')) {
                    Current_User::disallow();
                }
                if ($this->postTax()) {
                    if (Core\Error::logIfError($this->tax->save())) {
                        $this->forwardMessage(dgettext('vshop', 'Error occurred when saving tax.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu&tab=taxes');
                    } else {
                        $this->forwardMessage(dgettext('vshop', 'Tax saved successfully.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu&tab=taxes');
                    }
                } else {
                    $this->loadForm('edit_tax');
                }
                break;
    
            case 'delete_tax':
                $settingsPanel->setCurrentTab('taxes');
                if (!Current_User::authorized('vshop', 'settings')) {
                    Current_User::disallow();
                }
                $this->loadTax();
                $this->tax->delete();
                $this->message = dgettext('vshop', 'Tax deleted.');
                $this->loadForm('taxes');
                break;
                

            case 'post_settings':
                $settingsPanel->setCurrentTab('settings');
                if (!Current_User::authorized('vshop', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('vshop', 'vShop settings saved.'));
                    Core\Core::reroute('index.php?module=vshop&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;


            case 'view_order':
//                $settingsPanel->setCurrentTab('orders');
//                if (!Current_User::authorized('vshop', 'edit_orders')) {
//                    Current_User::disallow();
//                }
                $this->loadOrder();
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $this->forms = new vShop_Forms;
                $this->forms->vshop = & $this;
                $this->title = $this->order->getTitle(true);
                $this->content = $this->order->view();
                $this->content .= $this->forms->orderDetails();
                break;

            case 'edit_order':
//                $settingsPanel->setCurrentTab('orders');
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_order');
                break;
    
            case 'post_order':
//                $settingsPanel->setCurrentTab('orders');
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                if ($this->postOrder(true)) {
                    if (Core\Error::logIfError($this->order->save())) {
                        $this->forwardMessage(dgettext('vshop', 'Error occurred when saving order.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu&tab=orders');
                    } else {
                        $this->sendNotice($this->order->id, 'customer', 'update');
                        $this->sendNotice($this->order->id, 'admin', 'update');
                        $this->forwardMessage(dgettext('vshop', 'Order saved successfully.'));
                        Core\Core::reroute('index.php?module=vshop&aop=menu&tab=orders');
                    }
                } else {
                    $this->loadForm('edit_order');
                }
                break;
    
            case 'delete_order':
//                $settingsPanel->setCurrentTab('orders');
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                $this->loadOrder();
                $this->order->delete();
                $this->message = dgettext('vshop', 'Order deleted.');
                $this->loadForm('orders');
                break;
                
            case 'cancel_order':
//                $settingsPanel->setCurrentTab('orders');
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                $this->loadOrder();
                $this->order->cancelled = 1;
                $this->message = dgettext('vshop', 'Order cancelled');
                if (Core\Settings::get('vshop', 'use_inventory')) {
                    $this->order->updateInventory(true);
                    $this->message .= dgettext('vshop', 'and items returned to inventory');
                }
                $this->order->save();
                $this->loadForm('orders');
                break;

            case 'set_status':
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                $this->loadOrder();
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $content = vShop_Forms::setStatus($this->order);
                Layout::nakedDisplay($content);
                break;
    
            case 'update_status':
                if (!Current_User::authorized('vshop', 'edit_orders')) {
                    Current_User::disallow();
                }
                $this->loadOrder();
                $this->order->status = (int)$_POST['status'];
                $this->order->save();
                $this->forwardMessage(sprintf(dgettext('vshop', 'Order %s status updated.'), $this->order->id));
                if (isset($_POST['notice'])) {
                    $this->sendNotice($this->order->id, 'customer', 'status');                
                    $this->sendNotice($this->order->id, 'admin', 'status');                
                }
                $url = 'index.php?module=vshop&aop=menu&tab=orders';
                $js['location'] = $url;
                javascript('close_refresh', $js);
                break;
    
                

        }

        /* This switch creates the sub panels when needed */
        switch($_REQUEST['aop']) {
            case 'post_settings':
            case 'view_tax':
            case 'new_tax':
            case 'edit_tax':
            case 'post_tax':
            case 'delete_tax':
                $settingsPanel->setContent($this->content);
                $this->content = $settingsPanel->display();
                break;
            case 'sales_report':
            case 'inventory_report':
                $ordersPanel->setContent($this->content);
                $this->content = $ordersPanel->display();
                break;
            case 'menu':
                if (isset($_GET['tab'])) {
                    if ($_GET['tab'] == 'settings' || $_GET['tab'] == 'taxes') {
                        $settingsPanel->setContent($this->content);
                        $this->content = $settingsPanel->display();
                    } elseif ($_GET['tab'] == 'orders' || $_GET['tab'] == 'reports' || $_GET['tab'] == 'incompleted' || $_GET['tab'] == 'cancelled') {
                        $ordersPanel->setContent($this->content);
                        $this->content = $ordersPanel->display();
                    }
                }
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'vshop', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(Core\Template::process($tpl, 'vshop', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }
        
   }


    public function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                Core\Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }
        $this->loadMessage();

        switch($action) {

            case 'list_depts':
                if (vShop::countDepts() == 1) {
                    $this->loadDept($this->getSingleDept());
                    Layout::addPageTitle($this->dept->getTitle());
                    if (Core\Settings::get('vshop', 'use_breadcrumb')) {
                        $this->title = $this->dept->getTitle(true,true);
                    } else {
                        $this->title = $this->dept->getTitle(true);
                    }
                    $this->content = $this->dept->view();
                } else {
                    Core\Core::initModClass('vshop', 'vShop_Forms.php');
                    $this->forms = new vShop_Forms;
                    $this->forms->vshop = & $this;
                    $this->forms->listDepts();
                }
                break;

            case 'view_dept':
                $this->loadDept();
                Layout::addPageTitle($this->dept->getTitle());
                if (Core\Settings::get('vshop', 'use_breadcrumb')) {
                    $this->title = $this->dept->getTitle(true,true);
                } else {
                    $this->title = $this->dept->getTitle(true);
                }
                $this->content = $this->dept->view();
                break;

            case 'view_item':
                $this->loadItem();
                Layout::addPageTitle($this->item->getTitle());
                if (Core\Settings::get('vshop', 'use_breadcrumb')) {
                    $this->title = $this->item->getTitle(true,true);
                } else {
                    $this->title = $this->item->getTitle(true);
                }
                $this->content = $this->item->view();
                break;

            case 'addto_cart':
                $this->loadItem();
                Core\Core::initModClass('vshop', 'vShop_Cart.php');
                $cart = vShop_Cart::CreateInstance();
                if (isset($_REQUEST['qty'])) {
                    $qty = $_REQUEST['qty'];
                } else {
                    $qty = 1;
                }
                if (Core\Settings::get('vshop', 'use_inventory')) {
                    $cart_data = $cart->GetCart();
                    $qty_incart = $cart_data[$this->item->id]['count'];
                    $new_qty = $qty_incart + $qty;
                }
// old                if (Core\Settings::get('vshop', 'use_inventory') && $new_qty <= $this->item->stock) {
                if ((Core\Settings::get('vshop', 'use_inventory') && $new_qty <= $this->item->stock) || (!Core\Settings::get('vshop', 'use_inventory'))) { // fixed thanks wendall
                    $cart->addItems($this->item->id, null, $qty);
                    $this->forwardMessage(sprintf(dgettext('vshop', '%s successfully added to your cart.'), $this->item->getTitle(true)));
                } else {
                    $this->forwardMessage(sprintf(dgettext('vshop', 'Sorry, we do not have enough %s in stock for your request.'), $this->item->getTitle(true)));
                }
                Core\Core::goBack();
                break;

            case 'subtractfrom_cart':
                $this->loadItem();
                Core\Core::initModClass('vshop', 'vShop_Cart.php');
                if (isset($_REQUEST['qty'])) {
                    $qty = $_REQUEST['qty'];
                } else {
                    $qty = 1;
                }
                $cart = vShop_Cart::CreateInstance();
                $cart->RemoveItems($this->item->id, $qty);
                $this->forwardMessage(sprintf(dgettext('vshop', '%s successfully removed from your cart.'), $this->item->getTitle(true)));
                Core\Core::goBack();
                break;

            case 'clear_cart':
                Core\Core::initModClass('vshop', 'vShop_Cart.php');
                $cart = vShop_Cart::CreateInstance();
                $cart->EmptyCart();
                $this->forwardMessage(dgettext('vshop', 'Your cart was successfully cleared.'));
                Core\Core::goBack();
                break;

            case 'update_cart':
                Core\Core::initModClass('vshop', 'vShop_Cart.php');
                $cart = vShop_Cart::CreateInstance();
                if (Core\Settings::get('vshop', 'use_inventory')) {
//                    $cart_data = $cart->GetCart();
                    Core\Core::initModClass('vshop', 'vShop_Item.php');
                }
                foreach ($_REQUEST['qtys'] as $id => $var) {
                    $msg = null;
                    $qty[$id] = $var;
                    if (Core\Settings::get('vshop', 'use_inventory')) {
//                        $qty_incart = $cart_data[$id]['count'];
//                        $new_qty = $qty_incart + $qty;
                        $item = new vShop_Item($id);
//                        print($new_qty); exit;
                        if ($qty[$id] <= $item->stock) {
                            $qty[$id] = $qty[$id];
                        } else {
                            $qty[$id] = $item->stock;
                            $msg .= sprintf(dgettext('vshop', 'We did not have enough %s in stock to fully meet your request, but have fulfilled what we could.'), $item->getTitle()) . '<br />';
                        }
//                        $new_qty = $qty_incart + $qty;
                    }
                    $cart->UpdateItems($id, $qty[$id]);
                }
                $msg .= dgettext('vshop', 'Your cart was successfully updated.');
                $this->forwardMessage($msg);
                Core\Core::goBack();
                break;

            case 'checkout':
                $this->loadOrder();
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $this->forms = new vShop_Forms;
                $this->forms->vshop = & $this;
                $this->forms->checkout();
                break;

            case 'post_order':
                if ($this->postOrder()) {
                    if (Core\Error::logIfError($this->order->save())) {
                        $this->forwardMessage(dgettext('vshop', 'Error occurred when saving order.'));
                        Core\Core::reroute('index.php?module=vshop&uop=checkout');
                    } else {
                        $this->forwardMessage(dgettext('vshop', 'Order saved successfully.'));
//                        print_r($this->order); exit;
                        Core\Core::initModClass('vshop', 'vShop_Cart.php');
                        $cart = vShop_Cart::CreateInstance();
                        $cart->EmptyCart();
                        $_SESSION['vShop_order'] = $this->order->id;
                        Core\Core::reroute('index.php?module=vshop&uop=payment');
                    }
                } else {
//                    $this->loadForm('checkout');
                    $this->forms->checkout();
                }
                break;
    
            case 'payment':
                $this->loadOrder($_SESSION['vShop_order']);
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $this->forms = new vShop_Forms;
                $this->forms->vshop = & $this;
                $this->forms->payment();
                break;

            case 'complete_order':
                $this->loadOrder($_SESSION['vShop_order']);

                $payclass = $this->order->pay_method;
                Core\Core::initModClass('vshop', 'pay_mods/' . $payclass . '.php');
                $payment = new $payclass($this->order->id);
//print_r($payment->complete()); exit;                
                /* process the payment */
                if ($payment->complete($this->order) && !is_array($payment->complete($this->order))) {

                    /* send notices */
                    $this->sendNotice($_SESSION['vShop_order'], 'customer', 'new');
                    $this->sendNotice($_SESSION['vShop_order'], 'admin', 'new');
    
                    /* update the order */
                    $this->order->completed = 1;
                    if (Core\Settings::get('vshop', 'use_inventory')) {
                        $this->order->updateInventory();
                    }
                    $this->order->save();
                    
                    $message = $payment->successMessage();
                    $message .= '<br /><br />';
                    $message .= Core\Text::parseOutput(Core\Settings::get('vshop', 'order_message'));
                    $message .= '<br /><br />';
                    $this->forwardMessage($message);
                    
                    /* unset the order in session */
                    unset($_SESSION['vShop_order']);
                    Core\Core::reroute('index.php?module=vshop');

                } else {
                    $this->message = implode('<br />', $payment->complete());
                    Core\Core::initModClass('vshop', 'vShop_Forms.php');
                    $this->forms = new vShop_Forms;
                    $this->forms->vshop = & $this;
                    $this->forms->payment();
                }

                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'vshop', 'main_user.tpl'));
        } else {
            Layout::add(Core\Template::process($tpl, 'vshop', 'main_user.tpl'));
        }
        
   }


    public function forwardMessage($message, $title=null)
    {
        $_SESSION['vShop_Message']['message'] = $message;
        if ($title) {
            $_SESSION['vShop_Message']['title'] = $title;
        }
    }
    

    public function loadMessage()
    {
        if (isset($_SESSION['vShop_Message'])) {
            $this->message = $_SESSION['vShop_Message']['message'];
            if (isset($_SESSION['vShop_Message']['title'])) {
                $this->title = $_SESSION['vShop_Message']['title'];
            }
            Core\Core::killSession('vShop_Message');
        }
    }


    public function loadForm($type)
    {
        Core\Core::initModClass('vshop', 'vShop_Forms.php');
        $this->forms = new vShop_Forms;
        $this->forms->vshop = & $this;
//print_r($this->forms->vshop); exit;
        $this->forms->get($type);
    }


    public function loadDept($id=0)
    {
        Core\Core::initModClass('vshop', 'vShop_Dept.php');

        if ($id) {
            $this->dept = new vShop_Dept($id);
        } elseif (isset($_REQUEST['dept_id'])) {
            $this->dept = new vShop_Dept($_REQUEST['dept_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->dept = new vShop_Dept($_REQUEST['id']);
        } elseif (isset($_REQUEST['dept'])) {
            $this->dept = new vShop_Dept($_REQUEST['dept']);
        } else {
            $this->dept = new vShop_Dept;
        }
    }


    public function loadItem($id=0)
    {
        Core\Core::initModClass('vshop', 'vShop_Item.php');

        if ($id) {
            $this->item = new vShop_Item($id);
        } elseif (isset($_REQUEST['item_id'])) {
            $this->item = new vShop_Item($_REQUEST['item_id']);
        } elseif (isset($_REQUEST['item'])) {
            $this->item = new vShop_Item($_REQUEST['item']);
        } else {
            $this->item = new vShop_Item;
        }

        if (empty($this->dept)) {
            if (isset($this->dept->id)) {
                $this->loadDept($this->item->dept_id);
            } else {
                $this->loadDept();
                $this->item->dept_id = $this->dept->id;
            }
        }
    }


    public function loadTax($id=0)
    {
        Core\Core::initModClass('vshop', 'vShop_Tax.php');

        if ($id) {
            $this->tax = new vShop_Tax($id);
        } elseif (isset($_REQUEST['tax_id'])) {
            $this->tax = new vShop_Tax($_REQUEST['tax_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->tax = new vShop_Tax($_REQUEST['id']);
        } elseif (isset($_REQUEST['tax'])) {
            $this->tax = new vShop_Tax($_REQUEST['tax']);
        } else {
            $this->tax = new vShop_Tax;
        }
    }


    public function loadOrder($id=0)
    {
        Core\Core::initModClass('vshop', 'vShop_Order.php');

        if ($id) {
            $this->order = new vShop_Order($id);
        } elseif (isset($_REQUEST['order_id'])) {
            $this->order = new vShop_Order($_REQUEST['order_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->order = new vShop_Order($_REQUEST['id']);
        } elseif (isset($_REQUEST['order'])) {
            $this->order = new vShop_Order($_REQUEST['order']);
        } else {
            $this->order = new vShop_Order;
        }
    }


    public function loadPanel()
    {
        Core\Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('vshop-panel');
        $link = 'index.php?module=vshop&aop=menu';
        
        if (Current_User::allow('vshop', 'edit_items')) {
            $tags['new_dept'] = array('title'=>dgettext('vshop', 'New Dept'), 'link'=>$link);
        }
        $tags['list_depts'] = array('title'=>dgettext('vshop', 'List Depts'), 'link'=>$link);
        if (Current_User::allow('vshop', 'edit_items')) {
            $tags['new_item'] = array('title'=>dgettext('vshop', 'New Item'), 'link'=>$link);
        }
        $tags['list_items'] = array('title'=>dgettext('vshop', 'List Items'), 'link'=>$link);
        if (Current_User::allow('vshop', 'settings', null, null, true)) {
            $tags['settings'] = array('title'=>dgettext('vshop', 'Settings'), 'link'=>$link);
        }
        if (Current_User::allow('vshop', 'edit_orders')) {
            $tags['orders'] = array('title'=>dgettext('vshop', 'Orders'), 'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('vshop', 'Read me'), 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postDept()
    {
        $this->loadDept();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('vshop', 'You must give this department a title.');
        } else {
            $this->dept->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('vshop', 'You must give this department a description.');
        } else {
            $this->dept->setDescription($_POST['description']);
        }

        if (isset($_POST['file_id'])) {
            $this->dept->setFile_id((int)$_POST['file_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_dept');
            return false;
        } else {
            return true;
        }

    }


    public function postItem()
    {
        $this->loadItem();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('vshop', 'You must give this item a title.');
        } else {
            $this->item->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('vshop', 'You must give this item a description.');
        } else {
            $this->item->setDescription($_POST['description']);
        }

        if (isset($_POST['file_id'])) {
            $this->item->setFile_id((int)$_POST['file_id']);
        }

// old        if (!empty($_POST['price'])) {
        if (is_numeric($_POST['price'])) {
            $this->item->setPrice($_POST['price']);
        }

        isset($_POST['taxable']) ?
            $this->item->setTaxable(1) :
            $this->item->setTaxable(0) ;

// old        if (!empty($_POST['stock'])) {
        if (is_int($_POST['stock'])) {
            $this->item->setStock((int)$_POST['stock']);
        }

// old        if (!empty($_POST['weight'])) {
        if (is_numeric($_POST['weight'])) {
            $this->item->setWeight($_POST['weight']);
        }

// old        if (!empty($_POST['shipping'])) {
        if (is_numeric($_POST['shipping'])) {
            $this->item->setShipping($_POST['shipping']);
        }

        $this->item->setDept_id($_POST['dept_id']);

        if (empty($this->item->dept_id)) {
            $errors[] = dgettext('vshop', 'Fatal error: Cannot create item. Missing department id.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_item');
            return false;
        } else {
            return true;
        }

    }


    public function postTax()
    {
        $this->loadTax();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('vshop', 'You must give this tax a title.');
        } else {
            $this->tax->setTitle($_POST['title']);
        }

        if (empty($_POST['zones'])) {
            $errors[] = dgettext('vshop', 'You must give this tax at least one zone.');
        } else {
            $this->tax->setZones($_POST['zones']);
        }

        if (!empty($_POST['rate']) ) {
            $this->tax->setRate((int)$_POST['rate']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_tax');
            return false;
        } else {
            return true;
        }

    }


    public function postSettings()
    {

        isset($_POST['enable_sidebox']) ?
            Core\Settings::set('vshop', 'enable_sidebox', 1) :
            Core\Settings::set('vshop', 'enable_sidebox', 0);

        isset($_POST['sidebox_homeonly']) ?
            Core\Settings::set('vshop', 'sidebox_homeonly', 1) :
            Core\Settings::set('vshop', 'sidebox_homeonly', 0);

        if (!empty($_POST['mod_title'])) {
            Core\Settings::set('vshop', 'mod_title', strip_tags(Core\Text::parseInput($_POST['mod_title'])));
        } else {
            Core\Settings::reset('vshop', 'mod_title');
        }

        if (!empty($_POST['sidebox_text'])) {
            Core\Settings::set('vshop', 'sidebox_text', Core\Text::parseInput($_POST['sidebox_text']));
        } else {
            Core\Settings::set('vshop', 'sidebox_text', null);
        }

        if (isset($_POST['enable_files'])) {
            Core\Settings::set('vshop', 'enable_files', 1);
            if ( !empty($_POST['max_width']) ) {
                $max_width = (int)$_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 600 ) {
                    Core\Settings::set('vshop', 'max_width', $max_width);
                }
            }
            if ( !empty($_POST['max_height']) ) {
                $max_height = (int)$_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 600 ) {
                    Core\Settings::set('vshop', 'max_height', $max_height);
                }
            }
        } else {
            Core\Settings::set('vshop', 'enable_files', 0);
        }

        isset($_POST['use_inventory']) ?
            Core\Settings::set('vshop', 'use_inventory', 1) :
            Core\Settings::set('vshop', 'use_inventory', 0);

        isset($_POST['use_breadcrumb']) ?
            Core\Settings::set('vshop', 'use_breadcrumb', 1) :
            Core\Settings::set('vshop', 'use_breadcrumb', 0);

        if (!empty($_POST['checkout_inst'])) {
            Core\Settings::set('vshop', 'checkout_inst', Core\Text::parseInput($_POST['checkout_inst']));
        } else {
            Core\Settings::set('vshop', 'checkout_inst', null);
        }

        if (!empty($_POST['admin_email'])) {
            if (Core\Text::isValidInput($_POST['admin_email'], 'email')) {
                Core\Settings::set('vshop', 'admin_email', strip_tags(Core\Text::parseInput($_POST['admin_email'])));
            } else {
                $errors[] = dgettext('vshop', 'Check your e-mail address for formatting errors.');
            }
        } else {
            $errors[] = dgettext('vshop', 'You must provide an e-mail address.');
        }

        if (empty($_POST['pay_methods'])) {
            $errors[] = dgettext('vshop', 'You must select at least one payment method.');
        } else {
            Core\Settings::set('vshop', 'payment_methods', $_POST['pay_methods']);
        }

        if (!empty($_POST['order_message'])) {
            Core\Settings::set('vshop', 'order_message', Core\Text::parseInput($_POST['order_message']));
        } else {
            Core\Settings::set('vshop', 'order_message', null);
        }

        if (!empty($_POST['weight_unit'])) {
            Core\Settings::set('vshop', 'weight_unit', strip_tags(Core\Text::parseInput($_POST['weight_unit'])));
        } else {
            Core\Settings::reset('vshop', 'weight_unit');
        }

        if (!empty($_POST['currency'])) {
            if (eregi('[a-z]{3}', $_POST['currency'])) {
                Core\Settings::set('vshop', 'currency', strip_tags(Core\Text::parseInput(strtoupper($_POST['currency']))));
            } else {
                $errors[] = dgettext('vshop', 'Check your currency code for formatting errors.');
            }
        } else {
            Core\Settings::reset('vshop', 'currency');
        }

        if (!empty($_POST['currency_symbol'])) {
            Core\Settings::set('vshop', 'currency_symbol', strip_tags(Core\Text::parseInput(strtoupper($_POST['currency_symbol']))));
        } else {
            Core\Settings::reset('vshop', 'currency_symbol');
        }

        if (!empty($_POST['curr_symbol_pos'])) {
            Core\Settings::set('vshop', 'curr_symbol_pos', strip_tags(Core\Text::parseInput($_POST['curr_symbol_pos'])));
        } else {
            Core\Settings::reset('vshop', 'curr_symbol_pos');
        }

        isset($_POST['display_currency']) ?
            Core\Settings::set('vshop', 'display_currency', 1) :
            Core\Settings::set('vshop', 'display_currency', 0);

        Core\Settings::set('vshop', 'shipping_calculation', $_POST['shipping_calculation']);

        if (!empty($_POST['shipping_flat']) ) {
            Core\Settings::set('vshop', 'shipping_flat', (int)$_POST['shipping_flat']);
        }

        if (!empty($_POST['shipping_percent']) ) {
            Core\Settings::set('vshop', 'shipping_percent', (int)$_POST['shipping_percent']);
        }

        if (!empty($_POST['shipping_minimum'])) {
            $shipping_minimum = (float)$_POST['shipping_minimum'];
            if ($shipping_minimum > 0) {
                Core\Settings::set('vshop', 'shipping_minimum', $shipping_minimum);
            }
        } else {
            Core\Settings::reset('vshop', 'shipping_minimum');
        }

        if ( !empty($_POST['shipping_maximum']) ) {
            $shipping_maximum = (float)$_POST['shipping_maximum'];
            if ($shipping_maximum > 0) {
                Core\Settings::set('vshop', 'shipping_maximum', $shipping_maximum);
            }
        } else {
            Core\Settings::reset('vshop', 'shipping_maximum');
        }

        isset($_POST['secure_checkout']) ?
            Core\Settings::set('vshop', 'secure_checkout', 1) :
            Core\Settings::set('vshop', 'secure_checkout', 0);

        if (isset($_POST['secure_checkout'])) {
            if (!empty($_POST['secure_url'])) {
                if (Core\Text::isValidInput($_POST['secure_url'], 'url')) {
                    Core\Settings::set('vshop', 'secure_url', $_POST['secure_url']);
                } else {
                    $errors[] = dgettext('vshop', 'Check your secure URL for formatting errors.');
                }
            } else {
                $errors[] = dgettext('vshop', 'You must provide a secure URL.');
            }
        } else {
            Core\Settings::set('vshop', 'secure_url', null);
        }


        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (Core\Settings::save('vshop')) {
                return true;
            } else { 
                return falsel;
            }
        }

    }


    public function postOrder($admin=false)
    {
        $this->loadOrder();

        /* process the billing info */

        if (empty($_POST['first_name'])) {
            $errors[] = dgettext('vshop', 'You must provide a first name.');
        } else {
            $this->order->setFirst_name($_POST['first_name']);
        }

        if (empty($_POST['last_name'])) {
            $errors[] = dgettext('vshop', 'You must provide a last name.');
        } else {
            $this->order->setLast_name($_POST['last_name']);
        }

        if (empty($_POST['email'])) {
            $errors[] = dgettext('vshop', 'You must provide an e-mail address.');
        } else {
            if (!$this->order->setEmail($_POST['email'])) {
                $errors[] = dgettext('vshop', 'Check your e-mail address for formatting errors.');
            }
        }

        if (isset($_POST['phone'])) {
            $this->order->setPhone($_POST['phone']);
        } else {
            $this->order->phone = null;
        }

        if (empty($_POST['address_1'])) {
            $errors[] = dgettext('vshop', 'You must provide an address.');
        } else {
            $this->order->setAddress_1($_POST['address_1']);
        }

        if (isset($_POST['address_2'])) {
            $this->order->setAddress_2($_POST['address_2']);
        } else {
            $this->order->address_2 = null;
        }

        if (empty($_POST['city'])) {
            $errors[] = dgettext('vshop', 'You must provide a city.');
        } else {
            $this->order->setCity($_POST['city']);
        }

        if (empty($_POST['state'])) {
            $errors[] = dgettext('vshop', 'You must provide a prov/state.');
        } else {
            if (eregi('div_', $_POST['state'])) {
                $errors[] = dgettext('vshop', 'You must provide a prov/state.');
            } else {
                $this->order->setState($_POST['state']);
            }
        }

        if (empty($_POST['country'])) {
            $errors[] = dgettext('vshop', 'You must provide a country.');
        } else {
            $this->order->setCountry($_POST['country']);
        }

        if (empty($_POST['postal_code'])) {
            $errors[] = dgettext('vshop', 'You must provide a postal_code.');
        } else {
            $this->order->setPostal_code($_POST['postal_code']);
        }

        if (isset($_POST['comments'])) {
            $this->order->setComments($_POST['comments']);
        } else {
            $this->order->comments = null;
        }

        $this->order->setPay_method($_POST['pay_method']);

        if (!$admin) {
            /* process the cart */
    
            /* init the classes */
            Core\Core::initModClass('vshop', 'vShop_Cart.php');
            Core\Core::initModClass('vshop', 'vShop_Item.php');
            
            /* init the cart */
            $cart = vShop_Cart::CreateInstance();
            $cart_data = $cart->GetCart();
            $order_array = null;
    
            if (!empty($cart_data)) {
                
                /* init a few things */
                $order_array    = array();
                $total_items    = 0.00;
                $total_tax      = 0.00;
                $total_weight   = 0.00;
                $total_shipping = 0.00;
    
                /* see if any taxes apply to the zone */
                $db = new Core\DB('vshop_taxes');
                $db->addWhere('zones', '%' . Core\Text::parseInput($_POST['state']) . '%', 'LIKE');
                $taxes_result = $db->select();
                if ($taxes_result) {
                    foreach ($taxes_result as $tax) {
                        $rate = '.'.str_pad($tax['rate'], 2, "0", STR_PAD_LEFT);
                        $order_array['taxes'][$tax['id']]['name'] = $tax['title'];
                        $order_array['taxes'][$tax['id']]['rate'] = $rate;
                        $order_array['taxes'][$tax['id']]['tax_total'] = 0.00;
                    }
                }
    
                /* loop through the items in the cart */
                foreach ($cart_data as $id=>$val) {
                    
                    $qty = $cart_data[$id]['count'];
                    $item = new vShop_Item($id);
                    $subtotal = $item->price * $qty;
                    $item_tax = 0.00;
    
                    /* calculate the taxes for this item */
                    if ($item->taxable) {
                        if ($taxes_result) {
                            foreach ($taxes_result as $tax) {
                                $rate = '.'.str_pad($tax['rate'], 2, "0", STR_PAD_LEFT);
                                $taxes = $subtotal * $rate;
                                $order_array['items'][$id]['tax'][$tax['id']]['name'] = $tax['title'];
                                $order_array['items'][$id]['tax'][$tax['id']]['rate'] = $rate;
                                $order_array['items'][$id]['tax'][$tax['id']]['tax'] = $taxes;
                                $item_tax = $item_tax + $taxes;
                                $order_array['taxes'][$tax['id']]['tax_total'] += $taxes;
                            }
                        }
                    }
                        
                    /* increment our running totals */
                    $total_items = $total_items + $subtotal;
                    $total_tax = $total_tax + $item_tax;
                    $total_weight = $total_weight + $item->weight;

                    /* if shipping is by item */
                    if (Core\Settings::get('vshop', 'shipping_calculation') == 2) {
                        $total_shipping = $total_shipping + $item->shipping;
                        $order_array['items'][$id]['shipping'] = $item->shipping;
                    }
                    
                    /* build the item array */
                    $order_array['items'][$id]['id'] = $id;
                    $order_array['items'][$id]['price'] = $item->price;
                    $order_array['items'][$id]['qty'] = $qty;
                    $order_array['items'][$id]['subtotal'] = $subtotal;
                    $order_array['items'][$id]['taxable'] = $item->taxable;
                    $order_array['items'][$id]['weight'] = $item->weight;
                    $order_array['items'][$id]['item_tax'] = $item_tax;
                    $order_array['items'][$id]['name'] = $item->getTitle();
                }

                /* if shipping is free */
                if (Core\Settings::get('vshop', 'shipping_calculation') == 1) {
                    $shipping_calculation = dgettext('vshop', 'Free shipping');
                /* if shipping is by item */
                } elseif (Core\Settings::get('vshop', 'shipping_calculation') == 2) {
                    $shipping_calculation = dgettext('vshop', 'Flat rate per item');
                /* if shipping is by flat rate per order */
                } elseif (Core\Settings::get('vshop', 'shipping_calculation') == 3) {
                    $shipping_calculation = dgettext('vshop', 'Flat rate per order');
                    $total_shipping = Core\Settings::get('vshop', 'shipping_flat');
                /* if shipping is % by weight */
                } elseif (Core\Settings::get('vshop', 'shipping_calculation') == 4) {
                    $shipping_calculation = dgettext('vshop', '% rate * weight');
                    $rate = '.'.str_pad(Core\Settings::get('vshop', 'shipping_percent'), 2, "0", STR_PAD_LEFT);
                    $total_shipping = $total_weight * $rate;
                /* if shipping is % by price */
                } elseif (Core\Settings::get('vshop', 'shipping_calculation') == 5) {
                    $shipping_calculation = dgettext('vshop', '% rate * total');
                    $rate = '.'.str_pad(Core\Settings::get('vshop', 'shipping_percent'), 2, "0", STR_PAD_LEFT);
                    $total_shipping = $total_items * $rate;
                } 
                
                /* check for shipping min and max */
                if (Core\Settings::get('vshop', 'shipping_minimum') > 0) {
                    if ($total_shipping < Core\Settings::get('vshop', 'shipping_minimum')) {
                        $total_shipping = Core\Settings::get('vshop', 'shipping_minimum');
                        $shipping_calculation = sprintf(dgettext('vshop', 'Minimum shipping of %s'), number_format(Core\Settings::get('vshop', 'shipping_minimum'), 2, '.', ','));
                    } 
                }
                if (Core\Settings::get('vshop', 'shipping_maximum') > 0) {
                    if ($total_items > Core\Settings::get('vshop', 'shipping_maximum')) {
                        $total_shipping = 0.00;
                        $shipping_calculation = sprintf(dgettext('vshop', 'Free shipping on items over %s'), number_format(Core\Settings::get('vshop', 'shipping_maximum'), 2, '.', ','));
                    }
                }
                
                /* add our totals to the array */
                $order_array['total_items'] = $total_items;
                $order_array['total_tax'] = $total_tax;
                $order_array['total_weight'] = $total_weight;
                $order_array['shipping_calculation'] = $shipping_calculation;
                $order_array['total_shipping'] = $total_shipping;
                $order_array['total_grand'] = $total_items + $total_tax + $total_shipping;
            }
    
//            print_r($order_array); exit;
            $this->order->order_array = $order_array;
            $this->order->order_date = time();
            $this->order->update_date = time();
            $this->order->status = 1;
            $this->order->completed = 0;
            $this->order->cancelled = 0;
            $this->order->ip_address = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->order->update_date = time();
            $this->order->status = $_POST['status'];
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_order');
            return false;
        } else {
            return true;
        }

    }


    public function sendNotice($id, $to='customer', $type='new')
    {
        $this->loadOrder($id);
        $customer_name = $this->order->first_name . ' ' . $this->order->last_name;
        $customer_email = $this->order->email;
        $shop_name = Core\Settings::get('vshop', 'mod_title');
        $shop_email = Core\Settings::get('vshop', 'admin_email');
        $url = Core\Core::getHomeHttp();
        $message = null;

        if ($to == 'customer') {
            $sendto = sprintf('%s<%s>', $customer_name, $customer_email);
            $sendfrom = sprintf('%s<%s>', $shop_name, $shop_email);
            if ($type == 'new') {
                $subject = dgettext('vshop', 'Thank you for your order.');
                $message .= Core\Text::parseOutput(Core\Settings::get('vshop', 'order_message')) . "\n\n";
                $message .= sprintf(dgettext('vshop', 'This message from %s was sent from %s.'), $shop_name, $url) . "\n";
                $message .= dgettext('vshop', 'Thank You for placing your order with us. Order details are below.') . "\n\n";
                $message .= "\n\n";
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $this->forms = new vShop_Forms;
                $this->forms->vshop = & $this;
                $message .= $this->forms->orderDetails(false);
                $message .= "\n\n";
                $message .= sprintf(dgettext('vshop', 'Order placed from this IP address: %s.'), $this->order->ip_address) . "\n";
            } elseif ($type == 'update') {
                $subject = dgettext('vshop', 'Your order has been updated.');
                $message .= sprintf(dgettext('vshop', 'Your order id# %s at %s has been updated.'), $id, $shop_name) . "\n\n";
                $message .= "\n\n";
            } elseif ($type == 'status') {
                $subject = dgettext('vshop', 'Your order status has been updated.');
                $message .= sprintf(dgettext('vshop', 'Your order id# %s at %s has been updated. '), $id, $shop_name) . "\n\n";
                $message .= sprintf(dgettext('vshop', 'The order status has been changed to %s.'), $this->order->getStatus(true)) . "\n\n";
                $message .= "\n\n";
            } 
        } elseif ($to == 'admin') {
            $sendto = sprintf('%s<%s>', $shop_name, $shop_email);
            $sendfrom = sprintf('%s<%s>', $customer_name, $customer_email);
            if ($type == 'new') {
                $subject = dgettext('vshop', 'A new order has been placed.');
                $message .= sprintf(dgettext('vshop', 'A new order has been placed by %s at %s.'), $customer_name, $shop_name) . "\n\n";
                $message .= "\n\n";
                Core\Core::initModClass('vshop', 'vShop_Forms.php');
                $this->forms = new vShop_Forms;
                $this->forms->vshop = & $this;
                $message .= $this->forms->orderDetails(false);
                $message .= "\n\n";
                $message .= sprintf(dgettext('vshop', 'Order placed from this IP address: %s.'), $this->order->ip_address) . "\n";
            } elseif ($type == 'update') {
                $subject = dgettext('vshop', 'An order has been updated.');
                $message .= sprintf(dgettext('vshop', 'Order id# %s has been updated at %s.'), $id, $shop_name) . "\n\n";
                $message .= "\n\n";
            } elseif ($type == 'status') {
                $subject = dgettext('vshop', 'An order status has been updated.');
                $message .= sprintf(dgettext('vshop', 'Order id# %s at %s has been updated. '), $id, $shop_name) . "\n\n";
                $message .= sprintf(dgettext('vshop', 'The order status has been changed to %s.'), $this->order->getStatus(true)) . "\n\n";
                $message .= "\n\n";
            }
        }
//print($message); exit;
                $mail = new PHPWS_Mail;
        $mail->addSendTo($sendto);
        $mail->setSubject($subject);
        $mail->setFrom($sendfrom);
        $mail->setMessageBody($message);
//print_r($mail); exit;
        return $mail->send();
    }


    public function navLinks()
    {
        $links = array();
        if (!Core\Settings::get('vshop', 'use_breadcrumb')) {
            if (vShop::countDepts() !== 1) {
                $links[] = Core\Text::moduleLink(dgettext('vshop', 'List all departments'), 'vshop');
            }
        }
        if (isset($_SESSION['vShop_cart']) && $_SESSION['vShop_cart'] !== 'N;') { // && !is_null($_SESSION['vShop_cart'])
            if (!Core\Settings::get('vshop', 'secure_checkout')) {
                $links[] = '<a href="index.php?module=vshop&amp;uop=checkout"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/checkout.gif" width="12" height="12" alt="' . dgettext('vshop', 'Checkout') . '" title="' . dgettext('vshop', 'Checkout') . '" border="0" /> ' . dgettext('vshop', 'Checkout') . '</a>';
            } else {
                $links[] = '<a href="' . Core\Settings::get('vshop', 'secure_url') . 'index.php?module=vshop&amp;uop=checkout"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/checkout.gif" width="12" height="12" alt="' . dgettext('vshop', 'Checkout') . '" title="' . dgettext('vshop', 'Checkout') . '" border="0" /> ' . dgettext('vshop', 'Checkout') . '</a>';
            }
        }
        if (Current_User::allow('vshop', 'settings', null, null, true) && !isset($_REQUEST['aop'])){
            $links[] = Core\Text::moduleLink(dgettext('vshop', 'Settings'), "vshop",  array('aop'=>'menu', 'tab'=>'settings'));
        }
        
        return $links;
    }


    public function countDepts()
    {

        $db = new Core\DB('vshop_depts');
        $db->addColumn('id');
        $result = $db->count();
        
        return $result;
    }


    public function getSingleDept()
    {

        $db = new Core\DB('vshop_depts');
        $db->addColumn('id');
        $result = $db->select('row');
        
        return $result['id'];
    }



}
?>