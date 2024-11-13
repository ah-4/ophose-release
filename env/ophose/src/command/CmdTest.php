<?php

namespace Ophose\Command;

use AH4\Database\Database;
use AutoLoader;
use Ophose\Env;
use Ophose\Response;
use Ophose\Test\Test;

use Ophose\Util\Configuration;
use function AH4\Database\db;
use function Ophose\Util\clr;

class CmdTest extends Command {

    private array $tests_files = [];
    private bool $stop_on_failure = false;

    public function before(){
        $this->tests_files = o_get_files_recursive(ROOT . "tests", "php");
        define("TEST_MODE", true);
    }

    public function run() {

        if($this->hasOption("name")) {
            $name = $this->getOption("name");
            echo "Running tests with name: $name\n";
            $this->tests_files = array_filter($this->tests_files, function($file) use ($name) {
                return strpos($file, $name) !== false;
            });
        }

        if($this->hasOption("stop-on-failure")) {
            echo "Tests will stop on failure.\n";
            $this->stop_on_failure = true;
        }

        $this->runTests();

    }

    private function runTestsFromTest(Test $test) {
        $test->before();
        $failed = 0;
        $tested = 0;
        $methods = get_class_methods($test);
        foreach ($methods as $method) {
            if (strpos($method, "test") === 0) {
                Response::clearLastResponse();
                echo clr("\n    Running test: &cyan;$method\n");
                $tested++;
                $test->beforeEach();
                try {
                    $test->$method();
                    echo clr("        &green;Test passed\n");
                } catch (\Exception $e) {
                    echo clr("        &red;Test failed: " . $e->getMessage() . "\n");
                    // Print file and line before the exception itself
                    echo clr("        &red;File: " . $e->getTrace()[0]["file"] . ":" . $e->getTrace()[0]["line"] . "\n");
                    echo clr("        &yellow;Trace: " . $e->getTraceAsString() . "\n");
                    $failed++;
                    if($this->stop_on_failure) {
                        echo clr("        &yellow;Tests on this file are now stopped because the previous test failed.\n");
                        $test->after();
                        return [
                            "failed" => $failed,
                            "tested" => $tested
                        ];
                    }
                } finally {
                    $test->afterEach();
                }
            }
        }
        $test->after();
        return [
            "failed" => $failed,
            "tested" => $tested
        ];
    }

    private function setTestDatabase() {
        echo clr("&yellow;Setting up test database...\n");
        $database_name = (new Configuration(ROOT .'tests/config'))->get("database.name");
        echo clr("Database name: &cyan;$database_name\n");
        $db = db($database_name);
        // Drop all tables
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $db->query("SHOW TABLES")->fetchAll();
        foreach($tables as $table) {
            $table_name = $table[0];
            $db->exec("DROP TABLE $table_name");
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        Database::createDatabaseIfNotExists();

        if(AutoLoader::getEnvironmentPath('AH4/Storage') !== false) {
            echo clr("&yellow;Setting up test database for Storage...\n");
            $env = Env::getEnvironment(ROOT . 'env/ext/AH4/Storage');
            $env->onInstall();
            echo clr("&green;Storage schema transferred.\n");
        }

        if(AutoLoader::getEnvironmentPath('AH4/Auth') !== false) {
            echo clr("&yellow;Setting up test database for Auth...\n");
            $env = Env::getEnvironment(ROOT . 'env/ext/AH4/Auth');
            $env->onInstall();
            echo clr("&green;Auth schema transferred.\n");
        }

        if(AutoLoader::getEnvironmentPath('AH4/Model') !== false) {
            echo clr("&yellow;Transferring schema...\n");
            \AH4\Model\Schema::get()->transfer();
            echo clr("&green;Schema transferred.\n");
        }
        echo clr("&green;Test database set up.\n");
    }

    private function runTests() {
        $failed = 0;
        $tested = 0;
        $total_files = count($this->tests_files);
        if($total_files == 0) {
            echo "No tests found.\n";
            return;
        }
        $this->setTestDatabase();
        echo clr("Tests are now &green;running&reset;...\n");
        echo "There is a total of $total_files test files.\n";
        foreach($this->tests_files as $test_file) {
            $test_name = basename($test_file, ".php");
            echo clr("\nRunning test file: &cyan;$test_name\n");
            $test_class = include_once($test_file);
            $test = new $test_class();
            $result = $this->runTestsFromTest($test);
            $result_failed = $result["failed"];
            $result_tested = $result["tested"];
            $failed += $result_failed;
            $tested += $result_tested;
            echo clr("\nTests finished for &cyan;$test_name&reset;: &green;$result_tested tests&reset;, " . ($result_failed > 0 ? "&red;" : "") . "$result_failed failed\n\n");
        }
        echo "Tests complete on $total_files files: $tested tests, $failed failed\n";
        $succeeded = $tested - $failed;
        $this->printProgress($succeeded, $total_files);
        echo " " . round($succeeded / $tested * 100) . "% tests passed.\n";
    }

    private function printProgress($current, $total, $bars_count = 30) {
        $progress = $current / $total;
        $bars = round($progress * $bars_count);
        $spaces = $bars_count - $bars;
        echo "[";
        for($i = 0; $i < $bars; $i++) {
            echo "=";
        }
        for($i = 0; $i < $spaces; $i++) {
            echo " ";
        }
        echo "]";
    }

}