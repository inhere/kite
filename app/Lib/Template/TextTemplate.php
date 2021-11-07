<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use InvalidArgumentException;
use RuntimeException;
use Toolkit\FsUtil\Dir;
use Toolkit\Stdlib\Obj;
use Toolkit\Sys\Sys;
use function array_merge;
use function date;
use function extract;
use function file_exists;
use function file_put_contents;
use function md5;
use function ob_get_clean;
use function ob_start;
use function sprintf;
use const EXTR_OVERWRITE;
use const PHP_EOL;

/**
 * Class TextTemplate
 *
 * @package Inhere\Kite\Model\Logic
 */
class TextTemplate extends AbstractTemplate
{
    /**
     * @var string[]
     */
    protected array $allowExt = ['.php'];

    /**
     * The dir for auto generated temp php file
     *
     * @var string
     */
    public string $tmpDir = '';

    /**
     * The auto generated temp php file
     *
     * @var string
     */
    private string $tmpPhpFile = '';

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars): string
    {
        $tempFile = $this->genTempPhpFile($tplCode);

        return $this->doRenderFile($tempFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars): string
    {
        return $this->doRenderFile($tplFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    protected function doRenderFile(string $tplFile, array $tplVars): string
    {
        if (!file_exists($tplFile)) {
            throw new InvalidArgumentException('no such template file:' . $tplFile);
        }

        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        ob_start();
        extract($tplVars, EXTR_OVERWRITE);
        // require \BASE_PATH . '/runtime/go-snippets-0709.tpl.php';
        require $tplFile;
        return ob_get_clean();
    }

    /**
     * generate temp php file
     *
     * @param string $tplCode
     *
     * @return string
     */
    protected function genTempPhpFile(string $tplCode): string
    {
        $tmpDir  = $this->tmpDir ?: Sys::getTempDir() . '/php-tpl-gen';
        $tmpFile = sprintf('%s/%s-%s.php', $tmpDir, date('ymd'), md5($tplCode));

        if (!file_exists($tmpFile)) {
            Dir::create($tmpDir);

            // write contents
            $num = file_put_contents($tmpFile, $tplCode . PHP_EOL);
            if ($num < 1) {
                throw new RuntimeException('write template contents to temp file error');
            }
        }

        $this->tmpPhpFile = $tmpFile;
        return $tmpFile;
    }

    /**
     * @return string[]
     */
    public function getAllowExt(): array
    {
        return $this->allowExt;
    }

    /**
     * @param string[] $allowExt
     */
    public function setAllowExt(array $allowExt): void
    {
        $this->allowExt = $allowExt;
    }

    /**
     * @return string
     */
    public function getTmpPhpFile(): string
    {
        return $this->tmpPhpFile;
    }

}
