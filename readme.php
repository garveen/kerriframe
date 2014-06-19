
本框架简要使用说明书


目录结构
app/
	controller/
	model/
	view/
	config.php

常见操作
<?
// 获取控制器对象
$controller = KF::getController('path/controller_name');
// 获取模型对象
$model = KF::getModel('path/model_name');
// 获取框架内置对象
// 目前常用的有 response application router
$obj = KF::singleton('name');
// 获取app内对象
$yyy = KF::getZZZ('yyy');


// $_GET
KF::get('name', $xss_clean = false);
// $_POST
KF::post('name', $xss_clean = false);



//in controller
{
	$vars = [
		'name' => $value,
		//...
	];
	$this->display('path/template_name', $vars, $returnOutput);
}
// debug notice error
KF::log($e, $level = 'debug');
KF::log('some message', $level = 'error');

?>

数据库操作

<?
$db = KF::getDB();
/**
 * 结果集对象
 * @var KF_DATABASE_record
 */
$query = $db->query($sql);

// 简单变量
$query->one();
// object
$query->row();
// array
$query->column($column_name);
// array of objects
$query->result();
?>

缓存操作
<?
// memcache, redis, dummy
$cache = KF::getCache('dummy');
if(!$cache->get($key)) {
	// do sth....
	$cache->set($key, $value, 0, $expire);
}

?>
