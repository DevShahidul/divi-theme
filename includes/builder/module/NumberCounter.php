<?php

class ET_Builder_Module_Number_Counter extends ET_Builder_Module {
	function init() {
		$this->name       = esc_html__( 'Number Counter', 'et_builder' );
		$this->plural     = esc_html__( 'Number Counters', 'et_builder' );
		$this->slug       = 'et_pb_number_counter';
		$this->vb_support = 'on';
		$this->custom_css_fields = array(
			'percent' => array(
				'label'    => esc_html__( 'Percent', 'et_builder' ),
				'selector' => '.percent',
			),
			'number_counter_title' => array(
				'label'    => esc_html__( 'Number Counter Title', 'et_builder' ),
				'selector' => 'h3',
			),
		);

		$this->main_css_element = '%%order_class%%.et_pb_number_counter';

		$this->settings_modal_toggles = array(
			'general'  => array(
				'toggles' => array(
					'main_content' => esc_html__( 'Text', 'et_builder' ),
					'elements'     => esc_html__( 'Elements', 'et_builder' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text' => array(
						'title'    => esc_html__( 'Text', 'et_builder' ),
						'priority' => 49,
					),
				),
			),
		);

		$this->advanced_fields = array(
			'fonts'                 => array(
				'title' => array(
					'label'    => esc_html__( 'Title', 'et_builder' ),
					'css'      => array(
						'main'      => "{$this->main_css_element} h3, {$this->main_css_element} h1.title, {$this->main_css_element} h2.title, {$this->main_css_element} h4.title, {$this->main_css_element} h5.title, {$this->main_css_element} h6.title",
						'important' => 'plugin_only',
					),
					'header_level' => array(
						'default' => 'h3',
					),
				),
				'number'   => array(
					'label'    => esc_html__( 'Number', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .percent p",
					),
					'line_height' => array(
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
					'text_color' => array(
						'old_option_ref' => 'counter_color',
						'default' => et_builder_accent_color(),
					),
				),
			),
			'background'            => array(
				'settings' => array(
					'color' => 'alpha',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'important' => array( 'custom_margin' ),
				),
			),
			'max_width'             => array(
				'css' => array(
					'module_alignment' => '%%order_class%%.et_pb_number_counter.et_pb_module',
				),
			),
			'text'                  => array(
				'use_background_layout' => true,
				'options' => array(
					'text_orientation'  => array(
						'default' => 'center',
					),
					'background_layout' => array(
						'default' => 'light',
						'hover' => 'tabs',
					),
				),
				'css' => array(
					'main' => '%%order_class%% .title, %%order_class%% .percent',
				)
			),
			'button'                => false,
		);

		if ( et_builder_has_limitation( 'force_use_global_important' ) ) {
			$this->advanced_fields['fonts']['number']['css']['important'] = 'all';
		}

		$this->help_videos = array(
			array(
				'id'   => esc_html( 'qEE6z2t2oJ8' ),
				'name' => esc_html__( 'An introduction to the Number Counter module', 'et_builder' ),
			),
		);
	}

	function get_fields() {
		$fields = array(
			'title' => array(
				'label'           => esc_html__( 'Title', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Input a title for the counter.', 'et_builder' ),
				'toggle_slug'     => 'main_content',
				'dynamic_content' => 'text',
				'mobile_options'  => true,
				'hover'           => 'tabs',
			),
			'number' => array(
				'label'           => esc_html__( 'Number', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'value_type'      => 'float',
				'description'     => esc_html__( "Define a number for the counter. (Don't include the percentage sign, use the option below.)", 'et_builder' ),
				'toggle_slug'     => 'main_content',
				'default_on_front' => '0',
				'mobile_options'   => true,
				'hover'            => 'tabs',
			),
			'percent_sign' => array(
				'label'             => esc_html__( 'Percent Sign', 'et_builder' ),
				'type'              => 'yes_no_button',
				'option_category'   => 'configuration',
				'options'           => array(
					'on'  => esc_html__( 'On', 'et_builder' ),
					'off' => esc_html__( 'Off', 'et_builder' ),
				),
				'default_on_front' => 'on',
				'toggle_slug'       => 'elements',
				'description'       => esc_html__( 'Here you can choose whether the percent sign should be added after the number set above.', 'et_builder' ),
				'mobile_options'    => true,
				'hover'             => 'tabs',
			),
			'counter_color' => array(
				'type'              => 'hidden',
				'default'           => '',
				'tab_slug'          => 'advanced',
			),
		);
		return $fields;
	}

	function render( $attrs, $content = null, $render_slug ) {
		wp_enqueue_script( 'easypiechart' );

		$multi_view                      = et_pb_multi_view_options( $this );
		$number                          = $this->props['number'];
		$percent_sign                    = $this->props['percent_sign'];
		$title                           = $multi_view->render_element( array(
			'tag'     => et_pb_process_header_level( $this->props['title_level'], 'h3' ),
			'content' => '{{title}}',
			'attrs'   => array(
				'class' => 'title',
			),
		) );
		$counter_color                   = $this->props['counter_color'];

		// Background Layout.
		$background_layout               = $this->props['background_layout'];
		$background_layout_hover         = et_pb_hover_options()->get_value( 'background_layout', $this->props, 'light' );
		$background_layout_hover_enabled = et_pb_hover_options()->is_enabled( 'background_layout', $this->props );
		$background_layout_values        = et_pb_responsive_options()->get_property_values( $this->props, 'background_layout' );
		$background_layout_tablet        = isset( $background_layout_values['tablet'] ) ? $background_layout_values['tablet'] : '';
		$background_layout_phone         = isset( $background_layout_values['phone'] ) ? $background_layout_values['phone'] : '';


		if ( et_builder_has_limitation( 'register_fittext_script' ) ) {
			wp_enqueue_script( 'fittext' );
		}

		$separator                 = strpos( $number, ',' ) ? ',' : '';
		$number                    = str_ireplace( array( '%', ',' ), '', $number );
		$video_background          = $this->video_background();
		$parallax_image_background = $this->get_parallax_image_background();

		// Module classnames
		$this->add_classname( array(
			"et_pb_bg_layout_{$background_layout}",
			$this->get_text_orientation_classname(),
		) );

		if ( ! empty( $background_layout_tablet ) ) {
			$this->add_classname( "et_pb_bg_layout_{$background_layout_tablet}_tablet" );
		}

		if ( ! empty( $background_layout_phone ) ) {
			$this->add_classname( "et_pb_bg_layout_{$background_layout_phone}_phone" );
		}

		if ( '' !== $title ) {
			$this->add_classname( 'et_pb_with_title' );
		}

		$data_background_layout       = '';
		$data_background_layout_hover = '';
		if ( $background_layout_hover_enabled ) {
			$data_background_layout = sprintf(
				' data-background-layout="%1$s"',
				esc_attr( $background_layout )
			);
			$data_background_layout_hover = sprintf(
				' data-background-layout-hover="%1$s"',
				esc_attr( $background_layout_hover )
			);
		}

		$multi_view->set_conditional_value( 'percent_sign', '%', array(
			'percent_sign' => 'on',
		) );

		$multi_view->set_conditional_value( 'percent_sign', '&nbsp;', array(
			'percent_sign' => 'off',
		) );

		$multi_view_data_attr = $multi_view->render_attrs( array(
			'attrs' => array(
				'data-number-value'     => '{{number}}',
				'data-number-separator' => '{{number}}',
				'data-percent-sign'     => '{{percent_sign}}',
			),
		) ) ;

		$output = sprintf(
			'<div%1$s class="%2$s" data-number-value="%3$s" data-number-separator="%7$s"%10$s%11$s>
				%9$s
				%8$s
				<div class="percent" %4$s%12$s><p><span class="percent-value"></span><span class="percent-sign">%5$s</span></p></div>
				%6$s
			</div><!-- .et_pb_number_counter -->',
			$this->module_id(),
			$this->module_classname( $render_slug ),
			esc_attr( $number ),
			( '' !== $counter_color ? sprintf( ' style="color:%s"', esc_attr( $counter_color ) ) : '' ),
			( 'on' == $multi_view->get_value( 'percent_sign' ) ? '%' : ''), // #5
			$title,
			esc_attr( $separator ),
			$video_background,
			$parallax_image_background,
			et_core_esc_previously( $data_background_layout ), // #10
			et_core_esc_previously( $data_background_layout_hover ),
			$multi_view_data_attr
		 );

		return $output;
	}

	/**
	 * Filter multi view value.
	 *
	 * @since ??
	 * 
	 * @see ET_Builder_Module_Helper_MultiViewOptions::filter_value
	 *
	 * @param mixed $raw_value Props raw value.
	 * @param array $args {
	 *     Context data.
	 *
	 *     @type string $context      Context param: content, attrs, visibility, classes.
	 *     @type string $name         Module options props name.
	 *     @type string $mode         Current data mode: desktop, hover, tablet, phone.
	 *     @type string $attr_key     Attribute key for attrs context data. Example: src, class, etc.
	 *     @type string $attr_sub_key Attribute sub key that availabe when passing attrs value as array such as styes. Example: padding-top, margin-botton, etc.
	 * }
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return mixed
	 */
	public function multi_view_filter_value( $raw_value, $args, $multi_view ) {
		$name     = isset( $args['name'] ) ? $args['name'] : '';
		$mode     = isset( $args['mode'] ) ? $args['mode'] : '';
		$attr_key = isset( $args['attr_key'] ) ? $args['attr_key'] : '';

		if ( $raw_value && 'number' === $name ) {
			if ( 'data-number-separator' === $attr_key ) {
				return strpos( $raw_value, ',' ) ? ',' : '';
			}

			return str_replace( array( '%' ), '', $raw_value );
		}

		$fields_need_escape = array(
			'title',
		);

		if ( $raw_value && in_array( $name, $fields_need_escape, true ) ) {
			return $this->_esc_attr( $multi_view->get_name_by_mode( $name, $mode ) );
		}

		return $raw_value;
	}
}

new ET_Builder_Module_Number_Counter;
