<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_separate', trailingslashit( get_stylesheet_directory_uri() ) . 'ctc-style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

// functions.php
function vgf_post_filter_shortcode() {
    ob_start(); ?>

    <div class="vgf-container-title">
        <div class="vgf-left">
            <h2 class="vgf-filter-heading">Browse Categories</h2>
        </div>
        <div class="vgf-right">
            <div class="vgf-filter-toggle">
                <button class="vgf-filter-icon">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="vgf-container">
        <div class="vgf-sidebar">
            <div class="vgf-filters">
                <?php echo vgf_render_filters(); ?>
            </div>
        </div>
        
        <div class="vgf-post-container">
            <div class="vgf-posts" id="vgf-posts">
                <?php echo vgf_get_posts(); // This should display all posts by default ?>
            </div>
        </div>
    </div>

    <?php return ob_get_clean();
}

add_shortcode('vgf_post_filter', 'vgf_post_filter_shortcode');


function vgf_render_filters() {
    $parent_categories = get_categories(array('parent' => 0, 'exclude' => 1));
    $output = '<div class="vgf-filters">'; // Wrap all in a filters container
    $output .= '<div class="vgf-tabs">';

    // Create tabs for parent categories
    foreach ($parent_categories as $index => $category) {
        $active_class = ($index === 0) ? 'active' : '';
        $output .= '<div class="vgf-tab ' . $active_class . '" data-category-id="' . $category->term_id . '">' . esc_html($category->name) . '</div>';
    }
    $output .= '</div>'; // Close tabs

    // Create accordions for sub-parent categories
    foreach ($parent_categories as $index => $category) {
        $subcategories = get_categories(array('parent' => $category->term_id, 'exclude' => 1));
        $accordion_class = ($index === 0) ? 'active' : '';
    
        // Start the accordion
        $output .= '<div class="vgf-accordion ' . $accordion_class . '" data-category-id="' . $category->term_id . '">';
    
        foreach ($subcategories as $subcat) {
            $children = get_categories(array('parent' => $subcat->term_id, 'exclude' => 1));
    
            // Only display subcategory if it has children
            if ($children) {
                $output .= '<div class="vgf-subcategory">';
                $output .= '<h4 class="vgf-subcategory-header">' . esc_html($subcat->name) . '<i class="fa-solid fa-chevron-down"></i></h4>';
                $output .= '<div class="vgf-children">';
    
                foreach ($children as $child) {
                    $output .= '<label><input type="checkbox" class="vgf-filter" value="' . $child->term_id . '"> ' . esc_html($child->name) . '</label>';
                }
                $output .= '</div></div>'; // Close subcategory and its children
            }
        }
    
        $output .= '</div>'; // Close accordion
    }
    

    // Add Apply and Cancel buttons for mobile only
    $output .= '<div class="vgf-filters-buttons mobile-only">'; // Class to show only on mobile
    $output .= '<button id="vgf-mobile-apply">Apply</button>';
    $output .= '<button id="vgf-mobile-cancel" class="cancel">Cancel</button>';
    $output .= '</div>'; // Close buttons

    $output .= '</div>'; // Close filters container
    return $output;
}


function vgf_get_posts($selected_categories = []) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 8,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $selected_categories,
                'operator' => 'IN',
            ),
        ),
    );

    if (empty($selected_categories)) {
        unset($args['tax_query']);
    }

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $thumbnail = get_the_post_thumbnail(get_the_ID(), 'full');
            $title = get_the_title();
            $description = wp_trim_words(get_the_excerpt(), 15, '...');
            $author = get_the_author();
            $date = get_the_date();

            // Get the categories for the current post
            $categories = get_the_category();
            if ($categories) {
                $main_category = esc_html($categories[0]->name); // Get the main category
                $additional_count = count($categories) - 1; // Count additional categories
                $additional_text = $additional_count > 0 ? ' + ' . $additional_count . ' more' : '';
                $category_badge = '<span class="vgf-category-badge">' . $main_category . $additional_text . '</span>';
            } else {
                $category_badge = '';
            }

            $output .= '<div class="vgf-post-card" onclick="location.href=\'' . get_permalink() . '\'">';
            $output .= '<div class="vgf-post-image">' . $thumbnail . $category_badge . '</div>';
            $output .= '<div class="vgf-post-content">';
            $output .= '<h2 class="vgf-post-title">' . esc_html($title) . '</h2>';
            $output .= '<p class="vgf-post-description">' . esc_html($description) . '</p>';
            $output .= '<div class="vgf-post-meta"><span class="vgf-author"><i class="fa-solid fa-user"></i> ' . esc_html($author) . '</span> <span class="vgf-date">' . esc_html($date) . '</span></div>';
            $output .= '</div></div>'; // Close card
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No posts found.</p>';
    }

    return $output;
}

add_action('wp_ajax_vgf_filter_posts', 'vgf_filter_posts');
add_action('wp_ajax_nopriv_vgf_filter_posts', 'vgf_filter_posts');

function vgf_filter_posts() {
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    echo vgf_get_posts($selected_categories); // Get filtered posts
    wp_die(); // Required to terminate immediately and return a proper response
}

function vgf_enqueue_scripts() {
    wp_enqueue_script('vgf-ajax', get_stylesheet_directory_uri() . '/js/vgf-ajax.js', array('jquery'), null, true);
    wp_localize_script('vgf-ajax', 'vgf_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_style('vgf-style', get_stylesheet_directory_uri() . '/css/vgf-style.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'vgf_enqueue_scripts');
