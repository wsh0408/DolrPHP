<div class="clearfix"></div>
       <div id="dolrTraceTool" style="display:block;" onclick="document.getElementById('DolrPHPtraceInfo').style.display='block';this.style.display='none';">TRACE</div>
       <div class="traceInfo" id="DolrPHPtraceInfo" style="display:none;">
        <div class="head"><span class="hd left">DolrPHP TRACE:</span><span class="close" onclick="var x=document.getElementById('DolrPHPtraceInfo');x.style.display='none';document.getElementById('dolrTraceTool').style.display='block';"><img style="vertical-align:top;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw=="></span></div>
            <div class="content">
            <p><span>运行用时:</span>%s 秒</p>
            <p><span>内存占用:</span>%s</p>
            <p><span>当前页面:</span>%s</p>
            <p><span>模块目录:</span>%s</p>
            <p><span>模板目录:</span>%s</p>
            <p><span>[ 加载类 ]:</span></p>
            <div class="classList">
                %s
            </div>
            <p><span>[ 运行信息 ]:</span></p>
            <div class="infoList">
                %s
            </div>
        </div>
    <style>
    .clearfix{clear:both;height:0;width:100_PERCENT_;}
    #dolrTraceTool{wdith:100px;position:fixed;bottom:0;right:0;background:#fff;color:#777;padding:5px 10px;font-weight:bold;font-size:16px;box-shadow:0 0 6px #555;}
    .traceInfo{ position:fixed;left:20_PERCENT_;bottom:5_PERCENT_; clear:both; width:80_PERCENT_; text-align:left;margin:0px auto; margin-bottom:60px;border:1px solid #f2f2f2; width:60_PERCENT_; border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 100px #777; word-break:break-all; background: #fff;font-size:12px;padding:20px; color:#000;z-index:9999999;overflow:hidden;}
    .traceInfo .red {color:red;}
    .traceInfo *{margin:0;padding:0;font-family:Consolas,Verdana,"Microsoft YaHei", Geneva, sans-serif;}
    .traceInfo .head{ padding: 0 5px 10px 10px; border-bottom: 1px solid #c0c0c0;  }
    .traceInfo .head .hd{font-size:27px;}
    .traceInfo .head span.close{ position:absolute; right:20px;top:20px; cursor:pointer; padding:0 5px; text-align:center; overflow:hidden; color:#444;font-size:22px;}
    .traceInfo .content { padding: 10px; color: #666; }
    .traceInfo .content .classList,.traceInfo .content .infoList { border: 1px dashed #ccc; padding: 5px; margin: 5px; overflow:auto; max-height:100px;}
    .traceInfo .content p { line-height: 1.5em; }
    .traceInfo .content p span { font-weight: bold; text-align: left; display: inline-block; word-spacing:2px; margin-right: 5px; }
    .traceInfo .content p span.class{ font-weight:normal;}
    .traceInfo ol{ margin-left:30px; color:#444;}
    .traceInfo ol li{line-height: 1.5em;}
    .traceInfo ul strong.red{ color:#444;}
    .traceInfo ul li{list-style:none;padding:3px 0;line-height: 1.2em;}
    /* scrollbar */
    ::-webkit-scrollbar {width: 5px; height: 11px; border: none; background: #ddd !important;}
    ::-webkit-scrollbar-track-piece {border: none; position: absolute; padding: 0; box-shadow: none; background-color:#ddd; border-radius: 1px;}
    ::-webkit-scrollbar-thumb:vertical {background-color: #999; border-radius: 0px; border: none;}
    ::-webkit-scrollbar-thumb:horizontal {background-color: #999; border-radius: 0px; border: none;}
    </style>
    </div>