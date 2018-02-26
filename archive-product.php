<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
get_header('shop');

if(isset($_GET['reset_query'])){
    delete_transient('query_results');
    delete_transient('atrst');    
}

$size = isset($_GET['size']) ? trim($_GET['size']) : false;
$brand = isset($_GET['brands']) ? trim($_GET['brands']) : false;
$size_name = $size;
if ($brand) {
    $brand_name = get_term($brand, 'pa_brands')->name;
}
$res = getBrandsAndSizes($brand_name, $size);
if (false === ( $query = get_transient('query_results') )) {
    $randargs = array('posts_per_page' => -1, 'post_type' => 'product');
    $query = new WP_Query($randargs);
    set_transient('query_results', $query, DAY_IN_SECONDS);
}
if (false === ( $atrs = get_transient('atrst') )) {
    $atrs = array();
    if ($query->have_posts()) {
        do_action('woocommerce_before_shop_loop');
        woocommerce_product_loop_start();
        woocommerce_product_subcategories();
        while ($query->have_posts()) {
            $temp = array();
            $query->the_post();
            global $product;
            $temp['pa_country'] = array_map('trim', explode(',', $product->get_attribute('pa_country')));
            $temp['pa_size'] = array_map('trim', explode(',', $product->get_attribute('pa_size')));
            $temp['pa_brands'] = array_map('trim', explode(',', $product->get_attribute('pa_brands')));
            $temp['pa_shirt_style'] = array_map('trim', explode(',', $product->get_attribute('pa_shirt-style')));
            $temp['pa_neck_style'] = array_map( 'trim', explode( ',', $product->get_attribute( 'pa_neck-style' ) ) );
            $temp['pa_length_style'] = array_map( 'trim', explode( ',', $product->get_attribute( 'pa_length-style' ) ) );
            $atrs[$product->id] = $temp;
        }
    }
    set_transient('atrst', $atrs, DAY_IN_SECONDS );
}
$ids = array();

?>
<style>
    a.lmp_button {
        background: #000!important;
        color: #fff!important;
        margin-top: 20px;
    }
</style>
<div class="pro_inner_banner" style="position: relative;">
    <img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id(24)); ?>" > 
    <div class="banner-title-top">
        <?php
        $field = get_field('banner-title', 24);
        if ($field) {
            //2017-04-06
            //echo $field;
        }
        ?>
    </div>    
</div>
<?php
global $woocommerce_loop;
do_action('woocommerce_archive_description');
do_action('woocommerce_before_shop_loop');
woocommerce_product_loop_start();
woocommerce_product_subcategories();
global $woocommerce_loop;
global $i;
$i = 0;
do_action('woocommerce_archive_description');
$posts = $query->get_posts();
foreach ($posts as $post) :
    global $post, $pa_brands, $pa_shirt_style, $pa_neck_style, $pa_length_style;
    if ($res && $size_name && $brand_name) {
        $attrs = $atrs[$post->ID];
        //print_r($attrs);
        $pa_country = $attrs['pa_country'];
        $pa_size = $attrs['pa_size'];
        $pa_brands = $attrs['pa_brands'];
        $pa_shirt_style = $attrs['pa_shirt_style'];
        $pa_neck_style = $attrs['pa_neck_style'];
        $pa_length_style = $attrs['pa_length_style'];
        foreach ($res as $db_brand => $db_sizes) {
            foreach ($db_sizes['sizes'] as $db_size) {
                if (in_array($db_size, $pa_size) && in_array($db_brand, $pa_brands)) {
                    wc_get_template('loop/loop-loop-new.php');
                }
            }
        }
    }
endforeach;
woocommerce_product_loop_end();
do_action('woocommerce_after_shop_loop');
wp_reset_postdata();
do_action('woocommerce_after_main_content');
get_footer('shop'); 
