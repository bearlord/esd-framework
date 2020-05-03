<?php

namespace ESD\Yii\Gii\Console;

use ESD\Yii\Console\Controller;
use ESD\Yii\Gii\Generator;
use ESD\Yii\Helpers\Console;
use ESD\Yii\Yii;

class GenerateController extends Controller
{
    /**
     * @var boolean whether to overwrite all existing code files when in non-interactive mode.
     * Defaults to false, meaning none of the existing code files will be overwritten.
     * This option is used only when `--interactive=0`.
     */
    public $overwrite = false;

    /**
     * @var \ESD\Yii\Gii\Generator | \ESD\Yii\Gii\Generators\Model\Generator
     */
    public $generator;

    public function runAction($type, $options)
    {
        $generator = $this->getGenerator($type);
        /** @var \ESD\Yii\Gii\Generator | \ESD\Yii\Gii\Generators\Model\Generator $generatorObject */
        $this->generator = Yii::createObject(array_merge($generator, $options));

        $this->generateCode();
    }

    public function generateCode()
    {
        $files = $this->generator->generate();
        $n = count($files);
        if ($n === 0) {
            echo "No code to be generated.\n";
            return;
        }
        echo "The following files will be generated:\n";
        $skipAll = $this->interactive ? null : !$this->overwrite;
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if (is_file($file->path)) {
                if (file_get_contents($file->path) === $file->content) {
                    echo '  ' . $this->ansiFormat('[unchanged]', Console::FG_GREY);
                    echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                    $answers[$file->id] = false;
                } else {
                    echo '    ' . $this->ansiFormat('[changed]', Console::FG_RED);
                    echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                    if ($skipAll !== null) {
                        $answers[$file->id] = !$skipAll;
                    } else {
                        $answer = $this->select("Do you want to overwrite this file?", [
                            'y' => 'Overwrite this file.',
                            'n' => 'Skip this file.',
                            'ya' => 'Overwrite this and the rest of the changed files.',
                            'na' => 'Skip this and the rest of the changed files.',
                        ]);
                        $answers[$file->id] = $answer === 'y' || $answer === 'ya';
                        if ($answer === 'ya') {
                            $skipAll = false;
                        } elseif ($answer === 'na') {
                            $skipAll = true;
                        }
                    }
                }
            } else {
                echo '        ' . $this->ansiFormat('[new]', Console::FG_GREEN);
                echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                $answers[$file->id] = true;
            }
        }

        if (!array_sum($answers)) {
            $this->stdout("\nNo files were chosen to be generated.\n", Console::FG_CYAN);
            return;
        }

        if (!$this->confirm("\nReady to generate the selected files?", true)) {
            $this->stdout("\nNo file was generated.\n", Console::FG_CYAN);
            return;
        }

        if ($this->generator->save($files, (array) $answers, $results)) {
            $this->stdout("\nFiles were generated successfully!\n", Console::FG_GREEN);
        } else {
            $this->stdout("\nSome errors occurred while generating the files.", Console::FG_RED);
        }
        echo preg_replace('%<span class="error">(.*?)</span>%', '\1', $results) . "\n";
    }

    /**
     * Return the special code generator
     *
     * @param $type
     * @return mixed
     */
    public function getGenerator($type)
    {
        $coreGenerators = $this->coreGenerators();
        if (!empty($coreGenerators[$type])) {
            return $coreGenerators[$type];
        }
    }

    /**
     * Returns the list of the core code generator configurations.
     * @return array the list of the core code generator configurations.
     */
    protected function coreGenerators()
    {
        return [
            'model' => ['class' => 'ESD\Yii\Gii\Generators\Model\Generator'],
        ];
    }
}