<?php

namespace Ophose;

class Command {

    /**
     * @var array[string] the arguments
     */
    private $arguments;

    public function __construct(array $arguments = null){
        $this->arguments = $arguments;
    }

    /**
     * Returns the arguments.
     *
     * @return array the arguments
     */
    public function getArguments() : array{
        return array_slice($this->arguments, 3);
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
        if($value->startsWith('-') && !is_numeric($value)) return null;
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
            if($argument->startsWith('--')){
                $options[] = $argument->substring(2);
            } else if($argument->startsWith('-') && !is_numeric($argument)){
                $options[] = $argument->substring(1);
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
}