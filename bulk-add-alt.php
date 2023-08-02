<?php
/**
 * Plugin Name: Bulk Add ALT
 * Plugin URI: https://www.effi10.com
 * Description: Easily adds the product name to the ALT tag of images associated with WooCommerce products that don't have one (or have a value of less than 10 characters)
 * Version: 1.0
 * Author: Cédric GIRARD (effi10)
 * Author URI: https://www.effi10.com
 */

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

function bulk_add_alt_menu() {
    add_media_page('Bulk add ALT', 'Bulk add ALT', 'manage_options', 'bulk-add-alt', 'bulk_add_alt_page_callback');
}
add_action('admin_menu', 'bulk_add_alt_menu');

function bulk_add_alt_page_callback() {
    if (isset($_POST['check'])) {
        $images = get_products_images();
        echo '<p>' . count($images) . ' images need alt text</p>';
    } 
    
    if (isset($_POST['fix'])) {
        $images = get_products_images();
        set_image_alt($images);
        
        echo '<p>All done!</p>';
        echo '<table>
        <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>ALT</th>
            <th>Product ID</th>
        </tr>';

        foreach ($images as $image) {
            echo '<tr>
                <td>' . $image['id'] . '</td>
                <td>' . $image['filename'] . '</td>
                <td>' . get_post_meta($image['id'], '_wp_attachment_image_alt', true) . '</td>
                <td>' . $image['product_id'] . '</td>
            </tr>';
        }

        echo '</table>';
    }

    echo '<form method="post">
    <input type="submit" name="check" value="Check images" />
    <input type="submit" name="fix" value="Fix images" />
    </form>';
}

function get_products_images() {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );
    $products = get_posts( $args );
    $images = array();

    foreach ( $products as $product ) {
        $product_id = $product->ID;
        $product_name = get_the_title($product_id);
        $product_images = get_attached_media( 'image', $product_id );

        foreach ($product_images as $image) {
            $image_id = $image->ID;
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if (strlen($alt_text) < 10) {
                $images[] = array(
                    'id' => $image_id,
                    'filename' => basename ( get_attached_file( $image_id ) ),
                    'alt' => $alt_text,
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                );
            }
        }
    }

    return $images;
}


function set_image_alt($images) {
	echo "<p>";
    foreach ($images as $image) {
		
		$cpt=0;
		
        if (strlen($image['alt']) < 10) {
             update_post_meta($image['id'], '_wp_attachment_image_alt', $image['product_name']);
			echo 'Media ID='.$image['id'].' ALT value updated with value "'.$image['product_name'].'"<br />';
			$cpt+=1;
        }
    } 
	echo "</p>";
	echo "<p>$cpt updated ALT images.</p>";
}
?>
