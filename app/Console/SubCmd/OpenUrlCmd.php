<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use function str_starts_with;

/**
 * Class ProjectInit
 */
class OpenUrlCmd extends Command
{
    protected static string $name = 'url';
    protected static string $desc = 'open input url on browser';

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
        $this->flags->addArg('url', 'want opened URL address', 'string', true);
    }

    /**
     * @param Input  $input
     * @param Output $output
     *
     * @return int|mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $pageUrl = $this->flags->getArg('url');
        if (!str_starts_with($pageUrl, 'http')) {
            $pageUrl = 'https://' . $pageUrl;
        }

        $output->info("will open URL: $pageUrl");
        AppHelper::openBrowser($pageUrl);

        return 0;
    }
}
