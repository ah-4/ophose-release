<?php

namespace Ophose\Command;

class Command {

    /**
     * @var array[string] the arguments
     */
    private $arguments = [];

    public function __construct(array $arguments = []){
        $this->arguments = $arguments;
        $this->before();
    }

    /**
     * Run the command before the command is executed.
     */
    public function before() {
        // Override this method to run code before the command is executed
    }

    /**
     * Returns the arguments.
     *
     * @return array the arguments
     */
    public function getArguments() : array{
        return array_slice($this->arguments, 3);
    }

    public function getAllArguments() : array {
        return $this->arguments;
    }

    /**
     * Returns the command name.
     *
     * @return string the command name
     */
    public function getCommandName() : string{
        return $this->arguments[2];
    }

    /**
     * Returns true if the command has the given option (an argument preceded by '--' or '-').
     *
     * @param string $optionName the option name
     * @return bool true if the command has the given option
     */
    public function hasOption(string $optionName) : bool {
        return in_array('--' . $optionName, $this->arguments) || in_array('-' . $optionName, $this->arguments);
    }

    /**
     * Returns the option value or false if not found (or null if option exists
     * but has no value).
     *
     * @param string $optionName
     * @return string
     */
    public function getOption(string $optionName) : string{
        $optionIndex = array_search('--' . $optionName, $this->arguments);
        if($optionIndex === false){
            $optionIndex = array_search('-' . $optionName, $this->arguments);
            if($optionIndex === false) return false;
        }

        if($optionIndex + 1 >= count($this->arguments)) return null;
        $value = $this->arguments[$optionIndex + 1];
        if(str_starts_with($value, '-') && !is_numeric($value)) return null;
        return $value;
    }

    /**
     * Returns the options.
     *
     * @return array the options
     */
    public function getOptions() : array{
        $options = [];
        foreach($this->arguments as $argument){
            if(str_starts_with($argument, '--')){
                $options[] = substr($argument, 2);
            } else if(str_starts_with($argument, '-') && !is_numeric($argument)){
                $options[] = substr($argument, 1);
            }
        }
        return $options;
    }

    /**
     * Returns true if the command has the given argument.
     *
     * @param string $argumentName the argument name
     * @param int $argsOffset the arguments offset (default: 3 because the first 3 arguments are the environment, the command and the command name)
     * @return bool true if the command has the given argument
     */
    function hasArgument(string $argumentName, int $argsOffset = 3) : bool {
        return in_array($argumentName, array_slice($this->arguments, $argsOffset));
    }

    /**
     * Run the command.
     */
    public function run() {
        echo "Command not implemented.\n";
    }
    
}