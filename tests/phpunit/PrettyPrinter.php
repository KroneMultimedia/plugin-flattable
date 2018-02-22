<?php
namespace Test;

class PrettyPrinter extends \PHPUnit\TextUI\ResultPrinter implements \PHPUnit\Framework\TestListener
{
    private $headerPrinted = false;
    /**
     * Function name is slightly deceiving because it is called at the beginning of the
     * unit test summary (after all tests have run)
     */
    protected function printHeader()
    {
        parent::printHeader();
    }

    protected function formatExceptionMsg($exceptionMessage)
    {
        $exceptionMessage = str_replace("+++ Actual\n", '', $exceptionMessage);
        $exceptionMessage = str_replace("--- Expected\n", '', $exceptionMessage);
        $exceptionMessage = str_replace("@@ @@\n", '', $exceptionMessage);
        $exceptionMessage = preg_replace("/(Failed.*)$/m", " \033[01;31m$1\033[0m", $exceptionMessage);
        $exceptionMessage = preg_replace("/\-+(.*)$/m", "\n \033[01;32m$1\033[0m", $exceptionMessage);
        return preg_replace("/\++(.*)$/m", " \033[01;31m$1\033[0m", $exceptionMessage);
    }

    protected function printDefectTrace(\PHPUnit\Framework\TestFailure $defect)
    {
        $this->write($this->formatExceptionMsg($defect->getExceptionAsString()));
        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace(
          $defect->thrownException()
        );
        if (!empty($trace)) {
            $this->write("\n" . $trace);
        }
        $e = $defect->thrownException()->getPrevious();
        while ($e) {
            $this->write(
            "\nCaused by\n" .
            \PHPUnit\Framework_TestFailure::exceptionToString($e). "\n" .
            \PHPUnit\Util_Filter::getFilteredStacktrace($e)
          );
            $e = $e->getPrevious();
        }
    }
    /**
     * Output to the console
     * @param string $message to print
     * @param string $color optional color (if supported by console)
     */
    private function out($message, $color='', $linebreak=false)
    {
        echo($color ? $this->formatWithColor($color, $message) : $message) . ($linebreak ? "\n" : '');
    }
    /**
     * Fired prior to each individual test
     */
    public function startTest(\PHPUnit\Framework\Test $test)
    {
        $this->out(">> RUN '".preg_replace("/^test/", "",$test->getName())."'...");
    }
    /**
     * Fired after the competion of each individual test
     * @param PHPUnit\Framework\TestCase
     * @param int time of execution
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        // copied from parent:endTest()
        if ($test instanceof \PHPUnit\Framework\TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        } elseif ($test instanceof \PHPUnit\Extensions_PhptTestCase) {
            $this->numAssertions++;
        }
        $this->lastTestFailed = false;
        // custom printing code
        if (get_class($test) == 'PHPUnit\Framework\TestSuite') {
            // this occurs when the test suite setup has thrown an error
            $this->out(" SETUP FAIL", 'fg-red', true);
        } elseif ($test->hasFailed()) {
            $this->out(" FAIL", 'fg-red', true);
        } else {
            $numAssertions = ($test instanceof \PHPUnit\Framework\TestCase) ? $test->getNumAssertions() : 1;
            if ($numAssertions > 0) {
                $this->out(' OK (' . $numAssertions . ' assertions)', 'fg-green', true);
            } else {
                $this->out(' SKIPPED (0 assertions)', 'fg-yellow', true);
            }
        }
    }
    /**
     * called at the initialization of each test suite
     */
    public function prettySuiteName($s) {
      return preg_replace("/^Tests\\\/", "", $s);
    }
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        parent::startTestSuite($suite);
        if (!$this->headerPrinted) {
            $header = "██████╗ ██╗  ██╗██████╗ ██╗   ██╗███╗   ██╗██╗████████╗
██╔══██╗██║  ██║██╔══██╗██║   ██║████╗  ██║██║╚══██╔══╝
██████╔╝███████║██████╔╝██║   ██║██╔██╗ ██║██║   ██║
██╔═══╝ ██╔══██║██╔═══╝ ██║   ██║██║╚██╗██║██║   ██║
██║     ██║  ██║██║     ╚██████╔╝██║ ╚████║██║   ██║
╚═╝     ╚═╝  ╚═╝╚═╝      ╚═════╝ ╚═╝  ╚═══╝╚═╝   ╚═╝  ";
            $this->out($header, 'fg-blue', true);
            $this->out(" - - - - T E S T   A L L   T H E   T H I N G S - - - - ", 'fg-blue', true);
            $this->out('', '', true);
            $this->headerPrinted = true;
        }
        if ($suite->getName() != 'PHPUnit') {
            $this->out("BEGIN SUITE '".$this->prettySuiteName($suite->getName())."'\n");
        }
    }
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        if ($suite->getName() != 'PHPUnit') {
            $this->out("END SUITE '".$this->prettySuiteName($suite->getName())."'\n\n");
        }
    }
    /**
     * Overriding this method suppresses all of the various dots
     * result codes that PHPUnit sends to the console
     * @param string $progress
     */
    protected function writeProgress($progress)
    {
        // suppress output;
    }
}