<head>
	<style>
		body {
			font-family:Arial,Geneva,Sans-Serif;
			text-align:left;
		}

		table {
			border-collapse:collapse;
			width:100%;
			margin-bottom:10px;
		}

		td {
			padding:5px;
			border:1px solid black;
			font-size:9px;
		}

		.label{font-weight:bold;width:50px;}
	</style>
</head>
<body>
	<?php foreach($data['result'] as $row){ ?>
    <table class="form">
        <tr>
            <td class="label">Program:</td>
            <td class="text"><?php echo $row['name']; ?></td>
        </tr>
        <tr>
            <td class="label">Popis:</td>
            <td class="text"><?php echo $row['description']; ?></td></tr>
        <tr>
            <td class="label">Lektor:</td>
            <td class="text"><?php echo $row['tutor']; ?></td>
        </tr>
        <tr>
            <td class="label">E-mail:</td>
            <td class="text">
                <a href="mailto:<?php echo $row['email']; ?>" title="e-mail"><?php echo $row['email']; ?></a>
            </td>
        </tr>
    </table>
    <?php } ?>
</body>
