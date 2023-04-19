<?php
function custom_slider_register_post_type() {
    $labels = array(
        'name' => __( 'Slider Images' ),
        'singular_name' => __( 'Slider Image' ),
        'menu_name' => __( 'Slider Images' ),
        'all_items' => __( 'All Slider Images' ),
        'add_new_item' => __( 'Add New Slider Image' ),
        'edit_item' => __( 'Edit Slider Image' ),
        'new_item' => __( 'New Slider Image' ),
        'view_item' => __( 'View Slider Image' ),
        'search_items' => __( 'Search Slider Images' ),
        'not_found' => __( 'No Slider Images found' ),
        'not_found_in_trash' => __( 'No Slider Images found in Trash' ),
    );
    $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'thumbnail' )
    );
    register_post_type( 'slider_image', $args );
}
add_action( 'init', 'custom_slider_register_post_type' );

function custom_slider_shortcode($atts) {
    // Extract shortcode attributes
    extract(shortcode_atts(array(
        'autoplay' => 'true',
        'dots' => 'true',
        'arrows' => 'true',
    ), $atts));

    // Get slider settings
    $speed = get_option('custom_slider_speed', '5000');
    $height = get_option('custom_slider_height', '500');

    // Slider code
    $output = '<div class="custom-slider" style="height: ' . esc_attr($height) . 'px;">';
    $images = get_posts(array(
        'post_type' => 'slider_image',
        'posts_per_page' => -1,
    ));
    foreach ($images as $image) {
        $output .= '<div><img src="' . wp_get_attachment_image_url(get_post_thumbnail_id($image->ID), 'full') . '"></div
        // Delete the image from slider after it is shown
    wp_delete_post($image->ID, true);
}
$output .= '</div>';

// Slick Slider options
$options = '{';
$options .= '"autoplay": ' . $autoplay . ',';
$options .= '"dots": ' . $dots . ',';
$options .= '"arrows": ' . $arrows . ',';
$options .= '"speed": ' . $speed . ',';
$options .= '}';

// Add Slick Slider scripts
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);

// Initialize Slick Slider
$output .= '<script>jQuery(".custom-slider").slick(' . $options . ');</script>';

return $output;
}
add_shortcode('custom_slider', 'custom_slider_shortcode');

/* 
Bu kod, slider'ı oluşturmak için önce "slider_image" post tipindeki tüm görselleri alır. Daha sonra, slider'ın HTML kodunu oluşturmak için bu görselleri kullanır. Slider'da gösterildikten sonra, her görseli siler.

Kullanıcıların bu özellikten yararlanabilmesi için, öncelikle "Görseller" sayfasını oluşturmanız gerekiyor. Bunun için, WordPress yönetim panelinde "Sayfalar > Yeni ekle" sayfasına gidin ve sayfaya "Görseller" adını verin.

Daha sonra, sayfanızda blok ekleme seçeneğine gidin ve "Görsel" bloğunu ekleyin. Bu blok, kullanıcılara görsel yüklemelerine izin verir. Kullanıcılar, görsellerini yükledikten sonra, "slider_image" post tipine eklenir.

Slider'ınızı kullanmak için, sayfanızda blok ekleme seçeneğine gidin ve "Özel Slider" bloğunu ekleyin. Bu blok, "autoplay", "dots" ve "arrows" özelliklerini ayarlamanızı sağlar.

Son olarak, "Görseller" sayfanızı açın ve yüklediğiniz görselleri seçin. Bu görseller, slider'ınızda görüntülenir.
 */

 add_action('admin_menu', 'custom_slider_menu');

function custom_slider_menu() {
    add_menu_page('Custom Slider Settings', 'Custom Slider', 'manage_options', 'custom_slider', 'custom_slider_page');
}

function custom_slider_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form id="custom-slider-form" method="post" enctype="multipart/form-data">
            <input type="file" name="custom_slider_files[]" multiple>
            <?php submit_button('Upload'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_post_custom_slider_upload', 'custom_slider_upload');

function custom_slider_upload() {
    if (!empty($_FILES['custom_slider_files'])) {
        $files = $_FILES['custom_slider_files'];
        $uploaded = array();

        foreach ($files['name'] as $key => $value) {
            if ($files['error'][$key] == 0) {
                $upload_overrides = array('test_form' => false);
                $uploaded_file = wp_handle_upload($files, $upload_overrides);

                $attachment = array(
                    'guid' => $uploaded_file['url'],
                    'post_mime_type' => $uploaded_file['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploaded_file['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);
                $uploaded[] = $attach_id;
            }
        }

        if (!empty($uploaded)) {
            update_option('custom_slider_images', $uploaded);
            wp_redirect(admin_url('admin.php?page=custom_slider&message=success'));
            exit;
        }
    }

    wp_redirect(admin_url('admin.php?page=custom_slider&message=error'));
    exit;
}

function custom_slider_group_post_type() {
    $labels = array(
        'name' => 'Slider Groups',
        'singular_name' => 'Slider Group',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Slider Group',
        'edit_item' => 'Edit Slider Group',
        'new_item' => 'New Slider Group',
        'all_items' => 'All Slider Groups',
        'view_item' => 'View Slider Group',
        'search_items' => 'Search Slider Groups',
        'not_found' => 'No Slider Groups found',
        'not_found_in_trash' => 'No Slider Groups found in Trash',
        'menu_name' => 'Slider Groups',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'slider-group'),
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => array('title', 'thumbnail'),
    );
    register_post_type('custom_slider_group', $args);
}
add_action('init', 'custom_slider_group_post_type');

function save_custom_slider_group() {
    if (isset($_POST['submit'])) {
        $group_name = sanitize_text_field($_POST['slider_name']);
        $selected_images = $_POST['images'];

        // Insert slider group post
        $post_id = wp_insert_post(array(
            'post_title' => $group_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'custom_slider_group',
        ));

        // Save slider group and images
function save_custom_slider_group() {
    if (isset($_POST['submit'])) {
    $name = sanitize_text_field($_POST['slider_name']);
    $selected_images = $_POST['images'];
        // Insert slider group post
        $post_id = wp_insert_post(array(
            'post_title' => $name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'custom_slider_group',
        ));
    
        // Save selected images as post meta
        if ($selected_images) {
            update_post_meta($post_id, 'custom_slider_images', $selected_images);
        }
    
        // Save slider settings
        update_post_meta($post_id, 'custom_slider_autoplay', isset($_POST['autoplay']));
        update_post_meta($post_id, 'custom_slider_dots', isset($_POST['dots']));
        update_post_meta($post_id, 'custom_slider_arrows', isset($_POST['arrows']));
    
        wp_redirect(admin_url('edit.php?post_type=custom_slider_group'));
        exit;
    }
}
add_action('admin_post_save_custom_slider_group', 'save_custom_slider_group');

// Custom slider group shortcode
function custom_slider_group_shortcode($atts) {
// Get group name from shortcode attributes
$group = $atts['name'];

// Get slider group ID
$group_id = get_page_by_title($group, OBJECT, 'custom_slider_group')->ID;

// Get slider settings
$autoplay = get_post_meta($group_id, 'custom_slider_autoplay', true);
$dots = get_post_meta($group_id, 'custom_slider_dots', true);
$arrows = get_post_meta($group_id, 'custom_slider_arrows', true);

// Slider code
$output = '<div class="custom-slider">';
$images = get_post_meta($group_id, 'custom_slider_images', true);
foreach ($images as $image_id) {
    $output .= '<div>' . wp_get_attachment_image($image_id, 'full') . '</div>';
}
$output .= '</div>';

// Slick Slider options
$options = '{';
$options .= '"autoplay": ' . ($autoplay ? 'true' : 'false') . ',';
$options .= '"dots": ' . ($dots ? 'true' : 'false') . ',';
$options .= '"arrows": ' . ($arrows ? 'true' : 'false') . ',';
$options .= '}';

// Add Slick Slider scripts
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);

// Initialize Slick Slider
$output .= '<script>jQuery(".custom-slider").slick(' . $options . ');</script>';

return $output;
}
add_shortcode('custom_slider_group', 'custom_slider_group_shortcode');

function custom_slider_group_post_type() {
    $labels = array(
        'name' => 'Slider Groups',
        'singular_name' => 'Slider Group',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Slider Group',
        'edit_item' => 'Edit Slider Group',
        'new_item' => 'New Slider Group',
        'all_items' => 'All Slider Groups',
        'view_item' => 'View Slider Group',
        'search_items' => 'Search Slider Groups',
        'not_found' => 'No Slider Groups found',
        'not_found_in_trash' => 'No Slider Groups found in Trash',
        'menu_name' => 'Slider Groups',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'slider-group'),
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => array('title', 'thumbnail'),
    );
    register_post_type('custom_slider_group', $args);
}
add_action('init', 'custom_slider_group_post_type');

function custom_slider_settings_page() {
    $uploaded_images = get_option('custom_slider_images');
    $groups = get_posts(array(
        'post_type' => 'custom_slider_group',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php if (!empty($_GET['message'])) : ?>
            <div class="notice <?php echo esc_attr($_GET['message'] === 'success' ? 'notice-success' : 'notice-error'); ?>">
                <?php echo esc
                </div>
                <h2><?php echo esc_html__('Custom Slider Groups', 'custom-slider'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Group Name', 'custom-slider'); ?></th>
                            <th><?php echo esc_html__('Images', 'custom-slider'); ?></th>
                            <th><?php echo esc_html__('Settings', 'custom-slider'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $groups = get_posts(array(
                            'post_type' => 'custom_slider_group',
                            'orderby' => 'title',
                            'order' => 'ASC',
                            'posts_per_page' => -1
                        ));
                        if ($groups) {
                            foreach ($groups as $group) {
                                $group_id = $group->ID;
                                $group_name = $group->post_title;
                                $group_images = get_post_meta($group_id, 'custom_slider_images', true);
                                $autoplay = get_post_meta($group_id, 'custom_slider_autoplay', true);
                                $dots = get_post_meta($group_id, 'custom_slider_dots', true);
                                $arrows = get_post_meta($group_id, 'custom_slider_arrows', true);
                                ?>
                                <tr>
                                    <td><?php echo esc_html($group_name); ?></td>
                                    <td>
                                        <?php
                                        if ($group_images) {
                                            foreach ($group_images as $image_id) {
                                                echo wp_get_attachment_image($image_id, 'thumbnail');
                                            }
                                        } else {
                                            echo esc_html__('No images', 'custom-slider');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <ul>
                                            <li><?php echo esc_html__('Autoplay:', 'custom-slider'); ?> <?php echo $autoplay ? esc_html__('Yes', 'custom-slider') : esc_html__('No', 'custom-slider'); ?></li>
                                            <li><?php echo esc_html__('Dots:', 'custom-slider'); ?> <?php echo $dots ? esc_html__('Yes', 'custom-slider') : esc_html__('No', 'custom-slider'); ?></li>
                                            <li><?php echo esc_html__('Arrows:', 'custom-slider'); ?> <?php echo $arrows ? esc_html__('Yes', 'custom-slider') : esc_html__('No', 'custom-slider'); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3"><?php echo esc_html__('No custom slider groups found.', 'custom-slider'); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    </table>
                    ?>
<div class="wrap">
<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<form id="custom-slider-form" method="post" enctype="multipart/form-data">
<input type="file" name="custom_slider_files[]" multiple>
<?php submit_button('Upload'); ?>
</form>
</div>
<?php
}

add_action('admin_post_custom_slider_upload', 'custom_slider_upload');

function custom_slider_upload() {
if (!empty($_FILES['custom_slider_files'])) {
$files = $_FILES['custom_slider_files'];
$uploaded = array();
foreach ($files['name'] as $key => $value) {
    if ($files['error'][$key] == 0) {
        $upload_overrides = array('test_form' => false);
        $uploaded_file = wp_handle_upload($files, $upload_overrides);

        $attachment = array(
            'guid' => $uploaded_file['url'],
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploaded_file['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);
        $uploaded[] = $attach_id;
    }
}

if (!empty($uploaded)) {
    update_option('custom_slider_images', $uploaded);
    wp_redirect(admin_url('admin.php?page=custom_slider&message=success'));
    exit;
}
}

wp_redirect(admin_url('admin.php?page=custom_slider&message=error'));
exit;
}

function custom_slider_group_post_type() {
$labels = array(
'name' => 'Slider Groups',
'singular_name' => 'Slider Group',
'add_new' => 'Add New',
'add_new_item' => 'Add New Slider Group',
'edit_item' => 'Edit Slider Group',
'new_item' => 'New Slider Group',
'all_items' => 'All Slider Groups',
'view_item' => 'View Slider Group',
'search_items' => 'Search Slider Groups',
'not_found' => 'No Slider Groups found',
'not_found_in_trash' => 'No Slider Groups found in Trash',
'menu_name' => 'Slider Groups',
);
$args = array(
'labels' => $labels,
'public' => true,
'has_archive' => true,
'rewrite' => array('slug' => 'slider-group'),
'menu_icon' => 'dashicons-images-alt2',
'supports' => array('title', 'thumbnail'),
);
register_post_type('custom_slider_group', $args);
}
add_action('init', 'custom_slider_group_post_type');

function save_custom_slider_group() {
if (isset($_POST['submit'])) {
$group_name = sanitize_text_field($_POST['slider_name']);
$selected_images = $_POST['images'];
    // Insert slider group post
    $post_id = wp_insert_post(array(
        'post_title' => $group_name,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'custom_slider_group',
    ));

    // Save slider group and images
    if ($selected_images) {
        update_post_meta($post_id, 'custom_slider_images', $selected_images);
    }

    // Save slider settings
    update_post_meta($post_id, 'custom_slider_autoplay', isset($_POST['autoplay']));
    update_post_meta($post_id,
// Custom slider group shortcode
function custom_slider_group_shortcode($atts) {
    // Get group name from shortcode attributes
    $group = $atts['name'];
    // Get slider group ID
$group_id = get_page_by_title($group, OBJECT, 'custom_slider_group')->ID;

// Get slider settings
$autoplay = get_post_meta($group_id, 'custom_slider_autoplay', true);
$dots = get_post_meta($group_id, 'custom_slider_dots', true);
$arrows = get_post_meta($group_id, 'custom_slider_arrows', true);

// Slider code
$output = '<div class="custom-slider">';
$images = get_post_meta($group_id, 'custom_slider_images', true);
foreach ($images as $image_id) {
    $output .= '<div>' . wp_get_attachment_image($image_id, 'full') . '</div>';
}
$output .= '</div>';

// Slick Slider options
$options = '{';
$options .= '"autoplay": ' . ($autoplay ? 'true' : 'false') . ',';
$options .= '"dots": ' . ($dots ? 'true' : 'false') . ',';
$options .= '"arrows": ' . ($arrows ? 'true' : 'false') . ',';
$options .= '}';

// Add Slick Slider scripts
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);

// Initialize Slick Slider
$output .= '<script>jQuery(".custom-slider").slick(' . $options . ');</script>';

return $output;
}
add_shortcode('custom_slider_group', 'custom_slider_group_shortcode');

// Display slider group form shortcode
function custom_slider_group_form_shortcode() {
ob_start();
include 'custom-slider-group-form.php';
return ob_get_clean();
}
add_shortcode('custom_slider_group_form', 'custom_slider_group_form_shortcode');

// Load admin scripts and styles
function custom_slider_admin_scripts() {
wp_enqueue_script('custom-slider-admin', plugin_dir_url(FILE) . 'js/custom-slider-admin.js', array('jquery'), '1.0.0', true);
wp_enqueue_style('custom-slider-admin', plugin_dir_url(FILE) . 'css/custom-slider-admin.css', array(), '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'custom_slider_admin_scripts');

// Load front-end scripts and styles
function custom_slider_scripts() {
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_style('custom-slider', plugin_dir_url(FILE) . 'css/custom-slider.css', array('slick', 'slick-theme'), '1.0.0', 'all');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);
}
add_action('wp_enqueue_scripts', 'custom_slider_scripts');

// Install plugin
function custom_slider_install() {
// Create custom post
function custom_slider_page() {
    ?>
    <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="custom-slider-form" method="post" enctype="multipart/form-data">
    <input type="file" name="custom_slider_files[]" multiple>
    <?php submit_button('Upload'); ?>
    </form>
    </div>
    <?php
    }
    
    add_action('admin_post_custom_slider_upload', 'custom_slider_upload');
    
    function custom_slider_upload() {
    if (!empty($_FILES['custom_slider_files'])) {
    $files = $_FILES['custom_slider_files'];
    $uploaded = array();
    foreach ($files['name'] as $key => $value) {
        if ($files['error'][$key] == 0) {
            $upload_overrides = array('test_form' => false);
            $uploaded_file = wp_handle_upload($files, $upload_overrides);

            $attachment = array(
                'guid' => $uploaded_file['url'],
                'post_mime_type' => $uploaded_file['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploaded_file['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);
            $uploaded[] = $attach_id;
        }
    }

    if (!empty($uploaded)) {
        update_option('custom_slider_images', $uploaded);
        wp_redirect(admin_url('admin.php?page=custom_slider&message=success'));
        exit;
    }
}

wp_redirect(admin_url('admin.php?page=custom_slider&message=error'));
exit;
}

function custom_slider_group_post_type() {
$labels = array(
'name' => 'Slider Groups',
'singular_name' => 'Slider Group',
'add_new' => 'Add New',
'add_new_item' => 'Add New Slider Group',
'edit_item' => 'Edit Slider Group',
'new_item' => 'New Slider Group',
'all_items' => 'All Slider Groups',
'view_item' => 'View Slider Group',
'search_items' => 'Search Slider Groups',
'not_found' => 'No Slider Groups found',
'not_found_in_trash' => 'No Slider Groups found in Trash',
'menu_name' => 'Slider Groups',
);
$args = array(
'labels' => $labels,
'public' => true,
'has_archive' => true,
'rewrite' => array('slug' => 'slider-group'),
'menu_icon' => 'dashicons-images-alt2',
'supports' => array('title', 'thumbnail'),
);
register_post_type('custom_slider_group', $args);
}
add_action('init', 'custom_slider_group_post_type');

function save_custom_slider_group() {
if (isset($_POST['submit'])) {
$name = sanitize_text_field($_POST['slider_name']);
$selected_images = $_POST['images'];
    // Insert slider group post
    $post_id = wp_insert_post(array(
        'post_title' => $name,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'custom_slider_group',
    ));

    // Save selected images as post meta
    if ($selected_images) {
        update_post_meta($post_id, 'custom_slider_images', $selected_images);
    }

    // Save slider settings
    update_post_meta($post_id, 'custom_slider_autoplay', isset($_POST['autoplay']));
// Save selected images as post meta
if ($selected_images) {
    update_post_meta($post_id, 'custom_slider_images', $selected_images);
    }
    
    // Save slider settings
    update_post_meta($post_id, 'custom_slider_autoplay', isset($_POST['autoplay']));
    update_post_meta($post_id, 'custom_slider_dots', isset($_POST['dots']));
    update_post_meta($post_id, 'custom_slider_arrows', isset($_POST['arrows']));
    
    wp_redirect(admin_url('edit.php?post_type=custom_slider_group'));
    exit;
    }
    add_action('admin_post_save_custom_slider_group', 'save_custom_slider_group');
    
    // Custom slider group shortcode
    function custom_slider_group_shortcode($atts) {
    // Get group name from shortcode attributes
    $group = $atts['name'];
    // Get slider group ID
$group_id = get_page_by_title($group, OBJECT, 'custom_slider_group')->ID;

// Get slider settings
$autoplay = get_post_meta($group_id, 'custom_slider_autoplay', true);
$dots = get_post_meta($group_id, 'custom_slider_dots', true);
$arrows = get_post_meta($group_id, 'custom_slider_arrows', true);

// Slider code
$output = '<div class="custom-slider">';
$images = get_post_meta($group_id, 'custom_slider_images', true);
foreach ($images as $image_id) {
    $output .= '<div>' . wp_get_attachment_image($image_id, 'full') . '</div>';
}
$output .= '</div>';

// Slick Slider options
$options = '{';
$options .= '"autoplay": ' . ($autoplay ? 'true' : 'false') . ',';
$options .= '"dots": ' . ($dots ? 'true' : 'false') . ',';
$options .= '"arrows": ' . ($arrows ? 'true' : 'false') . ',';
$options .= '}';

// Add Slick Slider scripts
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);

// Initialize Slick Slider
$output .= '<script>jQuery(".custom-slider").slick(' . $options . ');</script>';

return $output;
}
add_shortcode('custom_slider_group', 'custom_slider_group_shortcode');
// Save selected images as post meta
if ($selected_images) {
    update_post_meta($post_id, 'custom_slider_images', $selected_images);
    }
        // Save slider settings
        update_post_meta($post_id, 'custom_slider_autoplay', isset($_POST['autoplay']));
        update_post_meta($post_id, 'custom_slider_dots', isset($_POST['dots']));
        update_post_meta($post_id, 'custom_slider_arrows', isset($_POST['arrows']));
    
        wp_redirect(admin_url('edit.php?post_type=custom_slider_group'));
        exit;
    }
}
add_action('admin_post_save_custom_slider_group', 'save_custom_slider_group');

// Custom slider group shortcode
function custom_slider_group_shortcode($atts) {
// Get group name from shortcode attributes
$group = $atts['name'];
// Get slider group ID
$group_id = get_page_by_title($group, OBJECT, 'custom_slider_group')->ID;

// Get slider settings
$autoplay = get_post_meta($group_id, 'custom_slider_autoplay', true);
$dots = get_post_meta($group_id, 'custom_slider_dots', true);
$arrows = get_post_meta($group_id, 'custom_slider_arrows', true);

// Slider code
$output = '<div class="custom-slider">';
$images = get_post_meta($group_id, 'custom_slider_images', true);
foreach ($images as $image_id) {
    $output .= '<div>' . wp_get_attachment_image($image_id, 'full') . '</div>';
}
$output .= '</div>';

// Slick Slider options
$options = '{';
$options .= '"autoplay": ' . ($autoplay ? 'true' : 'false') . ',';
$options .= '"dots": ' . ($dots ? 'true' : 'false') . ',';
$options .= '"arrows": ' . ($arrows ? 'true' : 'false') . ',';
$options .= '}';

// Add Slick Slider scripts
wp_enqueue_style('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css');
wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css');
wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);

// Initialize Slick Slider
$output .= '<script>jQuery(".custom-slider").slick(' . $options . ');</script>';

return $output;
}
add_shortcode('custom_slider_group', 'custom_slider_group_shortcode');

// End of code.

?>
