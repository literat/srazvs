<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta name="keywords" content="HKVS, vodní skauti, seascout" />
    <meta name="description" content="Hlavní kapitanát vodních skautů" />
    <meta name="author" content="HKVS team" />
    <meta name="generator" content="SunLight CMS 7.5.1 STABLE0" />
    <meta name="robots" content="index, follow" />
    <link href='<?php echo CSS_DIR ?>default.css' rel='stylesheet' type='text/css' />
    <link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/system.css?1" type="text/css" rel="stylesheet" />
    <link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/layout.css?1" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>datedit.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>dgxcolormixer_s.css" type="text/css" media="screen,projection,tv" />
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>smoothness/jquery.ui.css" type="text/css" media="screen" />
    <script type="text/javascript">/* <![CDATA[ */var sl_indexroot='./';/* ]]> */</script>
    <script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/jscript.php?1&amp;default"></script>

    <link rel="stylesheet" href="<?php echo HTTP_DIR; ?>remote/lightbox/style.css?1" type="text/css" media="screen" />
    <script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/lightbox/script.js?1"></script>

    <link rel="stylesheet" href="<?php echo CSS_DIR ?>colorbox.css" />
    <script type="text/javascript" src="<?php echo JS_DIR; ?>jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo JS_DIR; ?>jquery/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php echo JS_DIR; ?>colorbox/jquery.colorbox-min.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        $(".program-link").colorbox({rel:'programDetail', width:"75%", height:"50%", transition:"fade"});
        //Example of preserving a JavaScript event for inline calls.
        $("#click").click(function(){
            $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
            return false;
        });
    });
    </script>

	<script type="text/javascript" src="<?php echo JS_DIR ?>jquery/jquery.tinytips.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('a.programLink').tinyTips('light', 'title');
    });
    </script>

    <link rel="stylesheet" href="<?php echo CSS_DIR ?>tinytips/tinyTips.css" />
    <link rel="alternate" type="application/rss+xml" href="<?php echo HTTP_DIR; ?>remote/rss.php?tp=4&amp;id=-1" title="Nejnovější články" />
    <link rel="shortcut icon" href="<?php echo HTTP_DIR; ?>favicon.ico?1" />
    <title><?php echo $data['page_title']; ?></title>

    <!-- GA Tracking Code -->
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-325304-10']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
    <style>
        #footer {
            background: url('<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/images/outer-bottom-program.png') no-repeat scroll left top transparent;
        }

        <?php echo isset($data['css']) ? $data['css'] : ''; ?>
        <?php echo isset($data['style']) ? $data['style'] : ''; ?>
    </style>

    <!-- PlexIS -->
    <title>Program - Sraz vodních skautů</title>
</head>

<body>

<!-- outer -->
<div id="outer-program">

  <!-- page -->
  <div id="page-program">

    <!-- head -->
    <div id="head">
    <a href="<?php echo HTTP_DIR; ?>" title="HKVS - Hlavní kapitanát vodních skautů"><span>HKVS</span></a>
    </div>

    <!-- menu -->
    <div id="menu">
        <ul class='menu'>
            <li class="act menu-item-100 first"><a href='<?php echo HTTP_DIR; ?>'>Novinky</a></li>
            <li class="menu-item-7"><a href='<?php echo HTTP_DIR; ?>najdi-oddil-vs'>Najdi oddíl VS</a></li>
            <li class="menu-item-21"><a href='<?php echo HTTP_DIR; ?>o-vodnim-skautingu'>O vodním skautingu</a></li>
            <li class="menu-item-6"><a href='<?php echo HTTP_DIR; ?>vs-v-obrazech'>VS v obrazech</a></li>
            <li class="menu-item-44 last"><a href='<?php echo HTTP_DIR; ?>english-2'>English</a></li>
        </ul>
    </div>

    <hr class="hidden" />
