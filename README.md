Kerriframe
==========

本框架简要使用说明书
--------------------

### 应用程序流程


	index.php  ====>  Routing  ==> controller  <==>  models libraries etc...
	           <====   view    <==
	                    |
	                  widget

1. index.php 定义基础运行环境，使用哪个app，框架文件位置。index.php 引入的kerriframe.php 负责资源初始化。
2. router 检查请求，确定具体的控制器。
3. 控制器装载各个具体使用的资源，（在许多情况下）处理业务逻辑。
4. 控制器讲信息发送给view，进行渲染。
5. view内部可以装载widget，以多次使用可重用组件。
6. 包装后返回给客户端。






### 架构目标

kerriframe 致力成为高效、低资源占用、易扩展的小型php框架。

高效：代码流程逻辑简单，没有不必要的接口与类继承；
低占用：不预先载入任何不必要的类库（包括数据库！），而是在必要时让控制器自行判断；
易扩展：可以通过在app目录下建立对应文件的方式扩展任意一个核心类。




### 主题


#### URL
本段阐述 krriframe 的 URI 构成

##### URI

前端web服务器必须被配置为传递 REQUEST_URI ， kerriframe 基于该变量进行URL处理。
在大多数情况下，URL的格式是

	example.com/controller/method/param1/param2/...

但如果controller在一个子目录下，可以为

	example.com/path/to/controller

的形式。
在应用此形式时，将匹配第一个找到的控制器。应用于本例时，如果存在path/to.php，则不会访问 controller.php 。

##### 去掉index.php
只需要在配置文件内将 index_page 设为空字符串，且在 web server 进行字符串重写即可。




#### 控制器

##### 创建一个简单的控制器

使用文本编辑器，创建一个 hello.php ，保存到 APP/controller 目录下。
	<?php
	// APP/controller/hello.php
	class KF_Controller_Hello extends KF_Controller
	{
		public function init() {
			echo 'Hello ';
		}

		public function index() {
			echo 'World！';
		}
	}


	现在访问
		example.com/index.php/hello/

	那么你应该看到：
		Hello World!

鉴于php并不严格区分大小写，所以类名这么写也行，虽然建议不要这么干：

kf_controller_hello

当类存在一个 init 方法时，该方法将被在资源初始化后自动调用（参见加载类库）。这样，你可以在 init 里面使用保留的类成员变量：
	__objectName
	__objectPath
以上两个变量提供了一种简易获取当前类信息的手段，可以方便地用在路径相关的场合。

##### 方法

如上例所示，当通过 URI 成功获取控制器后，如果根据 URI 无法获得方法名（亦即 URI 已经被用完），则会自动调用 index 方法。
	<?php
	// APP/controller/hello.php
	class KF_Controller_Hello extends KF_Controller
	{
		public function init() {
			echo 'Hello ';
		}

		public function index() {
			echo 'World！';
		}

		public function kerriframe() {
			echo 'PHP!';
		}
	}

现在访问
	example.com/index.php/hello/kerriframe/

那么你应该看到：
	Hello PHP!

##### 控制器内路由

当控制器内含有 _remap 方法时，可以重定向请求：

	public function _remap(&$action, &$params) {
		$action = 'method';
	}

在没有 _remap 时，kerriframe 会将请求的方法作为第一个参数，所有后续的 URI 段作为第二个参数。当需要修改请求时，请使用引用方式获取这两个参数，并直接修改即可。


##### 不可见方法

任何以一个下划线（“_”）开始的方法都将无法从外部访问。如果强行访问，会产生一个403错误。

这意味着，你可以放心在控制器内添加必要的内部处理函数，并放心使用。



#### 视图

##### 创建视图

你可以创建任意一个后缀名为 .php 的文件，保存到 APP/view 目录下。

##### 载入视图

你可以在控制器内，使用以下方法载入一个视图文件。
	$this->display('name');

上面的 name 即为视图文件的名字。你可以在 view 下创建子目录，并将视图文件置于其中。此时，代码变为

	$this->display('PATH/name');

如果，你期望视图内使用控制器提供的数据，那么你可以把数据置于第二个参数内：

	$data = ['message' => 'Hello World!'];
	$this->display('name', $data);

此时，你可以在视图内，直接使用 $message 获得该字符串。

##### 获取视图内容

如果你期望视图不直接输出到浏览器，而是保存以作它用，你可以将第三个三处设为true，来获取html代码：
	$this->display('name', null, true);


#### 使用其它类库

你可以：
* 创建项目自有类库
* 扩展内部类库


##### 命名约定

* 文件名全小写，且不含下划线
* 类名格式为 前缀_路径_文件名，如 KF_Cache_Memcache

##### 类文件
一个简单的示例如下

	<?php
	// APP/thirdparty/encoder.php
	class KF_Thirdparty_Encoder
	{
		public function some_method() {

		}
	}

##### 加载类库

根据你的类库保存位置，你可以简单地载入类库，无论是你自己的还是 kerriframe 的。

	// library/foo.php
	$foo = KF::getLibrary('foo');
	$foo->some_method();

	// thirdparty/encoder.php
	$encoder = KF::getThirdparty('encoder');
	$encoder->encode($bin);

###### 传递参数

在第二个参数传入数组，则将会自动展开，并传入 init 方法：

	<?php
	// APP/thirdparty/encoder.php
	class KF_Thirdparty_Encoder
	{
		public function init($param1, $param2) {
			echo $param1;
			echo $param2;
		}
	}

	// 在控制器文件
	KF::getThirdparty($encoder, ['hello', 'encoder']);

将会输出：
	helloencoder

##### 配置文件

当在 APP/config 目录下的对应路径，存在与类文件同名的 php 时，其将被自动加载，内容将被附加在类的 __objectConfig 属性下。

	<?php
	// APP/config/model/name.php
	return ['item' => 'value'];

在控制器文件
	$nameModel = KF::getModel('name');
	// 不应该直接使用，但这里为了演示方便
	var_dump($nameModel->__objectConfig);

##### 扩展内部类库

当在 APP/core 目录下的对应路径，存在与类文件同名的 php 时，其将被自动加载，但前缀必须与配置文件内的 class_prefix 段相同（默认为 MY_ ）：

	<?php
	// APP/core/controller.php
	class MY_Controller extends KF_Controller {
		public function display($action_template = null, $vars = array() , $returnOutput = false) {
			return parent::display($action_template, $vars, $returnOutput);
		}
	}

注意：KF 和 KF_Factory 不能被使用此方法扩展。


##### 核心类

以下类将被自动装载：

* KF
* KF_Factory
* KF_Logger
* KF_Application
* KF_Controller
* KF_Router
* KF_Object
* KF_Response