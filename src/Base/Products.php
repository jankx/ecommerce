<?php
namespace Jankx\Ecommerce\Base;

use Jankx\Ecommerce\Constracts\Renderer;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Base\GetProductQuery;

class Products implements Renderer
{
    protected static $supportedFirstTabs;

    protected $categoryIds = array();
    protected $readmore = array();
    protected $firstTab;
    protected $tabs;
    protected $args;

    public function __construct($categoryIds, $firstTab = null, $args = array())
    {
        $this->categoryIds = array_filter($categoryIds, function ($id) {
            $id = (int) trim($id);
            if ($id <= 0) {
                return;
            }
            return $id;
        });
        $this->firstTab = $firstTab;
        $this->args     = $args;

        if (is_null(static::$supportedFirstTabs)) {
            static::$supportedFirstTabs = apply_filters(
                'jankx_ecommerce_category_tabs_products_first_tabs',
                array(
                    'featured' => __('Featured', 'jankx'),
                    'recents' => __('Recents', 'jankx'),
                )
            );
        }
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    public function setReadMore($url, $text = null)
    {
        if (is_null($text)) {
            $text = __('View all', 'jankx');
        }

        $this->readmore = array(
            'text' => $text,
            'url' => $url
        );
    }

    public function generateTabs()
    {
        $this->tabs = [];
        if ($this->firstTab && isset(static::$supportedFirstTabs[$this->firstTab])) {
            $this->tabs[static::$supportedFirstTabs[$this->firstTab]] = array(
                'tab' => $this->firstTab,
                'url' => '#',
            );
        }
        $taxonomy = jankx_ecommerce()->getShopPlugin()->getProductCategoryTaxonomy();

        foreach ($this->categoryIds as $categoryId) {
            $term = get_term($categoryId, $taxonomy);
            if (is_null($term) || is_wp_error($term)) {
                continue;
            }
            $this->tabs[$term->name] = array(
                'tab' => $term->term_id,
                'url' => get_term_link($term, $taxonomy),
            );
        }

        return $this->tabs = apply_filters(
            'jankx_ecommerce_category_tabs_products_tabs',
            $this->tabs
        );
    }

    public function buildFirstTabQuery()
    {
        if (!count($this->tabs)) {
            return;
        }
        $tabs = array_values($this->tabs);
        $firstTab = array_shift($tabs);
        if (is_array($firstTab)) {
            $firstTab = array_get($firstTab, 'tab', 'featured');
        }

        $firstTabQuery = GetProductQuery::buildQuery(wp_parse_args(
            array(
                'query_type' => $firstTab,
            ),
            $this->args,
        ));

        return $firstTabQuery->getWordPressQuery();
    }

    public function render()
    {
        $tabs       = $this->generateTabs();
        $pluginName = jankx_ecommerce()->getShopPlugin()->getName();
        $wp_query   = $this->buildFirstTabQuery();

        if (is_null($wp_query)) {
            return __('The products not found', 'jankx');
        }

        // Render the first tab content
        $firstTabContent = EcommerceTemplate::render("{$pluginName}/product-list", array(
            'wp_query' => $wp_query,
            'columns' => 4,
        ), 'product_list', false);

        // Render the output
        return EcommerceTemplate::render(
            'base/category/tabs-products',
            array(
                'tabs' => $tabs,
                'widget_title' => array_get($this->args, 'widget_title'),
                'first_tab_content' => $firstTabContent,
                'readmore' => $this->readmore,
            ),
            null,
            false
        );
    }
}