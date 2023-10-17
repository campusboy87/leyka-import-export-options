<?php

defined( 'LIEO' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'У вас недостаточно прав для просмотра этой страницы.' );
}
?>

<div class="wrap">

	<h2>
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h2>

	<div id="message" class="notice notice-error">
		<p>
			<b>
				После переноса настроек Лейки деактивируйте плагин,
				так как держать его включённым потенциально небезопасно!
			</b>
		</p>
	</div>

	<nav class="nav-tab-wrapper lieo-nav-tab-wrapper">
		<a href="<?php echo lieo__tab_url( 'list' ) ?>" class="nav-tab <?php echo lieo__maybe_add_css_for_active_tab( 'list' ) ?>">
			Список
		</a>
		<a href="<?php echo lieo__tab_url( 'export' ) ?>" class="nav-tab <?php echo lieo__maybe_add_css_for_active_tab( 'export' ) ?>">
			Экспорт
		</a>
		<a href="<?php echo lieo__tab_url( 'import' ) ?>" class="nav-tab <?php echo lieo__maybe_add_css_for_active_tab( 'import' ) ?>">
			Импорт
		</a>
	</nav>

	<div class="tabs-container">
		<?php
		if ( lieo__is_tab( 'list' ) ) {
			include __DIR__ . '/tab-list.php';
		}

		if ( lieo__is_tab( 'export' ) ) {
			include __DIR__ . '/tab-export.php';
		}

		if ( lieo__is_tab( 'import' ) ) {
			include __DIR__ . '/tab-import.php';
		}
		?>
	</div>

</div>
