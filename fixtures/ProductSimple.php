<?php

require_once 'Abstract.php';

/**
 * Class Mage_Shell_Fixtures_ProductSimple
 */
class Mage_Shell_Fixtures_ProductSimple extends Mage_Shell_Fixtures_Abstract
{
    /**
     * @return array|int|string
     * @throws \Varien_Exception
     * @throws \Exception
     */
    public function generate()
    {
        $generated = 0;

        $activeCategories = $this->getAllActiveCategoryIds();
        $activeStores = $this->getAllActiveStores();
        $images = $this->getAllFixtureImages();
        $productsToGenerate = $this->getProductsToGenerate();

        /** @var \Mage_Catalog_Model_Product $product */
        $productModel = Mage::getModel('catalog/product');

        foreach ($activeStores as $store) {
            $index = 0;
            while ($index <= $productsToGenerate) {
                $this->generateProduct($productModel, $store, $activeCategories, $images, $index);
            }

            $generated = $generated + $index;
        }

        return $generated;
    }

    /**
     * @param \Mage_Catalog_Model_Product $productModel
     * @param \Mage_Core_Model_Store      $store
     * @param array                       $activeCategories
     * @param array                       $images
     * @param int                         $index
     * @throws \Varien_Exception
     * @throws \Exception
     */
    public function generateProduct($productModel, $store, $activeCategories, $images, &$index)
    {
        $sku = 'simple-product-' . $index;

        $product = $productModel->loadByAttribute('sku', $sku);
        if (!$product) {
            $product = clone $productModel;

            $product
                ->setWebsiteIds(array($store->getWebsiteId()))
                ->setStoreId($store->getId())
                ->setAttributeSetId($product->getDefaultAttributeSetId())
                ->setTypeId('simple')
                ->setCreatedAt(strtotime('now'))
                ->setSku($sku)
                ->setName('Simple product ' . $index)
                ->setWeight(1.0000)
                ->setStatus(1)//product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(1)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                ->setPrice($this->randFloat(10, 600, 2))
                ->setDescription('Simple product description' . $index)
                ->setShortDescription('Simple product short description' . $index)
                ->setStockData(
                    array(
                        'use_config_manage_stock' => 1,
                        'is_in_stock'             => 1,
                        'qty'                     => 999
                    )
                )
                ->setCategoryIds($this->randArray($activeCategories, 5));

            $imagesPerProduct = (int)$this->getImagesPerProduct();
            if ($imagesPerProduct > 0) {
                $product->setMediaGallery(array('images' => array(), 'values' => array()));
                $randomImages = $this->randArray($images, $imagesPerProduct);

                foreach ($randomImages as $randomImage) {
                    $randomImg = 'media/catalog/fixtures/' . $randomImage;
                    $product->addImageToMediaGallery(
                        $randomImg, array('image', 'thumbnail', 'small_image'), false, false
                    );
                }
            }

            $product->save();
            $index++;
        }
    }

    /**
     * @return array|string
     */
    public function getImagesPerProduct()
    {
        return $this->getConfigValue('product-images')->asArray()['images-per-product'];
    }

    /**
     * @return array|string
     */
    public function getProductsToGenerate()
    {
        return $this->getConfigValue('simple_products')->asArray();
    }
}
