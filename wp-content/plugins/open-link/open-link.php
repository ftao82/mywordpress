<?php
/*
Plugin Name: Open Link
Version: 2.1
Plugin URI: https://www.xiaomac.com/open-link.html
Description: Use <code>[wp-openlink]</code> to output all your Blogroll in a Page, with website screenshot thumbnail and clicks countable, no database altered or images storage needed.
Author: Link
Author URI: https://www.xiaomac.com/
Text Domain: open-link
Domain Path: /lang
*/

add_action('init', 'open_link_init', 1);
function open_link_init() {
    if(okop('enable_link_manager')) add_filter('pre_option_link_manager_enabled', '__return_true');
    if(isset($_GET['open_link_id'])){
        $link_id = intval($_GET['open_link_id']);
        $link = get_bookmark($link_id);
        if($link && !empty($link->link_url)){
            global $wpdb;
            $wpdb->update($wpdb->links, array('link_rating' => $link->link_rating+1), array('link_id' => $link_id));
            wp_redirect($link->link_url);
        }
        exit();
    }
}

function okop($k, $v=null){
    if(!isset($GLOBALS['okop'])) $GLOBALS['okop'] = get_option('okop');
    return isset($GLOBALS['okop'][$k]) ? (isset($v) ? $GLOBALS['okop'][$k] == $v : $GLOBALS['okop'][$k]) : '';
}

add_action('admin_init', 'open_link_admin_init');
function open_link_admin_init(){
    load_plugin_textdomain('open-link', false, 'open-link/lang');
    register_setting('open_link_options_group', 'okop');
    add_filter('manage_link-manager_columns', 'open_link_columns');
    add_action('current_screen', 'open_link_this_screen');
}

add_action('admin_menu', 'open_link_meta_box');
function open_link_meta_box() {
    $menu = __('Open Link','open-link');
    add_options_page($menu, $menu, 'manage_options', 'open-link', 'open_link_options_page');
}

function open_link_data($key){
    $data = get_plugin_data( __FILE__ );
    return isset($data) && is_array($data) && isset($data[$key]) ? $data[$key] : '';
}

function open_link_columns($columns) {
    $columns['rating'] = __('Clicked','open-link');
    return $columns;
}

function open_link_this_screen() {
    $screen = get_current_screen();
    if($screen->id != 'settings_page_open-link') return;
    $screen->add_help_tab(array(
        'id'        => 'overview',
        'title'     => __('Overview','open-link'),
        'content'   => '<p>' . __('Use <code>[wp-openlink]</code> to output all your Blogroll in a Page, with website screenshot thumbnail and clicks countable, no database altered or images storage needed.','open-link') . '</p><br/>'.
            '<p><a href="//wordpress.org/plugins/open-link/" class="button" target="_blank">'.__('Ra-ting','open-link').'</a> ' .
            '<a href="//www.xiaomac.com/201312193.html" class="button" target="_blank">'.__('Su-pport','open-link').'</a> ' .
            '<a href="//www.xiaomac.com/about" class="button" target="_blank">'.__('Do-nate','open-link').'</a></p>'
    ));
}

add_action('submitlink_box', 'open_link_submitlink_box');
function open_link_submitlink_box() {
    static $link_box = true;
    if(!$link_box) return;
    $link_id = isset($_GET['link_id']) ? intval($_GET['link_id']) : '';
    if(!empty($link_id)) $link = get_bookmark($link_id);
    if(!empty($link)){
        $button = get_submit_button(__('Update Screenshot','open-link'), 'primary', 'renew', true);
        $html = '<script>
        jQuery(document).ready(function(){
            jQuery("#link_notes").before("<img src=\'"+jQuery("#link_notes").val()+"\'/>");
            jQuery("label[for=link_notes]").text("'.__('Data URI','open-link').'");
            jQuery("#link_rating").parent().parent().after("<tr><th scope=row><label>'.__('Last updated','open-link').'</label></th><td id=link_update>'.$link->link_updated.'</td></tr>");
            jQuery("#link_rating").parent().html("'.$link->link_rating.'")
            jQuery("label[for=link_rating]").text("'.__('Clicked','open-link').'");
            jQuery("#link_update").append(\'<br/>'.$button.'\');
        });
        </script>';
        echo $html;
    }
    $link_box = false;
}

add_action('edit_link', 'open_link_edit');
add_action('add_link', 'open_link_edit');
function open_link_edit($link_ID) {
    global $wpdb;
    $arr = array();
    if(isset($_POST['renew']) && okop('api_link')){
        $link = get_bookmark($link_ID);
        if($url = $link->link_url){
            $img = wp_remote_get(okop('api_link').$url, array('timeout'=>30,'sslverify'=>false));
            if(is_wp_error($img) || !isset($img['body'])){
                $arr = array('link_notes' => $img->get_error_message());
            }else{
                $base64 = 'data:image/png;base64,'.preg_replace('/\r|\n/i','',chunk_split(base64_encode($img['body'])));
                $arr = array('link_notes' => $base64);
            }
        }
    }
    $updated = array('link_updated' => date('Y-m-d H:i:s', strtotime(current_time('mysql'))));
    $wpdb->update($wpdb->links, array_merge($arr, $updated), array('link_id' => $link_ID));
    if(isset($_POST['renew'])){
        wp_redirect(admin_url('link.php?action=edit&link_id='.$link_ID));
        exit();
    }
}

add_shortcode('wp-openlink', 'open_link_list_bookmarks');
function open_link_list_bookmarks($args=''){
    $defaults = array(
        'orderby' => 'rating',
        'order' => 'DESC',
        'limit' => -1,
        'category_name' => '',
        'category' => '',
        'exclude_category' => '',
        'category_orderby' => 'id',
        'category_order' => 'ASC'
    );
    $arr = wp_parse_args($args, $defaults);
    $cats = get_terms('link_category', array(
        'name__like' => $arr['category_name'],
        'include' => $arr['category'],
        'exclude' => $arr['exclude_category'],
        'orderby' => $arr['category_orderby'],
        'order' => $arr['category_order'],
        'hierarchical' => 0
    ));
    $css = (okop('edit_style', 1) && okop('edit_style_code')) ? okop('edit_style_code') : '
        .link_span{
        font-size: 12px;
        text-align: center;
        color: #333;
        width: 100px;
        margin-right: 40px;
        margin-bottom: 22px;
        display: inline-block;
        display: -moz-inline-stack;
        zoom: 1;
        *display:inline;
    }
    .link_img {
        height: 68px;
        display: block;
        margin-bottom: 8px;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0, 0.2);
    }
    .link_text {
        display: block;
        white-space: nowrap;
        text-overflow: ellipsis;
        -o-text-overflow: ellipsis;
        overflow: hidden;
    }';
    $html = '<style>'.$css.'</style>';
    $rel = okop('external_nofollow', 1) ? 'rel="external nofollow"' : '';
    foreach ((array) $cats as $cat){
        $links = get_bookmarks(array_merge($arr, array('category'=>$cat->term_id)));
        if(empty($links)) continue;
        $html .= '<h4 class=link_cate_title>'.$cat->name.'</h4>';
        $html .= '<div id="link_cate_'.$cat->term_id.'">';
        foreach ((array) $links as $link){
            $html .= '<span class="link_span">';
            $url = okop('count_click', 1) ? '?open_link_id='.$link->link_id : $link->link_url;
            if(!okop('hide_screenshot_img',1)) $html .= '<a class="link_img" target="_blank" '.$rel.' style="background:url('.$link->link_notes.')" href="'.esc_url($url).'" title="'.esc_attr($link->link_description).'"></a>';
            $html .= '<a class="link_text" target="_blank" '.$rel.' href="'.esc_url($url).'" title="'.esc_attr($link->link_name).'">'.esc_attr($link->link_name).'</a>';
            $html .= '</span>';
        }
        $html .= '</div>';
    }
    return $html;
}

function open_link_options_page() { ?> 
    <div class="wrap">
        <h1><?php _e('Open Link','open-link')?><a class="page-title-action" href="<?php echo open_link_data('PluginURI');?>" target="_blank"><?php echo open_link_data('Version');?></a>
        </h1>
        <form action="options.php" method="post">
        <?php settings_fields('open_link_options_group'); ?>
        <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e('Enable','open-link')?></th>
        <td>
            <p><label><input name="okop[enable_link_manager]" type="checkbox" value="1" <?php checked(okop('enable_link_manager'),1);?> /> <?php _e('Enable link manager','open-link')?></label>
            <?php if(okop('enable_link_manager',1)) echo '<a href="'.admin_url('link-manager.php').'">?</a>' ?></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Link','open-link')?></th>
        <td>
            <p><label><input name="okop[hide_screenshot_img]" type="checkbox" value="1" <?php checked(okop('hide_screenshot_img'),1);?> /> <?php _e('Hide screenshot thumbnail','open-link')?></label></p>
            <p><label><input name="okop[count_click]" type="checkbox" value="1" <?php checked(okop('count_click'),1);?> /> <?php _e('Make click of the links countable','open-link')?></label></p>
            <p><label><input name="okop[external_nofollow]" type="checkbox" value="1" <?php checked(okop('external_nofollow'),1);?> /> <?php _e('Mark links external and nofollow','open-link')?></label></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Style','open-link')?></th>
        <td>
            <p><label><input name="okop[edit_style]" type="checkbox" value="1" <?php checked(okop('edit_style'),1);?> /> <?php _e('Enable to customize style code','open-link')?></label></p>
            <p><textarea name="okop[edit_style_code]" rows="4" cols="100" placeholder=""><?php echo esc_textarea( okop('edit_style_code') ) ?></textarea></p>
<pre style="border: 1px solid #ccc; padding: 8px; width: 710px">
.link_span{
    font-size: 12px;
    text-align: center;
    color: #333;
    width: 100px;
    margin-right: 40px;
    margin-bottom: 22px;
    display: inline-block;
    display: -moz-inline-stack;
    zoom: 1;
    *display:inline;
}
.link_img {
    height: 68px;
    display: block;
    margin-bottom: 8px;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0, 0.2);
}
.link_text {
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
    -o-text-overflow: ellipsis;
    overflow: hidden;
}
</pre>
        </fieldset>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('API','open-link')?></th>
        <td>
            <input name="okop[api_link]" title="<?php _e('Screenshot generating api link','open-link')?>" placeholder="http://...&url=" value="<?php echo okop('api_link')?okop('api_link'):'https://api.webthumbnail.org/?width=100&height=75&screen=1024&url=';?>" size=100 /> <a href="http://www.programmableweb.com/category/screenshots/api" target="_blank">?</a><br/>
            <p><small><code>https://api.webthumbnail.org/?width=100&amp;height=75&amp;screen=1024&amp;url=</code> <a href="https://webthumbnail.org" target="_blank">?</a></small></p>
            <p><small><code>https://api.pagelr.com/capture?b_width=1280&width=100&height=70&maxage=86400&uri=</code> <a href="https://www.pagelr.com/" target="_blank">?</a></small></p>
            <p><small><code>http://api.screenshotlayer.com/api/capture?access_key={API-KEY}&amp;viewport=1024x768&amp;width=100&amp;url=</code> <a href="https://screenshotlayer.com/" target="_blank">?</a></small></p>
            <p><small><code>http://free.pagepeeker.com/v2/thumbs.php?size=s&amp;url=</code> <a href="http://pagepeeker.com/" target="_blank">?</a></small></p>
            <p><small><code>http://api.page2images.com/directlink?p2i_key={API-KEY}&amp;p2i_url=</code> <a href="http://www.page2images.com/" target="_blank">?</a></small></p>
            <p><small><code>http://api.screenshotmachine.com/?key={API-KEY}&size=T&format=PNG&url= </code><a href="https://screenshotmachine.com/" target="_blank">?</a></small></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Shortcode','open-link')?></th>
        <td>
            <p><code>[wp-openlink]</code></p>
        </td><tr>
        </table>
        <?php submit_button();?>
        </form>
    </div>
    <?php
} 

?>