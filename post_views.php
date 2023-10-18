<?php
/*
Plugin Name: MiFans Views
Description: MiFans Views allows you to display the number of views for each post at the top of the post content. Keep your readers informed about the popularity of your posts. Show your readers you are a celebrity to increase trust.
Version: 1.0.3
Author: Tyavbee Victor
Author URI: https://www.iamtsquare07.com
License: MIT License
Text Domain: mifans-views
*/

// Function to display post views
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

        $views_text = generate_views_text($views);
        
        if ($content) {
            $content = $views_text . $content;
        } else {
            return $views_text;
        }
    }
    return $content;
}

add_filter('the_content', 'display_post_views');

// Helper function to format views with abbreviations
function format_views($views) {
    if ($views >= 1000000000) {
        return round($views / 1000000000, 1) . 'B';
    } elseif ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return round($views / 1000, 1) . 'K';
    } else {
        return $views;
    }
}


// Function to generate the views text
function generate_views_text($views) {
    $views_text = 
    '<div style="border:1.5px solid #6b6f80;border-radius:3px 5px 3px 5px;margin-bottom:10px;padding:5px;" class="post-views">Viewed👀: <span style="font-weight: bold;" id="mifans-post-views">' 
    . format_views($views);

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

    $views_text .= '</span></div>';

    return $views_text;
}

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

// Function to display post view counts via shortcode
function post_views_shortcode($atts) {
    // Get the current post's view count
    $post_id = get_the_ID();
    $views = get_post_meta($post_id, 'post_views', true);

    // Check if fake views are enabled
    $fake_views_enabled = get_option('fake_views_enabled', false);

    // Add fake views if they are enabled
    if ($fake_views_enabled) {
        $fake_views = get_post_meta($post_id, 'fake_views', true);
        $views += $fake_views;
    }
    
    // Generate the views text using the same function
    $views_text = generate_views_text($views);
    
    // Return the views text
    return $views_text;
}
add_shortcode('post_views', 'post_views_shortcode');



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
            <p>
                Check this box to enable fake views for your posts. (Fake views will appear when editing post)<br>
                We promote transparency by displaying your genuine view counts, reflecting your honesty with your readers. 
                This feature is included for niches where a slight embellishment can be used to genuinely enhance your business, 
                but we are relying on your integrity to not misuse it.🙏
            </p>

            <input type="hidden" name="mifans_views_settings_submit" value="1">
            <p><input type="submit" class="button button-primary" value="Save Changes"></p>
            <p>Post views are displayed at the top of each post, If you wish to show the post views anywhere else, use this shortcode <b>[post_views]<b></p>
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
