<?php

namespace app\models;

use yii\base\BaseObject;

/**
 * Product is the model
 *
 * @property id|null $name|string $category|string
 *
 */
class ProductFactory extends BaseObject
{
    private $products = [];
    private $models = [];

    /**
     * ProductFactory constructor.
     * Works as a factory and adapter
     * @param $fileName
     */
    public function __construct($fileName)
    {
        $this->loadJsonFile($fileName);
        foreach ( $this->products as $product ) {
            $id = $product['produkt_id'];
            $name = $product['produkt_namn'];
            $category = $product['kategori_namn'];
            $this->models[] = new Product($id, $name, $category);
        }
    }

    /**
     * @property - models
     * @return array
     */
    public function getModels() {
        return $this->models;
    }
    /**
     * Load the given json file into array
     * @param $fileName
     */
    private function loadJsonFile($fileName) {
        try {
            $contents = file_get_contents($fileName);
            $this->products = json_decode( $contents, true);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
