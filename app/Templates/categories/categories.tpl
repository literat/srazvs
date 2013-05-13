		<div class='siteContentRibbon'>Správa kategorií</div>
		<div class='pageRibbon'>seznam kategorií</div>

		<?php echo $data['error']; ?>

		<div class='link'>
			<a class='link' href='?category&amp;cms=new'>
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
		<?php echo $data['render']; ?>