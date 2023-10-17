<?php
/**
 * Plugin Name: Импорт и экспорт настроек Лейки
 *
 * Description: Позволяет экспортировать и импортировать настройки Лейки.
 *
 * Author: campusboy
 * Plugin URI: https://github.com/campusboy87/leyka-import-export-options
 * Author URI: https://github.com/campusboy87/
 *
 * Requires PHP: 7.4
 * Requires at least: 5.9.0
 *
 * Version: 1.0.1
 */

defined( 'ABSPATH' ) || exit;

define( 'LIEO', true );

add_action( 'admin_menu', 'lieo__register_menu' );
add_action( 'admin_print_styles-tools_page_leyka-import-export-options', 'lieo__render_css' );

/**
 * Регистрирует пункт меню плагина в разделе "Инструменты" админ-меню.
 *
 * @return void
 */
function lieo__register_menu() {
	add_management_page(
		'Импорт/Экспорт настроек Лейки',
		'Импорт/Экспорт настроек Лейки',
		'manage_options',
		'leyka-import-export-options',
		'lieo__render_admin_page'
	);
}

/**
 * Выводит на страницах плагина CSS.
 *
 * @return void
 */
function lieo__render_css() {
	include __DIR__ . '/css.php';
}

/**
 * Получает все опции Лейки.
 *
 * @param string[] Поля, которые нужно вернуть.
 *
 * @return stdClass[]
 */
function lieo__options_list( $fields = [] ) {
	global $wpdb;

	if ( ! $fields ) {
		$fields = [ 'option_id', 'option_name', 'option_value', 'autoload' ];
	}

	$sql_from = implode( ',', $fields );
	$result   = $wpdb->get_results( "SELECT $sql_from FROM $wpdb->options WHERE option_name LIKE 'leyka_%'" );

	return $result ?: [];
}

/**
 * Генерирует админ-страницу плагина.
 *
 * @return void
 */
function lieo__render_admin_page() {
	include __DIR__ . '/templates/admin-page.php';
}

/**
 * Проверяет, является ли вкладка активной.
 *
 * @param string $check_tab
 *
 * @return bool
 */
function lieo__is_tab( $check_tab ) {
	$current_tab = $_GET['tab'] ?? 'list';

	return $check_tab === $current_tab;
}

/**
 * Получает CSS стиль для активного таба.
 *
 * @param string $check_tab
 *
 * @return string
 */
function lieo__maybe_add_css_for_active_tab( $check_tab ) {
	return lieo__is_tab( $check_tab ) ? 'nav-tab-active' : '';
}

/**
 * Получает ссылку на указанную вкладку плагина.
 *
 * @param $anchor
 *
 * @return string
 */
function lieo__tab_url( $anchor ) {
	$base_url = admin_url( 'admin.php?page=leyka-import-export-options' );

	if ( $anchor === 'list' ) {
		return $base_url;
	}

	return add_query_arg( 'tab', $anchor, $base_url );
}

/**
 * Получет опции Лейки в формате json.
 *
 * @return false|string|null
 */
function lieo__options_encode() {
	$options = lieo__options_list( [ 'option_name', 'option_value', 'autoload' ] );

	// Кодируем значение опции, чтобы избежать ошибки парсинга json при наличии двойчных кавычек
	foreach ( $options as $option ) {
		$option->option_value = base64_encode( $option->option_value );
	}

	return $options ? wp_json_encode( $options, JSON_PRETTY_PRINT ) : null;
}

/**
 * Декодирует переданные при импорте опции Лейки.
 *
 * @param $json
 *
 * @return stdClass[]|false
 */
function lieo__options_decode( $json ) {
	$options = json_decode( wp_unslash( $json ) );

	if ( is_array( $options ) ) {
		foreach ( $options as $option ) {
			$option->option_value = base64_decode( $option->option_value );
		}
	} else {
		$options = false;
	}

	return $options;
}

/**
 * Импортирует настройки в базу данных, если есть запрос на это.
 *
 * @return bool|WP_Error
 */
function lieo__maybe_import_options() {
	$json = $_POST['lieo_import_field'] ?? false;

	if ( false === $json ) {
		return false;
	}

	if ( ! $json ) {
		return new WP_Error( 'lieo_import_error', 'Не переданы данные для импорта.' );
	}

	$result = isset( $_REQUEST['lieo_export_nonce'] ) ? wp_verify_nonce( $_REQUEST['lieo_export_nonce'], 'lieo_export' ) : false;

	if ( ! $result ) {
		return new WP_Error( 'lieo_import_error', 'Импорт прерван в целях безопаности.' );
	}

	$incoming_options = lieo__options_decode( $json );

	if ( ! $incoming_options ) {
		return new WP_Error( 'lieo_import_error', 'Переданные данные не в формате json.' );
	}

	$options_for_save = [];

	foreach ( $incoming_options as $incoming_option ) {
		if ( isset( $incoming_option->option_name ) && str_starts_with( $incoming_option->option_name, 'leyka_' ) ) {
			$options_for_save[] = $incoming_option;
		}
	}

	if ( ! $options_for_save ) {
		return new WP_Error( 'lieo_import_error', 'Данные приняты, но они не от Лейки.' );
	}

	lieo__clear_options();
	lieo__save_options( $options_for_save );

	return true;
}

/**
 * Очищает базу данных от опций Лейки.
 *
 * @return void
 */
function lieo__clear_options() {
	$options = lieo__options_list( [ 'option_name' ] );

	// Удаляем старые опции через delete_option(), чтобы сбросить кеш
	foreach ( $options as $option ) {
		delete_option( $option->option_name );
	}
}

/**
 * Сохраняет настройки Лейки в базу данных.
 *
 * @param array $options
 *
 * @return int Количество сохраненных опций
 */
function lieo__save_options( $options ) {
	$check_list = [];

	foreach ( $options as $option ) {
		$check_list[] = add_option( $option->option_name, $option->option_value, '', $option->autoload );
	}

	return count( array_filter( $check_list ) );
}

/**
 * Получает данные об особых файлах Лейки, которые нужно перенести пользователю вручную.
 *
 * @return array|false
 */
function lieo__leyka_files_data() {
	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] ) {
		return false;
	}

	$path = $upload_dir['basedir'] . '/leyka/';

	if ( ! is_dir( $path ) ) {
		return false;
	}

	$dirs_and_files = scandir( $path );
	$dirs_and_files = is_array( $dirs_and_files ) ? array_diff( $dirs_and_files, [ '..', '.', 'index.php' ] ) : false;

	if ( $dirs_and_files ) {
		return [
			'path'           => $path,
			'dirs_and_files' => $dirs_and_files,
		];
	}

	return false;
}