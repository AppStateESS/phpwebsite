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


/**
    * look for info in the docs folder on the following classes
    * all I have done is 
    * add vShop_ to the beginning of the class names
    * add vShop_ to the beginning of the session/cookie names
    * add function UpdateItems()
*/

/**
 * Project: Shopping Cart
 * Version: 1.0
 * File:   class.cart.php
 * Author:   Sergey Pimenov (sergey@pimenov.com.ua)
 * URL:   http://www.casp.com.ua
 * Note:  Class presents shopping cart 
 * License:  GPL 2
 * Lastupd:  06 sep 2008
 */

  define('CART_MODE_SESSION', 1);
  define('CART_MODE_COOKIES', 2);

  /**
   * Cart item class
  */
  class vShop_CartItem {               
    var $_itemID;
    var $_itemData;
    
    /**
     * Constructor of Cart Item Class
     * @param mixed $itemID Item ID
     * @param mixed $itemData Item data
     */    
    function __construct($itemID, $itemData){
      $this->_itemID = $itemID;
      $this->_itemData = $itemData;
    }
  }
  
  /**
   * Cart class
  */
  class vShop_Cart {
    private static $_instance; //Instance of cart
    private $_cart; // Cart Data
    private $_flushMode; //Cart store mode (COOKIES or SESSION)
    private $_cookie_lifetime; 
    
    public function SetCookieLifeTime($time = 86400){
      $this->_cookie_lifetime = time() + $time;
    }
    
    /**
     * Constructor of Cart Class
     * @param int $mode (1 or CART_MODE_SESSION || 2 or CART_MODE_COOKIES) Cart store mode
    */    
    private function __construct($mode){
      $this->SetFlushMode($mode);
      if ($this->_flushMode == CART_MODE_SESSION) {
        $this->_cart = unserialize( $_SESSION['vShop_cart'] );
      } else {
        if (isset($_COOKIE)) {
          $this->_cart = unserialize( $_COOKIE['vShop_cart'] );
        } else {
          $this->SetFlushMode(CART_MODE_SESSION);
          $this->_cart = unserialize( $_SESSION['vShop_cart'] );
        }
      }
    }
    
    /**
     * Desstructor of Cart Class
    */    
    function __destruct(){
      $this->FlushCart();
    }
    
    /**
     * function for safe create of Cart Instance
     * @param int $mode (1 or CART_MODE_SESSION || 2 or CART_MODE_COOKIES) Cart store mode
     * Use: $cart = Cart::CreateInstance(); 
    */    
    public function CreateInstance($mode = CART_MODE_SESSION){
      if (self::$_instance == null || !self::$_instance instanceof vShop_Cart) {
        self::$_instance = new vShop_Cart($mode);
      }
      return self::$_instance;
    }
    
    /**
     * function for set Cart store mode
     * @param int $mode (1 or CART_MODE_SESSION || 2 or CART_MODE_COOKIES) Cart store mode
    */        
    public function SetFlushMode($mode = CART_MODE_SESSION){
      $this->_flushMode = $mode;
    }
    
    /**
     * function for get Cart store mode
    */        
    public function GetFlushMode(){
      return $this->_flushMode;
    }
    
    /**
     * function for Add Item to Cart
     * @param mixed $itemID
     * @param mixed $itemData
     * @param int $count 
    */        
    public function AddItems($itemID, $itemData = null, $count = 1){
      if ((int)$count <= 0) return;
      if (isset($this->_cart[$itemID])) {
        $this->_cart[$itemID]['count'] += $count;
      } else {
        $this->_cart[$itemID]['count'] = $count;
        $this->_cart[$itemID]['data'] = new vShop_CartItem($itemID, $itemData);
      }
      $this->FlushCart();
    }
    
    /**
     * function for Del Item from Cart
     * @param mixed $itemID 
     * @param int $count
    */        
    public function RemoveItems($itemID, $count){
      if ($count < 0) return;
      if (isset($this->_cart[$itemID])) {
        if ($this->_cart[$itemID]['count'] > $count) {
          $this->_cart[$itemID]['count'] -= $count;
        } else {
          unset($this->_cart[$itemID]);
        }
      }
      $this->FlushCart();
    }

    /**
     * function for Updating Item qty in Cart
     * added by verdon vaillancourt
     * @param mixed $itemID 
     * @param int $count
    */        
    public function UpdateItems($itemID, $count){
      if ($count < 0) return;
      if (isset($this->_cart[$itemID])) {
        if ($count > 0) {
          $this->_cart[$itemID]['count'] = $count;
        } else {
          unset($this->_cart[$itemID]);
        }
      }
      $this->FlushCart();
    }

    /**
     * function for empties Cart
    */        
    public function EmptyCart(){
      unset($this->_cart);
      $this->FlushCart();
    }
    
    /**
     * function return Cart
    */        
    public function GetCart(){
      return $this->_cart;
    }
    
    /**
     * function return Items total count from Cart
    */        
    public function GetItemsCount(){
      $count_total = 0;
      foreach($this->_cart as $cart_position){
        $count_total += $cart_position['count'];
      }
      return (int)$count_total;
    }
    
    /**
     * function retrun Count Positions from Cart
    */        
    public function GetPositionsCount(){
      return (int)count($this->_cart);
    }
    
    /**
     * function for store Cart into SESSION or COOKIES
    */        
    public function FlushCart(){
      if ($this->_flushMode === CART_MODE_SESSION) {
        $_SESSION['vShop_cart'] = serialize( $this->GetCart() );
      } else {
        setcookie("vShop_cart", serialize($this->_cart), $this->_cookie_lifetime);
      }
    }
    
  }


?>