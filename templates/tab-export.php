<?php
/**
 * Шаблон вывода настроек Лейки для экспорта (копирования).
 */

defined( 'LIEO' ) || exit;

$json_options = lieo__options_encode();
?>

<?php if ( $json_options ): ?>
	<p>
		Скопируйте дамп настроек Лейки из этого поля и примените в поле на вкладке "Импорт"
		на другом сайте (там также должен быть установлен данный плагин).
	</p>

	<p>
		Значение поля <b>option_value</b> закодировано для корректного переноса.
		Реальные значения можно увидеть во вкладке "<a href="<?php echo lieo__tab_url( 'list' ) ?>">Список</a>".
	</p>

	<label>
		<textarea class="lieo_export_field" onClick="this.select();"><?php echo $json_options; ?></textarea>
	</label>
<?php else: ?>
	<p>Настройки Лейки не найдены.</p>
<?php endif; ?>
