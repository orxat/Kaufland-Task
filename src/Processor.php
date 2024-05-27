<?php
require_once 'vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\Statement;

class Processor
{
    private $file;
    private $type;
    private $db;
    private $logger;
    private $config;

    public function __construct($config, $db, $logger)
    {
        $this->config = $config;
        $this->file = $this->config->get('file', 'path');
        $this->type = $this->config->get('file', 'type');
        $this->db = $db;
        $this->logger = $logger;
    }

    public function process()
    {
        if (!file_exists($this->file)) {
            $this->logger->log($this->file . " not found: ");
            return;
        }
        if ($this->type != 'xml' && $this->type != 'csv') {
            $this->logger->log($this->type . " not correct: ");
            return;
        }
        if ($this->type === 'xml') {
            $xmlContent = file_get_contents($this->file);
            $xml = simplexml_load_string($xmlContent);
            foreach ($xml->item as $item) {
                try {
                    $this->db->insertItem([
                        'entity_id' => (int)$item->entity_id,
                        'category' => (string)$item->CategoryName,
                        'sku' => (string)$item->sku,
                        'name' => (string)$item->name,
                        'description' => (string)$item->description,
                        'shortdesc' => (string)$item->shortdesc,
                        'price' => (float)$item->price,
                        'link' => (string)$item->link,
                        'image' => (string)$item->image,
                        'brand' => (string)$item->Brand,
                        'rating' => (int)$item->Rating,
                        'caffeine_type' => (string)$item->CaffeineType,
                        'count' => (int)$item->Count,
                        'flavored' => (string)$item->Flavored,
                        'seasonal' => (string)$item->Seasonal,
                        'instock' => (string)$item->Instock,
                        'facebook' => (int)$item->Facebook,
                        'iskcup' => (int)$item->IsKCup,
                    ]);
                } catch (Exception $e) {
                    $this->logger->log("Error processing item: " . $e->getMessage());
                }
            }
        }
        if ($this->type === 'csv') {
            try {
                // Load the CSV document from a file path
                $csv = Reader::createFromPath($this->file, 'r');
                $csv->setHeaderOffset(0); // Setting it to 0 to find headers
                $headers = $csv->getHeader();

                // Define the expected headers
                $expectedHeaders = array(
                    "entity_id",
                    "category",
                    "sku",
                    "name",
                    "description",
                    "shortdesc",
                    "price",
                    "link",
                    "image",
                    "brand",
                    "rating",
                    "caffeine_type",
                    "count",
                    "flavored",
                    "seasonal",
                    "instock",
                    "facebook",
                    "iskcup"
                );

                // Compare the actual headers with the expected headers
                if ($headers !== $expectedHeaders) {

                    throw new Exception("CSV headers do not match the expected headers.");
                }

                $stmt = (new Statement())->offset(0);
                // will start fetching after the header


                $records = $stmt->process($csv);

                try {
                    foreach ($records as $item) {

                        $entity_id = isset($item['entity_id']) ? (int)$item['entity_id'] : null;
                        $category = isset($item['category']) ? (string)$item['category'] : null;
                        $sku = isset($item['sku']) ? (string)$item['sku'] : null;
                        $name = isset($item['name']) ? (string)$item['name'] : null;
                        $description = isset($item['description']) ? (string)$item['description'] : null;
                        $shortdesc = isset($item['shortdesc']) ? (string)$item['shortdesc'] : null;
                        $price = isset($item['price']) ? (float)$item['price'] : null;
                        $link = isset($item['link']) ? (string)$item['link'] : null;
                        $image = isset($item['image']) ? (string)$item['image'] : null;
                        $brand = isset($item['brand']) ? (string)$item['brand'] : null;
                        $rating = isset($item['rating']) ? (int)$item['rating'] : null;
                        $caffeine_type = isset($item['caffeine_type']) ? (string)$item['caffeine_type'] : null;
                        $count = isset($item['count']) ? (int)$item['count'] : null;
                        $flavored = isset($item['flavored']) ? (string)$item['flavored'] : null;
                        $seasonal = isset($item['seasonal']) ? (string)$item['seasonal'] : null;
                        $instock = isset($item['instock']) ? (string)$item['instock'] : null;
                        $facebook = isset($item['facebook']) ? (int)$item['facebook'] : null;
                        $iskcup = isset($item['iskcup']) ? (int)$item['iskcup'] : null;

                        $this->db->insertItem([
                            'entity_id' => $entity_id,
                            'category' => $category,
                            'sku' => $sku,
                            'name' => $name,
                            'description' => $description,
                            'shortdesc' => $shortdesc,
                            'price' => $price,
                            'link' => $link,
                            'image' => $image,
                            'brand' => $brand,
                            'rating' => $rating,
                            'caffeine_type' => $caffeine_type,
                            'count' => $count,
                            'flavored' => $flavored,
                            'seasonal' => $seasonal,
                            'instock' => $instock,
                            'facebook' => $facebook,
                            'iskcup' => $iskcup,
                        ]);
                    }
                } catch (Exception $e) {
                    $this->logger->log("Error processing item: " . $e->getMessage());
                }
            } catch (Exception $e) {

                $this->logger->log("Error processing CSV file: " . $e->getMessage());

                echo "An error occurred while processing the CSV file. Check log for details...";
            }
        }
    }
}
