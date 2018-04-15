<?php

require_once 'Abstract.php';

/**
 * Class Mage_Shell_Fixtures_Image
 */
class Mage_Shell_Fixtures_Image extends Mage_Shell_Fixtures_Abstract
{
    /**
     * @return array|int|string
     * @throws \Mage_Core_Model_Store_Exception
     * @throws \Varien_Exception
     */
    public function generate()
    {
        $generated = 0;

        $path = $this->getFixturesMediaDir();
        $imagesToGenerate = $this->getImagesToGenerate();

        for ($i = 0; $i < $imagesToGenerate; $i++) {
            $this->generateImage($path, 300, 300, $i);
            $generated++;
        }

        return $generated;
    }

    /**
     * @return array|string
     */
    public function getImagesToGenerate()
    {
        return $this->getConfigValue('product-images')->asArray()['images-count'];
    }

    /**
     * Generates image from $data and puts its to /tmp folder
     *
     * @param string $path
     * @param int $width
     * @param int $height
     * @param int $index
     * @return string $imagePath
     */
    public function generateImage($path, $width, $height, $index)
    {
        $imageName = md5($index) . '.jpg';

        $binaryData = '';
        $data = str_split(sha1($imageName), 2);
        foreach ($data as $item) {
            $binaryData .= base_convert($item, 16, 2);
        }

        $binaryData = str_split($binaryData, 1);
        $image = imagecreate($width, $height);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $fgColor = imagecolorallocate($image, mt_rand(0, 230), mt_rand(0, 230), mt_rand(0, 230));
        $colors = array($fgColor, $bgColor);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        for ($row = 10; $row < ($height - 10); $row += 10) {
            for ($col = 10; $col < ($width - 10); $col += 10) {
                if (next($binaryData) === false) {
                    reset($binaryData);
                }

                imagefilledrectangle($image, $row, $col, $row + 10, $col + 10, $colors[current($binaryData)]);
            }
        }

        $imagePath = $path . DS . $imageName;
        imagejpeg($image, $imagePath, 100);

        return $imagePath;
    }
}
