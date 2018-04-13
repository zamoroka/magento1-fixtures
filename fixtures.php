<?php

require_once 'abstract.php';
require_once 'fixtures/Category.php';
require_once 'fixtures/Image.php';

/**
 * Class Mage_Shell_Fixtures
 */
class Mage_Shell_Fixtures extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('generate')) {
            $this->generateFixturesCategory();
            $this->generateFixturesImage();
        } else {
            $this->printMessage($this->usageHelp());
        }
    }

    /**
     * Generate categories
     */
    public function generateFixturesCategory()
    {
        try {
            $fixtures = new Mage_Shell_Fixtures_Category;

            $this->printMessage('generated categories: ' . $fixtures->generate());
        } catch (Exception $e) {
            $this->printMessage($e->getMessage());
        }
    }

    /**
     * Generate images
     */
    public function generateFixturesImage()
    {
        try {
            $fixtures = new Mage_Shell_Fixtures_Image();

            $this->printMessage('generated images: ' . $fixtures->generate());
        } catch (Exception $e) {
            $this->printMessage($e->getMessage());
        }
    }

    /**
     * @param string $message
     */
    public function printMessage($message)
    {
        echo $message . PHP_EOL;
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f fixtures.php -- [options]

  generate                      Generate data

USAGE;
    }
}

$shell = new Mage_Shell_Fixtures();
$shell->run();
