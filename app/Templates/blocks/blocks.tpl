		<div class='siteContentRibbon'>Správa bloků</div>
		<?php echo $data['error']; ?>
		<div class='pageRibbon'>seznam bloků</div>
		<div class='link'>
			<a class='link' href='?block&cms=new'>
		    	<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÝ BLOK
			</a>
		</div>

		<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
		<script>
		$(document).ready(function() {
			$("#BlocksTable").tablesorter( {
				headers: {
					0: { sorter: false},
					1: { sorter: false},
					7: { sorter: false},
					10: { sorter: false},
				}
			} );
		} );
		</script>

		<table id='BlocksTable' class='list tablesorter'>
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th class='tab1'>ID</th>
					<th class='tab1'>den</th>
					<th class='tab1'>od</th>
					<th class='tab1'>do</th>
					<th class='tab1'>název</th>
					<th class='tab1'>popis</th>
					<th class='tab1'>lektor</th>
					<th class='tab1'>e-mail</th>
					<th class='tab1'>kategorie</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th class='tab1'>ID</th>
					<th class='tab1'>den</th>
					<th class='tab1'>od</th>
					<th class='tab1'>do</th>
					<th class='tab1'>název</th>
					<th class='tab1'>popis</th>
					<th class='tab1'>lektor</th>
					<th class='tab1'>e-mail</th>
					<th class='tab1'>kategorie</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if($data['render'] == 0) { ?>
				<tr class='radek1'>
					<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/edit2.gif' /></td>
					<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/delete2.gif' /></td>
					<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>
				</tr>
				<?php
				} else {
					//var_dump(mysql_fetch_assoc($data['render']));
					while($row = mysql_fetch_assoc($data['render'])) {
				?>	
				<tr class='radek1'>
					<td>
						<a href='?block&id=<?php echo $row['id']; ?>&cms=edit&page=block' title='Upravit'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/edit.gif' />
						</a>
					</td>
					<td>
						<a href="javascript:confirmation('?block&id=<?php echo $row['id']; ?>&amp;cms=delete', 'blok: <?php echo $row['name']; ?> <?php echo $row['from']; ?> -> Opravdu SMAZAT tento blok? Jste si jisti?')" title='Odstranit'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' />
						</a>
					</td>
					<td class='text'><?php echo $row['id']; ?></td>
					<td class='text'><?php echo $row['day']; ?></td>
					<td class='text'><?php echo $row['from']; ?></td>
					<td class='text'><?php echo $row['to']; ?></td>
					<td class='text'><?php echo $row['name']; ?></td>
					<td class='text'><?php echo shortenText($row['description'], 70, " "); ?></td>
					<td class='text'><?php echo $row['tutor']; ?></td>
					<td class='text'><?php echo $row['email']; ?></td>
					<td class='text'><div class='cat-<?php echo $row['style']; ?>'><?php echo $row['cat_name']; ?></div></td>
				</tr>
				<?php
					}
				}
				?>
			</tbody>
		</table>