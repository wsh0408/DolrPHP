<!DOCTYPE html>
<html>
<head>
    <title>出错啦！</title>
    <style>
    *{margin:0;padding: 0;}
    body{font: 12px/1.5em Consolas,'Microsoft YaHei',Arial,"Microsoft Sans Serif"; background: #999;color: #000;}
    #dolr-container{border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 12px #555;padding: 20px;width:60%;background-color: white;color: black;line-height: 1.5em;margin: auto;min-width: 200px;margin-top: 50px;}
    .header{border-bottom: 1px solid #efefef;padding: 10px 0;}
    .footer{border-top: 1px solid #efefef;padding: 6px 0 0; color: #999;}
    .content{padding: 6px 0;}
    .content .errorString{word-break:break-all;}
    .content .errorString .t{width:50px;display: inline-block;}
    .content .trace{margin:5px 0;padding: 5px 0; border-top: 1px solid #efefef;}
    .content .trace ul li{list-style: none; position: relative;}
    .content .trace ul li.even{background: #f5f5f5;}
    .content .trace ul li span{margin-right: 10px;}
    .header h1{font-size: 27px;font-weight: normal;}
    .tright{text-align: right;}
    </style>
</head>
<body>
    <div id="dolr-container">
        <div class="header">
            <h1>异常!</h1>
        </div>
        <div class="content">
            <div class="errorString">
                <p><span class="t">消息：</span> [MESSAGE] </p>
                <p><span class="t">文件：</span> [FILENAME] </p>
                <p><span class="t">位置：</span> 第 [LINE] 行</p>
            </div>
            <div class="trace">
                <ul>[TRACE]</ul>
            </div>
        </div>
        <div class="footer">
            <div class="tright">&lt;?php define( 'DolrPHP' , 'less is more.' ); ?&gt;</div>
        </div>
    </div>
</body>
</html>