<?php
namespace GDO\Mail\tpl;
use GDO\Util\Strings;
/** @var $content string **/
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<h2><?=sitename()?></h2>
<div>
<?=Strings::nl2brHTMLSafe($content)?>
</div>
</body>
</html>
