<?php

namespace git_inline_check;

class Checker {

    /*
     * Callbacks to check file validity
     */
    private static $callbacks = ['inline_style', 'script_tags', 'inline_events'];

    /*
     * Checking files passed as script parameters
     */
    public function __construct() {
        $params = $_SERVER['argv'];
        array_shift($params);

        $checked_files = $this->get_files_list($params);
        if (empty($checked_files)) {
            exit(0);
        }

        $error_files = $this->check_files_with_callbacks($checked_files, self::$callbacks);
        if (!empty($error_files)) {
            $this->show_error(array_map(function($v) { return count($v); }, $error_files), 'Error statistics: ');
            $this->show_error($error_files, 'Detailed description: ');

            exit(1);
        }

        exit(0);
    }

    protected function check_files_with_callbacks($files, $callbacks) {
        $return = [];
        foreach ($files as $file) {
            $data = file_get_contents( $file );

            foreach ($callbacks as $cb) {
                $matches = call_user_func([$this, $cb], $data);
                if ($matches) {
                    $return[$cb][(string)$file] = $matches;
                }
            }
        }

        return $return;
    }

    protected function get_files_list($files) {
        $validated_files_list = [];
        foreach ($files as $file) {
            try {
                $validated_files_list[] = new \SplFileObject($file);
            } catch (\RuntimeException $e) {}
        }

        if (empty($validated_files_list)) {
            return $validated_files_list;
        }

        $list = [];
        foreach ($validated_files_list as $file) {
            if ($file->getExtension() == 'php') {
                $list[] = $file->getRealpath();
            }
        }

        return $list;
    }

    protected function show_error($var, $info='') {
        $error = $info . (!empty($var) ? print_r($var, true) : '');
        error_log($error);
    }

    protected function inline_style($content) {
        $num = preg_match_all('/(style=([\'"]).*?\2)/is', $content, $matches);
        if ($num)
            return $matches[1];

        return false;
    }

    protected function script_tags($content) {
        $num = preg_match_all('/(<script[^>]*>.*<\/script>)/imxsU', $content, $matches);
        if ($num)
            return $matches[1];

        return false;
    }

    protected function inline_events($content) {
        $num1 = preg_match_all('/(onclick=([\'"]).*?\2)/is', $content, $matches1);
        $num2 = preg_match_all('/(onmouse[^=]*=([\'"]).*?\2)/is', $content, $matches2);
        if ($num1 + $num2)
            return array_merge($matches1[1], $matches2[1]);

        return false;
    }

}

new Checker;