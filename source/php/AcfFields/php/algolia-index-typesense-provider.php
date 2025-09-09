<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_68bfb035c379f',
    'title' => __('Typesense Provider Settings', 'algolia-index-typesense-provider'),
    'fields' => array(
        0 => array(
            'key' => 'field_68bfb0352423b',
            'label' => __('Typesense Application ID', 'algolia-index-typesense-provider'),
            'name' => 'algolia_index_typesense_application_id',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => __('May be overridden by TYPESENSEINDEX_APPLICATION_ID constant', 'algolia-index-typesense-provider'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'allow_in_bindings' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        1 => array(
            'key' => 'field_68bfb2e1b3095',
            'label' => __('Typesense API Key', 'algolia-index-typesense-provider'),
            'name' => 'algolia_index_typesense_api_key',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => __('May be overridden by TYPESENSEINDEX_API_KEY constant', 'algolia-index-typesense-provider'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'allow_in_bindings' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        2 => array(
            'key' => 'field_68bfb2f7b3096',
            'label' => __('Typesense Index Name', 'algolia-index-typesense-provider'),
            'name' => 'algolia_index_typesense_index_name',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => __('May be overridden by TYPESENSEINDEX_INDEX_NAME constant. Leave blank to create one for you.', 'algolia-index-typesense-provider'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'allow_in_bindings' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'algolia-index-settings',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'show_in_rest' => 0,
));
}