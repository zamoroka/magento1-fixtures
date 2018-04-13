<?php

abstract class Mage_Shell_Fixtures_Abstract
{
    abstract public function generate();

    /**
     * @param $node
     *
     * @return \Varien_Simplexml_Element
     */
    public function getConfigValue($node)
    {

        $xmlPath = $this->getConfDir() . DS . 'config.xml';
        $xmlObj = new Varien_Simplexml_Config($xmlPath);

        return $xmlObj->getNode($node);
    }

    /**
     * @return string
     */
    public function getConfDir()
    {
        return dirname(__FILE__) . DS . 'etc';
    }

    /**
     * @return \Mage_Core_Model_Store[]
     */
    public function getAllActiveStores()
    {
        $stores = array();
        /** @var Mage_Core_Model_Website $website */
        foreach (Mage::app()->getWebsites() as $website) {
            /** @var \Mage_Core_Model_Store_Group $group */
            foreach ($website->getGroups() as $group) {
                $groupStores = $group->getStores();
                /** @var \Mage_Core_Model_Store $store */
                foreach ($groupStores as $store) {
                    if ($store->getIsActive()) {
                        $stores[] = $store;
                    }
                }
            }
        }

        return $stores;
    }

    /**
     * @return int[]
     */
    public function getAllActiveCategoryIds()
    {
        return Mage::getResourceModel('catalog/category_collection')
                   ->addAttributeToFilter('is_active', 1)
                   ->getColumnValues('entity_id');
    }

    /**
     * @param int $min
     * @param int $max
     * @param int $decimals
     * @return float|int
     */
    public function randFloat($min, $max, $decimals = 0)
    {
        $scale = pow(10, $decimals);

        $rand = mt_rand($min * $scale, $max * $scale) / $scale;

        return number_format((float)$rand, $decimals, '.', '');
    }

    /**
     * @param array $array
     * @param int   $amount
     * @return array
     */
    public function randArray($array, $amount = 1)
    {

        $keys = array_rand($array, $amount);

        $results = array();

        foreach ($keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * @return string
     */
    public function getFixturesMediaDir()
    {
        $dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'fixtures';
        $io = new Varien_Io_File();
        if (!$io->fileExists($dir, false)) {
            $io->mkdir($dir);
        }

        return $dir;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllFixtureImages()
    {
        $files = array();
        $dir = $this->getFixturesMediaDir();

        foreach (glob($dir . '/*.jpg') as $image) {
            $files[] = basename($image);
        }

        return $files;
    }
}
