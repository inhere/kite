<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Logic;

use InvalidArgumentException;
use RuntimeException;
use Toolkit\FsUtil\Dir;
use Toolkit\Sys\Sys;
use function date;
use function extract;
use function file_exists;
use function file_put_contents;
use function md5;
use function ob_get_clean;
use function ob_start;
use const PHP_EOL;

/**
 * Class TextTemplateLogic
 *
 * @package Inhere\Kite\Model\Logic
 */
class TextTemplateRender
{
    /**
     * @param string $tempFile
     * @param array  $vars
     *
     * @return string
     */
    public function renderFile(string $tempFile, array $vars): string
    {
        if (!file_exists($tempFile)) {
            throw new InvalidArgumentException('the template file is not exist. file:' . $tempFile);
        }

        ob_start();
        extract($vars, \EXTR_OVERWRITE);
        // eval($tplCode . "\n");
        // require \BASE_PATH . '/runtime/go-snippets-0709.tpl.php';
        /** @noinspection PhpIncludeInspection */
        require $tempFile;
        return ob_get_clean();
    }

    /**
     * @param string $tplCode
     * @param array  $vars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $vars): string
    {
        $tempDir  = Sys::getTempDir() . '/kitegen';
        $fileHash = md5($tplCode);
        $tempFile = $tempDir . '/' . date('ymd') . "-{$fileHash}.php";

        if (!file_exists($tempFile)) {
            // \vdump($tempFile);
            Dir::create($tempDir);

            // write contents
            $num = file_put_contents($tempFile, $tplCode . PHP_EOL);
            if ($num < 1) {
                throw new RuntimeException('write template contents to temp file error');
            }
        }

        return $this->renderFile($tempFile, $vars);
    }

    /**
     * @param string $tplCode
     * @param array  $vars
     *
     * @return string
     */
    private function renderByEval(string $tplCode, array $vars): string
    {
        \vdump($tplCode);
        ob_start();
        extract($vars, \EXTR_OVERWRITE);
        // eval($tplCode . "\n");
        // require \BASE_PATH . '/runtime/go-snippets-0709.tpl.php';
        return ob_get_clean();
    }

}
