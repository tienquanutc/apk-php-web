<?php


namespace App\utils;


use Medoo\Medoo;

class GooglePlayCategory {

    public $categories;
    public $appCategories;
    public $gameCategories;

    public $categoryMap;
    public $categorySlugMap;

    /**
     * GooglePlayCategory constructor.
     * @param Medoo $db
     */
    public function __construct(Medoo $db) {
        $this->categories = $db->query(/** @lang sql */ "SELECT * FROM `category` JOIN category_type WHERE category.category_type_id = category_type.category_type_id")->fetchAll();
        $this->appCategories = array_filter($this->categories, function ($it) { return $it['category_type_id'] == 'APPLICATION' && $it['category_id'] != 'APPLICATION'; });
        $this->gameCategories = array_filter($this->categories, function ($it) { return $it['category_type_id'] == 'GAME' && $it['category_id'] != 'GAME'; });

        $this->categoryMap = array_reduce($this->categories, function ($map, $category) {
            $map[$category['category_id']] = $category;
            return $map;
        }, []);

        $this->categorySlugMap = array_reduce($this->categories, function ($map, $category) {
            $map[$category['category_slug']] = $category;
            return $map;
        }, []);
    }

    function getCategoryBySlug($category_slug) {
        return $this->categorySlugMap[$category_slug];
    }

    function getCategoryById($category_id) {
        return $this->categoryMap[$category_id];
    }

    function getGameCategories() {
        return $this->gameCategories;
    }

    function getAppCategories() {
        return $this->appCategories;
    }

}