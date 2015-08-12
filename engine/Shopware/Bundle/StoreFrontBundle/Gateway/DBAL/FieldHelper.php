<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Service\CacheInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class FieldHelper
{
    /**
     * Contains the selection for the s_articles_attributes table.
     * This table contains dynamically columns.
     *
     * @var array
     */
    private $attributeFields = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param Connection $connection
     * @param CacheInterface $cache
     */
    public function __construct(Connection $connection, CacheInterface $cache)
    {
        $this->connection = $connection;
        $this->cache = $cache;
    }

    /**
     * Helper function which generates an array with table column selections
     * for the passed table.
     *
     * @param string $table
     * @param string $alias
     * @return array
     */
    public function getTableFields($table, $alias)
    {
        $key = $table;

        if (isset($this->attributeFields[$key])) {
            return $this->attributeFields[$key];
        }

        if ($columns = $this->cache->fetch($key)) {
            return $columns;
        }

        $schemaManager = $this->connection->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns($table);

        $columns = [];
        foreach ($tableColumns as $column) {
            $columns[] = $alias . '.' . $column->getName() . ' as __' . $alias . '_' . $column->getName();
        }

        $this->cache->save($key, $columns);
        $this->attributeFields[$key] = $columns;

        return $columns;
    }

    /**
     * Defines which s_articles fields should be selected.
     * @return array
     */
    public function getArticleFields()
    {
        $fields = [
            'product.id as __product_id',
            'product.supplierID as __product_supplierID',
            'product.name as __product_name',
            'product.description as __product_description',
            'product.description_long as __product_description_long',
            'product.shippingtime as __product_shippingtime',
            'product.datum as __product_datum',
            'product.active as __product_active',
            'product.taxID as __product_taxID',
            'product.pseudosales as __product_pseudosales',
            'product.topseller as __product_topseller',
            'product.metaTitle as __product_metaTitle',
            'product.keywords as __product_keywords',
            'product.changetime as __product_changetime',
            'product.pricegroupID as __product_pricegroupID',
            'product.pricegroupActive as __product_pricegroupActive',
            'product.filtergroupID as __product_filtergroupID',
            'product.laststock as __product_laststock',
            'product.crossbundlelook as __product_crossbundlelook',
            'product.notification as __product_notification',
            'product.template as __product_template',
            'product.mode as __product_mode',
            'product.main_detail_id as __product_main_detail_id',
            'product.available_from as __product_available_from',
            'product.available_to as __product_available_to',
            'product.configurator_set_id as __product_configurator_set_id',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_attributes', 'productAttribute')
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getTopSellerFields()
    {
        return [
            'topSeller.sales as __topSeller_sales'
        ];
    }

    /**
     * Defines which s_articles_details fields should be selected.
     * @return array
     */
    public function getVariantFields()
    {
        return [
            'variant.id as __variant_id',
            'variant.ordernumber as __variant_ordernumber',
            'variant.suppliernumber as __variant_suppliernumber',
            'variant.kind as __variant_kind',
            'variant.additionaltext as __variant_additionaltext',
            'variant.sales as __variant_sales',
            'variant.active as __variant_active',
            'variant.instock as __variant_instock',
            'variant.stockmin as __variant_stockmin',
            'variant.weight as __variant_weight',
            'variant.position as __variant_position',
            'variant.width as __variant_width',
            'variant.height as __variant_height',
            'variant.length as __variant_length',
            'variant.ean as __variant_ean',
            'variant.unitID as __variant_unitID',
            'variant.releasedate as __variant_releasedate',
            'variant.shippingfree as __variant_shippingfree',
            'variant.shippingtime as __variant_shippingtime',
        ];
    }

    /**
     * @return array
     */
    public function getEsdFields()
    {
        $fields = [
            'esd.id as __esd_id',
            'esd.articleID as __esd_articleID',
            'esd.articledetailsID as __esd_articledetailsID',
            'esd.file as __esd_file',
            'esd.serials as __esd_serials',
            'esd.notification as __esd_notification',
            'esd.maxdownloads as __esd_maxdownloads',
            'esd.datum as __esd_datum',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_esd_attributes', 'esdAttribute')
        );

        return $fields;
    }

    /**
     * Defines which s_core_tax fields should be selected
     * @return array
     */
    public function getTaxFields()
    {
        return [
            'tax.id as __tax_id',
            'tax.tax as __tax_tax',
            'tax.description as __tax_description'
        ];
    }

    /**
     * Defines which s_core_pricegroups fields should be selected
     * @return array
     */
    public function getPriceGroupFields()
    {
        return [
            'priceGroup.id as __priceGroup_id',
            'priceGroup.description as __priceGroup_description'
        ];
    }

    /**
     * Defines which s_articles_suppliers fields should be selected
     * @return array
     */
    public function getManufacturerFields()
    {
        $fields = [
            'manufacturer.id as __manufacturer_id',
            'manufacturer.name as __manufacturer_name',
            'manufacturer.img as __manufacturer_img',
            'manufacturer.link as __manufacturer_link',
            'manufacturer.description as __manufacturer_description',
            'manufacturer.meta_title as __manufacturer_meta_title',
            'manufacturer.meta_description as __manufacturer_meta_description',
            'manufacturer.meta_keywords as __manufacturer_meta_keywords'
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_supplier_attributes', 'manufacturerAttribute')
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getCategoryFields()
    {
        $fields = [
            'category.id as __category_id',
            'category.parent as __category_parent_id',
            'category.position as __category_position',
            'category.path as __category_path',
            'category.description as __category_description',
            'category.metakeywords as __category_metakeywords',
            'category.metadescription as __category_metadescription',
            'category.cmsheadline as __category_cmsheadline',
            'category.product_box_layout as __category_product_box_layout',
            'category.cmstext as __category_cmstext',
            'category.template as __category_template',
            'category.noviewselect as __category_noviewselect',
            'category.blog as __category_blog',
            'category.showfiltergroups as __category_showfiltergroups',
            'category.external as __category_external',
            'category.hidefilter as __category_hidefilter',
            'category.hidetop as __category_hidetop',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_categories_attributes', 'categoryAttribute')
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getPriceFields()
    {
        $fields = [
            'price.id as __price_id',
            'price.pricegroup as __price_pricegroup',
            'price.from as __price_from',
            'price.to as __price_to',
            'price.articleID as __price_articleID',
            'price.articledetailsID as __price_articledetailsID',
            'price.price as __price_price',
            'price.pseudoprice as __price_pseudoprice',
            'price.baseprice as __price_baseprice',
            'price.percent as __price_percent',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_prices_attributes', 'priceAttribute')
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getUnitFields()
    {
        return [
            'unit.id as __unit_id',
            'unit.description as __unit_description',
            'unit.unit as __unit_unit',
            'variant.packunit as __unit_packunit',
            'variant.purchaseunit as __unit_purchaseunit',
            'variant.referenceunit as __unit_referenceunit',
            'variant.purchasesteps as __unit_purchasesteps',
            'variant.minpurchase as __unit_minpurchase',
            'variant.maxpurchase as __unit_maxpurchase'
        ];
    }

    /**
     * @return array
     */
    public function getConfiguratorSetFields()
    {
        return [
            'configuratorSet.id as __configuratorSet_id',
            'configuratorSet.name as __configuratorSet_name',
            'configuratorSet.type as __configuratorSet_type'
        ];
    }

    /**
     * @return array
     */
    public function getConfiguratorGroupFields()
    {
        return [
            'configuratorGroup.id as __configuratorGroup_id',
            'configuratorGroup.name as __configuratorGroup_name',
            'configuratorGroup.description as __configuratorGroup_description',
            'configuratorGroup.position as __configuratorGroup_position'
        ];
    }

    /**
     * @return array
     */
    public function getConfiguratorOptionFields()
    {
        return [
            'configuratorOption.id as __configuratorOption_id',
            'configuratorOption.name as __configuratorOption_name',
            'configuratorOption.position as __configuratorOption_position'
        ];
    }

    /**
     * @return array
     */
    public function getAreaFields()
    {
        return [
            'countryArea.id as __countryArea_id',
            'countryArea.name as __countryArea_name',
            'countryArea.active as __countryArea_active',
        ];
    }

    /**
     * @return array
     */
    public function getCountryFields()
    {
        $fields = [
            'country.id as __country_id',
            'country.countryname as __country_countryname',
            'country.countryiso as __country_countryiso',
            'country.areaID as __country_areaID',
            'country.countryen as __country_countryen',
            'country.position as __country_position',
            'country.notice as __country_notice',
            'country.shippingfree as __country_shippingfree',
            'country.taxfree as __country_taxfree',
            'country.taxfree_ustid as __country_taxfree_ustid',
            'country.taxfree_ustid_checked as __country_taxfree_ustid_checked',
            'country.active as __country_active',
            'country.iso3 as __country_iso3',
            'country.display_state_in_registration as __country_display_state_in_registration',
            'country.force_state_in_registration as __country_force_state_in_registration',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_core_countries_attributes', 'countryAttribute')
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function getStateFields()
    {
        $fields = [
            'countryState.id as __countryState_id',
            'countryState.countryID as __countryState_countryID',
            'countryState.name as __countryState_name',
            'countryState.shortcode as __countryState_shortcode',
            'countryState.position as __countryState_position',
            'countryState.active as __countryState_active',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_core_countries_states_attributes', 'countryStateAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getCustomerGroupFields()
    {
        $fields = [
            'customerGroup.id as __customerGroup_id',
            'customerGroup.groupkey as __customerGroup_groupkey',
            'customerGroup.description as __customerGroup_description',
            'customerGroup.tax as __customerGroup_tax',
            'customerGroup.taxinput as __customerGroup_taxinput',
            'customerGroup.mode as __customerGroup_mode',
            'customerGroup.discount as __customerGroup_discount',
            'customerGroup.minimumorder as __customerGroup_minimumorder',
            'customerGroup.minimumordersurcharge as __customerGroup_minimumordersurcharge',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_core_customergroups_attributes', 'customerGroupAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getDownloadFields()
    {
        $fields = [
            'download.id as __download_id',
            'download.articleID as __download_articleID',
            'download.description as __download_description',
            'download.filename as __download_filename',
            'download.size as __download_size',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_downloads_attributes', 'downloadAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getLinkFields()
    {
        $fields = [
            'link.id as __link_id',
            'link.articleID as __link_articleID',
            'link.description as __link_description',
            'link.link as __link_link',
            'link.target as __link_target',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_information_attributes', 'linkAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getImageFields()
    {
        $fields = [
            'image.id as __image_id',
            'image.articleID as __image_articleID',
            'image.img as __image_img',
            'image.main as __image_main',
            'image.description as __image_description',
            'image.position as __image_position',
            'image.width as __image_width',
            'image.height as __image_height',
            'image.extension as __image_extension',
            'image.parent_id as __image_parent_id',
            'image.media_id as __image_media_id'
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_articles_img_attributes', 'imageAttribute')
        );

        return $fields;
    }

    /**
     * Returns an array with all required media fields for a full media selection.
     * Requires that the s_media table is included with table alias 'media'
     *
     * @return array
     */
    public function getMediaFields()
    {
        $fields = [
            'media.id as __media_id',
            'media.albumID as __media_albumID',
            'media.name as __media_name',
            'media.description as __media_description',
            'media.path as __media_path',
            'media.type as __media_type',
            'media.extension as __media_extension',
            'media.file_size as __media_file_size',
            'media.userID as __media_userID',
            'media.created as __media_created',
            'mediaSettings.id as __mediaSettings_id',
            'mediaSettings.create_thumbnails as __mediaSettings_create_thumbnails',
            'mediaSettings.thumbnail_size as __mediaSettings_thumbnail_size',
            'mediaSettings.icon as __mediaSettings_icon',
            'mediaSettings.thumbnail_high_dpi as __mediaSettings_thumbnail_high_dpi'
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_media_attributes', 'mediaAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getPriceGroupDiscountFields()
    {
        return [
            'priceGroupDiscount.id as __priceGroupDiscount_id',
            'priceGroupDiscount.groupID as __priceGroupDiscount_groupID',
            'priceGroupDiscount.discount as __priceGroupDiscount_discount',
            'priceGroupDiscount.discountstart as __priceGroupDiscount_discountstart'
        ];
    }

    /**
     * @return string[]
     */
    public function getPropertySetFields()
    {
        $fields = [
            'propertySet.id as __propertySet_id',
            'propertySet.name as __propertySet_name',
            'propertySet.position as __propertySet_position',
            'propertySet.comparable as __propertySet_comparable',
            'propertySet.sortmode as __propertySet_sortmode',
        ];

        $fields = array_merge(
            $fields,
            $this->getTableFields('s_filter_attributes', 'propertySetAttribute')
        );

        return $fields;
    }

    /**
     * @return string[]
     */
    public function getPropertyGroupFields()
    {
        return [
            'propertyGroup.id as __propertyGroup_id',
            'propertyGroup.name as __propertyGroup_name',
            'propertyGroup.filterable as __propertyGroup_filterable',
            'propertyGroup.default as __propertyGroup_default',
        ];
    }

    /**
     * @return string[]
     */
    public function getPropertyOptionFields()
    {
        return [
            'propertyOption.id as __propertyOption_id',
            'propertyOption.optionID as __propertyOption_optionID',
            'propertyOption.value as __propertyOption_value',
            'propertyOption.position as __propertyOption_position',
            'propertyOption.value_numeric as __propertyOption_value_numeric',
        ];
    }

    /**
     * @return string[]
     */
    public function getTaxRuleFields()
    {
        return [
            'taxRule.groupID as __taxRule_groupID',
            'taxRule.tax as __taxRule_tax',
            'taxRule.name as __taxRule_name',
        ];
    }

    /**
     * @return string[]
     */
    public function getVoteFields()
    {
        return [
            'vote.id as __vote_id',
            'vote.articleID as __vote_articleID',
            'vote.name as __vote_name',
            'vote.headline as __vote_headline',
            'vote.comment as __vote_comment',
            'vote.points as __vote_points',
            'vote.datum as __vote_datum',
            'vote.active as __vote_active',
            'vote.email as __vote_email',
            'vote.answer as __vote_answer',
            'vote.answer_date as __vote_answer_date',
        ];
    }

    public function getShopFields()
    {
        return [
            'shop.id as __shop_id',
            'shop.main_id as __shop_main_id',
            'shop.name as __shop_name',
            'shop.title as __shop_title',
            'shop.position as __shop_position',
            'shop.host as __shop_host',
            'shop.base_path as __shop_base_path',
            'shop.base_url as __shop_base_url',
            'shop.hosts as __shop_hosts',
            'shop.secure as __shop_secure',
            'shop.secure_host as __shop_secure_host',
            'shop.secure_base_path as __shop_secure_base_path',
            'shop.template_id as __shop_template_id',
            'shop.document_template_id as __shop_document_template_id',
            'shop.category_id as __shop_category_id',
            'shop.locale_id as __shop_locale_id',
            'shop.currency_id as __shop_currency_id',
            'shop.customer_group_id as __shop_customer_group_id',
            'shop.fallback_id as __shop_fallback_id',
            'shop.customer_scope as __shop_customer_scope',
            'shop.default as __shop_default',
            'shop.active as __shop_active',
            'shop.always_secure as __shop_always_secure',
        ];
    }

    public function getCurrencyFields()
    {
        return [
            'currency.id as __currency_id',
            'currency.currency as __currency_currency',
            'currency.name as __currency_name',
            'currency.standard as __currency_standard',
            'currency.factor as __currency_factor',
            'currency.templatechar as __currency_templatechar',
            'currency.symbol_position as __currency_symbol_position',
            'currency.position as __currency_position'
        ];
    }

    public function getTemplateFields()
    {
        return [
            'template.id as __template_id',
            'template.template as __template_template',
            'template.name as __template_name',
            'template.description as __template_description',
            'template.author as __template_author',
            'template.license as __template_license',
            'template.esi as __template_esi',
            'template.style_support as __template_style_support',
            'template.emotion as __template_emotion',
            'template.version as __template_version',
            'template.plugin_id as __template_plugin_id',
            'template.parent_id as __template_parent_id'
        ];
    }

    public function getLocaleFields()
    {
        return [
            'locale.id as __locale_id',
            'locale.locale as __locale_locale',
            'locale.language as __locale_language',
            'locale.territory as __locale_territory'
        ];
    }

    /**
     * Returns an array with all required related product stream fields.
     * Requires that the s_product_stream table is included with table alias 'stream'
     *
     * @return array
     */
    public function getRelatedProductStreamFields()
    {
        return [
            'stream.id as __stream_id',
            'stream.name as __stream_name',
            'stream.description as __stream_description',
            'stream.type as __stream_type',
        ];
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addAllPropertyTranslations(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addPropertySetTranslationWithSuffix($query);
        $this->addPropertyGroupTranslationWithSuffix($query);
        $this->addPropertyOptionTranslationWithSuffix($query);

        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addPropertySetTranslationWithSuffix($query, 'Fallback');
            $this->addPropertyGroupTranslationWithSuffix($query, 'Fallback');
            $this->addPropertyOptionTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addPropertySetTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addPropertySetTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addPropertySetTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addPropertyGroupTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addPropertyGroupTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addPropertyGroupTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addPropertyOptionTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addPropertyOptionTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addPropertyOptionTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addPropertyOptionTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'propertyOption',
            's_core_translations',
            'propertyOptionTranslation' . $suffix,
            'propertyOptionTranslation' . $suffix . '.objecttype = :optionTranslation AND
             propertyOptionTranslation' . $suffix . '.objectkey = propertyOption.id AND
             propertyOptionTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->setParameter(':optionTranslation', 'propertyvalue');

        $query->addSelect([
            'propertyOptionTranslation' . $suffix . '.objectdata as __propertyOption_translation' . $selectSuffix
        ]);
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addPropertyGroupTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';
        $query->leftJoin(
            'propertyGroup',
            's_core_translations',
            'propertyGroupTranslation' . $suffix,
            'propertyGroupTranslation' . $suffix . '.objecttype = :groupTranslation AND
             propertyGroupTranslation' . $suffix . '.objectkey = propertyGroup.id AND
             propertyGroupTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->setParameter(':groupTranslation', 'propertyoption');
        $query->addSelect([
            'propertyGroupTranslation' . $suffix . '.objectdata as __propertyGroup_translation' . $selectSuffix,
        ]);
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addPropertySetTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'propertySet',
            's_core_translations',
            'propertySetTranslation' . $suffix,
            'propertySetTranslation' . $suffix . '.objecttype = :setTranslation AND
             propertySetTranslation' . $suffix . '.objectkey = propertySet.id AND
             propertySetTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->setParameter(':setTranslation', 'propertygroup');

        $query->addSelect([
            'propertySetTranslation' . $suffix . '.objectdata as __propertySet_translation' . $selectSuffix
        ]);
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addImageTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addImageTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addImageTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addImageTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'image',
            's_core_translations',
            'imageTranslation' . $suffix,
            'imageTranslation' . $suffix . '.objecttype = :imageType AND
             imageTranslation' . $suffix . '.objectkey = image.id AND
             imageTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );
        $query->addSelect(
            [
            'imageTranslation' . $suffix . '.objectdata as __image_translation' . $selectSuffix,
            ]
        );

        $query->setParameter(':imageType', 'articleimage');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addConfiguratorTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addConfiguratorTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addConfiguratorTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addConfiguratorTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'configuratorGroup',
            's_core_translations',
            'configuratorGroupTranslation' . $suffix,
            'configuratorGroupTranslation' . $suffix . '.objecttype = :configuratorGroupType AND
             configuratorGroupTranslation' . $suffix . '.objectkey = configuratorGroup.id AND
             configuratorGroupTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->leftJoin(
            'configuratorOption',
            's_core_translations',
            'configuratorOptionTranslation' . $suffix,
            'configuratorOptionTranslation' . $suffix . '.objecttype = :configuratorOptionType AND
             configuratorOptionTranslation' . $suffix . '.objectkey = configuratorOption .id AND
             configuratorOptionTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->setParameter(':configuratorGroupType', 'configuratorgroup')
           ->setParameter(':configuratorOptionType', 'configuratoroption');

        $query->addSelect(
            [
            'configuratorGroupTranslation' . $suffix . '.objectdata as __configuratorGroup_translation' . $selectSuffix,
            'configuratorOptionTranslation' . $suffix . '.objectdata as __configuratorOption_translation' . $selectSuffix
            ]
        );
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addUnitTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addUnitTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addUnitTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addUnitTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'variant',
            's_core_translations',
            'unitTranslation' . $suffix,
            'unitTranslation' . $suffix . '.objecttype = :unitType AND
             unitTranslation' . $suffix . '.objectkey = 1 AND
             unitTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->addSelect(['unitTranslation' . $suffix . '.objectdata as __unit_translation' . $selectSuffix])
            ->setParameter(':unitType', 'config_units');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addVariantTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addVariantTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addVariantTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addVariantTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'variant',
            's_core_translations',
            'variantTranslation' . $suffix,
            'variantTranslation' . $suffix . '.objecttype = :variantType AND
             variantTranslation' . $suffix . '.objectkey = variant.id AND
             variantTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->addSelect('variantTranslation' . $suffix . '.objectdata as __variant_translation' . $selectSuffix)
            ->setParameter(':variantType', 'variant');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addCountryTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addCountryTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addCountryTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addCountryTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'country',
            's_core_translations',
            'countryTranslation' . $suffix,
            'countryTranslation' . $suffix . '.objecttype = :countryType AND
             countryTranslation' . $suffix . '.objectkey = 1 AND
             countryTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );
        $query->addSelect('countryTranslation' . $suffix . '.objectdata as __country_translation' . $selectSuffix)
            ->setParameter(':countryType', 'config_countries');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addCountryStateTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addCountryStateTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addCountryStateTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addCountryStateTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'countryState',
            's_core_translations',
            'stateTranslation' . $suffix,
            'stateTranslation' . $suffix . '.objecttype = :stateType AND
             stateTranslation' . $suffix . '.objectkey = 1 AND
             stateTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );
        $query->addSelect('stateTranslation' . $suffix . '.objectdata as __countryState_translation' . $selectSuffix)
            ->setParameter(':stateType', 'config_country_states')
        ;
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addProductTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addProductTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() != $context->getShop()->getId()) {
            $this->addProductTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addProductTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'variant',
            's_core_translations',
            'productTranslation' . $suffix,
            'productTranslation' . $suffix . '.objecttype = :productType AND
             productTranslation' . $suffix . '.objectkey = variant.articleID AND
             productTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );

        $query->addSelect(['productTranslation' . $suffix . '.objectdata as __product_translation' . $selectSuffix])
            ->setParameter(':productType', 'article');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addManufacturerTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addManufacturerTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addManufacturerTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addManufacturerTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'manufacturer',
            's_core_translations',
            'manufacturerTranslation' . $suffix,
            'manufacturerTranslation' . $suffix . '.objecttype = :manufacturerType AND
             manufacturerTranslation' . $suffix . '.objectkey = manufacturer.id AND
             manufacturerTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );
        $query->addSelect(
            ['manufacturerTranslation' . $suffix . '.objectdata as __manufacturer_translation' . $selectSuffix]
        )
            ->setParameter(':manufacturerType', 'supplier');
    }

    /**
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function addProductStreamTranslation(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $this->addProductStreamTranslationWithSuffix($query);
        $query->setParameter(':language', $context->getShop()->getId());

        if ($context->getShop()->getFallbackId() !== $context->getShop()->getId()) {
            $this->addProductStreamTranslationWithSuffix($query, 'Fallback');
            $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $suffix
     */
    private function addProductStreamTranslationWithSuffix(QueryBuilder $query, $suffix = '')
    {
        $selectSuffix = !empty($suffix) ? '_' . strtolower($suffix) : '';

        $query->leftJoin(
            'stream',
            's_core_translations',
            'streamTranslation' . $suffix,
            'streamTranslation' . $suffix . '.objecttype = :streamType AND
             streamTranslation' . $suffix . '.objectkey = stream.id AND
             streamTranslation' . $suffix . '.objectlanguage = :language' . $suffix
        );
        $query->addSelect(
            ['streamTranslation' . $suffix . '.objectdata as __stream_translation' . $selectSuffix]
        )
            ->setParameter(':streamType', 'productStream');
    }
}
