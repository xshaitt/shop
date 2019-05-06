@extends('apidoc.main')
@section('content')
<div class="container bs-docs-container">
  <div class="row">
    <div class="col-md-9" role="main">
      <div class="bs-docs-section">
        <?php foreach($list as $key =>$vol){?>
        <h1 id="<?php echo $vol['param']?>" class="page-header"><?php echo $vol['title']?></h1>
        <p class="lead"><code><?php echo $vol['class']?></code></p>
          <?php foreach($vol['method'] as $k => $vo){?>
          <h3 id="-<?php echo $k?>-<?php echo $vo['name']?>"><?php echo $k + 1?>. <?php echo $vo['title']?></h3>
          <p>方法名: <code><?php echo $vo['name']?>()</code></p>
          <div class="bs-callout bs-callout-info">
            <p><?php echo $vo['desc']?></p>
          </div>

          <h4>接收参数</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
              <tr>
                <td>字段名称</td>
                <th>字段类型</th>
                <th>是否必须</th>
                <th>默认值</th>
                <th>说明</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach($vo['params'] as $v){ ?>
              <tr>
                <th scope="row"><?php echo $v['name']?></th>
                <td class="text-success"><?php echo $v['type']?></td>
                <td class="text-success"><?php echo $v['require']?></td>
                <td class="text-muted"><?php echo $v['default']?></td>
                <td class="text-muted"><?php echo $v['desc']?></td>
              </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>

          <h4>返回参数</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>字段名称</th>
                <th>字段类型</th>
                <th>是否必须</th>
                <th>说明</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach($vo['returns'] as $v){ ?>
              <tr>
                <th scope="row"><?php echo $v['name']?></th>
                <td class="text-success"><?php echo $v['type']?></td>
                <td class="text-success"><?php echo $v['required']?></td>
                <td class="text-muted"><?php echo $v['detail']?></td>
              </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>

          <?php foreach($vo['example'] as $key=>$v){ ?>
          <h4>返回示例<?php echo $key + 1?>:</h4>
          <div>
            <pre id="json-renderer"><?php echo format_json(json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?></pre>
          </div>
          <?php } ?>
          <hr/>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
    <div class="col-md-3" role="complementary">
      <nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm">
        <ul class="nav bs-docs-sidenav">
          <?php foreach($list as $key=>$vol){ ?>
          <li>
            <a href="#<?php echo $vol['param']?>"><?php echo $vol['title']?></a>
            <ul class="nav">
              {foreach name="$vol.method" item="vo" key="k"}
              <?php foreach($vol['method'] as $k=>$vo){ ?>
              <li><a href="#<?php echo $key?>-<?php echo $k?>-<?php echo $vo['name']?>"><?php echo $vo['title']?></a></li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
        </ul>
        <a class="back-to-top" href="#top">
          返回顶部
        </a>
      </nav>
    </div>
  </div>
</div>
@stop
