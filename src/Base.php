<?php declare(strict_types=1);

namespace Funsoul\Funtask;

use Funsoul\Funtask\Exception\PhpVersionException;
use Funsoul\Funtask\Exception\SwooleNotFoundException;
use Swoole\Process;

class Base
{
    /**
     * @throws \Exception
     */
    protected function beforeStart()
    {
        $this->checkPhpVersion();

        $this->checkExtensionLoaded();
    }

    /**
     * check master process killed or not
     *
     * @param $masterPid
     *
     * @return bool
     */
    protected function isMasterKilled($masterPid)
    {
        return Process::kill($masterPid, 0) ? false : true;
    }

    /**
     * @throws SwooleNotFoundException
     */
    protected function checkExtensionLoaded()
    {
        if (! extension_loaded('swoole')) {
            throw new SwooleNotFoundException('swoole not found');
        }
    }

    /**
     * @throws PhpVersionException
     */
    protected function checkPhpVersion()
    {
        if (version_compare(phpversion(), '7.1.0', '<')) {
            throw new PhpVersionException('php version must be 7.1+');
        }
    }

    /**
     * @param $name
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function setProcessName($name)
    {
        if (PHP_OS == 'Darwin') {
            return false;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                throw new \Exception('set process name error');
            }
        }
    }

    protected function registerSignal()
    {
        Process::signal(SIGCHLD, function() {
            while($ret = Process::wait(false)) {

            }
        });
    }
}