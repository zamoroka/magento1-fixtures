<?php

require_once 'Abstract.php';

/**
 * Class Mage_Shell_Fixtures_Order
 */
class Mage_Shell_Fixtures_Order extends Mage_Shell_Fixtures_Abstract
{
    /**
     * @return array|int|string
     * @throws \Varien_Exception
     * @throws \Exception
     */
    public function generate()
    {
        $generated = 0;

        $activeStores = $this->getAllActiveStores();
        $ordersToGenerate = $this->getOrdersToGenerate();

        /** @var \Mage_Customer_Model_Resource_Customer_Collection $customerCollection */
        $customerCollection = Mage::getResourceModel('customer/customer_collection');
        $customerIds = $customerCollection->getAllIds();

//        echo print_r($customerIds, true);

//        $customerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        foreach ($activeStores as $store) {
            /** @var \Mage_Catalog_Model_Resource_Product_Collection $productCollection */
            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $productCollection->addStoreFilter($store);
            $productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));

            $index = 1;
            while ($index <= $ordersToGenerate) {
                $this->generateOrder($store, $productCollection, $customerIds, $index);
            }

            $generated = $generated + $index;
        }

        return $generated;
    }

    /**
     * @param \Mage_Core_Model_Store                          $store
     * @param \Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @param array                                           $customerIds
     * @param int                                             $index
     * @throws \Varien_Exception
     */
    public function generateOrder($store, $productCollection, $customerIds, &$index)
    {
        $customerId = $this->randArray($customerIds);
        $productIds = $this->randArray($productCollection->getAllIds(), $this->getOrderSimpleProductLimit());
        $websiteId = $store->getWebsiteId();
        $shippingMethod = 'flatrate_flatrate';
        $paymentMethod = 'checkmo';

        /** Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->setStoreId($store->getId());

        /** @var \Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId($websiteId)
                        ->load($customerId);

        $quote->assignCustomer($customer);
        $quote->setSendCconfirmation(1);

        foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $quote->addProduct($product, 1);
        }

        /** @var \Mage_Customer_Model_Address $customerAddress */
        $customerAddress = $customer->getDefaultBillingAddress();

        $address = array(
            'customer_address_id' => $customerAddress->getId(),
            'city'                => $customerAddress->getCity(),
            'country_id'          => $customerAddress->getCountryId(),
            'region'              => $customerAddress->getRegion(),
            'postcode'            => $customerAddress->getPostcode(),
        );

        $billingAddressData = $quote->getBillingAddress()->addData($address);
        $shippingAddressData = $quote->getShippingAddress()->addData($address);

        $shippingAddressData->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod($shippingMethod)
                            ->setPaymentMethod($paymentMethod);

        $quote->getPayment()->importData(array('method' => $paymentMethod));
        $quote->collectTotals();
        $quote->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $incrementId = $service->getOrder()->getRealOrderId();

        Mage::getSingleton('checkout/session')
            ->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId())
            ->clearHelperData();
        $index++;
    }

    /**
     * @return array|string
     */
    public function getOrdersToGenerate()
    {
        return $this->getConfigValue('orders')->asArray();
    }

    /**
     * @return array|string
     */
    public function getOrderSimpleProductCountFrom()
    {
        return (int)$this->getConfigValue('order_simple_product_count_from')->asArray();
    }

    /**
     * @return array|string
     */
    public function getOrderSimpleProductCountTo()
    {
        return (int)$this->getConfigValue('order_simple_product_count_to')->asArray();
    }

    /**
     * @return array|string
     */
    public function getOrderSimpleProductLimit()
    {
        return mt_rand($this->getOrderSimpleProductCountFrom(), $this->getOrderSimpleProductCountTo());
    }
}
