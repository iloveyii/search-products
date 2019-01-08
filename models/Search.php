<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\helpers\ArrayHelper;

define('SORT_BY', 'relevance'); // @property: relevance
define('RESULTS_COUNT', 5);

/**
 * Product is the model
 *
 * @property id|null $name|string $category|string
 *
 */
class Search extends BaseObject
{
    public $models;
    private $searchText;

    public function __construct($models)
    {
        $this->models = $models;
    }

    private function findTerms($searchText)
    {
        $terms = explode(' ', strtolower($searchText) );
        return $terms;
    }


    /**
     * Search the products using the given search text, and sort the results
     * @param $searchText
     * @return mixed
     */
    public function doSearch($searchText)
    {
        $this->searchText = trim($searchText);
        $this->searchText = preg_replace('/\s\s+/', ' ', $this->searchText);

        $terms = $this->findTerms($this->searchText);

        foreach ($this->models as $model) {
            $model->computeRelevance($this->searchText, $terms);
        }
        $searchedProducts = $this->sortBy();

        // Filter negative terms
        $filtered = array_filter($searchedProducts, function (Product $product){
            return ($product->termsSimilarity > -1);
        });

        return $filtered;
    }


    /**
     * This function sorts the searched Products by different relevance values of the searched string
     * similarity - is the similar_text function value
     * percent - is the similar_text function percent value
     * levenshtein - is levenshtein distance between two strings
     * final - is our custom relevance value based on all the three above and number of terms found
     *
     * @param null $sortBy
     */
     private function sortBy($property = SORT_BY)
     {
        usort($this->models, function ($a, $b) {
            return $b->relevance <=> $a->relevance;
        });

        return $this->models;
     }
}
