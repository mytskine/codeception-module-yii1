<?php

namespace Codeception\Lib\Connector\Yii1;

class Logger extends \CComponent
{
	const LEVEL_TRACE='trace';
	const LEVEL_WARNING='warning';
	const LEVEL_ERROR='error';
	const LEVEL_INFO='info';
	const LEVEL_PROFILE='profile';

	public $autoFlush=10000;
	public $autoDump=false;
	public function log($message,$level='info',$category='application')
	{
	}

	public function getLogs($levels='',$categories=array(), $except=array())
	{
		return [];
	}

	public function getExecutionTime()
	{
		return microtime(true)-YII_BEGIN_TIME;
	}

	public function getMemoryUsage()
	{
        return memory_get_usage();
	}

	public function getProfilingResults($token=null,$categories=null,$refresh=false)
	{
		return [];
	}

    public function flush($dumpLogs=false)
	{
	}

	public function onFlush($event)
	{
	}
}
