@extends('doc.main')
@section('content')
<div class="container bs-docs-container" style="min-height:800px;">
  <div class="row">
    <div class="col-md-9" role="main">
      <div class="bs-docs-section">
        <!-- 用于显示md编辑器的md格式 -->
        <div id="doc-content">
          <textarea style="display:none;"><?php echo $doc_content?></textarea>
        </div>
      </div>
    </div>
    <div class="col-md-3" role="complementary" style="min-width:250px;height:600px;white-space:nowrap; overflow:auto; text-overflow:ellipsis;">
      <nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm">
        <ul class="nav bs-docs-sidenav">
          <?php foreach($api_doc_sidebar_nav as $key=>$vol){ ?>
          <li id="<?php echo $vol['doc_nav_en']?>">
            <a href="?item={$item}&sidebar_nav={$vol.doc_nav_en}"><?php echo $vol['doc_nav_zh']?></a>
          </li>
          <?php }?>
        </ul>
        <a class="back-to-top" href="#top">
          返回顶部
        </a>
      </nav>
    </div>
  </div>
</div>
@stop
