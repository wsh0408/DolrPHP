<!DOCTYPE html>
<html>
<head>
    <title>出错啦！</title>
    <style>
    *{margin:0;padding: 0;}
    body{font: 14px/1.5em Consolas,'Microsoft YaHei',Arial,"Microsoft Sans Serif"; background: #999;color: #000;}
    #dolr-container{border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 12px #555;padding: 20px;background-color: white;color: black;line-height: 1.5em;margin: auto;max-width: 700px;min-width: 200px;margin-top: 50px;}
    .header{border-bottom: 1px solid #efefef;padding: 10px 0;}
    .footer{border-top: 1px solid #efefef;padding: 6px 0 0; color: #999;}
    .content{padding: 6px 0;}
    .content .errorString{word-break:break-all;}
    .content .errorString .t{width:50px;display: inline-block;}
    .header h1{font-size: 27px;font-weight: normal;}
    .tright{text-align: right;}
    </style>
</head>
<body>
    <div id="dolr-container">
        <div class="header">
            <h1>出错啦！</h1>
        </div>
        <div class="content">
            <div class="errorString">
                <p><span class="t">消息：</span>%s</p>
                <p><span class="t">文件：</span>%s</p>
                <p><span class="t">位置：</span>第 %s 行</p>
            </div>
        </div>
        <div class="footer">
            <div class="tright">&lt;?php define( 'DolrPHP' , 'less is more.' ); ?&gt;</div>
        </div>
    </div>
</body>
</html>