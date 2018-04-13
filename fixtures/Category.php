<?php

require_once 'Abstract.php';

class Mage_Shell_Fixtures_Category extends Mage_Shell_Fixtures_Abstract
{
    /**
     * @return array|int|string
     * @throws \Mage_Core_Model_Store_Exception
     * @throws \Varien_Exception
     */
    public function generate()
    {
        $generated = 0;

        foreach ($this->getAllActiveStores() as $store) {
            $parentId = Mage::app()->getStore($store->getId())->getRootCategoryId();

            /** @var \Mage_Catalog_Model_Category $parentCategory */
            $parentCategory = Mage::getModel('catalog/category')->load($parentId);
            $parentCategory->setTempLevel(array());

            $categoriesNumberOnLevel = abs(
                ceil(pow($this->getCategoryMaxNumber(), 1 / $this->getCategoryMaxNestingLevel()) - 2)
            );

            $categoryIndex = 1;

            $this->generateCategories(
                $parentCategory,
                1,
                $categoriesNumberOnLevel,
                $categoryIndex
            );

            $generated = $generated + $this->getCategoryMaxNumber();
        }

        return $generated;
    }

    /**
     * @param \Mage_Catalog_Model_Category $parentCategory
     * @param int                          $nestingLevel
     * @param int                          $categoriesNumberOnLevel
     * @param int                          $categoryIndex
     * @throws \Varien_Exception
     */
    public function generateCategories(
        $parentCategory,
        $nestingLevel,
        $categoriesNumberOnLevel,
        &$categoryIndex
    ) {
        $maxCategoriesNumberOnLevel = $nestingLevel === 1 ? $this->getCategoryMaxNumber() : $categoriesNumberOnLevel;
        for ($i = 0; $i < $maxCategoriesNumberOnLevel && $categoryIndex <= $this->getCategoryMaxNumber(); $i++) {
            $category = clone $parentCategory;
            $levelArr = (array)$category->getTempLevel();
            $levelArr[] = $i + 1;
            $category->setTempLevel($levelArr);
            $category->setId(null);
            $category->setName('Category ' . implode('.', $levelArr));
            $category->setUrlKey('category-' . implode('-', $levelArr));
            $category->setIsActive(1);
            $category->setDisplayMode('PRODUCTS');
            $category->setIsAnchor(1);
            $category->setPath($parentCategory->getPath());

            $category->save();

            $categoryIndex++;

            if ($nestingLevel < $this->getCategoryMaxNestingLevel()) {
                $this->generateCategories(
                    $category,
                    $nestingLevel + 1,
                    $categoriesNumberOnLevel,
                    $categoryIndex
                );
            }
        }
    }

    /**
     * @return array|string
     */
    public function getCategoryMaxNestingLevel()
    {
        return $this->getConfigValue('categories_nesting_level')->asArray();
    }

    /**
     * @return array|string
     */
    public function getCategoryMaxNumber()
    {
        return $this->getConfigValue('categories')->asArray();
    }
}
