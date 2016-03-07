		<div class='siteContentRibbon'>Správa programů</div>
		<?php echo $data['error']; ?>
		<div class='pageRibbon'>seznam programů</div>
		<div class='link'>
			<a class='link' href='?cms=new&page=program'>
		    	<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÝ PROGRAM
			</a>
		</div>

		<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
		<script>
		$(document).ready(function() {
			$("#ProgramsTable").tablesorter( {
				headers: {
					0: { sorter: false},
					1: { sorter: false},
					2: { sorter: false},
					4: { sorter: false},
					9: { sorter: false},
				}
			} );
		} );
		</script>

		<table id='ProgramsTable' class='list tablesorter'>
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th class='tab1'>ID</th>
					<th class='tab1'>název</th>
					<th class='tab1'>popis</th>
					<th class='tab1'>lektor</th>
					<th class='tab1'>e-mail</th>
					<th class='tab1'>blok</th>
					<th class='tab1'>kapacita</th>
					<th class='tab1'>kategorie</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th class='tab1'>ID</th>
					<th class='tab1'>název</th>
					<th class='tab1'>popis</th>
					<th class='tab1'>lektor</th>
					<th class='tab1'>e-mail</th>
					<th class='tab1'>blok</th>
					<th class='tab1'>kapacita</th>
					<th class='tab1'>kategorie</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if($data['render'] == 0) { ?>
				<tr class='radek1'>
					<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/edit2.gif' /></td>
					<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/pdf2.gif' /></td>
					<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/delete2.gif' /></td>
					<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>
				</tr>
				<?php
				} else {
					while($row = mysql_fetch_assoc($data['render'])) {
				?>	
				<tr class='radek1'>
					<td>
						<a href='?id=<?php echo $row['id']; ?>&cms=edit&page=program' title='Upravit'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/edit.gif' />
						</a>
					</td>
					<td>
						<a href='?cms=export-visitors&id=<?php echo $row['id']; ?>' title='Účastníci programu'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
						</a>
					</td>
					<td>
						<a href="javascript:confirmation('?id=<?php echo $row['id']; ?>&amp;cms=delete', 'program: <?php echo $row['name']; ?> -> Opravdu SMAZAT tento program? Jste si jisti?')" title='Odstranit'>
							<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' />
						</a>
					</td>
					<td class='text'><?php echo $row['id']; ?></td>
					<td class='text'><?php echo $row['name']; ?></td>
					<td class='text'><?php echo shortenText($row['description'], 70, " "); ?></td>
					<td class='text'><?php echo $row['tutor']; ?></td>
					<td class='text'><?php echo $row['email']; ?></td>
					<td class='text'><?php echo $row['block']; ?></td>
					<td class='text'><?php echo $row['capacity']; ?></td>
					<td class='text'><div class='category cat-<?php echo $row['style']; ?>'><?php echo $row['cat_name']; ?></div></td>
				</tr>
				<?php
					}
				}
				?>
			</tbody>
		</table>