<?php
/**
 * Шаблон вывода настроек Лейки в виде таблицы.
 */

defined( 'LIEO' ) || exit;

$options = lieo__options_list();
?>

<?php if ( $options ): ?>
	<p>Количество найденных настроек: <?php echo count( $options ) ?></p>

	<table class="widefat lieo-table-list striped">
		<thead>
		<tr>
			<th class="lieo-column-id">
				ID
				<small>option_id</small>
			</th>
			<th>
				Ключ
				<small>option_name</small>
			</th>
			<th>
				Значение
				<small>option_value</small>
			</th>
			<th class="lieo-column-autoload">
				Автозагрузка
				<small>autoload</small>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $options as $option ): ?>
			<tr>
				<td><?php echo esc_html( $option->option_id ) ?></td>
				<td><?php echo esc_html( $option->option_name ) ?></td>
				<td><?php echo esc_html( $option->option_value ) ?></td>
				<td><?php echo esc_html( $option->autoload ) ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>Настройки Лейки не найдены.</p>
<?php endif; ?>
