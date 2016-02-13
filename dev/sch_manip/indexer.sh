set -o verbose
php ../../shell/indexer.php --reindex catalog_product_price
php ../../shell/indexer.php --reindex catalog_product_flat
php ../../shell/indexer.php --reindex catalog_category_flat
php ../../shell/indexer.php --reindex catalog_category_product
php ../../shell/indexer.php --reindex catalogsearch_fulltext
php ../../shell/indexer.php --reindex cataloginventory_stock
php ../../shell/indexer.php --reindex tag_summary
php ../../shell/indexer.php --reindex catalogpermissions 
php ../../shell/indexer.php --reindex targetrule
php ../../shell/indexer.php --reindex catalog_url
