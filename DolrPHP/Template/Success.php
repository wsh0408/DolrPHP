<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>操作提示 - DolrPHP</title>
<style type="text/css">
* { margin:0; padding:0; }
.l { float:left; }
.r { float:right; }
body { font-size:12px; font-family: "Microsoft YaHei"; background:#f4f4f4; }
.msgBox { width:350px; box-shadow:0px 0px 10px #c0c0c0; -moz-box-shadow:0px 0px 10px #c0c0c0; -ms-box-shadow:0px 0px 10px #c0c0c0; -o-box-shadow:0px 0px 10px #c0c0c0; -webkit-box-shadow:0px 0px 10px #c0c0c0; position:absolute; top:40%; left:50%; margin-top:-100px; margin-left:-175px;background: #fff; border:1px solid #cbcbcb; overflow: hidden;}
.head { padding:5px 10px; height: 20px; line-height: 20px; background: #f0f0f0; border-bottom: 1px solid #cbcbcb; color: #999; }
.content { min-height:20px; padding:10px; margin-top: 5px;/*margin-top: 45px;*/ font-size: 18px; color: #063; text-align: center;}
#rightContent{color: #063;}
#rightContent i.right{padding:15px 24px; background: url(data:image/gif;base64,R0lGODlhIgAiAMQfAPf77LDPRIyzIrrbDNHgqcbkVbbZHO311bTNd6bMDb7fFcHWkrbYLcTjOlGFDabHQanPELnZRK/TFYOsCpq9NbDUHkd9Cq/TDb7eC8DgIajNH6XCaXejKqLJDa7RMv///yH5BAEAAB8ALAAAAAAiACIAAAX/4CeOZLmcS6muLMI5liNbMYew+LjJAlP8QI9AtsmpCDLPr5FROBWZxu8hIxhFG4umwMR4B5iBWGyQCizFXDZQaHrfY/Fl7gugcVml4s0Px+USBXZpJQQWbHt9fH9zF4EPFlYlDhQFiYqLcRISjgVDJTttmIpiCganBps+dyMOHg2jpFABAhMUERUQBRQOIwgOorF8UA8CBAfFDBIVBRY3HxwCBWFee06KxBQHAAAHAg+5BRwcIq6wYU4Z6tfVGcXb8AQTuRFUIhZtpRkRtQINT9m2cdsmLxeDCBY+LACmYACxCQQALBAQoYk7AQcyZgRA4FuuLRZOAANjgJ9Aif4u/26MNwEchAQdmom05DDABm44Fzx4QCEivAMLJgSoIOElyJkGBhgI8ABnzg0nMyIQSvRlh6Mf8GW4oICBgAVOw3Y7gECAh6oJYHpAWO7VBYcReorFSXZChFQvYXao1wsahwJzTsUFO5cWg6p5O8QcJ+JXAQNzlDKggCAs2VuI0yoG+eyDgwcNODkyMBkqPJ6HL0BIrDhuXyzAGDSSQJrCtwe2Dq9m3UGJA0LlpHGCMIc20wdDc+XVe1Xc6xGGHhQgvtrRMqJFlytuTiHSijUFJCTYTT6t5u0a6rH6bkE6A/Pwz29X0h04e2m4Opjfjj6CuPU5IEHJDxEwoEECGmjgASMbu1RxBQk7WMDBA/4BERcHNNj3YGMY0uBhDZ1tiAMKKWwYAgA7) no-repeat 0 center;}
.msgBox .foot { text-align: center; color: #999; word-spacing: 0px; letter-spacing: 1px; height: 20px; line-height: 20px; }
.msgBox .letter {height: 30px; line-height: 20px; }
.msgBox .foot a,#dolrContent p.link a{ font-size:12px; text-decoration: none; color: #999; }
.msgBox .foot a:hover, #dolrContent p.link a:hover{ color:#777; }
</style>
</head>
<body>
    <div class="msgBox">
        <div class="head">
            <span class="l">系统提示!</span>
            <span class="r">DolrPHP {{DOLRPHP_VERSION}}</span>
        </div>
        <div id="rightContent" class="content"><i class="right"></i>{{message}}</div>
        <div id="foot" class="foot letter" ></div>
    </div>
    <script>
        var foot = document.getElementById('foot');
        var time = {{delay}};
        var dot = '.';
        function red(){
            window.location.href = "{{url}}";
        }
        function dis(){
            foot.innerHTML = (time--) + '秒后自动跳转' + dot + '<a href="javascript:red();">立即跳转</a>        ';
            dot += '.';
            if (dot.length > 3 ) {
                dot = '.';
            }
            if (time == -1) {
                clearInterval(t);
                red();
            }
        }
        dis();
        t = setInterval(dis,1000);
    </script>
</body>
</html>
