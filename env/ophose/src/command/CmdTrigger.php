<?php

namespace Ophose\Command;

use Ophose\Command\Command;
use Ophose\Env;
use AutoLoader;

/**
 * Class CmdTrigger
 * Handles triggering specific actions for a given environment.
 * 
 * This command provides functionality to trigger predefined tasks, 
 * such as installing an environment, based on the triggers defined in the class.
 */
class CmdTrigger extends Command
{
    /**
     * Defines the available triggers and their configurations.
     * 
     * @return array The array of triggers, each with a message, requirement for the environment, and a callback function.
     */
    protected function triggers(): array
    {
        return [
            "install" => [
                "requireEnv" => true,
                "message" => "Installing the environment.",
                "callback" => function (Env $env) {
                    $env->onInstall();
                }
            ]
        ];
    }

    /**
     * Main execution method to run the appropriate trigger for the specified environment.
     * 
     * This method validates the input arguments, checks for the existence of the environment,
     * and invokes the respective trigger action. If required, the environment instance is passed
     * to the trigger's callback.
     * 
     * @return void
     */
    public function run(): void
    {
        // Validate environment name
        $envName = $this->getArguments()[0] ?? null;
        if (!$envName) {
            $this->displayUsageAndExit("Insufficient arguments...");
        }

        // Load the environment path
        $envPath = AutoLoader::getEnvironmentPath($envName);
        if (!$envPath) {
            $this->exitWithError("This environment does not exist.", $envName);
        }

        // Validate trigger name
        $triggerName = strtolower($this->getArguments()[1] ?? null);
        if (!$triggerName) {
            $this->displayUsageAndExit("Insufficient arguments...");
        }

        // Check if the trigger exists
        $triggers = $this->triggers();
        if (!array_key_exists($triggerName, $triggers)) {
            $this->exitWithError(
                "This trigger does not exist.",
                $triggerName,
                "Available triggers: " . implode(", ", array_keys($triggers))
            );
        }

        // Get the trigger configuration
        $trigger = $triggers[$triggerName];

        // Handle environment dependency if required
        $args = [];
        if ($trigger["requireEnv"] ?? false) {
            $env = Env::getEnvironment($envPath);
            if (!$env) {
                $this->exitWithError(
                    "This environment does not have an Environment class.",
                    $envName
                );
            }
            $args[] = $env;
        }

        // Execute the trigger
        echo $trigger["message"] . "\n";
        $trigger["callback"](...$args);
        echo "Done.\n";
    }

    /**
     * Displays usage information and exits the script with an error code.
     * 
     * @param string $message The error message to display.
     * @return void
     */
    private function displayUsageAndExit(string $message): void
    {
        echo $message . "\n";
        echo "Usage: php ocl oph trigger <environment> <trigger>\n";
        exit(1);
    }

    /**
     * Exits the script with an error message and additional information if provided.
     * 
     * @param string $message The main error message.
     * @param string $context The context or additional data related to the error.
     * @param string|null $additionalInfo Optional additional info to display.
     * @return void
     */
    private function exitWithError(string $message, string $context, ?string $additionalInfo = null): void
    {
        echo $message . "\n";
        echo "Context: " . $context . "\n";
        if ($additionalInfo) {
            echo $additionalInfo . "\n";
        }
        exit(1);
    }
}
