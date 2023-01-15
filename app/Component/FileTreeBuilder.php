<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use PhpPkg\EasyTpl\Contract\TemplateInterface;
use PhpPkg\EasyTpl\EasyTemplate;
use Toolkit\FsUtil\File;

/**
 * class FileTreeBuilder
 *
 * @author inhere
 * @date 2022/12/26
 */
class FileTreeBuilder extends \Toolkit\FsUtil\Extra\FileTreeBuilder
{
    /**
     * @var TemplateInterface|null
     */
    private ?TemplateInterface $tplEng = null;

    /**
     * @param string $tplFile
     * @param string $dstFile
     * @param array $tplVars
     *
     * @return $this
     */
    protected function doRender(string $tplFile, string $dstFile, array $tplVars = []): self
    {
        if (!$this->dryRun) {
            if ($this->tplVars) {
                $tplVars = array_merge($this->tplVars, $tplVars);
            }

            $content = $this->getTplEng()->renderFile($tplFile, $tplVars);
            File::putContents($dstFile, $content);
        }

        return $this;
    }

    /**
     * @return TemplateInterface
     */
    public function getTplEng(): TemplateInterface
    {
        if (!$this->tplEng) {
            $this->tplEng = new EasyTemplate(['tplDir' => $this->tplDir]);
        }
        return $this->tplEng;
    }

}
