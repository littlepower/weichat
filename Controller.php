<?php
class Controller{
	private $dir = 'Storage';

	public function __construct()
	{
		if(!is_dir($this->dir))
		{
			mkdir($this->dir,0755,true);
		}
	}

	//获取文件
	private function getFile($name)
	{
		return $this->dir .'/'.md5($name).".php";
	}

	//设置缓存
	public function cacheSet($name,$data,$expire=3600)
	{
		$file = $this->getFile($name);

		//缓存时间
		$expire = sprintf("%010d",$expire);

		$data = "<?php\n//" . $expire . serialize($data)."\n?>";

		return file_put_contents($file, $data);
	}

	//获取缓存
	public function cacheGet($name)
	{
		$file = $this->getFile($name);

		//缓存文件不存在
		if(!is_file($file)){
			return null;
		}
		$content = file_get_contents($file);
		$expire = intval(substr($content, 8,10));
		$mtime = filemtime($file);

		//缓存失效处理
		if($expire >0 && $mtime + $expire < time()){
			@unlink($file);
			return false;
		}
		return unserialize(substr($content, 18,-3));
	}
}
?>