<?php
/*
Plugin Name: MiFans Views
Description: MiFans Views allows you to display the number of views for each post at the top of the post content. Keep your readers informed about the popularity of your posts. Show your readers you are a celebrity to increase trust.
Version: 1.0
Author: Tyavbee Victor
Author URI: https://www.iamtsquare07.com
*/

// Function to count and display post views
function display_post_views() {
    $post_id = get_the_ID();
    $views = get_post_meta($post_id, 'post_views', true);

    if ($views == '') {
        $views = 0;
        add_post_meta($post_id, 'post_views', $views, true);
    }

    echo '<div class="post-views">Views: ' . $views . '</div>';
}

// Function to increment post views
function increment_post_views() {
    if (is_single()) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, 'post_views', true);
        $views++;
        update_post_meta($post_id, 'post_views', $views);
    }
}

add_action('wp_head', 'increment_post_views');

// Add the post views to the top of each post
add_filter('the_content', 'display_post_views');

// Cleanup the plugin files on plugin deletion
function mifans_views_delete_plugin() {
    // Check if the deletion is being triggered by WordPress, not directly
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete-selected') {
        // Delete the plugin files when the user clicks the "Delete" plugin button
        $plugin_path = plugin_dir_path(__FILE__);
        $plugin_files = array(basename(__FILE__)); // Add other plugin files if needed

        foreach ($plugin_files as $file) {
            $file_path = $plugin_path . $file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}
add_action('delete_plugin', 'mifans_views_delete_plugin');
