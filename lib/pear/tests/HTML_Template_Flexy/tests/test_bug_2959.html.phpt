--TEST--
Template Test: bug_2959.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('bug_2959.html');

--EXPECTF--
===Compiling bug_2959.html===



===Compiled file: bug_2959.html===
-------------------------
Dear Administrator,

     -- Automatically Generated Email from XXXXXXXXXX --
<?php if ($t->hasmessage)  {?>

*NOTE*: <?php echo $t->message;?>.
<?php }?>

     O R D E R   A W A I T I N G   A U T H O R I S A T I O N
_________________________________________________________________

ORDER:

ID:       <?php echo htmlspecialchars($t->order->h);?>

*Edit*:   http://<?php echo htmlspecialchars($t->host);?>/admin/sales/orders/edit?id=<?php echo htmlspecialchars($t->order);?>

_________________________________________________________________

CUSTOMER DETAILS:

Name:     <?php echo $t->customer->firstname;?> <?php echo $t->customer->lastname;?>

Email:    mailto:<?php echo $t->customer->email;?>

*Edit*:   http://<?php echo htmlspecialchars($t->host);?>/admin/sales/customers/edit?id=<?php echo htmlspecialchars($t->customer->id);?>

_________________________________________________________________

SHIPPING TO:                                      

*<?php echo $t->deliveryname;?>*
<?php echo $t->deliveryaddress;?>

_________________________________________________________________

XXXXXXXXXXXXXXXXXXX:

*<?php echo $t->post->transactionstatus;?>*

<?php if ($t->post->dubious)  {?>
*WARNING*! - This may not be a bona fide order! - *WARNING*!

*Reason*: <?php echo $t->post->dubiousreason;?>

/Contact tech support/ before proceeding with this order!

<?php }?>
Total (currency?):            <?php echo $t->post->total;?>

Client ID (XXXXXXXX):         <?php echo $t->post->clientid;?>

Order ID:                     <?php echo $t->post->oid;?>

Charge type:                  <?php echo $t->post->chargetype;?>

Timestamp (from XXXXXXXX):    <?php echo $t->post->datetime;?>

VbV Status:                   <?php echo $t->post->ecistatus;?>

https://XXXXX.XXXXXXXX.XXXXXX.com/XXXXX/XXXXXXX/XXXXXx
_________________________________________________________________

<?php if ($this->options['strict'] || (is_array($t->orderlines)  || is_object($t->orderlines))) foreach($t->orderlines as $i => $o) {?>
<?php if ($i)  {?>

  - - -   - - -   - - -   - - -   - - -   - - -   - - -   - - -

<?php }?>
*PRODUCT*:<?php echo $o->productname;?>

          (<?php echo $o->producttypename;?>)

*Edit*:   http://<?php echo htmlspecialchars($t->host);?>/admin/catalog/products/edit?id=<?php echo urlencode($o->product);?>

FABRIC:   <?php echo $o->fabricname;?>

SIZE:     <?php echo $o->sizename;?>

Eurostop: <?php echo $o->altid;?>

QUANTITY: <?php echo $o->quantity;?>

Price ea: <?php echo $o->eachprice;?>

Tax each: <?php echo $o->eachtax;?>

Sub-total:                                       <?php echo $o->totalinctax;?>
<?php if ($o->isgift)  {?>

*GIFT MESSAGE* FOR THIS ITEM:
<?php echo htmlspecialchars($o->giftmessage);?><?php }?><?php }?>

_________________________________________________________________

Item total (ex tax):                             <?php echo $t->totals->itemstotal;?>

Item taxes:                                      <?php echo $t->totals->itemstax;?>

Shipping:                                       
<?php echo $t->totals->shippingcharges;?>

Tax on shipping:                                 <?php echo $t->totals->shippingtax;?>

*GRAND TOTAL*:                                   <?php echo $t->totals->grandtotal;?>

_________________________________________________________________

<?php echo $t->totals->itemsdiscountinfo;?>

<?php echo $t->totals->itemstaxinfo;?>

<?php echo $t->totals->shippinginfo;?>

<?php echo $t->totals->shippingtaxinfo;?>

_________________________________________________________________

blah blah

--------------------------

===With data file: bug_2959.html===
-------------------------
Dear Administrator,

     -- Automatically Generated Email from XXXXXXXXXX --

     O R D E R   A W A I T I N G   A U T H O R I S A T I O N
_________________________________________________________________

ORDER:

ID:       
*Edit*:   http:///admin/sales/orders/edit?id=
_________________________________________________________________

CUSTOMER DETAILS:

Name:      
Email:    mailto:
*Edit*:   http:///admin/sales/customers/edit?id=
_________________________________________________________________

SHIPPING TO:                                      

**

_________________________________________________________________

XXXXXXXXXXXXXXXXXXX:

**

Total (currency?):            
Client ID (XXXXXXXX):         
Order ID:                     
Charge type:                  
Timestamp (from XXXXXXXX):    
VbV Status:                   
https://XXXXX.XXXXXXXX.XXXXXX.com/XXXXX/XXXXXXX/XXXXXx
_________________________________________________________________


_________________________________________________________________

Item total (ex tax):                             
Item taxes:                                      
Shipping:                                       

Tax on shipping:                                 
*GRAND TOTAL*:                                   
_________________________________________________________________





_________________________________________________________________

blah blah

--------------------------