<div class='siteContentRibbon'>Správa bloků</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'><?php echo $data['heading']; ?></div>

<form action='?block' method='post'>

<div class='button-line'>
	<button type='submit' onclick=\"this.form.submit()\">
		<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
	<button type='button' onclick="window.location.replace('<?php echo BLOCK_DIR; ?>')">
		<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
 <?php
 if($data['cms'] == "edit") {
 ?>
	<button type='button' onclick="window.location.replace('?block&cms=mail&pid=<?php echo $data['id']; ?>')">
		<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat lektorovi</button>
 <?php } ?>
</div>

<table class='form'>
	<tr>
		<td class='label'><label class="required">Název:</label></td>
		<td><input type='text' name='name' size='50' value='<?php echo $data['name']; ?>' /><?php $data['error_name']; ?></td>
	</tr>
	<tr>
		<td class='label'><label class="required">Den:</label></td>
		<td><?php echo $data['day_roll']; ?></td>
	</tr>
 <tr>
  <td class='label'><label class="required">Od:</label></td>
  <td><?php echo $data['hour_roll']." ".$data['minute_roll']; ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Do:</label></td>
  <td><?php echo $data['end_hour_roll']." ".$data['end_minute_roll']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Popis:</label></td>
  <td><textarea name='description' rows='10' cols="80"><?php echo $data['description']; ?></textarea><?php $data['error_description']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Lektor:</label></td>
  <td><input type='text' name='tutor' size='30' value='<?php echo $data['tutor']; ?>' /><?php $data['error_tutor']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' name='email' size='30' value='<?php echo $data['email']; ?>' /><?php $data['error_email']; ?> (více mailů musí být odděleno čárkou)</td>
 </tr>
 <tr>
  <td class='label'><label>Programový blok:</label></td>
  <td><?php echo $data['program_checkbox']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Nezobrazovat programy:</label></td>
  <td><?php echo $data['display_progs_checkbox']; ?></td>
 </tr>
 <tr>
  <td class="label"><label>Kategorie:</label></td>
  <td><?php echo $data['cat_roll']; ?></td>
 </tr>
</table>

 <input type='hidden' name='cms' value='<?php echo $data['todo']; ?>'>
 <input type='hidden' name='page' value='<?php echo $data['page']; ?>'>
 <input type='hidden' name='id' value='<?php echo $data['id']; ?>'>	
</form>