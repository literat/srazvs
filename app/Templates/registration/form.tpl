

<?php $page_title = "Registrace srazu VS"; ?>

    <!-- content -->
    <div id="content-program">
    <div id="content-pad-program">
<h1>Registrace na Srazy VS</h1>
<h2><?php echo $data['meeting_heading']; ?></h2>
<br />
<?php echo $data['error']; ?>

<?php if($data['display_registration']) { ?>

<script type="text/javascript" src="<?php echo JS_DIR ?>/jquery/jquery.tinytips.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('a.programLink').tinyTips('light', 'title');
});
</script>

<!-- REGISTRACNI FORMULAR SRAZU -->

<form action='?registration' method='post'>

<div class='button-line'>
 <button <?php echo $data['disabled']; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>

<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

<script src='<?php echo JS_DIR; ?>jquery/jquery-ui.js' type='text/javascript'></script>
<script type="text/javascript">
$(function() {
  $.datepicker.setDefaults($.datepicker.regional['cs']);
  $( ".datePicker" ).datepicker({
    showOn: "button",
    buttonImage: "<?php echo IMG_DIR; ?>/calendar_button.png",
    buttonImageOnly: true,
    showWeek: true,
        firstDay: 1,
    showOtherMonths: true,
        selectOtherMonths: true,
    showButtonPanel: true,
    changeMonth: true,
    changeYear: true,
    dateFormat: 'dd.mm.yy',
    maxDate: '0',
  });
});
</script>


<table class='form'>
 <tr>
  <td class='label'><label><span class="required">*</span>Jméno:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='name' size='30' value='<?php echo $data['name']; ?>' /><?php $data['error_name']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Příjmení:</label></td>
  <td><input <?php echo $data['disabled']; ?> type="text" name='surname' size="30" value='<?php echo $data['surname']; ?>' /><?php $data['error_surname']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Přezdívka:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='nick' size='30' value='<?php echo $data['nick']; ?>' /><?php $data['error_nick']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>E-mail:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='email' size='30' value='<?php echo $data['email']; ?>' /><?php $data['error_email']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Datum narození:</label></td>
  <td><div class="picker"><input <?php echo $data['disabled']; ?> id="birthday" class="datePicker" type='text' name='birthday' size='30' value='<?php echo formatDateFromDB($data['birthday'],"d.m.Y"); ?>' /></div> (datum ve formátu dd.mm.rrrr) <?php $data['error_birthday']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Ulice:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='street' size='30' value='<?php echo $data['street']; ?>' /><?php $data['error_street']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Město:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='city' size='30' value='<?php echo $data['city']; ?>' /><?php $data['error_city']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>PSČ:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='postal_code' size='10' value='<?php echo $data['postal_code']; ?>' /> (formát: 12345)<?php $data['error_postal_code']; ?></td>
 </tr>
 <tr>
  <td></td>
  <td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Číslo střediska/přístavu:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='group_num' size='10' value='<?php echo $data['group_num']; ?>' /> (formát: 214[tečka]02)<?php $data['error_group_num']; ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Název střediska/přístavu:</label></td>
  <td>
   <input <?php echo $data['disabled']; ?> type='text' name='group_name' size='30' value='<?php echo $data['group_name']; ?>' /> (2. přístav Poutníci Kolín) <?php $data['error_group_name']; ?>
  </td>
 </tr>
 <tr>
  <td class='label'><label>Název oddílu:</label></td>
  <td><input <?php echo $data['disabled']; ?> type='text' name='troop_name' size='30' value='<?php echo $data['troop_name']; ?>' /> (22. oddíl Galeje)</td>
 </tr>
 <tr>
  <td class='label'><label>Kraj:</label></td>
  <td><?php echo $data['province']; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Stravování:</label></td>
  <td><?php echo $data['meals']; ?></td>
 </tr>
 <tr>
  <td colspan="2">Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu a v kolik hodin (přibližně) a jakým prostředkem sraz opustíte.
  </td>
 </tr>
 <tr>
  <td style="font-weight:bold;" colspan="2">Pokud máte volná místa v autě a jste ochotni někoho vzít, vyplňte prosím odkud jedete a kolik máte volných míst.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Informace o příjezdu:</label></td>
  <td><textarea <?php echo $data['disabled']; ?> name='arrival' cols="50" rows="3" ><?php echo $data['arrival']; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Informace o odjezdu:</label></td>
  <td><textarea <?php echo $data['disabled']; ?> name='departure' cols="50" rows="3" ><?php echo $data['departure']; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
  <td><textarea <?php echo $data['disabled']; ?> name='comment' cols="50" rows="8" ><?php echo $data['comment']; ?></textarea></td>
 </tr>
<!-- <tr>
  <td style="font-weight:bold;" colspan="2">Máte nezodpovězené otázky ohledně Junáka a celé organizace nebo HKVS? Nerozumíte něčemu? Trápí vás to? Nevyznáte se v něčem? Potřebujete poradit? Zde napiště svoji otázku a my vám na ní odpovíme! Odpovědi najdete na jarním srazu nebo později na webu HKVS.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Vaše otázka:</label></td>
  <td><textarea <?php echo $data['disabled']; ?> name='question' cols="50" rows="8" ><?php echo $data['question']; ?></textarea></td>
 </tr>-->
</table>

 <input <?php echo $data['disabled']; ?> type='hidden' name='cms' value='create' />
 <input <?php echo $data['disabled']; ?> type='hidden' name='mid' value='<?php echo $data['mid'];  ?>' />
 <input <?php echo $data['disabled']; ?> type="hidden" name="bill" value="0" />
 
 <?php echo $data['programs']; ?>
 
 <div class='button-line'>
 <button <?php echo $data['disabled']; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>
 
</form>


<!-- REGISTRACNI FORMULAR SRAZU -->

<?php } else { ?>
<div>Registrace není otevřena, sraz se stále ještě připravuje!</div>
<?php } ?>

<p> </p>
<p style="text-align: center;"> </p>    </div>
    </div>
    <div class="cleaner"></div>
  </div>
</div>