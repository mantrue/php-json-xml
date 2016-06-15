# php-json-xml
php json xml数据通信格式

```
require_once 'Api.php';
use App\Resource\Api as ApiFace;

```
### 生成xml数据

```
$info = array(
	'id' => 100,
	'name' => 'peen',
	'is_show'=>1,
);
$string = ApiFace::interfaceData(200,'成功',$info,'xml');
print_r($string);

```

### 根据xml转化为对象数组

```
$xml = simplexml_load_string($string);
print_r($xml);

```

### 生成json数据

```
$info = array(
	'id' => 100,
	'name' => 'peen',
	'is_show'=>1,
);
$string = ApiFace::interfaceData(200,'成功',$info,'json');
print_r($string);

```

### 开启debug

```
$info = array(
	'id' => 100,
	'name' => 'peen',
	'is_show'=>1,
);
$string = ApiFace::interfaceData(200,'成功',$info,'array');
print_r($string);

```

### 根据xml文件生成数组数据

```
$xmlNode = simplexml_load_file('demo.xml');
$arrayData = ApiFace::xmlToArray($xmlNode);
print_r($arrayData);

```
	