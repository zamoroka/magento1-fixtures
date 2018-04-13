<?php

require_once 'Abstract.php';

class Mage_Shell_Fixtures_Customer extends Mage_Shell_Fixtures_Abstract
{
    /**
     * @return array|int|string
     * @throws \Mage_Core_Model_Store_Exception
     * @throws \Varien_Exception
     */
    public function generate()
    {
        $generated = 0;

        $customersToGenerate = $this->getCustomersToGenerate();
        /** @var \Mage_Customer_Model_Customer $customerModel */
        $customerModel = Mage::getModel('customer/customer');

        foreach ($this->getAllActiveStores() as $store) {
            $index = 1;

            while ($index <= $customersToGenerate) {
                $this->generateCustomer($customerModel, $store, $index);
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * @return array|string
     */
    public function getCustomersToGenerate()
    {
        return $this->getConfigValue('customers')->asArray();
    }

    /**
     * @param \Mage_Customer_Model_Customer $customerModel
     * @param \Mage_Core_Model_Store        $store
     * @param int                           $index
     * @throws \Varien_Exception
     * @throws \Exception
     */
    protected function generateCustomer($customerModel, $store, &$index)
    {
        $customer = clone $customerModel;

        /** @var \Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        $customerData = array(
            'firstname'     => $this->getRandomName(mt_rand(3, 6)),
            'lastname'      => $this->getRandomName(mt_rand(6, 9)),
            'email'         => $this->getRandomEmail(),
            'password'      => 'qwerty1234',
            'password_hash' => $coreHelper->getHash('qwerty1234'),
            'add_date'      => strtotime('now'),
            'birth_date'    => '1980-01-01',
            'gender'        => rand(0, 1),
        );

        $customerAddressData = array(
            'firstname' => $customerData['firstname'],
            'lastname'  => $customerData['lastname'],
            'street'    => array(
                '0' => '1571 Foster Ave',
                '1' => '1. Floor',
            ),

            'city'       => 'Memphis',
            'region_id'  => '',
            'region'     => '',
            'postcode'   => '38106',
            'country_id' => 'US', /* Croatia */
            'telephone'  => $this->getRandomNumber(13),
        );

        $customer->setWebsiteId($store->getWebsiteId());
        $customer->loadByEmail($customerData['email']);

        if (!$customer->getId()) {
            $customer->setStore($store)
                     ->setFirstname($customerData['firstname'])
                     ->setLastname($customerData['lastname'])
                     ->setEmail($customerData['email'])
                     ->setPasswordHash($customerData['password'])
                     ->setCreatedAt($customerData['add_date'])
                     ->setDob($customerData['birth_date'])
                     ->setGender($customerData['gender']);
            $customer->save();

            $customAddress = Mage::getModel('customer/address');

            $customAddress->setData($customerAddressData)
                          ->setCustomerId($customer->getId())
                          ->setIsDefaultBilling('1')
                          ->setIsDefaultShipping('1')
                          ->setSaveInAddressBook('1');
            $customAddress->save();
            $index++;
        }
    }
}
