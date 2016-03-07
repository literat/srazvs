		<div class='siteContentRibbon'>Správa kategorií</div>
		<div class='pageRibbon'>seznam kategorií</div>

		<?php echo $data['error']; ?>

		<div class='link'>
			<a class='link' href='?cms=new'>
		    	<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÁ KATEGORIE</a>
		</div>

		<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
		<script>
		$(document).ready(function() {
			$("#CategoryTable").tablesorter( {
				headers: {
					0: { sorter: false},
					1: { sorter: false},
					3: { sorter: false}
				}
			} );
		} );
		</script>
		<table id='CategoryTable' class='list tablesorter'>
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th class='tab1'>název</th>
					<th class='tab1'>styl</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th class='tab1'>název</th>
					<th class='tab1'>styl</th>
				</tr>
			</tfoot>
			<tbody>
			<?php
			foreach($data['render'] as $id => $category) {
			?>
				<tr class='radek1'>
					<td>
						<a href='?id=<?php echo $id; ?>&amp;cms=edit' title='Upravit kategorii'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/edit.gif' />
						</a>
					</td>
					<td>
						<a href="javascript:confirmation('?id=<?php echo $id; ?>.&amp;cms=delete', 'opravdu smazat kategorii <?php echo $category->name; ?>? jste si jisti?')" title='Odstranit'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' />
						</a>
					</td>
					<td><?php echo $category->name; ?></td>
					<td>
						<div class='cat-<?php echo $category->style; ?>'><?php echo $category->style; ?></div>
					</td>
        		</tr>
			<?php } ?>
			</tbody>
		</table>