<?php

namespace Ophose;

use Ophose\Util\App;

class Template {

    /**
     * Return the path to the template file with the specified path (relative to the app/templates folder)
     *
     * @param string $template The path to the template file (relative to the app/templates folder)
     * @return string The path to the template file
     */
    public static function getTemplatePath(string $template) {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        return App::getAppPath("templates/$template.php");
    }

    /**
     * Check if the specified template file exists
     *
     * @param string $template The path to the template file (relative to the app/templates folder)
     * @return bool Whether the template file exists
     */
    public static function templateExists(string $template) {
        return file_exists(self::getTemplatePath($template));
    }
   
    /**
     * Render the specified template file with the specified data
     *
     * @param string $template The path to the template file (relative to the app/templates folder)
     * @param array $data The data to be used in the template
     * @return string|bool The rendered template or false if the template file doesn't exist
     */
    public static function render(string $template, array $data = []) {
        if(!self::templateExists($template)) {
            echo "The template file doesn't exist\n";
            return false;
        }
        $templatePath = self::getTemplatePath($template);
        ob_start();
        extract($data);
        extract(['__template' => $template, '__template_data' => $data, '__private_data' => [
            'section' => []
        ]]);
        include $templatePath;
        if(isset($GLOBALS['__private_data']['from'])) {
            $from = $GLOBALS['__private_data']['from'];
            unset($GLOBALS['__private_data']['from']);
            include $from;
        }
        return ob_get_clean();
    }

    // IN THE TEMPLATE FILE

    /**
     * Define a section in the template file. When a section is defined, it will be replaced by
     * the child template section definition when the child template extends the parent template
     *
     * @param string $section The name of the section
     * @return void
     */
    public static function section(string $section) {
        if(isset($GLOBALS['__private_data']['section'][$section])) {
            echo $GLOBALS['__private_data']['section'][$section];
        }
    }

    // IN THE CHILD TEMPLATE FILE

    /**
     * Extend the specified template file
     *
     * @param string $template The path to the template file (relative to the app/templates folder)
     * @return void
     */
    public static function extends(string $template) {
        $templatePath = self::getTemplatePath($template);
        if(self::templateExists($template)) {
            $GLOBALS['__private_data']['from'] = $templatePath;
        }
    }

    /**
     * Start a section in the template file
     *
     * @param string $section The name of the section
     * @return void
     */
    public static function inSection(string $section) {
        $GLOBALS['__private_data']['section'][$section] = '';
        $GLOBALS['__private_data']['in_section'] = $section;
        ob_start();
    }

    /**
     * End a section in the template file
     *
     * @param string $section The name of the section
     * @return void
     */
    public static function endSection(string $section) {
        if($GLOBALS['__private_data']['in_section'] !== $section) {
            echo "The section '$section' is not open\n";
            return;
        }
        $GLOBALS['__private_data']['in_section'] = null;
        $GLOBALS['__private_data']['section'][$section] = ob_get_clean();
    }    

}