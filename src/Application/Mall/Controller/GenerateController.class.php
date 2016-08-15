<?php

namespace Mall\Controller;
use Think\Controller;
use Think\Model;

//生成模块
class GenerateController extends Controller {
    protected $has_text_filed = FALSE;
      
    public function index(){
        $this->assign('db_prefix',C('DB_PREFIX'));
        $tableNameList = getTableNameList();
        $this->assign('tableNameList', $tableNameList);
        $moduleNameList = getModuleNameList();
        $this->assign('moduleNameList', $moduleNameList);
        $layoutNameList = $this->getLayoutTemplateNameList();
        $this->assign('layoutNameList', $layoutNameList);
        $this->assign('selectTableName', $this->getSessionTableName());
        $this->display();
    }
    
    //在指定目录下创建布局模板文件
    public function creatFiles(){
        $moduleName = I('moduleName');
        $selectTables = I('selectTableName');
        
        $layoutName = I('layoutName');
        $layoutPath = APP_PATH. $moduleName;
        if(! is_dir($layoutPath . '/View')){
            FileUtil::copyDir(MODULE_PATH .'Template/'. $layoutName, $layoutPath, true);
            //FileUtil::unlinkFile($layoutPath."layout.html");
        }
        
        foreach ($selectTables as $table_name){
            session('selectTableName',$table_name);
            \Think\Log::record('selectTableName=' . $table_name);
            $this->creatAllFiles($layoutPath, $table_name);
        }

        echo $layoutName. '写入布局模板成功，路径：'. $layoutPath;
    }
    
    
    /*================================================*/
    //  FUNCTION
    /*================================================*/
    
    /**
     * 返回数据库字段名
     * @param unknown $dbName
     * @param unknown $tableName
     * @return Ambigous <boolean, unknown, Ambigous, multitype:, unknown>
     */
    function getTableFieldArray($tableName){
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $dbName = C('DB_NAME');
        $sql = "select COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT, COLUMN_KEY, EXTRA  from information_schema.COLUMNS 
                where table_name='$tableName' and table_schema='$dbName'";
        $result = $Model->query($sql);
        return $result;
    }
    
    //获取列名列表
    function getTableInfoArray($tableName){
        $dbType = C('DB_TYPE');
        $tableName = C('DB_PREFIX').$tableName;
        
        if($dbType == 'mysql'){
            $dbName = C('DB_NAME');
            $sql = "select * from information_schema.columns 
                            where table_schema='$dbName' and table_name='$tableName'";
            
        }else{ //sqlite
            $sql = "pragma table_info ($tableName)";
        }
        if(empty($sql)){
            $this->error('数据库类型不支持');
        }
        else{
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $result = $Model->query($sql);
            return $result;
        }
    }
    
    //把带下划线的表名转换为驼峰命名（首字母大写）
    function tableNameToModelName($tableName){
        $tempArray = explode('_', $tableName);
        $result = "";
        for($i = 0; $i < count($tempArray);$i++){
            $result .= ucfirst($tempArray[$i]);
        }
        return $result;
    }
    
    //列出所有记录页面代码（片段）
    private function generateIndexCode($viewPath, $model_name){
        $templateFilePath = MODULE_PATH. "Template/View/IndexCode.html";
        $code = $this->fetch($templateFilePath);
        file_put_contents($viewPath.$model_name."/index.html", $code);
    }
    
    private function indexCode(){
        echo $this->generateIndexCode();
    }
    
    
    //生成新建页面代码（片段）
    public function generateAddPage($viewPath, $model_name){
        if($this->has_text_filed){
            $templateFilePath = MODULE_PATH. "Template/View/tabAddCode.html";
        }
        else{
            $templateFilePath = MODULE_PATH. "Template/View/addCode.html";
        }
        $code = $this->fetch($templateFilePath);
        
        file_put_contents($viewPath.$model_name."/add.html", $code);
    }
    
    function set_table_info($tableName){
        $this->assign('tableName', $tableName);
        $tableInfoArray = $this->getTableInfoArray($tableName);
        $identity = $this->getIdentityKey($tableInfoArray);
        
        if($this->hasTextFiled($tableInfoArray)){
            $this->assign('has_text_filed', 1);
            $this->has_text_filed = true;
        }
        else{
            $this->assign('has_text_filed', 0);
            $this->has_text_filed = false;
        }
        
        $this->assign('ID', $identity);
        $this->assign('tableInfoArray', $tableInfoArray);
    	
    }
    
    //生成新建前台页面代码
    public function addPage(){
        echo $this->generateAddPage();
    }
    
    //根据数据库类型获取列名键
    function getColumnNameKey(){
        $dbType = C('DB_TYPE');
        if($dbType == 'mysql'){
            return MYSQL_COLUMN_NAME_KEY;
        }else{
            return SQLITE_COLUMN_NAME_KEY;
        }
    }
    
    function getIdentityKey($columns){
    	foreach ($columns as $col){
    	    \Think\Log::record('getIdentityKey column=' . json_encode($col));
    		if($col['column_key'] == 'longtext' || $col['extra'] == 'auto_increment'){
    			return $col['column_name'];
    		}
    	}
    	return '';
    }
    
    function hasTextFiled($columns){
        foreach ($columns as $col){
            \Think\Log::record('hasTextFiled column=' . json_encode($col));
            if($col['data_type'] == 'text' || $col['data_type'] == 'longtext'){
                return true;
            }
        }
        return false;
    }
    
    //编辑页面
    public function generateEditPage($viewPath, $model_name){
        if($this->has_text_filed){
            $templateFilePath = MODULE_PATH. "Template/View/tabEditCode.html";
        }
        else{
            $templateFilePath = MODULE_PATH. "Template/View/editCode.html";
        }
        
        $code = $this->fetch($templateFilePath);
        file_put_contents($viewPath.$model_name."/edit.html", $code);
    }
    
    public function editPage(){
        $tableName = I('session.selectTableName');
        $model_name = $this->tableNameToModelName($tableName); 
        echo $this->generateEditPage($tableName, $model_name);
    }
    
    //语言页面
    public function generateLanguagePage($langPath, $model_name){
        $model_name = strtolower($model_name);
        $templateFilePath = MODULE_PATH. "Template/Lang/zh-cn/lang.dwt";
        $code = $this->fetch($templateFilePath);
        file_put_contents($langPath.$model_name.".php", "<?php\r\n" . $code);
    }
    
    //搜索页面
    public function generateSearchPage($viewPath, $model_name){
        $templateFilePath = MODULE_PATH. "Template/View/custom_search.php";
        $code = file_get_contents($templateFilePath);
        file_put_contents($viewPath.$model_name."/search.html", $code);
    }
    
    //生成所有记录代码
    public function generateControlCode($controllerPath, $model_name){
        $model_name = strtolower($model_name);
        $model_name = ucfirst($model_name);
        $templateFilePath = MODULE_PATH. "Template/Controller/controller.dwt";
        
        $code = $this->fetch($templateFilePath);
        file_put_contents($controllerPath.$model_name."Controller.class.php", "<?php\r\n" .$code);
    }
    
    public function generateModelCode($modelPath, $model_name){
        $templateFilePath = MODULE_PATH. "Template/Model/model.dwt";
        
        $code = $this->fetch($templateFilePath);
        file_put_contents($modelPath.$model_name."Model.class.php", "<?php\r\n" .$code);    	
    }
    
    public function controlCode(){
        echo $this->generateControlCode();
    }
    
    //生成所有代码对应的文件，
    public function creatAllFiles($layoutPath, $tableName){
        $controllerPath = $layoutPath. "/Controller/";
        $modelPath = $layoutPath. "/Model/";
        $viewPath = $layoutPath. "/View/";
        $langPath = $layoutPath. "/Lang/zh-cn/";

		$model_name = $this->tableNameToModelName($tableName);
		$this->assign('model_name', $model_name);//修正为驼峰命名，首字母大写
		$this->assign('enter', "\r\n");
		$this->assign('tab_3', "            ");
		$this->assign('tab_4', "                ");
		$this->set_table_info($tableName);
		
		
		
        
        // 生成Controller文件
        $this->generateControlCode($controllerPath, $model_name);
        $this->generateModelCode($modelPath, $model_name);
        
        // 创建目录
        FileUtil::createDir($viewPath . ucfirst($tableName));
        
        $this->generateIndexCode($viewPath, $tableName);
        $this->generateAddPage($viewPath, $tableName);
        $this->generateEditPage($viewPath, $tableName);
        
        $this->generateSearchPage($viewPath, $tableName);
        
        $this->generateLanguagePage($langPath, $model_name);
    
        echo "生成完成。";
    }
    
    
    public function getSessionTableName(){
        $selectTableName = implode("','", session('selectTableName'));
        return "['".$selectTableName."']";
    }
    
    public function getLayoutTemplateNameList(){
        $layoutTemplateNameList0 = FileUtil::getDirList(MODULE_PATH."/Template");
        $layoutTemplateNameList = array();
        foreach($layoutTemplateNameList0 as $layoutDirName){
            if(substr($layoutDirName, -6) == 'layout'){    //判断以layout结尾的才是布局文件夹
                $layoutTemplateNameList[] = $layoutDirName;
            }
        }
        return $layoutTemplateNameList;
    }
}