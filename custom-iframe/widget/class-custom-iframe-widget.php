<?php

namespace custif\widget;

use custif\includes\embed_handlers\Embed_Converter;
use custif\includes\embed_handlers\PDF_Handler;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Custom IFrame Widget Class
 *
 * Custom widget for Elementor that allows embedding iframes with advanced features
 * including lazy loading, auto-height, custom styling, and security controls.
 *
 * @since 1.0.0
 */
class Custom_IFrame_Widget extends Widget_Base {

	/**
	 * Embed converter instance.
	 *
	 * @var Embed_Converter
	 */
	private $embed_converter;

	/**
	 * PDF handler instance.
	 *
	 * @var PDF_Handler
	 */
	private $pdf_handler;

	/**
	 * Widget constructor.
	 *
	 * Initialize the widget and set up the notices manager.
	 *
	 * @param  array      $data  Widget data.
	 * @param  array|null $args  Widget arguments.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		// Initialize the embed converter and PDF handler.
		$this->embed_converter = new Embed_Converter();
		$this->pdf_handler     = new PDF_Handler();
	}

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 * @since 1.0.0
	 */
	public function get_name() {
		return 'custif_iframe_widget';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 * @since 1.0.0
	 */
	public function get_title() {
		return __( 'Custom iFrame', 'custom-iframe' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 * @since 1.0.0
	 */
	public function get_icon() {
		return 'eicon-custom-css';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 * @since 1.0.0
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget script dependencies.
	 *
	 * @return array Widget script dependencies.
	 * @since 1.0.0
	 */
	public function get_script_depends() {
		return array( 'custif-scripts' );
	}

	/**
	 * Get Widget keywords.
	 *
	 * @since 1.0.4
	 */
	public function get_keywords() {
		return array( 'iframe', 'pdf embed', 'instagram embed', 'x embed', 'twitter embed', 'doc embed' );
	}

	/**
	 * Register widget controls.
	 *
	 * Register all the widget controls/settings.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source Type', 'custom-iframe' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default' => __( 'Default', 'custom-iframe' ),
					'Pdf'     => __( 'Pdf', 'custom-iframe' ),
				),

			)
		);

		$this->add_control(
			'iframe_url',
			array(
				'label'         => esc_html__( 'Source URL', 'custom-iframe' ),
				'type'          => Controls_Manager::URL,
				'dynamic'       => array( 'active' => true ),
				'default'       => array( 'url' => 'https://example.com' ),
				'options'       => false,
				'placeholder'   => esc_html__( 'https://example.com', 'custom-iframe' ),
				'label_block'   => true,
				'show_external' => false,
				'condition'     => array(
					'source' => 'default',
				),
			)
		);

		// PDF Controls Section - Show when source is 'Pdf'.
		$this->add_control(
			'pdf_type',
			array(
				'label'     => __( 'PDF Source', 'custom-iframe' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'file',
				'options'   => array(
					'file' => __( 'File', 'custom-iframe' ),
					'url'  => __( 'URL', 'custom-iframe' ),
				),
				'condition' => array(
					'source' => 'Pdf',
				),
			)
		);

		$this->add_control(
			'pdf_Uploader',
			array(
				'label'       => __( 'Upload File', 'custom-iframe' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array(
					'active' => true,
				),
				'media_type'  => array(
					'application/pdf',
				),
				'description' => __(
					'Upload a file or pick one from your media library for embed. Supported File Type: PDF',
					'custom-iframe'
				),
				'condition'   => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_file_link',
			array(
				'label'         => __( 'URL', 'custom-iframe' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => __( 'https://your-link.com/file.pdf', 'custom-iframe' ),
				'dynamic'       => array(
					'active' => true,
				),
				'options'       => false,
				'show_external' => false,
				'default'       => array(
					'url' => '',
				),
				'condition'     => array(
					'source'   => 'Pdf',
					'pdf_type' => 'url',
				),
			)
		);

		$this->add_control(
			'pdf_file_link_info',
			array(
				'type'        => Controls_Manager::NOTICE,
				'notice_type' => 'warning',
				'dismissible' => false,
				'heading'     => '',
				'content'     => esc_html__(
					"URL PDFs can't customize  toolbar options due to external source limits",
					'custom-iframe'
				),
				'condition'   => array(
					'source'   => 'Pdf',
					'pdf_type' => 'url',
				),
			)
		);

		$this->add_responsive_control(
			'iframe_height',
			array(
				'label'      => __( 'Height', 'custom-iframe' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 2000,
					),
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 500,
				),
				'selectors'  => array(
					'{{WRAPPER}} iframe' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'auto_height!' => 'yes',
				),
				'separator'  => 'before',
			)
		);

		$this->add_control(
			'auto_height',
			array(
				'label'        => __( 'Auto Height', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'custom-iframe' ),
				'label_off'    => __( 'No', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'show_scrollbars',
			array(
				'label'     => __( 'Show Scrollbars', 'custom-iframe' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'source' => 'default',
				),
			)
		);

		$this->add_control(
			'refresh_interval',
			array(
				'label'       => __( 'Refresh Interval (seconds)', 'custom-iframe' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'step'        => 1,
				'default'     => 0,
				'description' => __( 'Set 0 to disable auto-refresh', 'custom-iframe' ),
				'condition'   => array(
					'source' => 'default',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'performance_settings_section',
			array(
				'label' => __( 'Smart Load', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_lazy_load',
			array(
				'label'        => __( 'Lazy Load', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'custom-iframe' ),
				'label_off'    => __( 'No', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
				'condition'    => array(
					'source' => 'default',
				),
			)
		);

		$this->add_control(
			'pdf_lazyload',
			array(
				'label'        => __( 'Lazy Load', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'custom-iframe' ),
				'label_off'    => __( 'No', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'placeholder_image',
			array(
				'label'     => __( 'Placeholder Image', 'custom-iframe' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'enable_lazy_load' => 'yes',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display_options',
			array(
				'label'     => __( 'Display Options', 'custom-iframe' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_theme_mode',
			array(
				'label'     => __( 'Theme', 'custom-iframe' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => __( 'System Default', 'custom-iframe' ),
					'dark'    => __( 'Dark', 'custom-iframe' ),
					'light'   => __( 'Light', 'custom-iframe' ),
					'custom'  => __( 'Custom', 'custom-iframe' ),
				),
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_custom_color',
			array(
				'label'     => __( 'Custom Color', 'custom-iframe' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#38383d',
				'condition' => array(
					'source'         => 'Pdf',
					'pdf_theme_mode' => 'custom',
					'pdf_type'       => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_zoom',
			array(
				'label'       => __( 'Zoom', 'custom-iframe' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'auto',
				'options'     => array(
					'auto'        => __( 'Automatic Zoom', 'custom-iframe' ),
					'page-actual' => __( 'Actual Size', 'custom-iframe' ),
					'page-fit'    => __( 'Page Fit', 'custom-iframe' ),
					'page-width'  => __( 'Page Width', 'custom-iframe' ),
					'custom'      => __( 'Custom', 'custom-iframe' ),
					'50'          => __( '50%', 'custom-iframe' ),
					'75'          => __( '75%', 'custom-iframe' ),
					'100'         => __( '100%', 'custom-iframe' ),
					'125'         => __( '125%', 'custom-iframe' ),
					'150'         => __( '150%', 'custom-iframe' ),
					'200'         => __( '200%', 'custom-iframe' ),
					'300'         => __( '300%', 'custom-iframe' ),
					'400'         => __( '400%', 'custom-iframe' ),
				),
				'description' => __( 'Note: Initial zoom value when the file is loaded.', 'custom-iframe' ),
				'condition'   => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_zoom_custom',
			array(
				'label'     => __( 'Custom Zoom', 'custom-iframe' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_zoom' => 'custom',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'selection_tool',
			array(
				'label'     => __( 'Default Selection Tool', 'custom-iframe' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'0' => __( 'Text Tool', 'custom-iframe' ),
					'1' => __( 'Hand Tool', 'custom-iframe' ),
				),
				'default'   => '0',
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'scrolling',
			array(
				'label'     => __( 'Default Scrolling', 'custom-iframe' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'-1' => __( 'Page Scrolling', 'custom-iframe' ),
					'0'  => __( 'Vertical Scrolling', 'custom-iframe' ),
					'1'  => __( 'Horizontal Scrolling', 'custom-iframe' ),
					'2'  => __( 'Wrapped Scrolling', 'custom-iframe' ),
				),
				'default'   => '0',
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'spreads',
			array(
				'label'     => __( 'Default Spreads', 'custom-iframe' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'0' => __( 'No Spreads', 'custom-iframe' ),
					'1' => __( 'Odd Spreads', 'custom-iframe' ),
					'2' => __( 'Even Spreads', 'custom-iframe' ),
				),
				'default'   => '0',
				'condition' => array(
					'source'     => 'Pdf',
					'scrolling!' => '1',
					'pdf_type'   => 'file',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'toolbar_setting',
			array(
				'label'     => __( 'Toolbar Settings', 'custom-iframe' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_toolbar',
			array(
				'label'        => __( 'Toolbar', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'   => 'Pdf',
					'pdf_type' => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_toolbar_position',
			array(
				'label'     => __( 'Toolbar Position', 'custom-iframe' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'top'    => array(
						'title' => __( 'Top', 'custom-iframe' ),
						'icon'  => 'eicon-arrow-up',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'custom-iframe' ),
						'icon'  => 'eicon-arrow-down',
					),
				),
				'default'   => 'top',
				'toggle'    => true,
				'condition' => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_print_download',
			array(
				'label'        => __( 'Print/Download', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_presentation_mode',
			array(
				'label'        => __( 'Presentation Mode', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_text_copy',
			array(
				'label'        => __( 'Copy Text', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_add_text',
			array(
				'label'        => __( 'Add Text', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_draw',
			array(
				'label'        => __( 'Draw', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_add_image',
			array(
				'label'        => __( 'Add Image', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_rotate_access',
			array(
				'label'        => __( 'Rotation', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->add_control(
			'pdf_details',
			array(
				'label'        => __( 'Properties', 'custom-iframe' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'custom-iframe' ),
				'label_off'    => __( 'Hide', 'custom-iframe' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'source'      => 'Pdf',
					'pdf_toolbar' => 'yes',
					'pdf_type'    => 'file',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'advance_options',
			array(
				'label' => __( 'Advance', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'custif_custom_id',
			array(
				'label'       => __( 'Custom ID', 'custom-iframe' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'my-custom-iframe', 'custom-iframe' ),
				'description' => __(
					'Enter an ID for custom CSS or JavaScript. Leave empty for an auto-generated ID.',
					'custom-iframe'
				),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'Style', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'iframe_padding',
			array(
				'label'      => __( 'Padding', 'custom-iframe' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} iframe' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'custom-iframe' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'custom-iframe' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'custom-iframe' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'custom-iframe' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'prefix_class' => 'custif-iframe-align-',
				'separator'    => 'before',
			)
		);

		$this->add_responsive_control(
			'iframe_container_width',
			array(
				'label'      => __( 'Container Width', 'custom-iframe' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 180,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
					'vw' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					'{{WRAPPER}} .custif-iframe-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'background_style',
			array(
				'label' => __( 'Background Style', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'iframe_background',
				'label'     => __( 'Background Style', 'custom-iframe' ),
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} iframe',
				'separator' => 'before',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'border_style',
			array(
				'label' => __( 'Border Style', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'iframe_border',
				'selector' => '{{WRAPPER}} iframe',
			)
		);

		$this->add_control(
			'iframe_border_radius',
			array(
				'label'      => __( 'Border Radius', 'custom-iframe' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} iframe' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'box_style',
			array(
				'label' => __( 'Box Shadow Style', 'custom-iframe' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'iframe_box_shadow',
				'selector' => '{{WRAPPER}} iframe',
			)
		);
		$this->end_controls_section();

		if ( defined( 'CUSTIF_PATH' ) ) {
			include CUSTIF_PATH . '/widget/class-need-help-controller.php';
		}
	}

	/**
	 * Render widget output.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$iframe_id = ! empty( $settings['custif_custom_id'] ) ? esc_attr( $settings['custif_custom_id'] ) : 'custif-iframe-' . $this->get_id();
		$source    = ! empty( $settings['source'] ) ? $settings['source'] : 'default';
		$pdf_type  = ! empty( $settings['pdf_type'] ) ? $settings['pdf_type'] : 'file';

		// Properly get URL from settings.
		$url = ! empty( $settings['iframe_url']['url'] ) ? $settings['iframe_url']['url'] : '';
		$url = $this->embed_converter->convert_social_to_embed( $url );

		if ( 'url' === $pdf_type && ! empty( $settings['pdf_file_link']['url'] ) ) {
			$url = esc_url( $settings['pdf_file_link']['url'] );
		}

		// Prepare placeholder image if needed.
		$placeholder_style = '';
		if ( ! empty( $settings['enable_lazy_load'] ) && ! empty( $settings['placeholder_image']['url'] ) ) {
			$placeholder_style = 'background-image: url(' . esc_url( $settings['placeholder_image']['url'] ) . '); background-size: cover; background-position: center;';
		}

		$iframe_attributes = array(
			'style'                 => $placeholder_style,
			'scrolling'             => $settings['show_scrollbars'] ? 'yes' : 'no',
			'data-auto-height'      => $settings['auto_height'],
			'data-refresh-interval' => $settings['refresh_interval'],
			'loading'               => ! empty( $settings['enable_lazy_load'] ) ? 'lazy' : '',
			'src'                   => esc_url( $url ),
		);
		$iframe_attributes = apply_filters( 'custif_iframe_attributes', $iframe_attributes, $settings );

		$iframe_html = '<iframe';

		foreach ( $iframe_attributes as $attr => $value ) {
			if ( '' !== $value && null !== $value ) {
				$iframe_html .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		$iframe_html .= '></iframe>';

		?>
		<div class="custif-iframe-wrapper" id="<?php echo esc_attr( $iframe_id ); ?>">
			<?php if ( ( ! empty( $source ) && 'default' === $source ) || ( 'url' === $pdf_type && ! empty( $settings['pdf_file_link']['url'] ) ) ) : ?>
				<?php if ( ! empty( $url ) ) : ?>
					<?php
					if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
						echo $iframe_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo $url; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				<?php else : ?>
					<div class="custif-iframe-notice">
						<div class="notice-icon">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
								<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
									  stroke-width="2"
									  d="M12 8v4m0 4h.01M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z"/>
							</svg>
						</div>
						<p><?php esc_html_e( 'Please enter a valid URL', 'custom-iframe' ); ?></p>
					</div>
				<?php endif; ?>
				<?php
			elseif ( ! empty( $source ) && 'Pdf' === $source ) :
				$this->pdf_handler->embed_pdf( $settings );
			endif;
			?>
		</div>
		<?php
	}
}