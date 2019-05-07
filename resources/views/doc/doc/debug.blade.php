{extend name='main'}
{block name="content"}
<!-- 主内容 -->
<div class="container request-box">
        <div class="col-sm-12">
            <div class="d-left">
                <div class="content-box">
                    <div class="form-request">
                      <div class="request-controller">
                        <label>控制器：&nbsp;</label><select id="c"></select>
                      </div>
                      <div class="request-action">
                        <label>执行器：&nbsp;</label><select id="a"></select>
                        <select id="loadAPI-Base-URL"></select>
                      </div>
                      <div class="request-btn">
                        <input id="POST-BTN" type="button" value="POST" />
                        <input id="GET-BTN" type="button" value="GET" />
                      </div>&nbsp;&nbsp;
                    </div>
                    <div id="explan"></div>
                </div>
              <div class="form-box-content"><form target="api_iframe"><table id="p-box"></table></form></div>
            </div>
            <div class="d-right">
                <div name="api_iframe" id="api_iframe"></div>
            </div>
        </div>
</div>
<!-- 主内容END -->
<script>
    var API_Base_URLS = ['test.changrentech.com'];
    var API_Doc=<?php echo $list;?>;
</script>
<script src="/static/documents/js/debug.js"></script>
<script src="/static/plugs/jquery/jquery.cookie.js"></script>
{/block}