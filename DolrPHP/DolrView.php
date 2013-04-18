<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     DolrPHP
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * DolrPHP模板引擎
 *
 * @package     DolrPHP.Tpl
 * @author      Joychao <joy@joychao.cc>
 * @version     1.03 beta
 */
class DolrView
{
    /**
     * 单例对象
     *
     * @static var
     * @var object DolrView
     */
    protected static $_instance;

    /**
     * 模板变量
     *
     * @var array
     */
    protected $_tplVars = array();

    /**
     * 模板参数信息
     *
     * @var array
     */
    protected $_options = array(
                           'template_dir'   => 'templates',
                           'compile_dir'    => 'templates_c',
                           'cache_dir'      => 'cache',
                           'caching'        => false,
                           'cache_lifetime' => 3600,
                           'l_tag'          => '<{',
                           'r_tag'          => '}>',
                          );

    /**
     * 替换字符串
     *
     * @var array
     */
    private $_replace = array();

    /**
     * 单件模式调用方法
     *
     * @param array $options user options
     *
     * @return object DolrView
     */
    public static function getInstance($options = array())
    {
        if (!self :: $_instance instanceof self)
            self :: $_instance = new self($options);

        return self :: $_instance;
    }

    /**
     * 构造函数，初始化所有配置
     *
     * @param array $options = array(); 配置数组
     * @example:
     * <pre>
     * $options = array(
     *            'template_dir'   => 'templates',
     *            'compile_dir'    => 'templates_c',
     *            'cache_dir'      => 'cache',
     *            'caching'        => false,
     *            'cache_lifetime' => 3600,
     *            'l_tag'          => '<{',
     *            'r_tag'          => '}>',
     *        );
     * </pre>
     * $tpl = new DolrView($options);
     *
     * @return void
     */
    public function __construct($options = array())
    {
        $this->setOption($options);
        //去除定界符冲突
        $this->_options['l_tag'] = addcslashes($this->_options['l_tag'], '.*?(){}/');
        $this->_options['r_tag'] = addcslashes($this->_options['r_tag'], '.*?(){}/');
    }

    /**
     * 获取替换规则
     *
     * @return array
     */
    private function _getReplaceRules()
    {
        $l = $this->_options['l_tag'];
        $r = $this->_options['r_tag'];
        $default = array(
            //去除空白
            '/\?\>[\n\r]*\<\?/'                                        => ' ',
            //变量:$varname
            '/([L]\s*(\$.*?)\s*[R])/e'                                 => '$this->_parseVar(\'\\1\')',
            //函数: <{:date('Y-m-d');}>
            '/[L]\s*:(\w+)\((.*?)\)(.*?)[R]/'                          => '<?php echo \\1(\\2)\\3 ?>',
            //if： if $a > $b
            '/[L]\s*if\s+(.+?)\s*[R]/'                                 => '<?php if(\\1): ?>',
            //end if
            '/[L]\s*\/if\s*[R]/i'                                      => '<?php endif;?>',
            //elseif
            '/[L]\s*elseif\s+(.+?)\s*[R]/i'                            => '<?php elseif(\\1): ?>',
            //else
            '/[L]\s*else\s*[R]/i'                                      => '<?php else: ?>',
            //loop $array $v
            '/[L]\s*loop\s+(\S+)\s+(\S+)\s*[R]/'                       => '<?php if(!empty(\\1) and is_array(\\1) or is_object(\\1) ):$_i = 0; foreach(\\1 as \\2): ?>',
            //loop $array $k $v
            '/[L]\s*loop\s+(\S+)\s+(\S+)\s+(\S+)\s*[R]/'               => '<?php if(!empty(\\1) and is_array(\\1) or is_object(\\1) ):$_i = 0; foreach(\\1 as \\2 => \\3): ?>',
            //loopelse
            '/[L]\s*loopelse\s*[R](.*?)[L]\s*\/loop\s*[R]/is'          => '<?php $_i++; endforeach; else: ?>\\1<?php endif;?>',
            //end loop
            '/[L]\s*\/loop\s*[R]/i'                                    => '<?php endforeach;endif;?>',
            //cycle 'red','blue'
            '/[L]\s*cycle\s+(\'|\")(\S+)\\1\s*,\s*\\1(\S+)\\1\s*[R]/s' => '<?php if($_i%2): echo \'\\3\';else: echo \'\\2\';endif;?>',
            //$varname.title
            '/<\?php(.*?)(\$[\w]+(\.[\w]+)+)(.*?)\?>/es'               => '\'<?php \\1\'.$this->_parsePoint(\'\\2\').\'\\4?>\';',
        );

        return $default;
    }

    /**
     * 设置参数
     *
     * @param array $options options
     *
     * @return void
     */
    public function setOption($options)
    {
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * 分配变量到模板
     *
     * @param string $varName  变量名
     * @param mixed  $varValue 变量值
     *
     * @return void
     */
    public function set($varName, $varValue)
    {
        $this->_tplVars[$varName] = $varValue;
    }

    /**
     * 渲染并输出到页面
     *
     * @param string $tplFileName 模板文件名
     * @param mixed  $cacheId     缓存ID
     *
     * @return void
     */
    public function display($tplFileName, $cacheId = null)
    {
        $cacheId     = is_null($cacheId) ? $_SERVER['REQUEST_URI'] : $cacheId;
        $tplFilePath = $this->_getTplPath($tplFileName);
        //模板变量
        extract($this->_tplVars);
        //内置变量
        extract($this->_getSystemVars($tplFileName)); //系统变量
        //启用缓存
        if ($this->_options['caching'] and $this->isCached($tplFileName, $cacheId)) {
            include $this->_getCachePath($tplFileName, $cacheId);
        } else { //非缓存模式
            ob_start();
            include $this->_writeCompile($tplFileName, $this->_parseTpl($tplFileName, $cacheId), $cacheId);
            $cacheContent = ob_get_contents();
            ob_flush();
            ob_end_clean();
            //如果开启缓存则缓存文件s
            $this->_options['caching'] and $this->_writeCache($tplFileName, $cacheContent, $cacheId);
        }
    }

    /**
     * 渲染并提取内容
     *
     * @param string $tplFileName 模板文件名
     * @param mixed  $cacheId     缓存ID
     *
     * @return string 渲染后的html
     */
    public function fetch($tplFileName, $cacheId = null)
    {
        ob_start();
        $this->display($tplFileName, $cacheId = null);
        $cacheContent = ob_get_contents();
        ob_end_clean();

        return $cacheContent;
    }

    /**
     * 清除模板缓存
     *
     * @param string $tplFileName 模板文件名，不传入则删除所有缓存
     *
     * @return boolean 成功或者失败
     */
    public function clearCache($tplFileName = '', $cacheId = null)
    {
        $cacheId = is_null($cacheId) ? $_SERVER['REQUEST_URI'] : $cacheId;
        if (!empty($tplFileName)) {
            $cacheFile = $this->_getCachePath($tplFileName, $cacheId);
            if (file_exists($cacheFile)) {
                chmod($cacheFile, 0777);
                @unlink($cacheFile);
            }
        } else { //删除所有缓存文件
            foreach (glob($this->_options['cache_dir'] . '*') as $cacheFile) {
                chmod($cacheFile, 0777);
                @unlink($cacheFile);
            }
        }
    }

    /**
     * 检测是否缓存了指定模板文件
     *
     * @param string $tplFileName 模板文件名
     * @param string $cacheId     缓存ID
     *
     * @return boolean
     */
    public function isCached($tplFileName, $cacheId = null)
    {
        $tplFilePath = $this->_getTplPath($tplFileName);
        $cacheId     = is_null($cacheId) ? $_SERVER['REQUEST_URI'] : $cacheId;
        $cacheFile   = $this->_getCachePath($tplFileName, $cacheId);

        return file_exists($cacheFile) //存在
            and filemtime($cacheFile) + $this->_options['cache_lifetime'] > time() //未过期
                and filemtime($cacheFile) > filemtime($tplFilePath); //模板没有改动
    }

    /**
     * 获取内置变量
     *
     * @param string $tplFileName 模板文件名
     *
     * @return array
     */
    protected function _getSystemVars($tplFileName)
    {
        //内置变量
        $_sysVars = array('Dolr' => array());
        $_sysVars['Dolr']['now']      = time();
        $_sysVars['Dolr']['get']      = $_GET;
        $_sysVars['Dolr']['post']     = $_POST;
        $_sysVars['Dolr']['request']  = $_REQUEST;
        $_sysVars['Dolr']['cookie']   = isset($_COOKIE) ? $_COOKIE : null;
        $_sysVars['Dolr']['session']  = isset($_SESSION) ? $_SESSION : null;
        $_sysVars['Dolr']['template'] = basename($tplFileName);
        $const                        = get_defined_constants(true);
        $_sysVars['Dolr']['const']    = isset($const['user']) ? $const['user'] : null;

        return $_sysVars;
    }

    /**
     * 获取模板文件路径
     *
     * @param string $tplFileName 模板文件
     *
     * @return string              文件名
     */
    protected function _getTplPath($tplFileName)
    {
        if (file_exists($tplFileName))
            return $tplFileName;
        $tplFilePath = trim($this->_options['template_dir'], '/') . '/' . $tplFileName;
        $tplFilePath = $this->_formatPath($tplFilePath);
        if (!file_exists($tplFilePath) or !is_readable($tplFilePath)) {
            $this->_throwException('不能打开指定的模板文件"' . $tplFilePath . '"');
        }
        return $tplFilePath;
    }

    /**
     * 获取缓存的文件
     *
     * @param string $tplFileName 模板文件
     * @param string $cacheId     缓存ID
     *
     * @return string            文件名
     */
    protected function _getCachePath($tplFileName, $cacheId)
    {
        return $this->_formatPath(trim($this->_options['cache_dir'], '/') . '/' . basename($tplFileName) . '.cache.' . md5($cacheId) . '.php');
    }

    /**
     * 获取编译的文件名
     *
     * @param string $tplFileName 模板文件
     * @param string $cacheId     缓存ID
     *
     * @return string            文件名
     */
    protected function _getCompilePath($tplFileName, $cacheId)
    {
        return $this->_formatPath(trim($this->_options['compile_dir'], '/') . '/' . basename($tplFileName) . '.compile.php');
    }

    /**
     * 解析模板
     *
     * @param string $tplFileName 模板文件
     * @param string $cacheId     缓存ID
     *
     * @return string 解析后的内容
     */
    protected function _parseTpl($tplFileName, $cacheId)
    {
        $tplFilePath = $this->_getTplPath($tplFileName);
        $content = file_get_contents($tplFilePath);
        $content = $this->_replaceUserString($content);
        //检测包含,所有的include 'xxx.html'
        $l = $this->_options['l_tag'];
        $r = $this->_options['r_tag'];
        preg_match_all('/' . $l . '\s*include\s+(\'")\s*(.*?)\s*\\1\s*' . $r . '/s', $content, $matches);
        //递归包含子模板内容
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key => $fileName) {
                //将include换成文件内容
                $compileContent = $this->_parseTpl($fileName, $cacheId);
                $content        = str_replace($matches[0][$key], $compileContent, $content);
            }
        }
        //替换
        $rules = $this->_getReplaceRules();
        $content = preg_replace(array_keys($rules), $rules , $content);

        return stripslashes($content);
    }

    /**
     * 替换字符串
     *
     * @param  string $content  content
     *
     * @return string
     */
    private function _replaceString($content)
    {
        $search      = array_keys($this->_replace);
        $replacement = $this->_replace;
        return str_replace($search, $replacement, $content);
    }

    /**
     * 缓存模板文件
     *
     * @param string $tplFileName 模板文件
     * @param string $content     模板内容
     * @param string $cacheId     缓存ID
     *
     * @return string
     */
    protected function _writeCache($tplFileName, $content, $cacheId)
    {
        $cacheFilePath = $this->_getCachePath($tplFileName, $cacheId); //保存文件名
        if (!file_exists(dirname($cacheFilePath))) {
            $this->_makeDir(dirname($cacheFilePath));
        }
        file_put_contents($cacheFilePath, $content);

        return $cacheFilePath;
    }

    /**
     * 写入编译文件
     *
     * @param string $tplFileName 模板文件
     * @param string $cacheId     缓存ID
     * @param string $content     网页
     *
     * @return string
     */
    protected function _writeCompile($tplFileName, $content, $cacheId)
    {
        $compileFilePath = $this->_getCompilePath($tplFileName, $cacheId); //保存文件名
        if (!file_exists(dirname($compileFilePath))) {
            $this->_makeDir(dirname($compileFilePath));
        }
        file_put_contents($compileFilePath, $content);

        return $compileFilePath;
    }

    /**
     * 将路径修正为适合操作系统的形式
     *
     * @param string $path 路径名称
     *
     * @return string
     */
    protected function _formatPath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * 根据指定的路径创建不存在的文件夹
     *
     * @param string $path 路径/文件夹名称
     *
     * @return string
     */
    protected function _makeDir($path)
    {
        @mkdir($path, 0777, true); // > PHP5.0
        return true;
    }

    /**
     * 变量处理
     *
     * @param string $string 目标字符串
     *
     * @return string
     */
    protected function _parseVar($string)
    {
        $l           = $this->_options['l_tag'];
        $r           = $this->_options['r_tag'];
        $pattern     = array("/$l/", "/$r/", '/(\$\S+\|[^>\s]+);?/e'); //$title|striptags|html2text
        $replacement = array("<?php echo ", ' ?>', "\$this->_parseModifier('\\1');");

        return stripslashes(preg_replace($pattern, $replacement, stripslashes($string)));
    }

    /**
     * 变量调节器的处理
     *
     * @param string $string 模板中匹配到的变量
     *
     * @return string 处理后的字符串
     */
    protected function _parseModifier($string)
    {
        $arr     = explode('|', trim(stripslashes($string))); // $title|striptags = ###,true|html2text
        $varName = array_shift($arr);
        if (count($arr) == 0) //没有变量调节器
            return $varName;
        $tmp     = '';
        foreach ($arr as $value) {
            //date = 'Y-m-d',###
            if (false !== strpos($value, '=')) {
                $tmpArr   = explode('=', $value);
                $funcName = trim(array_shift($tmpArr));
            } else {
                $funcName = $value;
            }
            $args = ltrim(strstr($value, '='), '='); //去掉左边的" = "号，剩下参数部分：arg1,###,arg3
            if (!strpos($args, '###')) { //如果参数中没找到###
                $args = empty($args) ? '###' : "###,{$args}";
            }
            if (!function_exists($funcName)) { //如果函数不存在
                $this->_throwException("函数'{$funcName}'不存在");
            } else {
                $tmp = !empty($tmp) ? str_replace('###', $tmp, "{$funcName}({$args})") : "{$funcName}({$args})";
            }
        }

        return stripslashes(str_replace('###', $varName, $tmp) . ';');
    }

    /**
     * 数组操作的点支持
     *
     * @param string $string 目标字符串
     *
     * @return string
     */
    protected function _parsePoint($string)
    {
        $arr     = explode('.', $string); //$a.b.c.f
        $varName = array_shift($arr); //$a

        return stripslashes($varName . '[\'' . join('\'][\'', $arr) . '\']'); //$a['b']['c']['f']
    }

    /**
     * 使用属性设值方式分配内容
     *
     * @param string $proName  var name
     * @param mixed  $proValue var value
     *
     * @return void
     */
    protected function __set($proName, $proValue)
    {
        $this->set($proName, $proValue);
    }

    /**
     * 抛出一个错误信息
     *
     * @param string $message error msg
     * @param int    $line    error line
     *
     * @return void
     */
    protected function _throwException($message)
    {
        throw new Exception('DolrViews错误：' . $message);
    }

}
