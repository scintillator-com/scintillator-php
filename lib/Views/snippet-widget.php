<!DOCTYPE html>
<html lang="en">
<head>
	<title></title>

	<meta http-equiv="Content-Language" content="en-us" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/highlight.min.js"></script>
	<script charset="UTF-8" src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/languages/<?=$snippet->formatter->language;?>.min.js"></script>

	<!-- TODO: theme -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/styles/default.min.css" />

	<style type="text/css">
	body {
		background: #333;
	}

	a{
		color: unset;
		text-decoration: none;
	}

	#like {
		float: right;
	}

	a.set span.unset,
	a.unset span.set {
		display: inline;
	}
	
	a.set span.set,
	a.unset span.unset {
		display: none;
	}
	
	span.set:hover {
		color: red;
	}

	footer {
		color: white;
		font-family: Open Sans,Arial,sans-serif;
		padding: 5px;
	}

	#powered-by {
		font-style: oblique;
	}

	#powered-by a {
		font-style: normal;
		font-weight: bold;
	}

	.ns {
		margin:0;
		padding:0;
	}
	</style>
</head>
<body class="ns">
<main>
	<pre class="ns"><code class="language-<?=$snippet->formatter->language;?>"><?=$code;?></code></pre>
</main>
<footer>
	<span id="like"><a class="unset" href="#"><span class="set">❤︎</span><span class="unset">❤️</span></a></span>
	<span id="powered-by">Powered by <a href="https://www.scintillator.com/" target="_blank">Sctintillator.com</a></span>
</footer>
<script language="JavaScript" type="text/javascript">
hljs.initHighlightingOnLoad()
<?php
//TODO: 

//TODO: use fetch for likes
/*
window.addEventListener( 'load', () => {
})

document.addEventListener( 'DOMContentLoaded', () => {
  document.querySelectorAll('pre code').forEach((block) => {
    //hljs.highlightBlock(block);
  });
});
*/
?>
</script>
</body>
</html>