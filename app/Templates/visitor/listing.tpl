<div class='siteContentRibbon'>Správa účastníků</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'>informace a exporty</div>
<div>
	počet účastníků: <span style="font-size:12px; font-weight:bold;"><?php echo $data['visitor-count']; ?></span>
	<span style="margin-left:10px; margin-right:10px;">|</span> 
	<a style='text-decoration:none; padding-right:2px;' href='?visitor&cms=export'>
		<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/xlsx.png' />
		export účastníků
	</a>
</div>

<div class='pageRibbon'>seznam účastníků</div>
<div class='link'>
	<form action='?visitor' method='post'>
		<label>hledej:</label>
		<input type='text' name='search' size='30' value='<?php echo $data['search']; ?>' />
        <button type='submit' onClick=\"this.form.submit()\">
  			<img src='<?php echo IMG_DIR; ?>icons/search.png' /> Hledej
		</button>
 		<!--<button type='button' onclick=\"window.location.replace('index.php')\">
  		<img src='".$ICODIR; ?>small/storno.png'  /> Zpět</button>-->
		<input type='hidden' name='cms' value='search' />
	</form>
	<a class='link' href='?visitor&cms=new&page=visitor'>
		<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÝ ÚČASTNÍK
	</a>
</div>

<script src='<?php echo JS_DIR; ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#VisitorsTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			2: { sorter: false},
			3: { sorter: false},
			4: { sorter: false},
			5: { sorter: false},
			12: { sorter: false},
		}
	} );
} );
</script>
<form name='checkerForm' method='post' action='?visitor'>
	<a href='javascript:select(1)'>Zaškrtnout vše</a> /
	<a href='javascript:select(0)'>Odškrtnout vše</a>
	<span style='margin-left:10px;'>Zaškrtnuté:</span>
	<button onClick="this.form.submit()" title='Záloha' value='advance' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/advance.png' /> Záloha
	</button>
	<button onClick="this.form.submit()" title='Platba' value='pay' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/pay.png' /> Platba
	</button>
	<button onClick="return confirm('Opravdu SMAZAT tyto účastníky? Jste si jisti?')" title='Smazat' value='delete' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' /> Smazat
	</button>
	<button onclick="this.form.action='mail.php';" title='Hromadný e-mail' value='mail' name='cms' type='submit'>
		<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Hromadný e-mail
	</button>
	<table id='VisitorsTable' class='list tablesorter'>
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th class='tab1'>ID</th>
				<th class='tab1'>symbol</th>
				<th class='tab1'>evidence</th>
				<th class='tab1'>jméno</th>
				<th class='tab1'>příjmení</th>
				<th class='tab1'>přezdívka</th>
				<th class='tab1'>e-mail</th>
				<th class='tab1'>středisko/přístav</th>
				<th class='tab1'>město</th>
				<th class='tab1'>kraj</th>
				<th class='tab1'>zaplaceno</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th class='tab1'>ID</th>
				<th class='tab1'>symbol</th>
				<th class='tab1'>evidence</th>
				<th class='tab1'>jméno</th>
				<th class='tab1'>příjmení</th>
				<th class='tab1'>přezdívka</th>
				<th class='tab1'>e-mail</th>
				<th class='tab1'>středisko/přístav</th>
				<th class='tab1'>město</th>
				<th class='tab1'>kraj</th>
				<th class='tab1'>zaplaceno</th>
			</tr>
		</tfoot>
		<tbody>
		<?php if($data['render'] == 0) { ?>
			<tr class='radek1'>
				<td><input disabled type='checkbox' /></td>
				<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/edit2.gif' /></td>
				<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/delete2.gif' /></td>
				<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/advance2.png' /></td>
				<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/pay2.png' /></td>
				<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/pdf2.png' /></td>
				<td colspan='15' class='emptyTable'>Nejsou k dispozici žádné položky.</td>
			</tr>";
		<?php
			} else {
				while($row = mysql_fetch_assoc($data['render'])) {
					if($data['meeting-price'] <= $row['bill']) {
						$payment = "<acronym title='Zaplaceno'><img src='".IMG_DIR."icons/paid.png' alt='zaplaceno' /></acronym>";
					} elseif(200 <= $row['bill']) {
						$payment = "<acronym title='Zaplacena záloha'><img src='".IMG_DIR."icons/advancement.png' alt='zaloha' /></acronym>";
					} else {
						$payment = "<acronym title='Nezaplaceno'><img src='".IMG_DIR."icons/notpaid.png' alt='nezaplaceno' /></acronym>";
					}
		?>						
			<tr class='radek1'>
				<td><input type='checkbox' name='checker[]'  value='<?php echo $row['id']; ?>' /></td>
				<td>
					<a href='?visitor&id=<?php echo $row['id']; ?>&cms=edit&page=visitor' title='Upravit'>
						<img class='edit' src='<?php echo IMG_DIR; ?>icons/edit.gif' />
					</a>
				</td>
				<td>
					<a href='?visitor&id=<?php echo $row['id']; ?>&amp;cms=advance&amp;search=<?php echo $data['search']; ?>&page=visitor' title='Záloha'>
						<img class='edit' src='<?php echo IMG_DIR; ?>icons/advance.png' />
					</a>
				</td>
				<td>
					<a href='?visitor&id=<?php echo $row['id']; ?>&amp;cms=pay&amp;search=<?php echo $data['search']; ?>&page=visitor' title='Zaplatit'>
						<img class='edit' src='<?php echo IMG_DIR; ?>icons/pay.png' />
					</a>
				</td>
				<td>
					<a href='?export&evidence=confirm&vid=<?php echo $row['id']; ?>' title='Doklad'>
						<img class='edit' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
					</a>
				</td>
				<td>
					<a href="javascript:confirmation('?visitor&id=<?php echo $row['id']; ?>&amp;cms=delete&amp;search=<?php echo $data['search']; ?>', 'účastník: <?php echo $row['nick']; ?> -> Opravdu SMAZAT tohoto účastníka? Jste si jisti?')" title='Odstranit'>
						<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' />
					</a>
				</td>
				<td class='text'><?php echo $row['id']; ?></td>
				<td class='text'><?php echo $row['code']; ?></td>
				<td class='text'><?php echo $row['group_num']; ?></td>
				<td class='text'><?php echo $row['name']; ?></td>
				<td class='text'><?php echo $row['surname']; ?></td>
				<td class='text'><?php echo $row['nick']; ?></td>
				<td class='text'><?php echo $row['email']; ?></td>
				<td class='text'><?php echo $row['group_name']; ?></td>
				<td class='text'><?php echo $row['city']; ?></td>
				<td class='text'><?php echo $row['province']; ?></td>
				<td class='text'><?php echo $payment; ?> <?php echo $row['bill']; ?>,-Kč</td>
			</tr>
		<?php
				}
			}
		?>
		</tbody>
	</table>
	<a href='javascript:select(1)'>Zaškrtnout vše</a> /
	<a href='javascript:select(0)'>Odškrtnout vše</a>
	<span style='margin-left:10px;'>Zaškrtnuté:</span>
	<button onClick="this.form.submit()" title='Záloha' value='advance' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/advance.png' /> Záloha
	</button>
	<button onClick="this.form.submit()" title='Platba' value='pay' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/pay.png' /> Platba
	</button>
	<button onClick="return confirm('Opravdu SMAZAT tyto účastníky? Jste si jisti?')" title='Smazat' value='delete' name='cms' type='submit'>
		<img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' /> Smazat
	</button>
	<button onclick="this.form.action='mail.php';" title='Hromadný e-mail' value='mail' name='cms' type='submit'>
		<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Hromadný e-mail
	</button>

	<input type='hidden' name='page' value='visitor' />
</form>