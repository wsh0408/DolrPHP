<div class="clearfix"></div>
       <div id="dolrTraceTool" style="display:block;" onclick="document.getElementById('DolrPHPtraceInfo').style.display='block';this.style.display='none';">TRACE</div>
       <div class="traceInfo" id="DolrPHPtraceInfo" style="display:none;">
        <div class="traceInfoHead"><span class="hd left">TRACE:</span><span class="close" onclick="var x=document.getElementById('DolrPHPtraceInfo');x.style.display='none';document.getElementById('dolrTraceTool').style.display='block';">—</span></div>
            <div class="traceInfoContent">
            <p><span>运行用时:</span>[TIME_USAGE] 秒</p>
            <p><span>内存占用:</span>[MEM_USAGE]</p>
            <p><span>当前页面:</span>[CURRENT_FILE]</p>
            <p><span>模块目录:</span>[MODULE_DIR]</p>
            <p><span>模板目录:</span>[TEMPLATE_DIR]</p>
            <p><span>[ 加载类 ]:</span></p>
            <div class="classList">
                [CLASSES]
            </div>
            <p><span>[ 运行信息 ]:</span></p>
            <div class="infoList">
                [RUN_INFO]
            </div>
        </div>
    <style>
    .clearfix{clear:both;height:0;width:100%;}
    #dolrTraceTool{wdith:100px;position:fixed;bottom:0;right:0;background:#fff;color:#777;padding:5px 10px;font-weight:bold;font-size:16px;box-shadow:0 0 6px #555;text-align: left;}
    .traceInfo{ position:fixed;left:20%;bottom:5%; clear:both; width:80%; text-align:left;margin:0px auto; margin-bottom:60px;border:1px solid #ccc; width:60%; -webkit-box-shadow: 0px 0px 10px #999 inset;box-shadow: 0px 0px 10px #999; word-break:break-all; background: #fff;font-size:12px;padding:20px; color:#000;z-index:9999999;overflow:hidden;}
    .traceInfo .red {color:red;}
    .traceInfo *{margin:0;padding:0;font-family:Consolas,Verdana,"Microsoft YaHei", Geneva, sans-serif;}
    .traceInfo .traceInfoHead{ padding: 0 5px 10px 10px; border-bottom: 1px solid #c0c0c0;  }
    .traceInfo .traceInfoHead .hd{font-size:27px;}
    .traceInfo .traceInfoHead span.close{ position:absolute; right:20px;top:20px; cursor:pointer; padding:0 5px; text-align:center; overflow:hidden; color:#777;font-size:22px;}
    .traceInfo .traceInfoHead span.close:hover{color: #444;}
    .traceInfo .traceInfoContent { padding: 10px; color: #666; }
    .traceInfo .traceInfoContent .classList,.traceInfo .traceInfoContent .infoList { border: 1px solid #ccc; padding: 5px; margin: 5px; overflow:auto; max-height:100px;}
    .traceInfo .traceInfoContent p { line-height: 1.5em; text-align: left;}
    .traceInfo .traceInfoContent p span { font-weight: bold; text-align: left; display: inline-block; word-spacing:2px; margin-right: 5px; }
    .traceInfo .traceInfoContent p span.class{ font-weight:normal;}
    .traceInfo ol{ margin-left:30px; color:#444;}
    .traceInfo ol li{line-height: 1.5em;}
    .traceInfo ul strong.red{ color:#444;}
    .traceInfo ul li，.traceInfo ol li{list-style:none;padding:3px 0;line-height: 1.2em;float:none;text-align: left;}
    /* scrollbar */
    ::-webkit-scrollbar {width: 5px; height: 11px; border: none; background: #ddd !important;}
    ::-webkit-scrollbar-track-piece {border: none; position: absolute; padding: 0; box-shadow: none; background-color:#ddd; border-radius: 1px;}
    ::-webkit-scrollbar-thumb:vertical {background-color: #999; border-radius: 0px; border: none;}
    ::-webkit-scrollbar-thumb:horizontal {background-color: #999; border-radius: 0px; border: none;}
    </style>
    </div>
