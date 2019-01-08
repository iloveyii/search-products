<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\base\Model;

define('RELEVANCE_WEIGHTAGE', 5);
define('FOUND_TERMS_WEIGHTAGE', 20);
define('LEVENSHTEIN_WEIGHTAGE', 2);
define('SIMILARITY_WEIGHTAGE', 10);

/**
 * Product is the model
 *
 * @property id|null $name|string $category|string
 *
 */
class Product extends BaseObject
{
    public $id;
    public $name;
    public $category;

    public $similarity; // whole text similarity
    public $percentSimilarity;
    public $levenshtein;
    public $termsSimilarity; // words similarity

    public $relevance = null;

    private $searchText;
    private $terms;

    public function __construct($id, $name, $category)
    {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
    }

    private function negativeInSearchText()
    {
        foreach ($this->terms as $key=>$value) {
            if(strpos($value, '-') === 0 ) {
                $negativeValue = str_replace('-', '', $value);
                return $negativeValue;
            }
        }
        return false;
    }

    /**
     * This is a custom method to find the similarity of search text in product name and category
     * This counts the number of words in search text that matched to name and their str len
     * @return float|int
     */
    private function computeTermsSimilarity()
    {
        $regexTerms = '#(' . implode('|', $this->terms) . ')#i';
        // Negative in searchText - we assign a high negative value to similarity to filter it out
        $negative = $this->negativeInSearchText();
        if($negative !== false) {
            if(in_array(strtolower($negative) , explode(' ', strtolower($this->name)) ) ) {
                $this->termsSimilarity = -1000;
                return $this->termsSimilarity;
            }
        }

        $matchLength = 0;
        $foundTermsCount = 0;

        $matches = [];
        $nameMatches = [];
        $categoryMatches = [];
        $isMatched = preg_match_all($regexTerms, strtolower($this->name), $nameMatches, PREG_PATTERN_ORDER);
        $isMatched = preg_match_all($regexTerms, $this->category, $categoryMatches, PREG_PATTERN_ORDER);
        $matches = array_merge($nameMatches[0], $categoryMatches[0]);

        if(count($matches) > 1) {
            foreach ($matches as $matchedString) {
                $matchLength += strlen($matchedString);
            }
            $foundTermsCount = count($matches);
        }

        $this->termsSimilarity = ($matchLength * RELEVANCE_WEIGHTAGE) + ( $foundTermsCount * FOUND_TERMS_WEIGHTAGE );
        return $this->termsSimilarity;
    }

    public function computeRelevance($searchText, $terms)
    {
        $this->terms = $terms;
        $this->searchText = $searchText;

        /**
         * Compute the four similarities
         */
        $this->percentSimilarity = 0;
        $this->similarity =  similar_text($this->name, $this->searchText, $this->percentSimilarity);
        $this->percentSimilarity = floor($this->percentSimilarity);
        $this->levenshtein = levenshtein($this->name, $this->searchText);
        $this->termsSimilarity = $this->computeTermsSimilarity();

        $this->relevance = $this->termsSimilarity + $this->percentSimilarity
                           + floor(1 / $this->levenshtein * LEVENSHTEIN_WEIGHTAGE)
                           + ( $this->similarity * SIMILARITY_WEIGHTAGE );

        return $this->relevance;
    }
}
