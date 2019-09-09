<?php
/*
Plugin Name: WP Widget Visibility
Plugin URI: https://github.com/andyking93
Description: Adds controls for visibility to all existing widgets in Wordpress.
Version: 1.0
Author: Andy King
Author URI: https://github.com/andyking93

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class wp_widget_showhide {

    const
        VERSION = '1.0.0',
        ID = 'wp-widget-showhide';

    public function __construct() {

        // Set the constants needed by the plugin.
        add_action( 'plugins_loaded', array( &$this, 'constants' ), 1 );

        // Internationalize the text strings used.
        add_action( 'plugins_loaded', array( &$this, 'i18n' ), 2 );

        // Load the functions files.
        add_action( 'plugins_loaded', array( &$this, 'includes' ), 3 );

        // Load the admin style.
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_style' ) );

        // Load CSS
        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) ); 

        // Hooks
        add_action( 'in_widget_form', array( $this, 'add_settings_to_widgets_admin' ), 10, 3 );

    }

    public function constants() {

        // Set constant path to the plugin directory.
        define( 'WPVP_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

        // Set the constant path to the plugin directory URI.
        define( 'WPVP_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

        // Set the constant path to the includes directory.
        define( 'WPVP_INCLUDES', WPVP_DIR . trailingslashit( 'includes' ) );

        // Set the constant path to the includes directory.
        define( 'WPVP_CLASS', WPVP_DIR . trailingslashit( 'classes' ) );

        // Set the constant path to the assets directory.
        define( 'WPVP_CSS', WPVP_URI . trailingslashit( 'css' ) );
        
    }

    public function i18n() {

    }

    public function includes() {

    }

    public function admin_style() {

    }

    public function enqueue_scripts() {
        //wp_register_style('wpvp-css', WPVP_CSS . 'widget.css', array(), '1.0');
        //wp_enqueue_style('wpvp-css');
    }

    public function add_settings_to_widgets_admin( $widget, $empty, $instance ) {
        if ( isset( $instance['wpvp']['show_or_hide'] ) === false )
            $instance['wpvp']['show_or_hide'] = false;

        echo '
        <p>
            <label>' . __( 'Display / Hide Widget', self::ID ) . '</label>
            <select name="' . $widget->get_field_name( 'widget_select' ) . '">
                <option value="yes" ' . selected( $instance['wpvp']['show_or_hide'], true, false ) . '>' . __( 'Display widget on selected', self::ID ) . '</option>
                <option value="no" ' . selected( $instance['wpvp']['show_or_hide'], false, false ) . '>' . __( 'Hide widget on selected', self::ID ) . '</option>
            </select>
        </p>
        <p>
            <select multiple="multiple" size="10" name="' . $widget->get_field_name( 'widget_multiselect' ) . '[]">';

        foreach ( $this->widget_options as $option => $text ) {
            echo $this->get_selection_group( $option, 'widget', $widget, $instance );
        }

        echo '
            </select>
        </p>';
    }

    private function get_selection_group( $group_name, $type, $widget = '', $instance = '', $option = '' ) {
        $html = '';

        switch ( $group_name ) {
            case 'pages': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['pages'] . '">';

                    foreach ( $this->pages as $page ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['pages']['pageid_' . $page->ID] ) === false )
                                        $option['selection']['pages']['pageid_' . $page->ID] = false;

                                    $html .= '<option value="pageid_' . $page->ID . '" ' . selected( $option['selection']['pages']['pageid_' . $page->ID], true, false ) . '>' . $page->post_title . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['pages']['pageid_' . $page->ID] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['pageid_' . $page->ID] ) === false )
                                            $instance['rw_opt']['pageid_' . $page->ID] = 0;

                                        $html .= '<option value="pageid_' . $page->ID . '" ' . selected( $instance['rw_opt']['pageid_' . $page->ID], true, false ) . '>' . apply_filters( 'rw_option_display_name', $page->post_title, 'page' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'custom_post_types': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['custom_post_types'] . '">';

                    foreach ( $this->custom_post_types as $cpt ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['custom_post_types']['cpt_' . $cpt->name] ) === false )
                                        $option['selection']['custom_post_types']['cpt_' . $cpt->name] = false;

                                    $html .= '<option value="cpt_' . $cpt->name . '" ' . selected( $option['selection']['custom_post_types']['cpt_' . $cpt->name], true, false ) . '>' . sprintf( __( 'Single %s', self::ID ), $cpt->label ) . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['custom_post_types']['cpt_' . $cpt->name] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['cpt_' . $cpt->name] ) === false )
                                            $instance['rw_opt']['cpt_' . $cpt->name] = 0;

                                        $html .= '<option value="cpt_' . $cpt->name . '" ' . selected( $instance['rw_opt']['cpt_' . $cpt->name], true, false ) . '>' . apply_filters( 'rw_option_display_name', sprintf( __( 'Single %s', self::ID ), $cpt->label ), 'custom_post_type' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'custom_post_types_archives': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['custom_post_types_archives'] . '">';

                    foreach ( $this->custom_post_types_archives as $cpta ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['custom_post_types_archives']['cpta_' . $cpta->name] ) === false )
                                        $option['selection']['custom_post_types_archives']['cpta_' . $cpta->name] = false;

                                    $html .= '<option value="cpta_' . $cpta->name . '" ' . selected( $option['selection']['custom_post_types_archives']['cpta_' . $cpta->name], true, false ) . '>' . sprintf( __( '%s Archive', self::ID ), $cpta->label ) . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['custom_post_types_archives']['cpta_' . $cpta->name] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['cpta_' . $cpta->name] ) === false )
                                            $instance['rw_opt']['cpta_' . $cpta->name] = 0;

                                        $html .= '<option value="cpta_' . $cpta->name . '" ' . selected( $instance['rw_opt']['cpta_' . $cpta->name], true, false ) . '>' . apply_filters( 'rw_option_display_name', sprintf( __( '%s Archive', self::ID ), $cpta->label ), 'custom_post_type_archive' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'categories': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['categories'] . '">';

                    foreach ( $this->categories as $category ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['categories']['category_' . $category->cat_ID] ) === false )
                                        $option['selection']['categories']['category_' . $category->cat_ID] = false;

                                    $html .= '<option value="category_' . $category->cat_ID . '" ' . selected( $option['selection']['categories']['category_' . $category->cat_ID], true, false ) . '>' . $category->cat_name . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['categories']['category_' . $category->cat_ID] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['category_' . $category->cat_ID] ) === false )
                                            $instance['rw_opt']['category_' . $category->cat_ID] = 0;

                                        $html .= '<option value="category_' . $category->cat_ID . '" ' . selected( $instance['rw_opt']['category_' . $category->cat_ID], true, false ) . '>' . apply_filters( 'rw_option_display_name', $category->cat_name, 'category' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'taxonomies': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['taxonomies'] . '">';

                    foreach ( $this->taxonomies as $taxonomy ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['taxonomies']['taxonomy_' . $taxonomy->name] ) === false )
                                        $option['selection']['taxonomies']['taxonomy_' . $taxonomy->name] = false;

                                    $html .= '<option value="taxonomy_' . $taxonomy->name . '" ' . selected( $option['selection']['taxonomies']['taxonomy_' . $taxonomy->name], true, false ) . '>' . $taxonomy->label . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['taxonomies']['taxonomy_' . $taxonomy->name] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['taxonomy_' . $taxonomy->name] ) === false )
                                            $instance['rw_opt']['taxonomy_' . $taxonomy->name] = 0;

                                        $html .= '<option value="taxonomy_' . $taxonomy->name . '" ' . selected( $instance['rw_opt']['taxonomy_' . $taxonomy->name], true, false ) . '>' . apply_filters( 'rw_option_display_name', $taxonomy->label, 'taxonomy' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'others': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['others'] . '">';

                    foreach ( $this->others as $key => $value ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['others']['others_' . $key] ) === false )
                                        $option['selection']['others']['others_' . $key] = false;

                                    $html .= '<option value="others_' . $key . '" ' . selected( $option['selection']['others']['others_' . $key], true, false ) . '>' . $value . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['others']['others_' . $key] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['others_' . $key] ) === false )
                                            $instance['rw_opt']['others_' . $key] = 0;

                                        $html .= '<option value="others_' . $key . '" ' . selected( $instance['rw_opt']['others_' . $key], true, false ) . '>' . apply_filters( 'rw_option_display_name', $value, 'other' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'devices': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['devices'] . '">';

                    foreach ( $this->devices as $key => $value ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['devices']['devices_' . $key] ) === false )
                                        $option['selection']['devices']['devices_' . $key] = false;

                                    $html .= '<option value="devices_' . $key . '" ' . selected( $option['selection']['devices']['devices_' . $key], true, false ) . '>' . $value . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['devices']['devices_' . $key] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['devices_' . $key] ) === false )
                                            $instance['rw_opt']['devices_' . $key] = 0;

                                        $html .= '<option value="devices_' . $key . '" ' . selected( $instance['rw_opt']['devices_' . $key], true, false ) . '>' . apply_filters( 'rw_option_display_name', $value, 'device' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'bbpress': {
                    if ( $this->bbpress_active === false )
                        return $html;

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['bbpress'] . '">';

                    foreach ( $this->bbpress as $key => $value ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['bbpress']['bbpress_' . $key] ) === false )
                                        $option['selection']['bbpress']['bbpress_' . $key] = false;

                                    $html .= '<option value="bbpress_' . $key . '" ' . selected( $option['selection']['bbpress']['bbpress_' . $key], true, false ) . '>' . $value . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['bbpress']['bbpress_' . $key] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['bbpress_' . $key] ) === false )
                                            $instance['rw_opt']['bbpress_' . $key] = 0;

                                        $html .= '<option value="bbpress_' . $key . '" ' . selected( $instance['rw_opt']['bbpress_' . $key], true, false ) . '>' . apply_filters( 'rw_option_display_name', $value, 'bbpress' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'users': {
                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['users'] . '">';

                    foreach ( $this->users as $key => $value ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['users']['users_' . $key] ) === false )
                                        $option['selection']['users']['users_' . $key] = false;

                                    $html .= '<option value="users_' . $key . '" ' . selected( $option['selection']['users']['users_' . $key], true, false ) . '>' . $value . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['users']['users_' . $key] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['users_' . $key] ) === false )
                                            $instance['rw_opt']['users_' . $key] = 0;

                                        $html .= '<option value="users_' . $key . '" ' . selected( $instance['rw_opt']['users_' . $key], true, false ) . '>' . apply_filters( 'rw_option_display_name', $value, 'user' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
            case 'languages': {
                    if ( empty( $this->languages ) )
                        return $html;

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '<optgroup label="' . $this->widget_options['languages'] . '">';

                    foreach ( $this->languages as $key => $language ) {
                        switch ( $type ) {
                            case 'option': {
                                    if ( isset( $option['selection']['languages']['language_' . $key] ) === false )
                                        $option['selection']['languages']['language_' . $key] = false;

                                    $html .= '<option value="language_' . $key . '" ' . selected( $option['selection']['languages']['language_' . $key], true, false ) . '>' . $language['native_name'] . '</option>';

                                    break;
                                }
                            case 'widget': {
                                    if ( ! isset( $this->options['selection']['languages']['language_' . $key] ) || current_user_can( 'manage_options' ) ) {
                                        if ( isset( $instance['rw_opt']['language_' . $key] ) === false )
                                            $instance['rw_opt']['language_' . $key] = 0;

                                        $html .= '<option value="language_' . $key . '" ' . selected( $instance['rw_opt']['language_' . $key], true, false ) . '>' . apply_filters( 'rw_option_display_name', $language['native_name'], 'language' ) . '</option>';
                                    }

                                    break;
                                }
                        }
                    }

                    if ( ($this->options['groups'] === true && $type === 'widget') || current_user_can( 'manage_options' ) )
                        $html .= '</optgroup>';

                    return $html;
                }
        }
    }

}

new wp_widget_showhide;