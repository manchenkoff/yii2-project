<?php
/**
 * Created by Artyom Manchenkov
 * artyom@manchenkoff.me
 * manchenkoff.me © 2019
 */

namespace app\extensions\maker\actions;

use app\extensions\maker\commands\MakeAction;
use yii\helpers\StringHelper;

class MailAction extends MakeAction
{
    /**
     * Generates a new ActiveRecord class
     *
     * @param string $name
     */
    public function run(string $name)
    {
        // base namespace
        $namespace = "app\\mail";
        // get class base name from full path
        $class = stringy(StringHelper::basename($name))->upperCamelize();
        // build file path in lower case and append class base name
        $filename = stringy($name)
            ->replace($class, false)
            ->toLowerCase()
            ->append($class);

        // append namespace parts if exists
        if ($name != $class) {
            $namespace .= stringy($name)
                ->replace($class, false)
                ->trimRight("/")
                ->replace("/", "\\")
                ->prepend("\\")
                ->toLowerCase();
        }

        // check file name suffix
        if (!$class->endsWith("Email")) {
            return $this->error("The file name must contain 'Email' suffix");
        }

        // generate file content
        return $this->process(
            [
                "namespace" => $namespace,
                "class" => $class,
            ],
            [
                "core/mail.md" => "mail/{$filename}.php",
            ]
        );
    }
}