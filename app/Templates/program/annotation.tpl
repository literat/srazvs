<?php include_once(TPL_DIR."vodni_header.tpl"); ?>

    <!-- content -->
    <div id="content-program">
    <div id="content-pad-program">
<h1>Anotace programu na srazy VS</h1>
<br />
<h2><?php echo $data['meeting_heading']; ?></h2>
<br />
<?php echo $data['error']; ?>

<form action='?program' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div> 

<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

<table class='form'>
 <tr>
  <td class='label'><label><span class="required">*</span>Název:</label></td>
  <td><input type='text' name='name' size='30' value='<?php echo $data['name']; ?>' /><?php $data['error_name']; ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, popis Tvého programu (bude se zobrazovat účastníkům na webu při výběru programu).
  </td>
 </tr>
 <tr>
  <td class='label'><label>Popis:</label></td>
  <td><textarea name='description' rows='10' cols="70"><?php echo $data['description']; ?></textarea><?php $data['error_description']; ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, vybavení, které budeš potřebovat na Tvůj program a které máme zajistit.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Materiál:</label></td>
  <td><textarea name='material' rows='2' cols="70"><?php echo $data['material']; ?></textarea><?php $data['error_material']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Lektor:</label></td>
  <td><input type='text' name='tutor' size='30' value='<?php echo $data['tutor']; ?>' /><?php $data['error_tutor']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' name='email' size='30' value='<?php echo $data['email']; ?>' /><?php $data['error_email']; ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, maximální počet účastníků, kteří se mohou Tvého programu zúčastnit.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Kapacita:</label></td>
  <td><input type='text' name='capacity' size='10' value='<?php echo $data['capacity']; ?>' /> (omezeno na 255)</td>
 </tr>
</table>

 <input type='hidden' name='cms' value='modify'>
 <input type='hidden' name='id' value='<?php echo $data['id']; ?>'>
 <input type='hidden' name='block' value='<?php echo $data['block']; ?>'>
 <input type='hidden' name='category' value='<?php echo $data['category']; ?>'>
 <input type='hidden' name='formkey' value='<?php echo $hash; ?>'>
 <input type='hidden' name='type' value='<?php echo $type; ?>'>
 
 <div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>
 
</form>

        <p></p>
        <p style="text-align: center; "></p>
      </div>
    </div>
    <div class="cleaner"></div>
  </div>
</div>

<?php include_once(TPL_DIR."vodni_footer.tpl"); ?>