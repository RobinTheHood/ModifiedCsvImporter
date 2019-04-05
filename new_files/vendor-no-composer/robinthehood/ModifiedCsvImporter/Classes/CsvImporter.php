<?php
namespace RobinTheHood\ModifiedCsvImporter\Classes;

use RobinTheHood\ModifiedOrm\Core\Debug;
use RobinTheHood\ModifiedOrm\Models\Product;
use RobinTheHood\ModifiedOrm\Models\ProductDescription;
use RobinTheHood\ModifiedOrm\Models\ProductToCategory;
use RobinTheHood\ModifiedOrm\Models\Category;
use RobinTheHood\ModifiedOrm\Models\CategoryDescription;
use RobinTheHood\ModifiedOrm\Models\ProductTag;
use RobinTheHood\ModifiedOrm\Repositories\ProductRepository;
use RobinTheHood\ModifiedOrm\Repositories\CategoryRepository;
use RobinTheHood\ModifiedOrm\Repositories\CategoryDescriptionRepository;
use RobinTheHood\ModifiedOrm\Repositories\ProductToCategoryRepository;
use RobinTheHood\ModifiedOrm\Repositories\ShippingStatusRepository;
use RobinTheHood\ModifiedOrm\Repositories\ManufacturerRepository;
use RobinTheHood\ModifiedOrm\Repositories\ManufacturerInfoRepository;
use RobinTheHood\ModifiedOrm\Repositories\ProductTagOptionRepository;
use RobinTheHood\ModifiedOrm\Repositories\ProductTagValueRepository;
use RobinTheHood\ModifiedOrm\Repositories\ProductTagRepository;

use RobinTheHood\ModifiedCsvImporter\Classes\Task;

class CsvImporter
{
    protected $delimiter = ';';
    protected $csvEncodig = 'UTF-8';

    protected $categoryCache = [];
    protected $shippingStatusCache = [];
    protected $manufacturerCache = [];
    protected $productTagOptionCache = [];
    protected $productTagValueCache = [];

    protected $start;
    protected $end;

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function setCsvEncoding($csvEncoding)
    {
        $this->csvEncodig = $csvEncoding;
    }

    public function getTask()
    {
        if ($this->task) {
            return $this->task;
        }
        
        $this->task = new Task();
        $this->task->setId('001');
        return $this->task;
    }

    public function getFileLineCount($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while (!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }
        fclose($handle);
        return $linecount;
    }

    public function import($filePath, $start, $end = 0)
    {
        $task = $this->getTask();

        if ($end <= 0) {
            $end = $this->getFileLineCount($filePath);
        }

        $this->start = $start;
        $this->end = $end;


        $count = 0;

        $file = fopen($filePath, 'r');
        while (($row = fgetcsv($file, 0, $this->delimiter)) !== false) {
            if ($count++ < $start) {
                continue;
            }

            $row = array_map([$this, 'convert'], $row);
            $this->processRow($row, $count);
            $task->logValues();

            if ($count >= $end) {
                break;
            }
        }

        $task->setLogValues([
            'start' => $this->start,
            'end' => $this->end,
            'current' => $count,
            'status' => 'done'
        ]);
        $task->logValues();

        Debug::out('Done');
    }

    public function convert($str)
    {
        return iconv($this->csvEncodig, 'UTF-8', $str);
    }

    public function createOrGetCategoryHashed($categoryDescriptions, &$categoryCache)
    {
        $hash = $this->getCategoryPathHash($categoryDescriptions);
        $category = $categoryCache[$hash];
        if ($category) {
            //Debug::out('cache: ' . $hash);
            return $category;
        }
        Debug::out('Category no-cache: ' . $hash);
        $category = $this->createOrGetCategory($categoryDescriptions);
        $categoryCache[$hash] = $category;

        return $category;
    }

    public function getCategoryPathHash($categoryDescriptions)
    {
        $hash = '';
        foreach($categoryDescriptions as $categoryDescription) {
            $hash .= $categoryDescription->getName() . '_';
        }
        return $hash;
    }

    public function createOrGetCategory($categoryDescriptions)
    {
        $parentId = 0;
        for ($i=0; $i<count($categoryDescriptions); $i++) {
            $categoryDescription = $categoryDescriptions[$i];
            $name = $categoryDescription->getName();
            $languageId = $categoryDescription->getLanguageId();
            $category = $this->getCategoryByNameAndParentId($name, $parentId, $languageId);
            if (!$category) {
                break;
            }
            $parentId = $category->getId();
        }

        $categoryRepo = new CategoryRepository();
        $categoryDescriptionRepo = new CategoryDescriptionRepository();
        for ($n=$i; $n<count($categoryDescriptions); $n++) {
            $categoryDescription = $categoryDescriptions[$n];
            $category = new Category();
            $category->setParentId($parentId);
            $categoryId = $categoryRepo->insert($category, false);
            $categoryDescription->setCategoryId($categoryId);
            $categoryDescriptionRepo->insert($categoryDescription);
            $parentId = $categoryId;

            //Debug::out($categoryDescription);
        }

        return $category;
    }

    public function getCategoryByNameAndParentId($name, $parentId, $languageId)
    {
        $repo = new CategoryDescriptionRepository();
        $categoryDescriptions = $repo->getAllByName($name, $languageId);
        foreach($categoryDescriptions as $categoryDescription) {
            $category = $categoryDescription->getCategory();
            if ($category->getParentId() == $parentId) {
                return $category;
            }
        }
    }

    public function addProductToCategory($product, $category)
    {
        if ($product && $product->getId() > 0 && $category && $category->getId() > 0) {
            $productToCategory = new ProductToCategory();
            $productToCategory->setProductId($product->getId());
            $productToCategory->setCategoryId($category->getId());
            $repo = new ProductToCategoryRepository();
            $repo->insert($productToCategory);
            return $productToCategory;
        }
    }

    public function createOrGetShippingStatusHashed($shippingStatus, &$shippingStatusCache)
    {
        $hash = $shippingStatus->getName();
        $shippingStatusHashed = $shippingStatusCache[$hash];
        if ($shippingStatusHashed) {
            //Debug::out('ShippingStatus cache: ' . $hash);
            return $shippingStatusHashed;
        }
        Debug::out('ShippingStatus no-cache: ' . $hash);
        $shippingStatus = $this->createOrGetShippingStatus($shippingStatus);
        $shippingStatusCache[$hash] = $shippingStatus;
        return $shippingStatus;
    }

    public function createOrGetShippingStatus($shippingStatus)
    {
        $repo = new ShippingStatusRepository();
        $name = $shippingStatus->getName();
        $languageId = $shippingStatus->getLanguageId();
        $shippingStatusList = $repo->getAllByName($name, $languageId);
        if ($shippingStatusList[0]) {
            return $shippingStatusList[0];
        }

        $repo->insert($shippingStatus);
        //Debug::out($shippingStatus);
        return $shippingStatus;
    }

    public function setShippingStatusOfProduct($product, $shippingStatus)
    {
        if ($product && $shippingStatus) {
            $repo = new ProductRepository();
            $product->setShippingStatusId($shippingStatus->getShippingStatusId());
            $repo->update($product);
            return true;
            //Debug::out($product);
        }
        return false;
    }

    public function createOrGetManufacturerHashed($manufacturer, $manufacturerInfos, &$manufacturerCache)
    {
        $hash = $manufacturer->getName();
        $manufacturerHashed = $manufacturerCache[$hash];
        if ($manufacturerHashed) {
            //Debug::out('ShippingStatus cache: ' . $hash);
            return $manufacturerHashed;
        }
        Debug::out('Manufacturer no-cache: ' . $hash);
        $manufacturer = $this->createOrGetManufacturer($manufacturer, $manufacturerInfos);
        $manufacturerCache[$hash] = $manufacturer;
        return $manufacturer;
    }

    public function createOrGetManufacturer($manufacturer, $manufacturerInfos)
    {
        $repo = new ManufacturerRepository();
        $name = $manufacturer->getName();
        $manufacturers = $repo->getAllByName($name);
        if ($manufacturers[0]) {
            return $manufacturers[0];
        }
        $id = $repo->insert($manufacturer);

        $repo = new ManufacturerInfoRepository();
        foreach($manufacturerInfos as $manufacturerInfo) {
            $manufacturerInfo->setManufacturerId($id);
            $repo->insert($manufacturerInfo);
        }

        //Debug::out($shippingStatus);
        return $manufacturer;
    }

    public function setManufacturerOfProduct($product, $manufacturer)
    {
        if ($product && $manufacturer) {
            $repo = new ProductRepository();
            $product->setManufacturerId($manufacturer->getId());
            $repo->update($product);
            return true;
            //Debug::out($product);
        }
        return false;
    }

    public function createOrGetProductTagOptionHashed($productTagOption, &$productTagOptionCache)
    {
        $hash = $productTagOption->getName();
        $productTagOptionHashed = $productTagOptionCache[$hash];
        if ($productTagOptionHashed) {
            //Debug::out('ShippingStatus cache: ' . $hash);
            return $productTagOptionHashed;
        }
        Debug::out('ProductTagOption no-cache: ' . $hash);
        $productTagOption = $this->createOrGetProductTagOption($productTagOption);
        $productTagOptionCache[$hash] = $productTagOption;
        return $productTagOption;
    }

    public function createOrGetProductTagOption($productTagOption)
    {
        $repo = new ProductTagOptionRepository();
        $name = $productTagOption->getName();
        $languageId = $productTagOption->getLanguageId();
        $productTagOptions = $repo->getAllByName($name, $languageId);
        if ($productTagOptions[0]) {
            return $productTagOptions[0];
        }

        $repo->insert($productTagOption);
        return $productTagOption;
    }

    public function createOrGetProductTagValueHashed($productTagValue, &$productTagValueCache)
    {
        $hash = $productTagValue->getName() . '_' . $productTagValue->getProductTagOptionId();
        $productTagValueHashed = $productTagValueCache[$hash];
        if ($productTagValueHashed) {
            //Debug::out('ShippingStatus cache: ' . $hash);
            return $productTagValueHashed;
        }
        //Debug::out('ProductTagValue no-cache: ' . $hash);
        $productTagValue = $this->createOrGetProductTagValue($productTagValue);
        $productTagValueCache[$hash] = $productTagValue;
        return $productTagValue;
    }

    public function createOrGetProductTagValue($productTagValue)
    {
        $repo = new ProductTagValueRepository();
        $name = $productTagValue->getName();
        $languageId = $productTagValue->getLanguageId();
        $productTagValues = $repo->getAllByName($name, $languageId);
        foreach ($productTagValues as $obj) {
            if ($obj->getProductTagOptionId() == $productTagValue->getProductTagOptionId()) {
                return $obj;
            }
        }

        $repo->insert($productTagValue);
        return $productTagValue;
    }

    public function addTagToProduct($productTagOption, $productTagValue, $product)
    {
        if ($productTagOption && $productTagValue && $product) {
            $productTag = new ProductTag();
            $productTag->setProductTagOptionId($productTagOption->getProductTagOptionId());
            $productTag->setProductTagValueId($productTagValue->getProductTagValueId());
            $productTag->setProductId($product->getId());
            $repo = new ProductTagRepository();
            $repo->insert($productTag);
            return $productTag;
        }
    }
}
