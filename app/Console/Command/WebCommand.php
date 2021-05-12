<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\PhpDevServe;
use Throwable;

/**
 * Class WebCommand
 */
class WebCommand extends Command
{
    protected static $name = 'webui';

    protected static $description = 'start the kite web UI server';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['web', 'web-ui', 'webUi'];
    }

    public const DEF_SERVE_CONF = [
        // document root
        // 'root'     => 'public',
        'root'     => '',
        // 'entry'     => 'public/index.php',
        'entry'    => '',
        // 'php-bin'  => 'php'
        'php-bin'  => '',
        'addr' => '127.0.0.1:8552',
    ];

    /**
     * @param Output $output
     *
     * @return string[]
     */
    private function getConfig(Output $output): array
    {
        $conf = $this->app->getParam('webui', []);
        if ($conf) {
            $conf = array_merge(self::DEF_SERVE_CONF, $conf);
            if ($appConf = array_filter($conf)) {
                // print config
                $output->aList($appConf, 'Config Information', [
                    'ucFirst' => false,
                ]);
            }
        } else {
            $conf = self::DEF_SERVE_CONF;
        }

        return $conf;
    }

    /**
     * start the kite web UI server
     *
     * @usage
     *  {binWithCmd} [-S HOST]
     *  {binWithCmd} [-S :PORT]
     *  {binWithCmd} [-S HOST:PORT]
     *
     * @options
     *  -s, -S, --addr STRING    The http server address. e.g 127.0.0.1:8552
     *  -b, --php-bin STRING     The php binary file(<comment>php</comment>)
     *      --show-info          Only show serve info, not start listen
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Throwable
     * @example
     *  {binWithCmd} -s 127.0.0.1:8552
     */
    protected function execute($input, $output)
    {
        $conf = $this->getConfig($output);

        $svrAddr = $input->getSameStringOpt('s,S,addr', $conf['addr']);
        $phpBin  = $input->getStringOpt('php-bin', $conf['php-bin']);
        // $docRoot = $input->getSameStringOpt('t,doc-root', $conf['root']);

        // $pds = PhpDevServe::new($svrAddr, 'public', 'public/index.php');
        $pds = PhpDevServe::new($svrAddr, 'public');
        $pds->setPhpBin($phpBin);

        if ($input->getBoolOpt('show-info')) {
            $output->aList($pds->getInfo(), 'Listen Information', ['ucFirst' => false]);
            return;
        }

        $pds->listen();
    }
}
