﻿<?php echo $data['header']; ?>
<body>
	<?php
		$i = 1;
		while($row = mysql_fetch_assoc($data['result'])){
	?>
	<h2>PŘÍJMOVÝ POKLADNÍ DOKLAD</h2>
	<table>
        <tr>
            <td width="34" style="text-align:center;">
                <img width="32" src="<?php echo $data['LOGODIR']; ?>vs_logo.jpg" />
            </td>
            <td width="450">
                <b>Junák - svaz skautů a skautek ČR, Kapitanát vodních skautů</b><br />
                &nbsp;&nbsp;Senovážné náměstí 977/24, Praha 1, 116 47 <br />
                &nbsp;&nbsp;IČ: 65991753, číslo účtu: 2300183549/2010, Fio banka a.s.<br />
                &nbsp;&nbsp;http://vodni.skauting.cz/ | mustek@hkvs.cz | +420 777 222 141  <br />
            </td>
            <td>
                <strong>číslo:</strong> <?php echo $row['numbering'].'/PPD'.$i; ?><br />
                <strong>ze dne:</strong> <?php echo $row['date']; ?><br />
            </td>
         </tr>
         <tr>
            <td colspan="3" style="margin:5px;">
                <table style="border:none;width:100%;">
                    <tr>
                        <td style="border:none;">
                            <b>Přijato od:</b> <?php echo $row['name']." ".$row['surname'].", ".$row['street'].", ".$row['city'].", ".$row['postal_code']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none;">
                            <b>Účel platby:</b> účastnický poplatek na sraz VS
                        </td>
                    </tr>
                     <tr>
                        <td style="border:none;">
                            <b>Celkem Kč:</b> =<?php echo $data['balance']; ?>,- &nbsp;&nbsp;&nbsp;&nbsp; <strong>Slovy Kč:</strong> <?php echo ucfirst(number2word($row['balance'], true)); ?>korun~
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none;text-align:right;padding-left:450px;">
                            <strong>Převzal:</strong>..................................................
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>
	<br />
	<?php
		$i++;
		}
	?>
</body>