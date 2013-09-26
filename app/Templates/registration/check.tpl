<div id="content-program">
<div id="content-pad-program">
<h1>Registrace na srazy K + K</h1>
<h2><?php echo $data['meeting_heading']; ?></h2>
<br />
<?php echo $data['error']; ?>

<!-- REGISTRACNI FORMULAR SRAZU -->

<p style="font-weight:bold;">Zkontrolujte si, prosím, Vámi zadané údaje. V případě nesouhlasících údajů a provedení změny kontaktujte, prosím, <a href="mailto:tomaslitera&#64;hotmail.com" title="správce registrace">správce</a>. Pokud problémy přetrvávají, můžete se pokusit ho na nějakém srazu VS chytit a ukamenovat...</p>

<div class='button-line'>
 <button type="button" onClick="window.location.replace('<?php echo HTTP_DIR ?>srazvs/registration/')">
  <img src='<?php echo IMG_DIR; ?>icons/new.png' /> Nová přihláška</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>srazvs/registration/?hash=<?php echo $data['hash']; ?>')">
  <img src='<?php echo IMG_DIR; ?>icons/edit.gif'  /> Upravit</button>
</div>

<table class='form'>
  <tr>
    <td class='label'>Jméno:</td>
    <td><?php echo $data['name']; ?></td>
  </tr>
  <tr>
    <td class='label'>Příjmení:</td>
    <td><?php echo $data['surname']; ?></td>
  </tr>
  <tr>
    <td class='label'>Přezdívka:</td>
    <td><?php echo $data['nick']; ?></td>
  </tr>
  <tr>
    <td class='label'>E-mail:</td>
    <td><?php echo $data['email']; ?></td>
  </tr>
  <tr>
    <td class='label'>Datum narození:</td>
    <td><?php echo $data['birthday']; ?></td>
  </tr>
  <tr>
    <td class='label'>Ulice:</td>
    <td><?php echo $data['street']; ?></td>
  </tr>
  <tr>
    <td class='label'>Město:</td>
    <td><?php echo $data['city']; ?></td>
  </tr>
  <tr>
    <td class='label'>PSČ:</td>
    <td><?php echo $data['postal_code']; ?></td>
  </tr>
  <tr>
    <td class='label'>Číslo střediska/přístavu:</td>
    <td><?php echo $data['group_num']; ?></td>
  </tr>
  <tr>
    <td class='label'>Název střediska/přístavu:</td>
    <td>
      <div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div>
      <?php echo $data['group_name']; ?>
    </td>
  </tr>
  <tr>
    <td class='label'>Název oddílu:</td>
    <td><?php echo $data['troop_name']; ?></td>
  </tr>
  <tr>
    <td class='label'>Kraj:</td>
    <td><?php echo $data['province']; ?></td>
  </tr>
  <tr>
    <td class='label'>Stravování:</td>
    <td>
      <div>páteční večeře: <span style="font-weight:bold;"><?php echo $data['meals']['fry_dinner']; ?></span></div>
      <div>sobotní snídaně: <span style="font-weight:bold;"><?php echo $data['meals']['sat_breakfast']; ?></span></div>
      <div>sobotní oběd: <span style="font-weight:bold;"><?php echo $data['meals']['sat_lunch']; ?></span></div>
      <div>sobotní večeře: <span style="font-weight:bold;"><?php echo $data['meals']['sat_dinner']; ?></span></div>
      <div>nedělní snídaně: <span style="font-weight:bold;"><?php echo $data['meals']['sun_breakfast']; ?></span></div>
      <div>nedělní oběd: <span style="font-weight:bold;"><?php echo $data['meals']['sun_lunch']; ?></span></div>
    </td>
  </tr>
  <tr>
    <td class='label'>Informace o příjezdu:</td>
    <td><?php echo $data['arrival']; ?></td>
  </tr>
  <tr>
    <td class='label'>Informace o odjezdu:</td>
    <td><?php echo $data['departure']; ?></td>
  </tr>
  <tr>
    <td class='label'>Dotazy, přání, připomínky, stížnosti:</td>
    <td><?php echo $data['comment']; ?></td>
  </tr>
  <tr>
    <td class='label'>Vaše otázka:</td>
    <td><?php echo $data['question']; ?></td>
  </tr>
</table>
 
<?php echo $data['programs']; ?>

<p><h3>Děkujeme za přihlášení na sraz VS.</h3></p>

<div class='button-line'>
 <button type="button" onClick="window.location.replace('<?php echo HTTP_DIR ?>srazvs/registration/')">
  <img src='<?php echo IMG_DIR; ?>icons/new.png' /> Nová přihláška</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>srazvs/registration/?hash=<?php echo $data['hash']; ?>')">
  <img src='<?php echo IMG_DIR; ?>icons/edit.gif'  /> Upravit</button>
</div>

<!-- REGISTRACNI FORMULAR SRAZU -->

      <p></p>
      <p style="text-align: center;"> </p>    </div>
    </div>
    <div class="cleaner"></div>  
  </div>
</div>