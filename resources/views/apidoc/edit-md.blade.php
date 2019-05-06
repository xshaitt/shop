{extend name='main'}
{block name="content"}
<div class="container bs-docs-container" style="min-height:800px;">
  <div class="row">
    <div class="col-md-9" role="main">
      <div class="bs-docs-section">
        <!-- 用于显示md编辑器的md格式 -->
        <div id="doc-content">
          <textarea style="display:none;">{$doc_content}</textarea>
        </div>
      </div>
    </div>
    <div class="col-md-3" role="complementary" style="min-width:250px;height:600px;white-space:nowrap; overflow:auto; text-overflow:ellipsis;">
      <nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm">
        <ul class="nav bs-docs-sidenav">
          {foreach name="api_doc_sidebar_nav" item="vol" key="key"}
          <li id="{$vol.doc_nav_en}">
            <a href="?item={$item}&sidebar_nav={$vol.doc_nav_en}">{$vol.doc_nav_zh|raw}</a>
          </li>
          {/foreach}
        </ul>
        <a class="back-to-top" href="#top">
          返回顶部
        </a>
      </nav>
    </div>
  </div>
</div>
{/block}
