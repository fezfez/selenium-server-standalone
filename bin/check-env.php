<?php

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;

require 'vendor/autoload.php';

$output = new ConsoleOutput();

class FoundDriver
{
    /**
     * @var ConsoleOutput
     */
    private $output;
    private static $path;
    /*
    private $webdrivers = [
        'firefox' => ['geckodriver', 'geckodriver.exe'],
        'ie' => ['IEDriverServer.exe'],
        'edge' => ['MicrosoftWebDriver.exe'],
        'chrome' => ['chromedriver.exe', 'chromedriver'],
        'opera' => ['operadriver.exe', 'operadriver']
    ];
    */

    private $webdrivers = [
        'firefox' => [
            'WIN64' => [
                'url' => 'https://github.com/mozilla/geckodriver/releases/download/v0.14.0/geckodriver-v0.14.0-win64.zip',
                'file' => 'geckodriver.exe'
            ]
        ]
    ];

    private function getPath()
    {
        if (self::$path === null) {
            self::$path = explode(';', getenv('PATH'));
        }

        return self::$path;
    }

    private function formatSizeUnits($bytes)
    {
        return round($bytes / 1024);
    }

    public function __construct($output)
    {
        $this->output = $output;
    }

    public function getAllDriver()
    {
        return array_keys($this->webdrivers);
    }

    protected function download($url)
    {
        $output = $this->output;
        $progress = new ProgressBar($this->output);
        $ctx = stream_context_create(array(

            'http' => array(
                'proxy' => 'localhost:3128'
            ),

        ), array('notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
            switch ($notification_code) {
                case STREAM_NOTIFY_FILE_SIZE_IS:
                    $progress->start(round($bytes_max / 1024) / 10);
                    break;
                case STREAM_NOTIFY_PROGRESS:
                    $progress-> setProgress(round($bytes_max / 1024) / 10);
                    //$progress->setMessage($this->formatSizeUnits($bytes_max).'MB');
                    break;
            }
        }));
        file_put_contents('geckodriver.exe', file_get_contents($url, false, $ctx));
        $progress->finish();
    }

    public function isFound($driverName)
    {
        foreach (self::getPath() as $path) {
            foreach ($this->webdrivers[$driverName] as $executable) {
                var_dump($executable);
                if (!is_file($path.'/'.$executable['file'])) {
                    $this->download($executable['url']);
                    return true;
                }
            }
        }

        return false;
    }
}

$output->writeLn("Webdriver status:\n");
$output->write("-----------------\n\n");

$driverFinder = new FoundDriver($output);
$hasKo        = false;
$hasOnlyKo    = true;

foreach ($driverFinder->getAllDriver() as $driverName) {
    if ($foundIn = $driverFinder->isFound($driverName)) {
        $hasOnlyKo = false;
        $output->writeLn(sprintf('[OK] %s (found in %s)', $driverName, $foundIn));
    } else {
        $hasKo = true;
        $output->writeLn(sprintf('[KO] %s', $driverName));
    }
}

$output->write("\n");

if ($hasOnlyKo === true) {
    $output->writeLn('No driver found ! Please add some to your $PATH variable environnement');
}

$output->write("Start Selenium:\n");
$output->write("--------------\n\n");
