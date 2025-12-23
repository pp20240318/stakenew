<?php

namespace app\common\controller;

use Throwable;
use think\App;
use think\Response;
use think\facade\Db;
use app\BaseController;
use think\facade\Config;
use think\db\exception\PDOException;
use think\exception\HttpResponseException;
use think\Request;

/**
 * API控制器基类
 */
class Api extends BaseController
{
    /**
     * 默认响应输出类型,支持json/xml/jsonp
     * @var string
     */
    protected string $responseType = 'json';

    /**
     * 应用站点系统设置
     * @var bool
     */
    protected bool $useSystemSettings = true;

    public function __construct(App $app)
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:token');
        header('Access-Control-Allow-Headers:batoken');
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Max-Age:86400');
        parent::__construct($app);
    }

    /**
     * 控制器初始化方法
     * @access protected
     * @throws Throwable
     */
    protected function initialize(): void
    {
        // 系统站点配置
        if ($this->useSystemSettings) {
            // 检查数据库连接
            try {
                Db::execute("SELECT 1");
            } catch (PDOException $e) {
                $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
            }

            ip_check(); // ip检查
            set_timezone(); // 时区设定
        }

        parent::initialize();

        /**
         * 设置默认过滤规则
         * @see filter()
         */
        $this->request->filter('filter');
        
        // 设置多语言环境（从请求头获取语言设置）
        $this->setLanguageFromRequest();

        // 加载API语言包
        $langSet = $this->app->lang->getLangSet();
        
        // 首先加载通用语言包
        $apiLangFile = app_path()  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . '.php';
        if (file_exists($apiLangFile)) {
            $this->app->lang->load($apiLangFile);
            \think\facade\Log::info('API Language file loaded: ' . $apiLangFile);
        } else {
            \think\facade\Log::warning('API Language file not found: ' . $apiLangFile);
        }
        
        // 然后加载控制器特定语言包（如果存在）
        $controllerLangFile = app_path() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . (str_replace('/', DIRECTORY_SEPARATOR, $this->app->request->controllerPath)) . '.php';
        if (file_exists($controllerLangFile)) {
            $this->app->lang->load($controllerLangFile);
            \think\facade\Log::info('Controller Language file loaded: ' . $controllerLangFile);
        }
    }

    /**
     * 从请求头设置多语言环境
     */
    protected function setLanguageFromRequest(): void
    {
        // 默认语言设置为中文
        $defaultLanguage = 'zh-cn';
        
        // 检查是否是admin请求
        $requestPath = $this->request->pathinfo();
        $requestUrl = $this->request->url();
        $controllerName = $this->request->controller();
        
        $isAdminRequest = strpos($requestPath, 'admin') !== false || 
                         strpos($requestUrl, '/admin/') !== false ||
                         strpos($controllerName, 'admin') !== false ||
                         strpos($requestPath, 'backend') !== false;
        
        // 如果是admin请求，直接设置为中文
        if ($isAdminRequest) {
            try {
                \think\facade\Lang::setLangSet('zh-cn');
                \think\facade\Log::info('API: Admin request detected, language set to zh-cn');
                return;
            } catch (\Exception $e) {
                \think\facade\Log::error('API: Failed to set admin language: ' . $e->getMessage());
            }
        }
        
        // 获取语言设置，优先级：X-Requested-Lang > Accept-Language > 默认中文
        $language = $this->request->header('X-Requested-Lang') 
                   ?: $this->request->header('Accept-Language') 
                   ?: $this->request->header('x-requested-lang')
                   ?: $this->request->header('accept-language')
                   ?: $defaultLanguage;
        
        // 清理语言代码（移除空格、转小写）
        $language = strtolower(trim($language));
        
        // 支持的语言列表（项目中实际存在的语言包）
        $supportedLanguages = ['en', 'zh-cn', 'br'];
        
        // 语言映射表，将前端语言代码映射到后端支持的语言包
        $languageMap = [
            // 英语相关
            'en' => 'en',
            'english' => 'en',
            
            // 中文相关 - 统一映射到 zh-cn
            'zh-hans' => 'zh-cn',              // 中文简体
            'zh-hant' => 'zh-cn',              // 中文繁体 
            'zhhant' => 'zh-cn',               // 中文繁体（驼峰）
            'zhhans' => 'zh-cn',               // 中文简体（驼峰）
            'zh-cn' => 'zh-cn',                // 中文简体
            'zh-tw' => 'zh-cn',                // 中文繁体（台湾）
            'cn' => 'zh-cn',                   // 中文简写
            'chinese' => 'zh-cn',              // 中文
            
            // 巴西葡萄牙语
            'br' => 'br',                      // 巴西葡萄牙语
            'pt-br' => 'br',                   // 巴西葡萄牙语
            'portuguese' => 'br',              // 葡萄牙语
            
            // 其他语言 - 暂时映射到英文（可以根据需要扩展）
            'alby' => 'en',                    // 阿拉伯语
            'bly' => 'en',                     // 波兰语  
            'dy' => 'en',                      // 德语
            'fy' => 'en',                      // 法语
            'hy' => 'en',                      // 韩语
            'ptyy' => 'br',                    // 葡萄牙语 -> 映射到巴西葡萄牙语
            'ry' => 'en',                      // 日语
            'ty' => 'en',                      // 泰语
            'trqy' => 'en',                    // 土耳其语
            'wkly' => 'en',                    // 乌克兰语
            'xbyy' => 'en',                    // 西班牙语
            'ydly' => 'en',                    // 意大利语
            'ydy' => 'en',                     // 印地语
            'ar' => 'en',                      // 阿拉伯语
            'pl' => 'en',                      // 波兰语
            'de' => 'en',                      // 德语
            'fr' => 'en',                      // 法语
            'ko' => 'en',                      // 韩语 
            'ja' => 'en',                      // 日语
            'th' => 'en',                      // 泰语
            'tr' => 'en',                      // 土耳其语
            'uk' => 'en',                      // 乌克兰语
            'es' => 'en',                      // 西班牙语
            'it' => 'en',                      // 意大利语
            'hi' => 'en',                      // 印地语
        ];
        
        // 处理语言代码，去除区域标识符(如 en-US -> en)
        $langCode = explode('-', explode(',', $language)[0])[0];
        
        // 查找匹配的语言代码
        $mappedLanguage = $defaultLanguage; // 默认使用中文
        
        // 优先精确匹配完整语言代码
        if (isset($languageMap[$language])) {
            $mappedLanguage = $languageMap[$language];
        }
        // 其次匹配语言代码前缀
        elseif (isset($languageMap[$langCode])) {
            $mappedLanguage = $languageMap[$langCode];
        }
        // 直接检查是否是支持的语言
        elseif (in_array($language, $supportedLanguages)) {
            $mappedLanguage = $language;
        }
        elseif (in_array($langCode, $supportedLanguages)) {
            $mappedLanguage = $langCode;
        }
        
        // 确保映射的语言是支持的
        if (!in_array($mappedLanguage, $supportedLanguages)) {
            $mappedLanguage = $defaultLanguage; // 回退到默认中文
        }
        
        try {
            // 设置当前语言环境
            \think\facade\Lang::setLangSet($mappedLanguage);
            // 临时开启调试日志
            \think\facade\Log::info('API Language set to: ' . $mappedLanguage . ' (from request: ' . $language . ')');
        } catch (\Exception $e) {
            \think\facade\Log::error('API: Failed to set language: ' . $e->getMessage());
            // 失败时使用默认中文
            \think\facade\Lang::setLangSet($defaultLanguage);
        }
    }

    /**
     * 操作成功
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function success(string $msg = '', mixed $data = null, int $code = 1, string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 操作失败
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function error(string $msg = '', mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 返回 API 数据
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    public function result(string $msg, mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];

        $type = $type ?: $this->responseType;
        $code = $header['statusCode'] ?? 200;

        $response = Response::create($result, $type, $code)->header($header)->options($options);
        throw new HttpResponseException($response);
    }

    /**
     * 公共上传图片方法
     */
    public function uploadImage(Request $request)
    {
        $allowSize = 5; // 5MB
        $allowTypes = ['png', 'gif', 'jpeg', 'jpg'];
        
        if(!empty($_FILES['img'])){
            // 获取扩展名
            $pathinfo = pathinfo($_FILES['img']['name']);
            $exename = strtolower($pathinfo['extension']);
            
            // 检查扩展名
            if(!in_array($exename, $allowTypes)){
                $arr = array('status'=>'error', 'data' => '格式错误，只允许上传png、gif、jpeg、jpg格式', 'result' => '');
                exit(json_encode($arr));
            }
            
            // 检查文件大小
            if($_FILES['img']['size'] > $allowSize*1024*1024){
                $arr = array('status'=>'error', 'data' => '文件过大，最大允许'.$allowSize.'MB', 'result' => '');
                exit(json_encode($arr));
            }
            
            // 生成唯一文件名
            $file = uniqid().'.'.$exename;
            
            // 创建保存目录
            $saveDir = '/storage/default/uploadimgs/';
            $fullDir = $_SERVER['DOCUMENT_ROOT'] . $saveDir;
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            
            // 保存路径
            $imageSavePath = $saveDir.$file; 
            
            // 上传文件
            if(move_uploaded_file($_FILES['img']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imageSavePath)){
                // 获取当前站点域名
                $domain = request()->domain();
                
                // 返回成功信息
                $arr = array(
                    'status' => 'ok', 
                    'data' => $domain . $imageSavePath, 
                    'result' => $imageSavePath
                );
                exit(json_encode($arr));
            } else {
                $arr = array(
                    'status' => 'error', 
                    'data' => '上传失败，服务器错误', 
                    'result' => ''
                );
                exit(json_encode($arr));
            }
        }
        
        $arr = array('status'=>'error', 'data' => '未检测到上传文件', 'result' => '');
        exit(json_encode($arr));
    }
}