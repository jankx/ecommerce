<?php
use Jankx\Ecommerce\EcommerceTemplate;

function jankx_ecommerce_template()
{
    return call_user_func_array(
        array(
            EcommerceTemplate::getTemplateInstance(),
            'render'
        ),
        func_get_args()
    );
}

function jankx_ecommerce_single_product_layout()
{
    $layout = apply_filters('jankx_ecommerce_single_product_layout', null);
    if ($layout) {
        return $layout;
    }

    return 'default';
}

function jankx_ecommerce_asset_url($path = '')
{
    $ecommerceDirUrl = str_replace(
        ABSPATH,
        site_url('/'),
        dirname(JANKX_ECOMMERCE_FILE_LOADER)
    );

    return sprintf('%s/assets/%s', $ecommerceDirUrl, $path);
}
