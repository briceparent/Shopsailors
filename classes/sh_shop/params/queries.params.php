<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    /* PRODUCTS */
    'products_get_inactive' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `reference`,
            `shortDescription`,
            `image`,
            `price`,
            `hasVariants`,
            `variants_change_price`
            FROM
            ###shop_products
            WHERE `active` = FALSE;',
        'type' =>'get'
    ),
    
    'products_get_active' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `reference`,
            `shortDescription`,
            `image`,
            `price`,
            `hasVariants`,
            `variants_change_price`
            FROM
            ###shop_products
            WHERE `active` = TRUE;',
        'type' =>'get'
    ),
    'product_get_name' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `pack_id`,
            `pack_variant`,
            `reference`,
            `description`,
            `shortDescription`,
            `image`,
            `images`,
            `stock`,
            `active`,
            `price`,
            `taxRate`,
            `noShippingCost`,
            `hasVariants`,
            `variants_change_price`,
            `variants_change_ref`,
            `variants_change_stock`,
            `seo_titleBar`,
            `seo_metaDescription`,
            `date`,
            `layout`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_active' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `pack_id`,
            `pack_variant`,
            `reference`,
            `description`,
            `shortDescription`,
            `image`,
            `images`,
            `stock`,
            `active`,
            `price`,
            `taxRate`,
            `noShippingCost`,
            `hasVariants`,
            `variants_change_price`,
            `variants_change_ref`,
            `variants_change_stock`,
            `seo_titleBar`,
            `seo_metaDescription`,
            `date`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            AND `active` = 1
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_set_stock' => array(
        'query' => 'UPDATE ###shop_products
            SET
            `stock` = "{stock}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'product_switch_active_state' => array(
        'query' => 'UPDATE ###shop_products
            SET
            `active` = NOT `active`
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'product_get_active_state' => array(
        'query' => 'SELECT
            `active`
            FROM
            ###shop_products
            WHERE `id` = "{id}";',
        'type' =>'get'
    ),
    'product_exists' => array(
        'query' => 'SELECT
            COUNT(*) as count
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_name' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_create' => array(
        'query' => 'INSERT INTO ###shop_products
            (`date`)
            VALUES
            (NOW())',
        'type' =>'insert'
    ),
    'product_save' => array(
        'query' => 'UPDATE ###shop_products
            SET
            `name` = "{name}",
            `reference` = "{reference}",
            `description` = "{description}",
            `shortDescription` = "{shortDescription}",
            `image` = "{image}",
            `images` = "{images}",
            `stock` = "{stock}",
            `active` = "{active}",
            `layout` = "{layout}",
            `price` = "{price}",
            `taxRate` = "{taxRate}",
            `hasVariants` = "{hasVariants}",
            `variants_change_price` = "{variants_change_price}",
            `variants_change_ref` = "{variants_change_ref}",
            `variants_change_stock` = "{variants_change_stock}",
            `noShippingCost` = "{noShippingCost}",
            `seo_titleBar` = "{seo_titleBar}",
            `seo_metaDescription` = "{seo_metaDescription}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'product_unset_customProperties' => array(
        'query' => 'DELETE
            FROM
            ###shop_products_customProperties
            WHERE
            `product_id` = "{product_id}";',
        'type' =>'set'
    ),
    /* PRODUCTS IN CATEGORIES */
    'product_unset_categories' => array(
        'query' => 'DELETE
            FROM
            ###shop_products_categories
            WHERE
            `product_id` = "{product_id}";',
        'type' =>'set'
    ),
    'product_set_category' => array(
        'query' => 'INSERT INTO ###shop_products_categories
            (`product_id`,`category_id`)
            VALUES
            ("{product_id}","{category_id}")',
        'type' =>'insert'
    ),
    'product_get_categories' => array(
        'query' => 'SELECT
            `category_id`
            FROM
            ###shop_products_categories
            WHERE `product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'products_get_categories' => array(
        'query' => 'SELECT
            `category_id`
            FROM
            ###shop_products_categories;',
        'type' =>'get'
    ),
    'category_count_products' => array(
        'query' => 'SELECT
            COUNT(*) as count
            FROM
            ###shop_products_categories AS spc
            INNER JOIN ###shop_products AS sp ON spc.`product_id` = sp.`id`
            WHERE sp.`active` = TRUE
            AND spc.`category_id` = "{category_id}";',
        'type' =>'get'
    ),
    'category_get_products' => array(
        'query' => 'SELECT
            spc.`product_id`
            FROM
            ###shop_products_categories AS spc
            INNER JOIN ###shop_products AS sp ON spc.`product_id` = sp.`id`
            WHERE sp.`active` = TRUE
            AND spc.`category_id` = "{category_id}";',
        'type' =>'get'
    ),
    'category_get_products_part' => array(
        'query' => 'SELECT
            spc.`product_id`
            FROM
            ###shop_products_categories AS spc
            INNER JOIN ###shop_products AS sp ON spc.`product_id` = sp.`id`
            WHERE sp.`active` = TRUE
            AND spc.`category_id` = "{category_id}"
            AND spc.`product_id` != {exclude}
            LIMIT {start} , {length};',
        'type' =>'get'
    ),
    /* CATEGORIES */
    'category_get' => array(
        'query' => 'SELECT
            `id`,
            `type`,
            `name`,
            `image`,
            `description`,
            `shortDescription`,
            `seo_titleBar`,
            `seo_metaDescription`,
            `date`,
            `active`,
            `layout`
            FROM
            ###shop_categories
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'category_exists' => array(
        'query' => 'SELECT
            COUNT(*) as count
            FROM
            ###shop_categories
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'category_get_name' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###shop_categories
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'category_get_type' => array(
        'query' => 'SELECT
            `type`
            FROM
            ###shop_categories
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'categories_remove_product' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` + 1
            WHERE `id` IN (SELECT `category_id` as id FROM ###shop_products_categories WHERE `product_id` = "{id}")
            ;',
        'type' =>'set'
    ),
    'categories_remove_category' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` - 1
            WHERE `id` IN (SELECT `parent` AS id FROM ###shop_categories_order WHERE `id` = "{id}")
            ;',
        'type' =>'set'
    ),
    'category_add_product' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` - 1
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'category_remove_product' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` + 1
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'category_add_category' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` + 1
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'category_remove_category' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = `type` - 1
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'create_main_category' => array(
        'query' => 'INSERT INTO ###shop_categories
            (`id`,`name`,`description`,`shortDescription`,`image`,`seo_titleBar`,`seo_metaDescription`,`active`,`date`)
            VALUES
            (1,"{name}","{description}","{shortDescription}","/images/shared/default/defaultShopImage.png",0,0,1,NOW())',
        'type' =>'insert'
    ),
    'category_create' => array(
        'query' => 'INSERT INTO ###shop_categories
            (`date`)
            VALUES
            (NOW())',
        'type' =>'insert'
    ),
    'category_save' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `name` = "{name}",
            `description` = "{description}",
            `shortDescription` = "{shortDescription}",
            `image` = "{image}",
            `seo_titleBar` = "{seo_titleBar}",
            `seo_metaDescription` = "{seo_metaDescription}",
            `active` = {active},
            `layout` = {layout}
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'categories_get_list' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `image`
            FROM
            ###shop_categories;',
        'type' =>'get'
    ),
    'categories_get_containing_products' => array(
        'query' => 'SELECT
            `id`
            FROM
            ###shop_categories
            WHERE `type` <= 0;',
        'type' =>'get'
    ),
    'categories_get_containing_categories' => array(
        'query' => 'SELECT
            `id`
            FROM
            ###shop_categories
            WHERE `type` >= 0;',
        'type' =>'get'
    ),
    'categories_set_empty' => array(
        'query' => 'UPDATE ###shop_categories
            SET
            `type` = 0
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    /* CATEGORIES ORDER */
    'categories_get_all' => array(
        'query' => 'SELECT
            `id`,
            `deepness`
            FROM
            ###shop_categories_order
            ORDER BY `deepness` ASC;',
        'type' =>'get'
    ),
    'category_unset_parents' => array(
        'query' => 'DELETE
            FROM
            ###shop_categories_order
            WHERE
            `id` = "{id}";',
        'type' =>'set'
    ),
    'category_set_parent' => array(
        'query' => 'INSERT INTO ###shop_categories_order
            (`id`,`parent`,`deepness`)
            VALUES
            ("{id}","{parent}","{deepness}")',
        'type' =>'insert'
    ),
    'category_get_children' => array(
        'query' => 'SELECT
            `id`
            FROM
            ###shop_categories_order
            WHERE `parent` = "{parent}";',
        'type' =>'get'
    ),
    'category_get_children_part' => array(
        'query' => 'SELECT
            sco.`id`
            FROM
            ###shop_categories_order AS sco
            LEFT JOIN ###shop_categories AS sc ON sco.`id` = sc.`id`
            WHERE sco.`parent` = "{parent}"
            AND sc.`active` = TRUE
            LIMIT {start} , {length};',
        'type' =>'get'
    ),
    'category_get_shortest_parent' => array(
        'query' => 'SELECT
            sco.`parent`
            FROM
            ###shop_categories_order AS sco
            LEFT JOIN ###shop_categories AS sc ON sco.`id` = sc.`id`
            WHERE sco.`id` = "{id}"
            AND sc.`active` = TRUE
            ORDER BY sco.`deepness` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'category_get_shortest_parent_withInactive' => array(
        'query' => 'SELECT
            sco.`parent`
            FROM
            ###shop_categories_order AS sco
            LEFT JOIN ###shop_categories AS sc ON sco.`id` = sc.`id`
            WHERE sco.`id` = "{id}"
            ORDER BY sco.`deepness` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'category_get_parents' => array(
        'query' => 'SELECT
            `parent`,
            `deepness`
            FROM
            ###shop_categories_order
            WHERE `id` = "{id}";',
        'type' =>'get'
    ),
    'category_get_deepness' => array(
        'query' => 'SELECT
            `deepness`
            FROM
            ###shop_categories_order
            WHERE `id` = "{id}"
            ORDER BY `deepness` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'categories_set_deepness' => array(
        'query' => 'UPDATE ###shop_categories_order
            SET
            `deepness` = "{deepness}"
            WHERE `parent` = "{parent}";',
        'type' =>'set'
    ),

    /* DISCOUNTS */
    'discount_create' => array(
        'query' => 'INSERT INTO ###shop_discounts
            (`name`)
            VALUES
            (0)',
        'type' =>'insert'
    ),
    'discount_save' => array(
        'query' => 'UPDATE ###shop_discounts
            SET
            `name`="{name}",
            `when`="{when}",
            `from`="{from}",
            `to`="{to}",
            `monday`={monday},
            `tuesday`={tuesday},
            `wednesday`={wednesday},
            `thursday`={thursday},
            `friday`={friday},
            `saturday`={saturday},
            `sunday`={sunday},
            `quantity`="{quantity}",
            `type`="{type}",
            `percents`="{percents}",
            `monney`="{monney}",
            `gift_addMoney`="{gift_addMoney}",
            `gift_quantity`="{gift_quantity}",
            `gift_category`="{gift_category}",
            `title` = "{title}",
            `description_categories` = "{description_categories}",
            `description_product` = "{description_product}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'discount_get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `when`,
            `from`,
            `to`,
            `monday`,
            `tuesday`,
            `wednesday`,
            `thursday`,
            `friday`,
            `saturday`,
            `sunday`,
            `quantity`,
            `type`,
            `percents`,
            `monney`,
            `gift_addMoney`,
            `gift_quantity`,
            `gift_category`,
            `title`,
            `description_categories`,
            `description_product`
            FROM
            ###shop_discounts
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'discounts_get_all' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###shop_discounts;',
        'type' =>'get'
    ),
    'discounts_get_all_available' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###shop_discounts
            WHERE 
                ( 
                    ( `to` >= TO_DAYS(NOW()) )
                    OR
                    ( `when` = "always" )
                )
            ;',
        'type' =>'get'
    ),
    'discounts_get_all_for_cache' => array(
        'query' => 'SELECT
            `id`,`quantity`,`type`,`percents`,`monney`
            FROM
            ###shop_discounts
            WHERE (`type` = "percents" OR `type` = "monney")
            AND `{day}` = TRUE
            AND
                (
                    ( 
                        ( DATEDIFF(`from`,CURDATE()) <= 0)
                        AND
                        ( DATEDIFF(`to`,CURDATE()) >= 0)
                    )
                    OR
                    ( `when` = "always" )
                )
            ;',
        'type' =>'get'
    ),
    /* PRICES CACHE */
    'prices_cache_remove' => array(
        'query' => 'DELETE
            FROM
            ###shop_prices_cache;',
        'type' =>'set'
    ),
    'prices_cache_remove_for_product' => array(
        'query' => 'DELETE
            FROM
            ###shop_prices_cache
            WHERE
            `product` = {product};',
        'type' =>'set'
    ),
    'prices_cache_insert' => array(
        'query' => 'INSERT INTO ###shop_prices_cache
            (`product`,`variant`,`min_quantity`,`price`)
            VALUES
            ("{product}","{variant}","{min_quantity}",10000000);',
        'type' =>'insert'
    ),
    'prices_cache_update' => array(
        'query' => 'UPDATE ###shop_prices_cache
            SET
            `price` = {price},
            `discount_id` = {discount_id}
            WHERE `product` = "{product}"
            AND `variant` = "{variant}"
            AND `min_quantity` = "{min_quantity}"
            AND `price` > {price}
            LIMIT 1;',
        'type' =>'set'
    ),
    'product_get_smallest_price' => array(
        'query' => 'SELECT
            `price`,
            `discount_id`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            ORDER BY `price` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'prices_cache_get_quantities_for_products' => array(
        'query' => 'SELECT DISTINCT
            `min_quantity`
            FROM
            ###shop_prices_cache
            WHERE `product` IN ({products_list})
            ORDER BY `min_quantity` ASC;',
        'type' =>'get'
    ),
    'prices_cache_get_for_products' => array(
        'query' => 'SELECT
            `price`
            FROM
            ###shop_prices_cache
            WHERE `product` IN ({products_list})
            AND `min_quantity` = "{min_quantity}"
            ORDER BY `price`;',
        'type' =>'get'
    ),
    'product_get_price_noDiscount' => array(
        'query' => 'SELECT
            `price`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            AND `min_quantity` <= "{quantity}"
            AND `discount_id` = 0
            ORDER BY `price` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_normal_price' => array(
        'query' => 'SELECT
            `price`,
            `hasVariants`,
            `variants_change_price`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'variant_get_normal_price' => array(
        'query' => 'SELECT
            `price`
            FROM
            ###shop_products_variants
            WHERE `product_id` = "{product_id}"
            AND `variant_id` = "{variant_id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_price' => array(
        'query' => 'SELECT
            `price`,
            `discount_id`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            AND `min_quantity` <= "{quantity}"
            ORDER BY `price` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_prices' => array(
        'query' => 'SELECT
            `price`,
            `discount_id`,
            `min_quantity`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            ORDER BY `price` ASC;',
        'type' =>'get'
    ),
    'variant_get_price' => array(
        'query' => 'SELECT
            `price`,
            `discount_id`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            AND `variant` = "{variant}"
            AND `min_quantity` <= "{quantity}"
            ORDER BY `price` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    'variant_get_smallest_price' => array(
        'query' => 'SELECT
            `price`,
            `discount_id`
            FROM
            ###shop_prices_cache
            WHERE `product` = "{product}"
            AND `variant` = "{variant}"
            ORDER BY `price` ASC
            LIMIT 1;',
        'type' =>'get'
    ),
    /* PRICES CACHE FOR CATEGORIES */
    'categories_cache_remove_list' => array(
        'query' => 'DELETE
            FROM
            ###shop_categories_cached_datas
            WHERE `category` IN ({categories_list});',
        'type' =>'set'
    ),
    'categories_cache_remove' => array(
        'query' => 'DELETE
            FROM
            ###shop_categories_cached_datas;',
        'type' =>'set'
    ),
    'category_cache_remove' => array(
        'query' => 'DELETE
            FROM
            ###shop_categories_cached_datas
            WHERE `category`="{category}";',
        'type' =>'set'
    ),
    'category_cache_insert' => array(
        'query' => 'INSERT INTO ###shop_categories_cached_datas
            (`category`,`min_quantity`,`price`,`multiplePrices`)
            VALUES
            ("{category}","{min_quantity}","{price}","{multiplePrices}");',
        'type' =>'insert'
    ),
    
    

    /* DISCOUNTS ON CATEGORIES */
    'category_addDiscount' => array(
        'query' => 'INSERT INTO ###shop_discounts_categories
            (`category_id`,`discount_id`)
            VALUES
            ("{category_id}","{discount_id}");',
        'type' =>'insert'
    ),
    'category_removeDiscounts' => array(
        'query' => 'DELETE
            FROM
            ###shop_discounts_categories
            WHERE
            `category_id` = "{category_id}";',
        'type' =>'set'
    ),
    'category_getDiscounts' => array(
        'query' => 'SELECT
            `discount_id`
            FROM
            ###shop_discounts_categories
            WHERE `category_id` = "{category_id}"
            LIMIT 10;',
        'type' =>'get'
    ),
    'allCategories_getDiscounts' => array(
        'query' => 'SELECT
            `discount_id`,
            `category_id`
            FROM
            ###shop_discounts_categories;',
        'type' =>'get'
    ),

    /* DISCOUNTS ON PRODUCTS */
    'product_addDiscount' => array(
        'query' => 'INSERT INTO ###shop_discounts_products
            (`product_id`,`discount_id`)
            VALUES
            ("{product_id}","{discount_id}");',
        'type' =>'insert'
    ),
    'product_removeDiscounts' => array(
        'query' => 'DELETE
            FROM
            ###shop_discounts_products
            WHERE
            `product_id` = "{product_id}";',
        'type' =>'set'
    ),
    'product_getDiscounts' => array(
        'query' => 'SELECT
            `discount_id`
            FROM
            ###shop_discounts_products
            WHERE `product_id` = "{product_id}"
            LIMIT 10;',
        'type' =>'get'
    ),

    
    /* CUSTOM PROPERTIES */
    'customProperties_create' => array(
        'query' => 'INSERT INTO ###shop_customProperties
            (`name`)
            VALUES
            (0)',
        'type' =>'insert'
    ),
    'customProperty_save' => array(
        'query' => 'UPDATE ###shop_customProperties
            SET
            `name` = "{name}",
            `values` = "{values}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'customProperty_get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `values`
            FROM
            ###shop_customProperties
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'customProperty_get_name' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###shop_customProperties
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'customProperties_get_all' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `values`
            FROM
            ###shop_customProperties;',
        'type' =>'get'
    ),
    'customProperty_delete' => array(
        'query' => 'DELETE FROM ###shop_customProperties
            WHERE `id` = "{id}";',
        'type' =>'delete'
    ),
    'customProperty_checkUseCases' => array(
        'query' => 'SELECT
            spc.`product_id`,
            sp.`name`
            FROM ###shop_products_customProperties AS spc
            LEFT JOIN ###shop_products as sp
            ON spc.`product_id` = sp.`id`
            WHERE `customProperty_id` = "{customProperty_id}"
            ;',
        'type' =>'get'
    ),
    // PRODUCT'S CUSTOM PROPERTIES
    'product_get_customProperties' => array(
        'query' => 'SELECT
            `customProperty_id`,
            `customProperty_value`
            FROM
            ###shop_products_customProperties
            WHERE `product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'product_get_customProperties_withStructure' => array(
        'query' => 'SELECT
            spc.`customProperty_id`,
            spc.`customProperty_value`,
            sc.`name`
            FROM
            ###shop_products_customProperties AS spc
            INNER JOIN ###shop_customProperties AS sc
            ON spc.`customProperty_id` = sc.`id`
            WHERE spc.`product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'product_get_customProperty' => array(
        'query' => 'SELECT
            `customProperty_value`
            FROM
            ###shop_products_customProperties
            WHERE `product_id` = "{product_id}"
            AND `customProperty_id` = "{customProperty_id}";',
        'type' =>'get'
    ),
    'product_set_customProperty' => array(
        'query' => 'INSERT INTO ###shop_products_customProperties
            (`product_id`,`customProperty_id`,`customProperty_value`)
            VALUES
            ("{product_id}","{customProperty_id}","{customProperty_value}")',
        'type' =>'insert'
    ),
    'product_unset_customProperties' => array(
        'query' => 'DELETE
            FROM
            ###shop_products_customProperties
            WHERE
            `product_id` = "{product_id}";',
        'type' =>'set'
    ),
    // PRODUCT'S VARIANTS
    'variants_checkCPUseCases' => array(
        'query' => 'SELECT
            spv.`product_id`,
            sp.`name`
            FROM ###shop_products_variants AS spv
            LEFT JOIN ###shop_products as sp
            ON spv.`product_id` = sp.`id`
            WHERE `customProperties` LIKE "{customProperty_id}:%"
            OR `customProperties` LIKE "%|{customProperty_id}:%"
            ;',
        'type' =>'get'
    ),
    'variant_get_by_cp' => array(
        'query' => 'SELECT
            spv.`variant_id`
            FROM
            ###shop_products_variants AS spv
            INNER JOIN ###shop_products AS sp ON sp.`id` = spv.`product_id`
            WHERE sp.`hasVariants` AND
            spv.`product_id` = "{product_id}" AND
            spv.`customProperties` = "{cp}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'product_get_variant_if_any' => array(
        'query' => 'SELECT
            spv.`variant_id`,
            spv.`stock`,
            spv.`price`,
            spv.`ref`,
            spv.`customProperties`
            FROM
            ###shop_products_variants AS spv
            INNER JOIN ###shop_products AS sp ON sp.`id` = spv.`product_id`
            WHERE sp.`hasVariants` AND
            spv.`product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'product_get_variants' => array(
        'query' => 'SELECT
            `variant_id`,
            `stock`,
            `price`,
            `ref`,
            `customProperties`
            FROM
            ###shop_products_variants
            WHERE `product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'product_get_variant' => array(
        'query' => 'SELECT
            `product_id`,
            `variant_id`,
            `stock`,
            `price`,
            `ref`,
            `customProperties`
            FROM
            ###shop_products_variants
            WHERE `product_id` = "{product_id}"
            AND `variant_id` = "{variant_id}";',
        'type' =>'get'
    ),
    'product_set_variant' => array(
        'query' => 'INSERT INTO ###shop_products_variants
            (`product_id`,`variant_id`,`stock`,`price`,`ref`,`customProperties`)
            VALUES
            ("{product_id}","{variant_id}","{stock}","{price}","{ref}","{customProperties}");',
        'type' =>'insert'
    ),
    'product_unset_variants' => array(
        'query' => 'DELETE
            FROM
            ###shop_products_variants
            WHERE
            `product_id` = "{product_id}";',
        'type' =>'set'
    ),
    'product_has_variant' => array(
        'query' => 'SELECT
            spv.`variant_id` as hasVariants
            FROM
            ###shop_products AS sp
            LEFT JOIN ###shop_products_variants AS spv
            ON sp.`id` = spv.`product_id`
            WHERE sp.`id` = "{product_id}"
            AND sp.`hasVariants` = TRUE
            ORDER BY spv.`variant_id` DESC
            LIMIT 1;',
        'type' =>'get'
    ),

    /* CARTS */
    'carts_create' => array(
        'query' => 'INSERT INTO ###shop_carts
            (`user`,`name`,`date`)
            VALUES
            ("{user}","{name}",NOW());',
        'type' =>'insert'
    ),
    'carts_delete' => array(
        'query' => 'DELETE
            FROM
            ###shop_carts
            WHERE
            `id` = "{id}"
            AND `user` = "{user}";',
        'type' =>'count'
    ),
    'carts_list' => array(
        'query' => 'SELECT
            `id`,`name`,`date`
            FROM
            ###shop_carts
            WHERE `user` = "{user}"
            ORDER BY `date` DESC;',
        'type' =>'get'
    ),
    'carts_getOne' => array(
        'query' => 'SELECT
            `id`,`name`,`date`
            FROM
            ###shop_carts
            WHERE
            `id` = "{id}"
            AND `user` = "{user}"
            LIMIT 1;',
        'type' =>'get'
    ),

    /* CART CONTENTS*/
    'cart_add_content' => array(
        'query' => 'INSERT INTO ###shop_cart_contents
            (`cart_id`,`product_id`,`variant_id`,`class`,`quantity`)
            VALUES
            ("{cart_id}","{product_id}","{variant_id}","{class}","{quantity}");',
        'type' =>'insert'
    ),
    'cart_delete_content' => array(
        'query' => 'DELETE
            FROM
            ###shop_cart_contents
            WHERE
            `cart_id` = "{cart_id}";',
        'type' =>'set'
    ),
    'cart_get_contents' => array(
        'query' => 'SELECT
            `product_id`,`variant_id`,`class`,`quantity`
            FROM
            ###shop_cart_contents
            WHERE `cart_id` = "{cart_id}";',
        'type' =>'get'
    ),

    /* Packs */
    'packs_get_active' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###shop_packs
            WHERE `active` = TRUE;',
        'type' =>'get'
    ),
    'packs_get_inactive' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###shop_packs
            WHERE `active` = FALSE;',
        'type' =>'get'
    ),
    'pack_get_product' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `pack_id`,
            `pack_variant`,
            `reference`,
            `stock`,
            `price`,
            `hasVariants`,
            `variants_change_price`,
            `variants_change_stock`,
            `noShippingCost`
            FROM
            ###shop_products
            WHERE `pack_id` = "{pack_id}"
            AND `pack_variant` = "{pack_variant}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'pack_get_max_variant' => array(
        'query' => 'SELECT
            MAX(`variant_id`) AS max
            FROM
            ###shop_products_variants
            WHERE `product_id` = "{product_id}";',
        'type' =>'get'
    ),
    'pack_get_variant' => array(
        'query' => 'SELECT
            `product_id`,
            `variant_id`,
            `stock`,
            `price`,
            `ref`,
            `customProperties`
            FROM
            ###shop_products_variants
            WHERE `product_id` = "{product_id}"
            AND `customProperties` = "{customProperties}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'pack_insert_variant' => array(
        'query' => 'INSERT INTO ###shop_products_variants
            (`product_id`,`variant_id`,`stock`,`price`,`ref`,`customProperties`,`description`)
            VALUES
            ("{product_id}","{variant_id}","{stock}","{price}","{ref}","{customProperties}","{description}");',
        'type' =>'insert'
    ),

    'pack_create' => array(
        'query' => 'INSERT INTO ###shop_packs
            (`name`)
            VALUES
            ("0");',
        'type' =>'insert'
    ),
    'pack_product_save' => array(
        'query' => 'UPDATE ###shop_products
            SET
            `name` = "{name}",
            `pack_id` = "{pack_id}",
            `pack_variant` = "{pack_variant}",
            `reference` = "{reference}",
            `description` = "0",
            `shortDescription` = "{shortDescription}",
            `image` = "",
            `images` = "",
            `stock` = "{stock}",
            `active` = TRUE,
            `price` = "{price}",
            `taxRate` = "{taxRate}",
            `hasVariants` = "{hasVariants}",
            `variants_change_price` = "{variants_change_price}",
            `variants_change_ref` = FALSE,
            `variants_change_stock` = "{variants_change_stock}",
            `noShippingCost` = "{noShippingCost}",
            `seo_titleBar` = "0",
            `seo_metaDescription` = "0"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'pack_save' => array(
        'query' => 'UPDATE ###shop_packs
            SET
            `name` = "{name}",
            `active` = "{active}",
            `layout` = "{layout}",
            `cost` = "{cost}",
            `add` = "{add}",
            `total` = "{total}",
            `seo_titleBar` = "{seo_titleBar}",
            `seo_metaDescription` = "{seo_metaDescription}",
            `taxRate` = "{taxRate}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'pack_deActivate' => array(
        'query' => 'UPDATE ###shop_packs
            SET
            `active` = FALSE
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'pack_remove_elements' => array(
        'query' => 'DELETE
            FROM
            ###shop_packs_contents
            WHERE
            `pack_id` = "{pack_id}";',
        'type' =>'count'
    ),
    'pack_add_element' => array(
        'query' => 'INSERT into ###shop_packs_contents
            (
                `pack_id`,
                `type`,
                `element_type`,
                `element_id`,
                `quantity`,
                `free`,
                `name`,
                `number`
            ) VALUES (
                "{pack_id}",
                "{type}",
                "{element_type}",
                "{element_id}",
                "{quantity}",
                "{free}",
                "{name}",
                "{number}"
            );',
        'type' =>'set'
    ),
    'pack_get_product_id' => array(
        'query' => 'SELECT
            `product_id`
            FROM
            ###shop_packs
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'pack_get' => array(
        'query' => 'SELECT
            `id`,
            `product_id`,
            `name`,
            `active`,
            `cost`,
            `add`,
            `total`,
            `seo_titleBar`,
            `seo_metaDescription`,
            `taxRate`,
            `layout`
            FROM
            ###shop_packs
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'pack_get_content' => array(
        'query' => 'SELECT
            `type`,
            `element_type`,
            `element_id`,
            `quantity`,
            `free`,
            `number`,
            `name`
            FROM
            ###shop_packs_contents
            WHERE `pack_id` = "{pack_id}"
            AND `number` = "{number}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'pack_get_contents_active' => array(
        'query' => 'SELECT
            `type`,
            `element_type`,
            `element_id`,
            `quantity`,
            `free`,
            `number`,
            `name`
            FROM
            ###shop_packs_contents
            WHERE `pack_id` = "{pack_id}"
            ORDER BY `number` ASC;',
        'type' =>'get'
    ),
    'pack_get_contents' => array(
        'query' => 'SELECT
            sp.`id`,
            sp.`active`,
            sp.`cost`,
            sp.`add`,
            sp.`total`,
            spc.`type`,
            spc.`element_type`,
            spc.`element_id`,
            spc.`quantity`,
            spc.`free`,
            spc.`number`,
            spc.`name`,
            sp.`taxRate`
            FROM
            ###shop_packs AS sp
            LEFT JOIN ###shop_packs_contents AS spc
                ON sp.`id` = spc.`pack_id`
            WHERE sp.`id` = "{id}"
            ORDER BY spc.`number` ASC;',
        'type' =>'get'
    ),
    'pack_get_contents_by_categories_or_product' => array(
        'query' => 'SELECT
            sp.`id`,
            spc.`name`
            FROM
            ###shop_packs AS sp
            LEFT JOIN ###shop_packs_contents AS spc
                ON sp.`id` = spc.`pack_id`
            WHERE sp.`active` = TRUE
            AND spc.`type` = "main"
            AND (
                spc.`element_type` = "category"
                AND spc.`element_id` IN( {categories} )
            ) OR (
                spc.`element_type` = "product"
                AND spc.`element_id` = "{product}"
            )
            ORDER BY spc.`number` ASC;',
        'type' =>'get'
    ),
    'pack_get_contents_i18ns' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###shop_packs_contents
            WHERE `pack_id` = "{pack_id}";',
        'type' =>'get'
    ),
    
    /* LAYOUTS */
    
    'layout_create' => array(
        'query' => 'INSERT into ###shop_layouts
            (
                `name`,
                `top`,
                `bottom`
            ) VALUES (
                "{name}",
                "{top}",
                "{bottom}"
            );',
        'type' =>'insert'
    ),
    'layout_create_first' => array(
        'query' => 'INSERT into ###shop_layouts
            (
                `id`,
                `name`,
                `top`,
                `bottom`
            ) VALUES (
                "{id}",
                "{name}",
                "{top}",
                "{bottom}"
            );',
        'type' =>'insert'
    ),
    'layout_get' => array(
        'query' => 'SELECT
            `id`,`name`,`top`,`bottom`
            FROM
            ###shop_layouts
            WHERE `id` = "{id}";',
        'type' =>'get'
    ),
    'layouts_get_all' => array(
        'query' => 'SELECT
            `id`,`name`,`top`,`bottom`
            FROM
            ###shop_layouts
            ORDER BY `id` ASC;',
        'type' =>'get'
    ),
    'layout_update' => array(
        'query' => 'UPDATE ###shop_layouts
            SET
            `name` = "{name}",
            `top` = "{top}",
            `bottom` = "{bottom}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),


    /* CREATION OF THE TABLES */
    'create_table_1' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `name` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'create_table_2' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_cart_contents` (
  `cart_id` int(11) NOT NULL,
  `product_id` varchar(128) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `class` varchar(32) NOT NULL,
  `quantity` int(11) unsigned NOT NULL,
  UNIQUE KEY `cart_id` (`cart_id`,`product_id`,`variant_id`,`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_3' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL COMMENT \'Defines whether they contain products, categories, or nothing for the moment\',
  `name` bigint(20) NOT NULL COMMENT \'i18n id\',
  `image` varchar(256) NOT NULL,
  `shortDescription` bigint(20) NOT NULL COMMENT \'i18n id\',
  `description` bigint(20) NOT NULL COMMENT \'i18n id\',
  `seo_titleBar` bigint(20) NOT NULL COMMENT \'i18n id\',
  `seo_metaDescription` bigint(20) NOT NULL COMMENT \'i18n id\',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'create_table_4' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_categories_order` (
  `id` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `deepness` tinyint(4) NOT NULL,
  UNIQUE KEY `id` (`id`,`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_5' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_categories_ordering_cache` (
  `id` int(11) NOT NULL,
  `order` varchar(512) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_6' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_customProperties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` bigint(20) NOT NULL DEFAULT \'0\',
  `values` varchar(1024) NOT NULL COMMENT \'| separated i18n values\',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'create_table_7' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` bigint(20) NOT NULL COMMENT \'i18n id\',
  `reference` varchar(128) NOT NULL,
  `description` bigint(20) NOT NULL COMMENT \'i18n id\',
  `shortDescription` bigint(20) NOT NULL COMMENT \'i18n id\',
  `image` varchar(256) NOT NULL,
  `images` text NOT NULL,
  `stock` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `taxRate` decimal(10,2) NOT NULL,
  `noShippingCost` tinyint(1) NOT NULL,
  `hasVariants` tinyint(1) NOT NULL DEFAULT \'0\',
  `variants_change_price` tinyint(1) NOT NULL DEFAULT \'0\',
  `variants_change_ref` tinyint(1) NOT NULL DEFAULT \'0\',
  `variants_change_stock` tinyint(1) NOT NULL DEFAULT \'0\',
  `seo_titleBar` bigint(20) NOT NULL COMMENT \'i18n id\',
  `seo_metaDescription` bigint(20) NOT NULL COMMENT \'i18n id\',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'create_table_8' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_products_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_9' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_products_customProperties` (
  `product_id` int(11) NOT NULL,
  `customProperty_id` int(11) NOT NULL,
  `customProperty_value` int(11) unsigned NOT NULL,
  UNIQUE KEY `product_id` (`product_id`,`customProperty_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_10' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_products_variants` (
  `product_id` int(11) NOT NULL COMMENT \'product id\',
  `variant_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `ref` varchar(128) NOT NULL,
  `customProperties` varchar(1024) NOT NULL COMMENT \'ex : "12:4|13:1|16:5"\',
  UNIQUE KEY `id` (`product_id`,`variant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'addActiveFieldToCategories' => array(
        'query' => 'ALTER TABLE `###shop_categories`
            ADD `active` BOOLEAN NOT NULL DEFAULT \'1\' AFTER `date` ',
        'type' => 'set'
    ),
    'create_table_11' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_prices_cache` (
  `product` int(11) NOT NULL,
  `variant` int(11) NOT NULL,
  `price` float NOT NULL,
  UNIQUE KEY `product` (`product`,`variant`),
  KEY `product_2` (`product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_12' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `when` enum(\'always\',\'period\') NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `monday` tinyint(1) NOT NULL,
  `tuesday` tinyint(1) NOT NULL,
  `wednesday` tinyint(1) NOT NULL,
  `thursday` tinyint(1) NOT NULL,
  `friday` tinyint(1) NOT NULL,
  `saturday` tinyint(1) NOT NULL,
  `sunday` tinyint(1) NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` enum(\'percents\',\'monney\',\'gift\') NOT NULL,
  `percents` float NOT NULL,
  `monney` float NOT NULL,
  `gift_addMoney` float NOT NULL,
  `gift_quantity` int(11) NOT NULL,
  `gift_category` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'create_table_13' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_discounts_categories` (
  `discount_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  UNIQUE KEY `discount_id` (`discount_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'create_table_14' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_discounts_products` (
  `discount_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  UNIQUE KEY `discount_id` (`discount_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'update_table_price_cache_1' => array(
        'query' => 'ALTER TABLE `###shop_prices_cache` ADD `min_quantity` INT NOT NULL AFTER `variant`;',
        'type' => 'set'
    ),
    'update_table_price_cache_2' => array(
        'query' => 'ALTER TABLE `###shop_prices_cache` DROP INDEX `product` ,
ADD UNIQUE `unic` ( `product` , `variant` , `min_quantity` ) ;',
        'type' => 'set'
    ),
    'update_table_price_cache_3' => array(
        'query' => 'ALTER TABLE `###shop_prices_cache` ADD `discount_id` INT NOT NULL ;',
        'type' => 'set'
    ),
    'update_table_promotions_1' => array(
        'query' => 'ALTER TABLE `###shop_discounts` ADD `title` BIGINT NOT NULL COMMENT \'i18n id\',
ADD `description` BIGINT NOT NULL COMMENT \'i18n id\';',
        'type' => 'set'
    ),
    'update_table_promotions_2' => array(
        'query' => 'ALTER TABLE `###shop_discounts` CHANGE `description` `description_categories` BIGINT( 20 ) NOT NULL COMMENT \'i18n id\'',
        'type' => 'set'
    ),
    'update_table_promotions_3' => array(
        'query' => 'ALTER TABLE `###shop_discounts` ADD `description_product` INT NOT NULL COMMENT \'i18n id\'',
        'type' => 'set'
    ),

    'update_table_products' => array(
        'query' => 'ALTER TABLE `###shop_products` ADD `isPack` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `id` ',
        'type' => 'set'
    ),
    'create_table_packs' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_packs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` bigint(20) NOT NULL DEFAULT \'0\' COMMENT \'i18n id\',
  `active` tinyint(1) NOT NULL DEFAULT \'0\',
  `cost` enum(\'add\',\'total\') CHARACTER SET latin1 NOT NULL,
  `add` float NOT NULL,
  `total` float NOT NULL,
  `seo_titleBar` bigint(20) NOT NULL COMMENT \'i18n id\',
  `seo_metaDescription` bigint(20) NOT NULL COMMENT \'i18n id\',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ; ',
        'type' => 'set'
    ),
    'create_table_packs_contents' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_packs_contents` (
  `pack_id` int(11) NOT NULL,
  `type` enum(\'main\',\'secondary\') NOT NULL DEFAULT \'main\',
  `element_type` enum(\'product\',\'category\') NOT NULL,
  `element_id` int(11) NOT NULL COMMENT \'id of the category/product\',
  `quantity` int(11) NOT NULL,
  `free` tinyint(1) NOT NULL DEFAULT \'0\',
  `name` bigint(20) NOT NULL COMMENT \'i18n id\',
  `number` smallint(6) NOT NULL,
  UNIQUE KEY `element_type` (`element_type`,`element_id`,`pack_id`),
  KEY `pack_id` (`pack_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; ',
        'type' => 'set'
    ),

    'update_table_products_2' => array(
        'query' => 'ALTER TABLE `###shop_products` CHANGE `isPack` `pack_id` INT( 11 ) NOT NULL DEFAULT \'0\' COMMENT \'0 (for normal products) or the id of the pack\'',
        'type' => 'set'
    ),
    'update_table_products_3' => array(
        'query' => 'ALTER TABLE `###shop_products` ADD `pack_variant` VARCHAR( 256 ) NOT NULL AFTER `pack_id` ',
        'type' => 'set'
    ),
    'update_table_packs' => array(
        'query' => 'ALTER TABLE `###shop_packs`
            ADD `taxRate` decimal(10,2) NOT NULL AFTER `seo_metaDescription` ',
        'type' => 'set'
    ),
    'create_layout_table' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_layouts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR( 256 ) NOT NULL,
  `top` bigint(20) NOT NULL,
  `bottom` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'set'
    ),
    'update_categories_add_layout' => array(
        'query' => 'ALTER TABLE `###shop_categories` ADD `layout` INT NOT NULL DEFAULT \'1\' AFTER `active` ;',
        'type' => 'set'
    ),
    'update_products_add_layout' => array(
        'query' => 'ALTER TABLE `###shop_products` ADD `layout` INT NOT NULL DEFAULT \'1\' AFTER `active` ;',
        'type' => 'set'
    ),
    'update_packs_add_layout' => array(
        'query' => 'ALTER TABLE `###shop_packs` ADD `layout` INT NOT NULL DEFAULT \'1\' AFTER `taxRate` ;',
        'type' => 'set'
    ),
    'create_categories_cached_datas_table' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shop_categories_cached_datas` (
  `category` int(11) NOT NULL,
  `min_quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `multiplePrices` tinyint(1) NOT NULL,
  UNIQUE KEY `category` (`category`,`min_quantity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'set'
    ),
    'update_prices_cache_unique_add_min_quantity' => array(
        'query' => 'ALTER TABLE `###shop_prices_cache` DROP INDEX `product` ,
ADD UNIQUE `product` ( `product` , `variant` , `min_quantity` ) ;',
        'type' => 'set'
    ),

);
