<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>上海黑之白文档管理系统</title>

  <!-- Bootstrap core CSS -->
  <link href="{{ asset('doc/documents/css/bootstrap.min.css') }}" rel="stylesheet">

  <!-- Documentation extras -->
  <link href="{{ asset('doc/documents/css/docs.min.css') }}" rel="stylesheet">

  <link href="{{ asset('doc/documents/css/patch.css') }}" rel="stylesheet">
  @yield('styles')
  <!--[if lt IE 9]>
  <script src="{{ asset('doc/documents/js/ie8-responsive-file-warning.js') }}"></script>
  <![endif]-->
  <script src="{{ asset('doc/documents/js/ie-emulation-modes-warning.js') }}"></script>

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="{{ asset('doc/documents/js/html5shiv.min.js') }}"></script>
  <script src="{{ asset('doc/documents/js/espond.min.js') }}"></script>
  <![endif]-->
  <!-- Favicons -->
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="icon" href="/favicon.ico">
  <!--debug-->
  <link rel="stylesheet" href="{{ asset('doc/documents/css/debug.css') }}">
  <script src="{{ asset('doc/documents/js/crypto-js-3.1.9-1/crypto-js.js') }}"></script>

  <link rel="stylesheet" href="{{ asset('doc/editor-md/css/editormd.min.css') }}" />
  <script src="{{ asset('doc/documents/js/jquery.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/marked.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/prettify.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/raphael.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/underscore.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/sequence-diagram.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/flowchart.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/lib/jquery.flowchart.min.js') }}"></script>
  <script src="{{ asset('doc/editor-md/editormd.min.js') }}" type="text/javascript" charset="utf-8"></script>
  <script>
    var _hmt = _hmt || [];
  </script>
  @yield('scripts')
</head>
<body>
<a id="skippy" class="sr-only sr-only-focusable" href="#content">
  <div class="container"><span class="skiplink-text">Skip to main content</span></div>
</a>

<!-- Docs master nav -->
<header class="navbar navbar-static-top bs-docs-nav" id="top">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target="#bs-navbar" aria-controls="bs-navbar" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <div>
        <a href="#" class="navbar-brand">上海黑之白</a>
        <a href="/doc/doc/documents" class="navbar-brand">Api接口文档</a>
        <a href="/doc/doc/apiDebug" class="navbar-brand">Api接口调试</a>
        <a href="/doc/doc/apiDevDocuments" class="navbar-brand">接口开发说明文档</a>
      </div>
    </div>
  </div>
</header>
<!-- Docs page layout -->
<div class="bs-docs-header" id="content" tabindex="-1">
  <div class="container">

  </div>
</div>

@yield('content')

<footer class="bs-docs-footer">
  <div class="container">
    <ul class="bs-docs-footer-links">
      <li><a href="https://github.com/opqnext/reflection_api_doc">GitHub 仓库</a></li>
      <li><a href="https://packagist.org/packages/opqnext/reflection-api-doc">Composer</a></li>
    </ul>

    <p>本项目源码受 <a rel="license" href="https://github.com/twbs/bootstrap/blob/master/LICENSE" target="_blank">MIT</a>开源协议保护，Copyright
      &copy; 2017 文档自动生成 All Rights Reserved. </p>

  </div>
</footer>

<script src="{{ asset('doc/documents/js/jquery.min.js') }}"></script>
<script src="{{ asset('doc/documents/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('doc/documents/js/docs.min.js') }}"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="{{ asset('doc/documents/js/ie10-viewport-bug-workaround.js') }}"></script>
<script type="text/javascript">
    var nav = GetQueryString('nav');
    $("#" + nav).addClass('active');

    var sidebar_nav = GetQueryString('sidebar_nav');
    $("#" + sidebar_nav).attr('style',"background:#DCDCDC;color:#FFFFFF;");

    function GetQueryString(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)
            return  unescape(r[2]);
        return null;
    }

    var testEditor;
    $(function() {
        testEditor = editormd.markdownToHTML("doc-content", {//注意：这里是上面DIV的id
            htmlDecode : "style,script,iframe",
            emoji : true,
            taskList : true,
            tex : true,             // 默认不解析
            flowChart : true,       // 默认不解析
            sequenceDiagram : true, // 默认不解析
            codeFold : true,
            tocm     : true,         // Using [TOCM]
        });
    });
</script>
</body>
</html>

