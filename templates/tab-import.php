<?php
/**
 * Шаблон формы для импорта настроек Лейки.
 */

defined( 'LIEO' ) || exit;

$options      = lieo__options_list( [ 'option_id' ] );
$files_data   = lieo__leyka_files_data();
$maybe_import = lieo__maybe_import_options();
?>

<?php if ( $options ): ?>
	<p class="lieo-important-note">
		Внимание! Это действие необратимо, поэтому сделайте бэкап базы данных заранее!<br>
		Плагин удалит существующие настройки Лейки,
		если они есть, и установит те, что вы вставите в это поле.
	</p>
	<hr>
<?php endif; ?>

<?php if ( $files_data ): ?>
	<p>
		Обнаружены файлы в папке <code><?php echo esc_html( $files_data['path'] ) ?></code><br>
		Как правило в ней Лейка хранит различные сертификаты платёжных шлюзов и другие важные файлы.<br>
	</p>
	<p>
		Эту папку нужно перекопировать на новый сайт вручную со всем содержимым.<br>
		Cписок файлов для ознакомления: <code><?php echo implode( ', ', $files_data['dirs_and_files'] ) ?></code>
	</p>
	<hr>
<?php endif; ?>

<?php if ( is_wp_error( $maybe_import ) ): ?>
	<p class="lieo-important-note">
		<?php echo $maybe_import->get_error_message() ?>
	</p>
<?php endif; ?>

<?php if ( true === $maybe_import ): ?>
	<p class="lieo-success-note">
		Настройки успешно импортировались!
	</p>
<?php endif; ?>

<form method="post" action="<?= lieo__tab_url( 'import' ) ?>">
	<?php wp_nonce_field( 'lieo_export', 'lieo_export_nonce' ); ?>

	<p>
		<label>
			Вставьте дамп настроек Лейки в формате json и нажмите кнопку "Импорт".
			<textarea class="lieo_import_field" name="lieo_import_field"></textarea>
		</label>
	</p>

	<button class="button button-primary">Импорт</button>
</form>
