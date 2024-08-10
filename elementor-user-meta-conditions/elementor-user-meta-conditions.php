<?php
/*
 * Plugin Name: Elementor User Meta Conditions
 * Description: Add condition section in Elementor advanced tab to conditionally display elements based on user meta.
 * Version: 1.2
 * Author: Rohit Kumar
 * Author URI: https://iamrohit.net/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register necessary actions and filters
add_action( 'elementor/element/after_section_end', 'add_user_meta_condition_controls', 10, 3 );
add_action( 'elementor/frontend/widget/before_render', 'check_user_meta_conditions' );
add_action( 'elementor/frontend/section/before_render', 'check_user_meta_conditions' );
add_action( 'elementor/frontend/column/before_render', 'check_user_meta_conditions' );

function add_user_meta_condition_controls( $element, $section_id, $args ) {
    // Add the controls to the advanced section
    if ( 'section_custom_css' === $section_id ) {
        $element->start_controls_section(
            'user_meta_conditions_section',
            [
                'label' => __( 'User Meta Conditions', 'elementor-user-meta-conditions' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->add_control(
            'meta_key',
            [
                'label'       => __( 'Meta Key', 'elementor-user-meta-conditions' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter the user meta key', 'elementor-user-meta-conditions' ),
            ]
        );

        $element->add_control(
            'meta_value',
            [
                'label'       => __( 'Meta Value', 'elementor-user-meta-conditions' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter the user meta value', 'elementor-user-meta-conditions' ),
            ]
        );

        $element->add_control(
            'meta_visibility',
            [
                'label'       => __( 'Visibility', 'elementor-user-meta-conditions' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => [
                    'equal'     => __( 'Show if equal', 'elementor-user-meta-conditions' ),
                    'not_equal' => __( 'Show if not equal', 'elementor-user-meta-conditions' ),
                    'greater'   => __( 'Show if greater than', 'elementor-user-meta-conditions' ),
                    'present'   => __( 'Show if present', 'elementor-user-meta-conditions' ),
                    'absent'    => __( 'Show if absent', 'elementor-user-meta-conditions' ),
                    'hide'      => __( 'Hide if equal', 'elementor-user-meta-conditions' ),
                ],
                'default'    => 'equal',
            ]
        );

        $element->end_controls_section();
    }
}

function check_user_meta_conditions( $element ) {
    $meta_key        = $element->get_settings( 'meta_key' );
    $meta_value      = $element->get_settings( 'meta_value' );
    $meta_visibility = $element->get_settings( 'meta_visibility' );

    if ( ! empty( $meta_key ) ) {
        $user_id = get_current_user_id();
        if ( $user_id ) {
            $user_meta_value = get_user_meta( $user_id, $meta_key, true );

            $show_element = false;

            switch ( $meta_visibility ) {
                case 'equal':
                    $show_element = ( $user_meta_value == $meta_value );
                    break;
                case 'not_equal':
                    $show_element = ( $user_meta_value != $meta_value );
                    break;
                case 'greater':
                    $show_element = ( is_numeric( $user_meta_value ) && $user_meta_value > $meta_value );
                    break;
                case 'present':
                    $show_element = ( $user_meta_value !== '' && $user_meta_value !== null );
                    break;
                case 'absent':
                    $show_element = ( $user_meta_value === '' || $user_meta_value === null );
                    break;
                case 'hide':
                    $show_element = ( $user_meta_value != $meta_value );
                    break;
            }

            if ( ! $show_element ) {
                $element->add_render_attribute( '_wrapper', 'style', 'display:none;' );
            }
        }
    }
}