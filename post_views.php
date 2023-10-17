<?php
/*
Plugin Name: MiFans Views
Description: MiFans Views allows you to display the number of views for each post at the top of the post content. Keep your readers informed about the popularity of your posts. Show your readers you are a celebrity to increase trust.
Version: 1.0.1
Author: Tyavbee Victor
Author URI: https://www.iamtsquare07.com
*/

// Function to display post views
function display_post_views($content) {
    if (is_single()) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, 'post_views', true);
        
        // Check if fake views are enabled
        $fake_views_enabled = get_option('fake_views_enabled', false);

        // Add fake views if they are enabled
        if ($fake_views_enabled) {
            $fake_views = get_post_meta($post_id, 'fake_views', true);
            $views += $fake_views;
        }

        $views_text = '<div class="post-views">Viewed: <span id="mifans-post-views">' . $views;

        if ($views <= 1) {
            $views_text .= ' time';
        } else {
            $views_text .= ' times';
        }

        // Add emojis based on view count
        if ($views >= 1000000) {
            $views_text .= ' 🎆🎆🎆';
        } elseif ($views >= 500000) {
            $views_text .= ' 🎆🎆';
        } elseif ($views >= 100000) {
            $views_text .= ' 🎆';
        } elseif ($views >= 50000) {
            $views_text .= ' 🔥🔥🔥🔥';
        } elseif ($views >= 10000) {
            $views_text .= ' 🔥🔥🔥';
        } elseif ($views >= 1000) {
            $views_text .= ' 🔥🔥';
        } elseif ($views >= 100) {
            $views_text .= ' 🔥';
        }

        $views_text .= '👀</span></div>';
        $content = $views_text . $content;
    }
    return $content;
}

add_filter('the_content', 'display_post_views');

// Function to increment post views on the frontend
function increment_post_views() {
    if (is_single() && !is_admin()) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, 'post_views', true);
        $views++;
        update_post_meta($post_id, 'post_views', $views);
    }
}

add_action('wp_head', 'increment_post_views');

// Add a settings menu to enable fake views
function mifans_views_settings_menu() {
    add_menu_page('MiFans Views Settings', 'MiFans Views', 'manage_options', 'mifans-views-settings', 'mifans_views_settings_page');
}

add_action('admin_menu', 'mifans_views_settings_menu');

function mifans_views_settings_page() {
    if (isset($_POST['mifans_views_settings_submit'])) {
        $fake_views_enabled = isset($_POST['fake_views_enabled']) ? true : false;
        update_option('fake_views_enabled', $fake_views_enabled);
    }

    $fake_views_enabled = get_option('fake_views_enabled', false);
    ?>
    <div class="wrap">
        <h2>MiFans Views Settings</h2>
        <form method="post">
            <label for="fake_views_enabled">
                <input type="checkbox" name="fake_views_enabled" id="fake_views_enabled" <?php if ($fake_views_enabled) echo 'checked'; ?> />
                Enable fake views
            </label>
            <p>Check this box to enable fake views for your posts.</p>
            <input type="hidden" name="mifans_views_settings_submit" value="1">
            <p><input type="submit" class="button button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php
}

// Add a custom meta box for fake views in the post editor
function mifans_views_fake_views_meta_box() {
    add_meta_box(
        'mifans-views-fake-views-meta-box',
        'Fake Views',
        'display_fake_views_meta_box',
        'post',
        'side',
        'high'
    );
}

add_action('add_meta_boxes', 'mifans_views_fake_views_meta_box');

function display_fake_views_meta_box($post) {
    $fake_views = get_post_meta($post->ID, 'fake_views', true);
    ?>
    <label for="fake-views">Fake Views: </label>
    <input type="number" id="fake-views" name="fake_views" value="<?php echo $fake_views; ?>">
    <?php
}

// Save the fake views when the post is updated
function save_fake_views($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['fake_views'])) {
        $fake_views = sanitize_text_field($_POST['fake_views']);
        update_post_meta($post_id, 'fake_views', $fake_views);
    }
}

add_action('save_post', 'save_fake_views');

// Cleanup the plugin files on plugin deletion
function mifans_views_delete_plugin() {
    // Check if the deletion is being triggered by WordPress
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete-selected') {
        // Delete the plugin files when the user clicks the "Delete" plugin button
        $plugin_path = plugin_dir_path(__FILE__);
        $plugin_files = array(basename(__FILE__));
        foreach ($plugin_files as $file) {
            $file_path = $plugin_path . $file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}

add_action('delete_plugin', 'mifans_views_delete_plugin');
