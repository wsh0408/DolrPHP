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
#errorContent{color: #f00;}
#errorContent i.error{padding:15px 24px; background: url(data:image/gif;base64,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) no-repeat 0 center;}
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
        <div id="errorContent" class="content"><i class="error"></i>{{message}}</div>
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