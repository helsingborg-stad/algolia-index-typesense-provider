<?php

require_once './vendor/autoload.php';

if (!interface_exists('\AlgoliaIndex\Provider\AbstractProvider')) {
    interface AbstractProvider
    {
        public static function getIndex();
        public function setSettings(array $settings = []);
        public function search(string $query);
        public function clearObjects();
        public function deleteObject(string $objectId);
        public function deleteObjects(array $objectIds);
        public function saveObject(array $object, array $options = []);
        public function saveObjects(array $objects, array $options = []);
        public function getObjects(array $objectIds): array;
        public function shouldSplitRecord(): bool;
    }

    class_alias(
        AbstractProvider::class,
        '\AlgoliaIndex\Provider\AbstractProvider',
        true
    );
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $tag, $value)
    {
        return $value;
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string
    {
        return 'en_US';
    }
}
