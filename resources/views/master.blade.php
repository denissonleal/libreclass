<!doctype html>
<html lang="en">
<head>
  <title>
    Libreclass Online
  @section('title')
  @show
  </title>
  <link rel="shortcut icon" href="{{{asset("images/favicon.ico")}}}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no ">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="/css/home.css">
  <link rel="stylesheet" type="text/css" href="/css/fa/css/font-awesome.min.css">

  <!-- Necessário para validação de formulários -->
  <link rel="stylesheet" type="text/css" href="/css/validation.css">

  <!-- Scripts are placed here -->
  <link rel="stylesheet" type="text/css" href="/js/jquery.min.js" asyn defer>
  <link rel="stylesheet" type="text/css" href="/js/bootstrap.min.js" asyn defer>
  <link rel="stylesheet" type="text/css" href="/js/register.js" asyn defer>
  <link rel="stylesheet" type="text/css" href="/js/menu.js" asyn defer>

  <script type="text/javascript">
    if (navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ){
      window.location.href("/ie");
    }
  </script>

  @section('extraJS')
  @show

</head>
<body>
  @section('body')
  @show

  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-90938637-1', 'auto');
    ga('send', 'pageview');
  </script>
</body>
</html>
