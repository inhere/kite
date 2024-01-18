<?php declare(strict_types=1);

namespace Inhere\Kite\Component;


/**
 * class ShortNameMaker
 */
class ShortNameMaker
{
	public int $tooShortLen = 2;

	public bool $tooShortAsRaw = false;

	/**
	 * 用户自定义缩写字典 eg: ['number' => 'num']
	 *
	 * - 不用首字母生成，直接使用字典设置的缩写
	 */
	public array $shortDict = [];

	/**
	 * 记录已使用的
	 */
	private array $usedMap = [];

	public function make(string $name): string
	{
		$ln = strlen($name);
		if ($ln <= $this->tooShortLen) {
			$sName = $name[0];
		} else {
			$sName = $this->toShortName($name);
		}

		if (isset($this->usedMap[$sName])) {
			$sName .= '1';
		}

		$this->usedMap[$sName] = $name;
		return $sName;
	}

	private function toShortName(string $name) : string 
	{
		$matches = [];

		// [\s_-]+([a-z])|([A-Z])
		// check name style.
		if (preg_match('/\s_-/') !== false) {
			preg_match_all('/[\s_-]+([a-z])/', $name, $matches);
		} else { // is camel
			preg_match_all('/([A-Z])/', $name, $matches);
		}

		var_dump($matches);
	}
}