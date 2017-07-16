<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Supremecreative_Giftregistry
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart operation observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Get customer giftregistry model instance
     *
     * @param   int $customerId
     * @return  Supremecreative_Giftregistry_Model_Giftregistry || false
     */
    protected function _getGiftregistry($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return Mage::getModel('giftregistry/giftregistry')->loadByCustomer($customerId, true);
    }

    /**
     * Check move quote item to giftregistry request
     *
     * @param   Varien_Event_Observer $observer
     * @return  Supremecreative_Giftregistry_Model_Observer
     */
    public function processCartUpdateBefore($observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo();
        $productIds = array();

        $giftregistry = $this->_getGiftregistry($cart->getQuote()->getCustomerId());
        if (!$giftregistry) {
            return $this;
        }

        /**
         * Collect product ids marked for move to giftregistry
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['giftregistry'])) {
                if ($item = $cart->getQuote()->getItemById($itemId)) {
                    $productId  = $item->getProductId();
                    $buyRequest = $item->getBuyRequest();

                    if (isset($itemInfo['qty']) && is_numeric($itemInfo['qty'])) {
                        $buyRequest->setQty($itemInfo['qty']);
                    }
                    $giftregistry->addNewItem($productId, $buyRequest);

                    $productIds[] = $productId;
                    $cart->getQuote()->removeItem($itemId);
                }
            }
        }

        if (!empty($productIds)) {
            $giftregistry->save();
            Mage::helper('giftregistry')->calculate();
        }
        return $this;
    }

    public function processAddToCart($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedGiftregistry = Mage::getSingleton('checkout/session')->getSharedGiftregistry();
        $messages = Mage::getSingleton('checkout/session')->getGiftregistryPendingMessages();
        $urls = Mage::getSingleton('checkout/session')->getGiftregistryPendingUrls();
        $giftregistryIds = Mage::getSingleton('checkout/session')->getGiftregistryIds();
        $singleGiftregistryId = Mage::getSingleton('checkout/session')->getSingleGiftregistryId();

        if ($singleGiftregistryId) {
            $giftregistryIds = array($singleGiftregistryId);
        }

        if (count($giftregistryIds) && $request->getParam('giftregistry_next')){
            $giftregistryId = array_shift($giftregistryIds);

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $giftregistry = Mage::getModel('giftregistry/giftregistry')
                        ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            } else if ($sharedGiftregistry) {
                $giftregistry = Mage::getModel('giftregistry/giftregistry')->loadByCode($sharedGiftregistry);
            } else {
                return;
            }


            $giftregistry->getItemCollection()->load();

            foreach($giftregistry->getItemCollection() as $giftregistryItem){
                if ($giftregistryItem->getId() == $giftregistryId)
                    $giftregistryItem->delete();
            }
            Mage::getSingleton('checkout/session')->setGiftregistryIds($giftregistryIds);
            Mage::getSingleton('checkout/session')->setSingleGiftregistryId(null);
        }

        if ($request->getParam('giftregistry_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            Mage::getSingleton('checkout/session')->setGiftregistryPendingUrls($urls);
            Mage::getSingleton('checkout/session')->setGiftregistryPendingMessages($messages);

            Mage::getSingleton('checkout/session')->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        }
    }

    /**
     * Customer login processing
     *
     * @param Varien_Event_Observer $observer
     * @return Supremecreative_Giftregistry_Model_Observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        Mage::helper('giftregistry')->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param Varien_Event_Observer $observer
     * @return Supremecreative_Giftregistry_Model_Observer
     */
    public function customerLogout(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('customer/session')->setGiftregistryItemCount(0);

        return $this;
    }

}
